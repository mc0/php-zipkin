<?php
/**
 * Created by PhpStorm.
 * User: JohnWang <takato@vip.qq.com>
 * Date: 2017/2/23
 * Time: 14:56
 */
return [
    // service name
    'name'       => env('ZIPKIN_APP_NAME', 'laravel-app'),

    // transport driver
    'default'    => env('ZIPKIN_TRANSPORT', 'http'),

    // transports
    'transports' => [
        // HttpLogger
        'http' => [
            'driver' => 'http',
            'uri'    => env('ZIPKIN_HTTP_URI', 'http://127.0.0.1:9411/api/v1/spans')
        ]
    ]
];