<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Ikaros Management API',
            ],
            'routes' => [
                'api' => 'api/*',
                'docs' => 'docs',
                'oauth2_callback' => 'api/oauth2-callback',
            ],
            'scanOptions' => [
                'paths' => [
                    base_path('app'),
                    base_path('routes'),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('tests'),
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api',
                'auth',
            ],
        ],
        'swagger' => [
            'paths' => [
                base_path('swagger'),
            ],
        ],
        'annotations' => [
            base_path('app'),
            base_path('routes'),
        ],
        'open_api_spec_version' => '3.0.0',
        'format_to_use_for_docs' => 'yaml',
        'persist_authorization' => false,
        'show_web_ui' => true,
        'show_api_ui' => true,
    ],
];
