<?php namespace App\Services;

use App\Contracts\InterestCalculator;

class InterestCalculatorService implements InterestCalculator
{
    /**
     * Loan interest rates
     *
     * @var array
     */
    protected $interestRates;

    /**
     * Fetch current interest rates from config
     */
    public function __construct()
    {
        /*
         * Interest rates table, day rate is determined by dividing day number with array keys,
         * produces clean divide with no remainder
         *
         */
        $this->interestRates = \Config::get('app.interestRates', [
            '3' => 1,
            '5' => 2,
            '3/5' => 3,
            'default' => 4
        ]);
    }

    /**
     * Calculates interest sum for loan with given days
     *
     * @param float $sum
     * @param int $days
     * @return float
     */
    public function getInterestSum($sum, $days)
    {
        if (!filter_var($sum, FILTER_VALIDATE_FLOAT) || !filter_var($days, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException(
                "Invalid parameters for interest calculator -> sum: {$sum} days: {$days}"
            );
        }

        $interestSum = 0.0;

        for ($i = 1; $i <= $days; $i++) {
            $divisibles = [
                3 => false,
                5 => false,
            ];

            foreach ($divisibles as $divisible => &$isDividable) {
                $isDividable = $i % $divisible ? false : true;
            }

            if ($divisibles[3] && $divisibles[5]) {
                $interest = $this->interestRates['3/5'];
            } elseif ($divisibles[3]) {
                $interest = $this->interestRates['3'];
            } elseif ($divisibles[5]) {
                $interest = $this->interestRates['5'];
            } else {
                $interest = $this->interestRates['default'];
            }

            $interestSum += ($sum / 100) * $interest;
        }

        //Interest can only be positive number
        return abs($interestSum);
    }
}