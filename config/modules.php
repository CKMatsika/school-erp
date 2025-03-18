<?php
// config/modules.php

return [
    'core' => [
        'name' => 'Core',
        'path' => app_path('Modules/Core'),
        'namespace' => 'App\\Modules\\Core',
        'routes' => [
            'web' => app_path('Modules/Core/Routes/web.php'),
            'api' => app_path('Modules/Core/Routes/api.php'),
        ],
        'views' => resource_path('views/modules/core'),
        'migrations' => app_path('Modules/Core/Database/Migrations'),
        'translations' => app_path('Modules/Core/Resources/lang'),
        'config' => app_path('Modules/Core/config.php'),
        'providers' => [
            // Add any module-specific service providers here
        ],
    ],
    'timetable' => [
        'name' => 'Timetable',
        'path' => app_path('Modules/Timetable'),
        'namespace' => 'App\\Modules\\Timetable',
        'routes' => [
            'web' => app_path('Modules/Timetable/Routes/web.php'),
            'api' => app_path('Modules/Timetable/Routes/api.php'),
        ],
        'views' => resource_path('views/modules/timetable'),
        'migrations' => app_path('Modules/Timetable/Database/Migrations'),
        'translations' => app_path('Modules/Timetable/Resources/lang'),
        'config' => app_path('Modules/Timetable/config.php'),
        'providers' => [
            // Add any module-specific service providers here
        ],
    ],
    // Add other modules here as you develop them
];