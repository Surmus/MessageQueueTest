<?php

class ListenInterestQueueCommandTest extends TestCase
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;

    /**
     * @param Closure $infoCb
     * @param Closure $errorCb
     * @return \App\Contracts\InterestQueueLog
     */
    protected function getLogger(Closure $infoCb, Closure $errorCb)
    {
        $stub = $this->getMockBuilder('\App\Contracts\InterestQueueLog')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $stub->method('info')
            ->willReturnCallback($infoCb);

        $stub->method('error')
            ->willReturnCallback($errorCb);

        return $stub;
    }

    /**
     * @return \App\Contracts\InterestQueue
     */
    protected function getQueue()
    {
        $stub = $this->getMockBuilder('App\Contracts\InterestQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $stub->method('listenQueue')
            ->withConsecutive(
                [$this->isInstanceOf('\Closure')]
            )
            ->willReturnCallback(function (Closure $callback) {
                $msg = new \PhpAmqpLib\Message\AMQPMessage(
                    json_encode(array('sum' => 123, 'days' => 5))
                );
                $msg->delivery_info = ['delivery_tag' => 'dummy'];

                $callback($msg);
            });

        $stub->method('getChannel')->willReturn(
            $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
                ->setMethods(array('basic_ack'))
                ->disableOriginalConstructor()
                ->getMock()
        );

        return $stub;
    }

    public function testHandleDispatchJob()
    {
        $errors = [];

        $logger = $this->getLogger(
            function($msg) {}, //empty dummy function
            function($msg) use (&$errors) {
                $errors[] = $msg;
            }
        );

        $command = new \App\Console\Commands\ListenInterestQueue($logger);

        //Manually set Output handler for command using reflection
        $reflection = new ReflectionClass($command);
        $reflectionProperty = $reflection->getProperty('output');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($command, new Symfony\Component\Console\Output\NullOutput);

        //CalculateInterest Job must fire during the execution of handle
        $this->expectsJobs(App\Jobs\CalculateInterest::class);

        $command->handle($this->getQueue());

        //Check that no errors occurred
        $this->assertEquals(0, count($errors));
    }

}