<?php

return [
    'default' => env('METRICS_BACKEND'),
    'backends' => [
        'influxdb' => [
            'username' => env('IDB_USERNAME'),
            'password' => env('IDB_PASSWORD'),
            'host' => env('IDB_HOST'),
            'database' => env('IDB_DATABASE'),
            'tcp_port' => env('IDB_TCP_PORT', 8086),
            'udp_port' => env('IDB_UDP_PORT'),
            'version' => env('IDB_VERSION', 1),
            'token' => env('IDB_TOKEN'),
            'org' => env('IDB_ORG')
        ],
        'cloudwatch' => [
            'region' => env('CLOUDWATCH_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'namespace' => env('CLOUDWATCH_NAMESPACE')
        ],
        "posthog" => [
            'key' => env('POSTHOG_API_KEY'),
            'host' => env('POSTHOG_HOST', 'https://app.posthog.com'),
            'distinct_prefix' => env('POSTHOG_DISTINCT_PREFIX', 'user:')
        ]
    ],
];
