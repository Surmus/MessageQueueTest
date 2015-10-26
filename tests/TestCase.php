<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    public function setUp()
    {
        parent::setUp();

        //Set application token
        \Config::set('app.name', 'testApp');

        //Set app debug mode
        \Config::set('app.debug', true);

        //Set interest rates
        \Config::set('app.interestRates', [
            '3' => 1,
            '5' => 2,
            '3/5' => 3,
            'default' => 4
        ]);
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
