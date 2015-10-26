<?php

namespace App\Jobs;

use App\Contracts\InterestCalculator;
use App\Contracts\InterestQueue;
use App\Contracts\InterestQueueLog;
use App\Loan;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpAmqpLib\Message\AMQPMessage;

class CalculateInterest extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Loan sum
     *
     * @var float
     */
    protected $sum;

    /**
     * Loan period in days
     *
     * @var int
     */
    protected $days;

    /**
     * Create a new job instance.
     *
     * @param float $sum
     * @param int $days
     */
    public function __construct($sum, $days)
    {
        //Set vars
        $this->sum = $sum;
        $this->days = $days;
    }

    /**
     * Execute the job.
     *
     * @param InterestCalculator $calculator
     * @param InterestQueue $queue
     * @param InterestQueueLog $log
     * @throws \Exception
     */
    public function handle(InterestCalculator $calculator, InterestQueue $queue, InterestQueueLog $log)
    {
        $loan = new Loan($this->sum, $this->days, $calculator);
        $msg = new AMQPMessage(json_encode($loan), ['Content-Type' => 'application/json', 'delivery_mode' => 2]);

        try {
            //Push calculated loan back to the queue
            $queue->pushIntoQueue($msg);
        } catch (\Exception $e) {
            $log->error(
                sprintf(
                    'Push into interest solved queue failed for message: %s, error => %s',
                    $msg->body, $e->getMessage()
                )
            );

            throw $e;
        }

        //Log outgoing messages only in debug mode
        if (\Config::get('app.debug')) {
            $log->info('Message pushed into interest solved queue: ' . $msg->body);
        }
    }
}