<?php

declare(strict_types=1);

namespace Tests\Feature\TitanTrain;

use Tests\TestCase;

final class ChatbotPwaNativeContractTest extends TestCase
{
    public function test_titan_train_is_a_native_online_chatbot_app(): void
    {
        $root = base_path('app/Extensions/Chatbot');
        $schema = json_decode((string) file_get_contents($root.'/resources/titan-apps/TemplateSchemas/titan-train.json'), true, flags: JSON_THROW_ON_ERROR);
        $config = json_decode((string) file_get_contents($root.'/resources/titan-apps/TitanSuiteTemplates/titan-train/config.json'), true, flags: JSON_THROW_ON_ERROR);
        $workspace = (string) file_get_contents($root.'/resources/assets/js/titan-train-workspace.js');
        $header = (string) file_get_contents($root.'/resources/views/frontend-ui/components/header.blade.php');

        self::assertSame('titan-train', $schema['identity']['slug']);
        self::assertTrue($schema['online_only']);
        self::assertFalse($schema['offline']['enabled']);
        self::assertCount(4, $schema['navigation']['primary']);
        self::assertSame('titan-train', $config['slug']);
        self::assertContains('managed-training-channels', $config['features']);
        self::assertStringContainsString('chatbot:team-channel-open-requested', $workspace);
        self::assertStringNotContainsString('IndexedDB', $workspace);
        self::assertStringContainsString("\$titanShell['online_only']", $header);
        self::assertStringContainsString('titan-train:refresh-requested', $header);
    }
}
