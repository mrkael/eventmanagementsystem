<?php

use PHPageBuilder\Cache;
use PHPageBuilder\Modules\Auth\Auth;
use PHPageBuilder\Modules\GrapesJS\PageBuilder;
use PHPageBuilder\Modules\Router\DatabasePageRouter;
use PHPageBuilder\Modules\WebsiteManager\WebsiteManager;
use PHPageBuilder\Page;
use PHPageBuilder\PageTranslation;
use PHPageBuilder\Setting;
use PHPageBuilder\Theme;

return [
    'general' => [
        'base_url' => env('APP_URL'),
        'language' => 'en',
        'assets_url' => '/pagebuilder-assets/',
        'uploads_url' => '/pagebuilder-uploads/',
    ],
    'storage' => [
        'use_database' => false,
        'database' => [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST').':'.env('DB_PORT', 3306),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => 'pagebuilder__',
        ],
        'uploads_folder' => storage_path('app/public/pagebuilder/uploads'),
    ],
    'auth' => [
        'use_login' => false,
        'class' => Auth::class,
        'url' => '/pagebuilder-auth',
        'username' => '',
        'password' => '',
    ],
    'website_manager' => [
        'use_website_manager' => false,
        'class' => WebsiteManager::class,
        'url' => '/pagebuilder-admin',
    ],
    'setting' => [
        'class' => Setting::class,
    ],
    'pagebuilder' => [
        'class' => PageBuilder::class,
        'url' => '/pagebuilder-editor',
        'actions' => [
            'back' => '/admin/core/events',
        ],
    ],
    'page' => [
        'class' => Page::class,
        'table' => 'pages',
        'translation' => [
            'class' => PageTranslation::class,
            'table' => 'page_translations',
            'foreign_key' => 'page_id',
        ],
    ],
    'cache' => [
        'enabled' => false,
        'folder' => storage_path('framework/cache/pagebuilder'),
        'class' => Cache::class,
    ],
    'theme' => [
        'class' => Theme::class,
        'folder' => base_path('themes'),
        'folder_url' => '/themes',
        'active_theme' => 'event-site',
    ],
    'router' => [
        'class' => DatabasePageRouter::class,
        'use_router' => false,
    ],
    'class_replacements' => [],
];
