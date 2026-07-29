<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$read = static function (string $path) use ($root): string {
    $contents = file_get_contents($root.'/'.$path);
    if ($contents === false) {
        throw new RuntimeException("Unable to read {$path}");
    }
    return $contents;
};

$controller = $read('app/Http/Controllers/ChatController.php');
$routes = $read('routes/web.php');
$model = $read('app/Models/Conversation.php');
$ai = $read('app/Services/AIAssistantService.php');

$freshConversations = $read('database/migrations/2025_11_24_185342_create_conversations_table.php');
$freshMessages = $read('database/migrations/2025_11_24_185343_create_messages_table.php');
$upgradeMigration = $read('database/migrations/2026_07_27_000300_harden_conversation_tenancy.php');

$assertContains = static function (string $needle, string $haystack, string $message) use (&$errors): void {
    if (! str_contains($haystack, $needle)) {
        $errors[] = $message;
    }
};
$assertNotContains = static function (string $needle, string $haystack, string $message) use (&$errors): void {
    if (str_contains($haystack, $needle)) {
        $errors[] = $message;
    }
};

$assertContains("'company_id'", $model, 'Conversation model must be company-scoped.');
$assertContains("'created_by_user_id'", $model, 'Conversation model must record its creator/owner.');
$assertContains('attachmentDownload', $routes, 'Chat must expose an authorised attachment download route.');
$assertContains("'chat-attachments/'", $controller, 'Attachments must use the private chat-attachments path.');
$assertNotContains("storeAs('attachments', \$fileName, 'public')", $controller, 'Attachments must not be stored on the public disk.');
$assertContains("mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $controller, 'Attachment MIME allowlist is missing.');
$assertContains('assertConversationParticipant', $controller, 'Conversation participant checks must be centralised and reused.');
$assertContains('assertConversationOwner', $controller, 'Group administration must require the conversation owner.');
$assertContains('eligibleCompanyUserIds', $controller, 'Conversation participants and discovery must be limited to active company members.');
$assertContains("where('company_id', \$companyId)", $controller, 'Conversation queries must be company-scoped.');
$assertContains("where('type', 'ai_assistant')", $controller, 'AI conversation type check must remain.');
$assertContains('The requested AI conversation is not available.', $controller, 'AI conversation ID must be authorised and type-checked.');
$assertContains("whereHas('participants'", $controller, 'Conversation access must continue to require participation.');
$assertContains("has('participants', '=', 2)", $controller, 'P2P reuse must require exactly two participants.');
$assertContains('External AI provider request failed.', $ai, 'Provider errors must be sanitised before reaching users.');
$assertNotContains("'error' => 'OpenAI API error: ' . \$response->body()", $ai, 'OpenAI raw response body must not be returned.');
$assertNotContains("'error' => 'OpenRouter API error: ' . \$response->body()", $ai, 'OpenRouter raw response body must not be returned.');
$assertNotContains("'error' => 'Gemini API error: ' . \$response->body()", $ai, 'Gemini raw response body must not be returned.');
$assertNotContains("'error' => 'Failed to get AI response: ' . \$e->getMessage()", $ai, 'Exception details must not be exposed to users.');


$assertContains("string('type', 32)", $freshConversations, 'Fresh conversation schema must use an extensible conversation type column.');
$assertContains("foreignId('user_id')->nullable()", $freshMessages, 'Fresh message schema must permit AI messages without a human sender.');
$assertContains("->string('type', 32)", $upgradeMigration, 'Upgrade migration must repair the conversation type constraint.');
$assertContains("unsignedBigInteger('user_id')->nullable()->change()", $upgradeMigration, 'Upgrade migration must make message user_id nullable.');


$assertContains("COUNT(DISTINCT user_id)", $upgradeMigration, 'Legacy conversations must be assigned only to a company shared by every participant.');
$assertContains("company_memberships", $upgradeMigration, 'Legacy conversation tenancy backfill must use verified active memberships, not only a mutable active-company pointer.');

// Inline JavaScript handlers must never interpolate user-controlled attachment names/URLs.
$chatJs = $read('public/js/chat-app.js');
$assertNotContains('onclick="showImagePreview(\'', $chatJs, 'Attachment previews must use bound event handlers, not inline JavaScript with user-controlled data.');
$assertNotContains('alt="${user.name}"', $chatJs, 'User names must be escaped before insertion into HTML attributes.');
$assertNotContains('data-name="${user.name}"', $chatJs, 'User names must be escaped before insertion into data attributes.');


$assertNotContains("'avatar' => ['nullable', 'string'", $controller, 'Conversation creation must not accept an arbitrary avatar path or external URL.');
$assertContains('\'attachment_path\' => $message->attachment_path ? true : null', $controller, 'Chat APIs must not expose private attachment filesystem paths.');
$assertContains('Storage::disk(\'local\')->put($message->attachment_path', $controller, 'Authenticated access to a legacy public attachment must migrate it into private storage.');
$assertContains('Storage::disk(\'public\')->delete($message->attachment_path)', $controller, 'Migrated legacy public attachments must be removed from public storage.');
if (substr_count($controller, "return response()->json(['message' => 'Conversation deleted successfully']);") !== 1) {
    $errors[] = 'Conversation destroy response must not contain duplicated unreachable code.';
}
$assertNotContains("const avatar = conversation.avatar ||", $chatJs, 'Stored group avatar paths must be converted to public storage URLs consistently.');


if (! is_file($root.'/app/Console/Commands/MigrateChatAttachmentsCommand.php')) {
    $errors[] = 'A deployment command must migrate all legacy public chat attachments into private storage.';
} else {
    $migrationCommand = $read('app/Console/Commands/MigrateChatAttachmentsCommand.php');
    $assertContains("Storage::disk('public')->delete", $migrationCommand, 'Legacy chat attachment migration must remove public copies after private storage succeeds.');
    $assertContains("{--commit", $migrationCommand, 'Legacy attachment migration must default to a safe dry run.');
}

if ($errors !== []) {
    fwrite(STDERR, "RED: chat security regressions detected:\n - ".implode("\n - ", $errors)."\n");
    exit(1);
}

echo "GREEN: chat security and tenancy regressions pass.\n";
