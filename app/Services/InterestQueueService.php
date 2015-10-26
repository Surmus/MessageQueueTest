<?php namespace App\Services;

use App\Contracts\InterestQueue;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class InterestQueueService implements InterestQueue
{
    /** @var AMQPConnection*/
    protected $connection;
    /** @var \PhpAmqpLib\Channel\AMQPChannel*/
    protected $channel;
    /** @var string*/
    protected $queue;
    /** @var string*/
    protected $exchange;

    /**
     * Message queue config values
     *
     * @var array*/
    protected $config;

    const TYPE_INTEREST_QUEUE = 'InterestQueue';
    const TYPE_SOLVED_INTEREST_QUEUE = 'SolvedInterestQueue';

    /**
     * Init service
     *
     * @param AMQPConnection $connection
     * @param array $config
     */
    public function __construct(AMQPConnection $connection, array $config)
    {
        //If this is enabled you can see AMQP output on the CLI
        //define('AMQP_DEBUG', true);

        $this->connection = $connection;
        $this->channel = $this->connection->channel();
        $this->config = $config;

        //Close connection on event of error
        register_shutdown_function([$this, 'shutdown'], $this->channel, $this->connection);
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Sets up specified queue using config constants
     *
     * @param string $queue name of the queue
     */
    protected function initQueue($queue)
    {
        $queueParams = $this->config['queue_params'];
        $this->queue = $this->config['queues'][$queue];

        $exchangeParams = $this->config['exchange_params'];
        //If no exchange is defined use queue name as exchange name
        $this->exchange = $exchangeParams['name'] ? $exchangeParams['name'] : $this->queue;

        /*
            The following code is the same both in the consumer and the producer.
            In this way we are sure we always have a queue to consume from and an
            exchange where to publish messages.
        */

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $this->channel->exchange_declare(
            $this->exchange,
            $exchangeParams['type'],
            $exchangeParams['passive'],
            $exchangeParams['durable'],
            $exchangeParams['auto_delete']
        );

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $this->channel->queue_declare(
            $this->queue,
            $queueParams['passive'],
            $queueParams['durable'],
            $queueParams['exclusive'],
            $queueParams['auto_delete']
        );

        $this->channel->queue_bind($this->queue, $this->exchange, $this->queue);
    }


    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $ch
     * @param \PhpAmqpLib\Connection\AbstractConnection $conn
     */
    function shutdown($ch, $conn)
    {
        $ch->close();
        $conn->close();
    }

    /**
     * Pushes new message into interest solved message queue
     *
     * @param AMQPMessage $msg
     */
    public function pushIntoQueue(AMQPMessage $msg)
    {
        $this->initQueue(self::TYPE_SOLVED_INTEREST_QUEUE);

        //$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
        $this->channel->basic_publish($msg, $this->exchange, $this->queue);

        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Sets up interest queue listen and fires callback every time message is received
     *
     * @param \Closure $callback
     */
    public function listenQueue(\Closure $callback)
    {
        $this->initQueue(self::TYPE_INTEREST_QUEUE);
        $consumer_tag = \Config::get('app.name');

        /*
            queue: Queue from where to get the messages
            consumer_tag: Consumer identifier
            no_local: Don't receive messages published by this consumer.
            no_ack: Tells the server if the consumer will acknowledge the messages.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            nowait:
            callback: A PHP Callback
        */

        $this->channel->basic_consume($this->queue, $consumer_tag, false, false, false, false, $callback);

        // Loop as long as the channel has callbacks registered
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}