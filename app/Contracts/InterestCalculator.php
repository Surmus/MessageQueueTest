<?php namespace App\Contracts;

interface InterestCalculator
{
    /**
     * Calculates interest sum for loan with given days
     *
     * @param float $sum
     * @param int $days
     * @return float
     */
    public function getInterestSum($sum, $days);

}