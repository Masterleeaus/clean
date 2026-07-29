<?php

use App\Domains\Engine\EngineServiceProvider;
use App\Domains\Entity\EntityServiceProvider;
use App\Domains\Marketplace\MarketplaceServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AwsServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\MacrosServiceProvider;
use App\Providers\RouteServiceProvider;
use Barryvdh\DomPDF\ServiceProvider;
use Elseyyid\LaravelJsonLocationsManager\Providers\LaravelJsonLocationsManagerServiceProvider;
use Igaster\LaravelTheme\Facades\Theme;
use Igaster\LaravelTheme\themeServiceProvider;
use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Pipeline\PipelineServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    'menu_cache_by_user_id' => env('MENU_CACHE_BY_USER_ID', false),
    'status' => env('APP_STATUS', null),
    'show_load_time' => env('SHOW_LOAD_TIME', false),
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug_hash' => '$2a$12$tg2XA6fFcLeOZH.cHM2cSuAp7227WqHCN7vwcTj4HzpeUOghBgb2W',
    'system_slot_token' => '$2a$12$mHwWJKf24dwmQ.B3SEO24OShUkQihCc.gLuCC8KMoLOjtHJWIews.',
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL', '/'),
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'maintenance' => ['driver' => 'file'],
    'providers' => [
        AuthServiceProvider::class,
        BroadcastServiceProvider::class,
        BusServiceProvider::class,
        CacheServiceProvider::class,
        ConsoleSupportServiceProvider::class,
        CookieServiceProvider::class,
        DatabaseServiceProvider::class,
        EncryptionServiceProvider::class,
        FilesystemServiceProvider::class,
        FoundationServiceProvider::class,
        HashServiceProvider::class,
        MailServiceProvider::class,
        NotificationServiceProvider::class,
        PaginationServiceProvider::class,
        PipelineServiceProvider::class,
        QueueServiceProvider::class,
        RedisServiceProvider::class,
        PasswordResetServiceProvider::class,
        SessionServiceProvider::class,
        TranslationServiceProvider::class,
        ValidationServiceProvider::class,
        ViewServiceProvider::class,
        PermissionServiceProvider::class,
        AppServiceProvider::class,
        App\Domains\WorkCore\WorkCoreServiceProvider::class,
        App\Domains\TitanTrain\Infrastructure\Providers\TitanTrainServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        EventServiceProvider::class,
        RouteServiceProvider::class,
        App\Providers\ViewServiceProvider::class,
        MacrosServiceProvider::class,
        AwsServiceProvider::class,
        EntityServiceProvider::class,
        EngineServiceProvider::class,
        MarketplaceServiceProvider::class,
        LaravelJsonLocationsManagerServiceProvider::class,
        ServiceProvider::class,
        themeServiceProvider::class,
    ],
    'aliases' => Facade::defaultAliases()->merge([
        'PDF' => Barryvdh\DomPDF\Facade::class,
        'Theme' => Theme::class,
    ])->toArray(),
];
