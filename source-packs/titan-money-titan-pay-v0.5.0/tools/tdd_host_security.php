<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$read = static fn (string $path): string => (string) file_get_contents($root.'/'.$path);
$contains = static function (string $needle, string $haystack, string $message) use (&$errors): void {
    if (! str_contains($haystack, $needle)) $errors[] = $message;
};
$notContains = static function (string $needle, string $haystack, string $message) use (&$errors): void {
    if (str_contains($haystack, $needle)) $errors[] = $message;
};

$bootstrap = $read('bootstrap/app.php');
$routes = $read('routes/web.php');
$settings = $read('app/Http/Controllers/SettingController.php');
$helpers = $read('app/Helpers/helpers.php');
$settingsView = $read('resources/views/system_settings.blade.php');
$users = $read('app/Http/Controllers/UserManagementController.php');
$auth = $read('app/Http/Controllers/AuthController.php');
$profile = $read('app/Http/Controllers/SettingsController.php');
$channels = $read('routes/channels.php');
$chatJs = $read('public/js/chat-app.js');
$settingsSchema = $read('database/migrations/2025_11_24_083018_create_settings_table.php');
$settingsMigration = $read('database/migrations/2026_07_27_000400_harden_settings_names.php');

$contains('ResolveCompanyContext::class, UpdateUserLastSeen::class', $bootstrap, 'Web requests must resolve company context globally before chat/controllers run.');
$contains("company.permission:company.members.manage", $routes, 'User management must use company-scoped permissions.');
$notContains("User::latest()->paginate", $users, 'User index must not expose all platform users.');
$contains("whereHas('companyMemberships'", $users, 'User management must query current-company memberships.');
$contains('CompanyMembership::query()->create', $users, 'New managed users must receive a company membership.');
$contains("->text('value')", $settingsSchema, 'Fresh settings storage must be large enough for encrypted secrets.');
$contains("->text('value')->nullable()->change()", $settingsMigration, 'Upgrade migration must widen settings storage before encrypting secrets.');
$contains('Crypt::encryptString', $settings, 'AI API keys must be encrypted before persistence.');
$contains("'encrypted:'", $helpers, 'Secret settings must be decrypted through an explicit encrypted prefix.');
$notContains("value=\"{{ setting('ai_api_key') }}\"", $settingsView, 'Stored AI API keys must never be rendered back into HTML.');
$contains("Password::min(12)", $auth, 'Registration must enforce a strong password rule.');
$contains("Password::min(12)", $profile, 'Password changes must enforce a strong password rule.');
$contains("online.{companyId}", $channels, 'Presence broadcasting must be company-scoped.');
$contains('join(`online.${ChatApp.currentUser.active_company_id}`)', $chatJs, 'Frontend presence subscription must be company-scoped.');


$adminMiddleware = $read('app/Http/Middleware/IsAdmin.php');
$userModel = $read('app/Models/User.php');
$contains('is_platform_admin', $adminMiddleware, 'Platform administration must use an explicit immutable privilege flag, not an email comparison.');
$notContains("email !== config('app.admin_email')", $adminMiddleware, 'Platform admin middleware must not grant authority by mutable email address.');
$contains("'is_platform_admin' => 'boolean'", $userModel, 'Platform admin state must be cast explicitly.');
$notContains("Auth::user()->email === config('app.admin_email')", $auth, 'Login redirects must not infer platform authority from email address.');
$contains('ADMIN_EMAIL=', $read('.env.example'), 'The legacy admin email bootstrap setting must be explicit in the environment template.');

if ($errors !== []) {
    fwrite(STDERR, "RED: host security regressions detected:\n - ".implode("\n - ", $errors)."\n");
    exit(1);
}

echo "GREEN: host authentication, settings, membership and broadcasting regressions pass.\n";
