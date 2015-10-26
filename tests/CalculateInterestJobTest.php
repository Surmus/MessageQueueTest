<?php

class CalculateInterestJobTest extends TestCase
{
    /**
     * @param float $sum
     * @param int $days
     * @return \App\Jobs\CalculateInterest
     */
    protected function getJob($sum, $days)
    {
        $stub = $this->getMockBuilder('App\Jobs\CalculateInterest')
            ->disableOriginalConstructor()
            ->setMethods(array('pushIntoQueue'))
            ->getMock();

        $reflection = new ReflectionClass($stub);

        //Sum property
        $reflectionPropertySum = $reflection->getProperty('sum');
        $reflectionPropertySum->setAccessible(true);
        $reflectionPropertySum->setValue($stub, $sum);

        //Days property
        $reflectionPropertyDays = $reflection->getProperty('days');
        $reflectionPropertyDays->setAccessible(true);
        $reflectionPropertyDays->setValue($stub, $days);

        return $stub;
    }

    /**
     * @return \App\Contracts\InterestQueueLog
     */
    protected function getLogger()
    {
        $stub = $this->getMockBuilder('\App\Contracts\InterestQueueLog')
            ->disableOriginalConstructor()
            ->getMock();

        return $stub;
    }

    /**
     * @return \App\Contracts\InterestCalculator
     */
    protected function getInterestCalculator()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->getMockBuilder('App\Contracts\InterestCalculator')
            ->getMock();

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

        return $stub;
    }

    public function testCalculateInterestJob()
    {
        $sum = 123;
        $days = 5;
        $interest = 18.45;

        $job = $this->getJob($sum, $days);
        $queue = $this->getQueue();
        $calculator = $this->getInterestCalculator();
        $calculator->method('getInterestSum')
            ->willReturnMap([
                [$sum, $days, $interest]
            ]);

        $expectedJson = '{"sum":123,"days":5,"interest":18.45,"totalSum":141.45,"token":"testApp"}';
        $scope = $this;

        $queue->expects($this->once())
            ->method('pushIntoQueue')
            ->withConsecutive(
                [$this->isInstanceOf('\PhpAmqpLib\Message\AMQPMessage')]
                //array('{"sum":123,"days":5,"interest":18.45,"totalSum":141.45,"token":"testApp"}')
            )
            ->willReturnCallback(function () use ($expectedJson, $scope) {
                /** @var $message \PhpAmqpLib\Message\AMQPMessage*/
                $message = func_get_arg(0);

                $scope->assertEquals($expectedJson, $message->body);
            });

        $job->handle($calculator, $queue, $this->getLogger());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCalculateInterestJobThrowException()
    {
        $sum = 123;
        $days = -5;
        $interest = 18.45;

        $job = $this->getJob($sum, $days);
        $queue = $this->getQueue();
        $calculator = $this->getInterestCalculator();
        $calculator->method('getInterestSum')
            ->willReturnMap([
                [$sum, $days, $interest]
            ]);

        $job->handle($calculator, $queue, $this->getLogger());
    }
}