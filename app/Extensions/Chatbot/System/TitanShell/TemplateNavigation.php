<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\TitanShell;

final class TemplateNavigation
{
    private const SHARED_DRAWER = [
        ['id' => 'search', 'label' => 'Search', 'icon' => 'search'],
        ['id' => 'recent', 'label' => 'Recent', 'icon' => 'history'],
        ['id' => 'knowledge', 'label' => 'Knowledge', 'icon' => 'book'],
        ['id' => 'files', 'label' => 'Files', 'icon' => 'files'],
        ['id' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
        ['id' => 'approvals', 'label' => 'Approvals', 'icon' => 'checks'],
        ['id' => 'offline-queue', 'label' => 'Offline queue', 'icon' => 'cloud-off', 'requires_offline' => true],
    ];

    private const SETTINGS = [
        'ai-providers' => 'AI & BYO Providers',
        'privacy' => 'Privacy & Data',
        'device-security' => 'Device & Security',
        'offline-sync' => 'Offline & Sync',
        'workcore' => 'WorkCore Connection',
        'permissions' => 'Permissions',
        'notifications' => 'Notifications',
        'channels' => 'Channels',
        'appearance' => 'Appearance',
        'accessibility' => 'Language & Accessibility',
        'diagnostics' => 'Diagnostics',
    ];

    public static function resolve(?string $slug): array
    {
        $schema = TemplateSchema::resolve($slug);
        $identity = $schema['identity'];
        $navigation = $schema['navigation'];
        $offlineEnabled = (bool) ($schema['offline']['enabled'] ?? ! ($schema['online_only'] ?? false));

        $sharedDrawer = array_values(array_filter(
            self::SHARED_DRAWER,
            static fn (array $item): bool => $offlineEnabled || ! ($item['requires_offline'] ?? false),
        ));
        $sharedDrawer = array_map(static function (array $item): array {
            unset($item['requires_offline']);

            return $item;
        }, $sharedDrawer);

        $settingKeys = is_array($schema['settings_sections'] ?? null)
            ? $schema['settings_sections']
            : array_keys(self::SETTINGS);
        if (! $offlineEnabled) {
            $settingKeys = array_values(array_diff($settingKeys, ['offline-sync']));
        }

        return [
            'slug' => $identity['slug'],
            'name' => $identity['name'],
            'default_view' => $navigation['default_view'],
            'primary' => $navigation['primary'],
            'drawer' => array_merge($navigation['drawer'], $sharedDrawer),
            'schema' => $schema,
            'online_only' => ! $offlineEnabled,
            'settings' => array_intersect_key(self::SETTINGS, array_flip($settingKeys)),
        ];
    }
}
