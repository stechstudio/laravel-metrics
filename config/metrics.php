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
            'udp_port' => env('IDB_UDP_PORT')
        ],
        'influxdb2' => [
            'url' => env('IDB2_HOST'),
            'token' => env('IDB2_TOKEN'),
            'org' => env('IDB2_ORG'),
            'bucket' => env('IDB2_BUCKET'),
            'debug' => env('IDB2_DEBUG', false)
        ],
        'cloudwatch' => [
            'region' => env('CLOUDWATCH_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'namespace' => env('CLOUDWATCH_NAMESPACE')
        ]
    ],
];
