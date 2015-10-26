<?php

namespace App;

use App\Contracts\InterestCalculator;

class Loan implements \JsonSerializable
{
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
     * Calculated loan interest
     *
     * @var float
     */
    protected $interest = 0;

    /**
     * Create new Loan object
     *
     * @param $sum
     * @param $days
     * @param InterestCalculator $calculator
     */
    public function __construct($sum, $days, InterestCalculator $calculator)
    {
        $this->sum = $sum;
        $this->days = $days;

        $this->validateInput();
        $this->interest = $calculator->getInterestSum($sum, $days);
    }

    /**
     * @return float
     */
    public function getTotalSum()
    {
        return round($this->sum + $this->interest, 2);
    }

    /**
     * @return float
     */
    public function getInterest()
    {
        return round($this->interest, 2);
    }

    /**
     * Validate loan input
     */
    protected function validateInput()
    {
        if (filter_var($this->sum, FILTER_VALIDATE_FLOAT) === false) {
            throw new \InvalidArgumentException('Invalid parameter for Loan => sum : ' . $this->sum);
        }

        if ($this->sum <= 0) {
            throw new \InvalidArgumentException(sprintf('Loan sum %d cannot be 0 or negative', $this->sum));
        }

        if (!filter_var($this->days, FILTER_VALIDATE_INT) || $this->days < 0) {
            throw new \InvalidArgumentException('Invalid parameter for Loan => days : ' . $this->days);
        }
    }

    /**
     * JsonSerializable Interface function,
     * invoked during object json_encode
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'sum' => $this->sum,
            'days' => $this->days,
            'interest' => $this->getInterest(),
            'totalSum' => $this->getTotalSum(),
            'token' => \Config::get('app.name')
        ];
    }
}