<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | The Laravel queue API supports a variety of back-ends via an unified
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may set the default queue driver.
    |
    | Supported: "null", "sync", "database", "beanstalkd",
    |            "sqs", "iron", "redis"
    |
    */

    'default' => env('QUEUE_DRIVER', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'expire' => 60,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host'   => 'localhost',
            'queue'  => 'default',
            'ttr'    => 60,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key'    => 'your-public-key',
            'secret' => 'your-secret-key',
            'queue'  => 'your-queue-url',
            'region' => 'us-east-1',
        ],

        'iron' => [
            'driver'  => 'iron',
            'host'    => 'mq-aws-us-east-1.iron.io',
            'token'   => 'your-token',
            'project' => 'your-project-id',
            'queue'   => 'your-queue-name',
            'encrypt' => true,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue'  => 'default',
            'expire' => 60,
        ]
    ],

    /*
     * RabbitMQ server config for interest queue component
     */
    'interest_queue' => [
        'host' => env('INTEREST_QUEUE_HOST', '127.0.0.1'),
        'port' => env('INTEREST_QUEUE_PORT', 5672),

        'vhost' => env('INTEREST_QUEUE_VHOST', '/'),
        'login' => env('INTEREST_QUEUE_LOGIN', 'guest'),
        'password' => env('INTEREST_QUEUE_PASSWORD', 'guest'),

        'queues' => [
            'InterestQueue' => env('INTEREST_QUEUE', 'interest-queue'),
            'SolvedInterestQueue' => env('SOLVED_INTEREST_QUEUE', 'solved-interest-queue'),
        ],

        'exchange_declare'      => env('INTEREST_QUEUE_EXCHANGE_DECLARE', true), // create the exchange if not exists
        'queue_declare_bind'    => env('INTEREST_QUEUE_QUEUE_DECLARE_BIND', true), // create the queue if not exists and bind to the exchange

        'queue_params'          => [
            'passive'           => env('INTEREST_QUEUE_QUEUE_PASSIVE', false),
            'durable'           => env('INTEREST_QUEUE_QUEUE_DURABLE', true),
            'exclusive'         => env('INTEREST_QUEUE_QUEUE_EXCLUSIVE', false),
            'auto_delete'       => env('INTEREST_QUEUE_QUEUE_AUTODELETE', false),
        ],

        'exchange_params' => [
            'name'        => env('INTEREST_QUEUE_EXCHANGE_NAME', ''),
            'type'        => env('INTEREST_QUEUE_EXCHANGE_TYPE', 'direct'), // more info at http://www.rabbitmq.com/tutorials/amqp-concepts.html
            'passive'     => env('INTEREST_QUEUE_EXCHANGE_PASSIVE', false),
            'durable'     => env('INTEREST_QUEUE_EXCHANGE_DURABLE', true), // the exchange will survive server restarts
            'auto_delete' => env('INTEREST_QUEUE_EXCHANGE_AUTODELETE', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'database' => 'mysql', 'table' => 'failed_jobs',
    ],

];
