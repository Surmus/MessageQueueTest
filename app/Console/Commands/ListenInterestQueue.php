<?php

namespace App\Console\Commands;

use App\Contracts\InterestQueue;
use App\Contracts\InterestQueueLog;
use App\Jobs\CalculateInterest;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PhpAmqpLib\Message\AMQPMessage;

class ListenInterestQueue extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interest-queue:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Logger instance, used to log Command output
     *
     * @var InterestQueueLog
     */
    protected $log;

    /**
     * Create a new command instance.
     *
     * @param InterestQueueLog $log
     */
    public function __construct(InterestQueueLog $log)
    {
        parent::__construct();

        $this->log = $log;
    }

    /**
     * Execute the console command.
     *
     * @param InterestQueue $queue
     */
    public function handle(InterestQueue $queue)
    {
        $this->info('Interest queue listener :: Started');
        $scope = $this;

        $callback = function(AMQPMessage $msg) use ($scope, $queue) {
            $scope->info('Message received from queue: ' . $msg->body);
            $inputArr = json_decode($msg->body, true);

            try {
                $scope->dispatch(new CalculateInterest($inputArr['sum'], $inputArr['days']));
            } catch (\Exception $e) {
                $this->error(
                    sprintf(
                        'Dispatching calculate interest job failed for message: %s, error => %s',
                        $msg->body, $e->getMessage()
                    )
                );

                //Send no acknowledgments back to the message queue
                return;
            }

            //Tell message queue server that message was received
            $queue->getChannel()->basic_ack($msg->delivery_info['delivery_tag']);
        };

        try {
            $queue->listenQueue($callback);
        } catch (\Exception $e) {
            $this->error('Error during queue message fetch -> error : ' . $e->getMessage());
        }

        $this->info('Interest queue listener :: Shutdown');
    }

    /**
     * Print out console info messages and log them
     *
     * @param string $msg
     */
    public function info($msg)
    {
        $this->log->info($msg);

        parent::info($msg);
    }

    /**
     * Print out console error messages and log them
     *
     * @param string $msg
     */
    public function error($msg)
    {
        $this->log->error($msg);

        parent::error($msg);
    }
}
