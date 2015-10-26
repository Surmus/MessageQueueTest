<?php

class InterestCalculatorServiceTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetInterestCalculatorWithInvalidSum()
    {
        $calculator = new \App\Services\InterestCalculatorService();
        $calculator->getInterestSum('30,5', 2);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetInterestCalculatorWithInvalidDays()
    {
        $calculator = new \App\Services\InterestCalculatorService();
        $calculator->getInterestSum(4447.5, '56 days');
    }

    public function testGetInterestCalculatorWithAllDividables()
    {
        $calculator = new \App\Services\InterestCalculatorService();

        $this->assertEquals($calculator->getInterestSum(567, 15), 243.81);
        $this->assertEquals($calculator->getInterestSum(567, 89), 1445.85);
    }
}