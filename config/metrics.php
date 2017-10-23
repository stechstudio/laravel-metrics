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
        'cloudwatch' => [
            'namespace' => env('CLOUDWATCH_NAMESPACE')
        ]
    ],
];
