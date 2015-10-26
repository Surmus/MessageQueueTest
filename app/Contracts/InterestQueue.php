<?php namespace App\Contracts;

use PhpAmqpLib\Message\AMQPMessage;

interface InterestQueue
{
    /**
     * Sets up interest queue listen and fires callback every time message is received
     *
     * @param \Closure $callback
     */
    public function listenQueue(\Closure $callback);

    /**
     * Pushes new message into interest solved message queue
     *
     * @param AMQPMessage $msg
     */
    public function pushIntoQueue(AMQPMessage $msg);

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel();
}