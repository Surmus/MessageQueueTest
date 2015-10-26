<?php

class InterestQueueServiceTest extends TestCase
{
    protected $channel;

    /**
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    protected function getConnection()
    {
        $stub = $this->getMockBuilder('PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the stub.
        $stub->method('channel')->willReturn($this->getChannel());

        return $stub;
    }

    /**
     * @param string $body
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    protected function getMessage($body)
    {
        /* @var $stub PhpAmqpLib\Message\AMQPMessage */
        $stub = $this->getMockBuilder('PhpAmqpLib\Message\AMQPMessage')
            ->disableOriginalConstructor()
            ->getMock();

        $stub->body = $body;

        return $stub;
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    protected function getChannel()
    {
        if (!$this->channel) {
            /* @var $mockChannel \PhpAmqpLib\Channel\AMQPChannel */
            $this->channel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
                ->setMethods(array('queue_bind', 'basic_publish', 'exchange_declare', 'queue_declare', 'basic_consume'))
                ->disableOriginalConstructor()
                ->getMock();
        }

        $this->channel->close();

        return $this->channel;
    }

    public function testPushIntoQueue()
    {
        $service = new \App\Services\InterestQueueService(
            $this->getConnection(),
            \Config::get('queue.interest_queue')
        );
        $message = $this->getMessage('Hello world');

        $this->getChannel()->expects($this->once())
            ->method('basic_publish')
            ->withConsecutive(
                array($this->equalTo($message))
            );

        $service->pushIntoQueue($this->getMessage('Hello world'));
    }

    public function testListenQueue()
    {
        \Config::set(
            'queue.interest_queue.queues.' . \App\Services\InterestQueueService::TYPE_INTEREST_QUEUE, 'testQueue'
        );

        $this->channel = null;

        $service = new \App\Services\InterestQueueService(
            $this->getConnection(),
            \Config::get('queue.interest_queue')
        );
        $cbObject = new StdClass();
        $cbObject->hasBeenCalled = false;

        $callback = function() use ($cbObject) {
            $cbObject->hasBeenCalled = true;
        };

        $this->getChannel()->expects($this->once())
            ->method('basic_consume')
            ->withConsecutive(
                array('testQueue', 'testApp', false, false, false, false, $this->isInstanceOf('\Closure'))
            )
            ->willReturnCallback(function () {
                /** @var $cb callable*/
                $cb = func_get_arg(6);

                $cb();
            });

        $service->listenQueue($callback);
        $this->assertTrue($cbObject->hasBeenCalled);
    }
}