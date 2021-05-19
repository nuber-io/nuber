<?php

use Origin\Core\Config;
use Origin\Storage\Engine\S3Engine;
use Origin\Job\Engine\DatabaseEngine;
use Origin\Model\Engine\SqliteEngine;
use App\Lxd\LogEngine as LxdLogEngine;
use Origin\Storage\Engine\LocalEngine;
use Origin\Http\Session\Engine\PhpEngine;
use Origin\Log\Engine\FileEngine as LogEngine;
use Origin\Cache\Engine\FileEngine as CacheEngine;

return [
    'App' => [
        'debug' => env('APP_DEBUG', true),
        'name' => 'nuber',
        'url' => env('APP_URL', 'https://localhost:3000'),
        'environment' => env('APP_ENV'),
        'namespace' => 'App',
        'encoding' => 'UTF-8',
        'defaultTimezone' => 'UTC',
        'imageDownloadTimeout' => 60 * 5,
        'securityKey' => env('APP_KEY'),
        'schemaFormat' => 'php',
        'mailboxKeepEmails' => '+30 days'
    ],
    'Cache' => [
        'default' => [
            'className' => CacheEngine::class,
            'path' => CACHE,
            'duration' => '+60 minutes', // string or number of seconds e.g. 3600,
            'prefix' => 'cache_',
            'serialize' => true // set to false if you going to cache strings such as output
        ],
        /**
         * Caching setup used by framework
         *
         * - Model uses this to cache table metadata.
         * IMPORTANT: If you make changes to table then use the console command Cache::clear
         */
        'origin' => [
            'className' => CacheEngine::class,
            'path' => CACHE . '/origin',
            'duration' => Config::read('App.debug') ? '+2 minutes' : '+24 hours',
            'prefix' => 'cache_',
            'serialize' => true
        ]
    ],
    'Database' => [
        'default' => [
            'database' => 'data/nuber.db',
            'className' => SqliteEngine::class
        ],
        'test' => [
            'database' => 'data/nuber-test.db',
            'className' => SqliteEngine::class
        ]
    ],
    'Email' => [
        'default' => [
            'host' => env('EMAIL_HOST'),
            'port' => env('EMAIL_PORT'),
            'username' => env('EMAIL_USERNAME'),
            'password' => env('EMAIL_PASSWORD'),
            'timeout' => 30,
            'ssl' => env('EMAIL_SSL'),
            'tls' => env('EMAIL_TLS'),
        ],
        /**
         * Test engine does not actually send the email
         */
        'test' => [
            'engine' => 'Test'
        ]
    ],
    'Log' => [
        'default' => [
            'className' => LogEngine::class,
            'file' => LOGS . '/application.log',
            'channels' => ['application']
        ],
        'lxd' => [
            'className' => LxdLogEngine::class,
            'file' => LOGS . '/lxd.log',
            'channels' => ['lxd']
        ]
    ],
    'Lxd' => [
        'remote' => [
            'images' => [
                'server' => 'https://images.linuxcontainers.org:8443',
                'protocol' => 'simplestreams'
            ]
        ]
    ],
    'Session' => [
        'className' => PhpEngine::class,
        'name' => 'id',
        'idLength' => 32, // Must be at least 128 bits (16 bytes)
        'timeout' => 900 // Logout after 15 minutes of in activity
    ],
    'Storage' => [
        'default' => [
            'className' => LocalEngine::class,
            'root' => ROOT . '/storage'
        ],
        's3' => [
            'className' => S3Engine::class,
            'credentials' => [
                'key' => env('S3_KEY'), // * required
                'secret' => env('S3_SECRET'), // * required
            ],
            'region' => 'us-east-1', // * required
            'version' => 'latest',
            'endpoint' => env('S3_ENDPOINT'), // for S3 comptabile protocols
            'bucket' => env('S3_BUCKET'), // * required
            // don't check SSL cert
            'http' => [
                'verify' => false
            ]
        ]
    ],
    'Queue' => [
        'default' => [
            'className' => DatabaseEngine::class,
            'connection' => 'default'
        ],
        'test' => [
            'className' => DatabaseEngine::class,
            'connection' => 'test'
        ]
    ]

];
