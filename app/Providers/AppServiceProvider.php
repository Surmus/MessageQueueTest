<?php

namespace App\Providers;

use App\Services\InterestQueueLogService;
use App\Services\InterestQueueService;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPConnection;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //InterestCalculator implementation abstract binding with actual code
        $this->app->bind('App\Contracts\InterestCalculator', 'App\Services\InterestCalculatorService');

        //InterestQueue implementation abstract binding with actual code
        $this->app->bind('App\Contracts\InterestQueue', function ($app) {
            $queueSettings = \Config::get('queue.interest_queue');
            $connection = new AMQPConnection(
                $queueSettings['host'],
                $queueSettings['port'],
                $queueSettings['login'],
                $queueSettings['password'],
                $queueSettings['vhost']
            );

            return new InterestQueueService($connection, $queueSettings);
        });

        //ListenInterestQueue logger
        $this->app->singleton('App\Contracts\InterestQueueLog', function ($app) {
            // create a log channel
            $logger = new InterestQueueLogService('InterestQueue');
            $logger->pushHandler(
                new RotatingFileHandler(app()->storagePath() . '/logs/interest_queue.log', 5, Logger::INFO)
            );

            return $logger;
        });
    }
}
