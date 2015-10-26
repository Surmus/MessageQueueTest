<?php

class LoanTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateLoanWithNullSum()
    {
        $calculator = $this->getInterestCalculator();

        new \App\Loan(0, 5, $calculator);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateLoanWithInvalidSum()
    {
        $calculator = $this->getInterestCalculator();

        new \App\Loan('foo', 5, $calculator);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateLoanWithNegativeSum()
    {
        $calculator = $this->getInterestCalculator();

        new \App\Loan(-4.3, 5, $calculator);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateLoanWithInvalidDays()
    {
        $calculator = $this->getInterestCalculator();

        new \App\Loan(100.4, 'hello', $calculator);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateLoanWithNullDays()
    {
        $calculator = $this->getInterestCalculator();

        new \App\Loan(100.4, null, $calculator);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateLoanWithNegativeDays()
    {
        $calculator = $this->getInterestCalculator();

        new \App\Loan(100.4, -5, $calculator);
    }

    public function testCreateValidLoan()
    {
        $calculator = $this->getInterestCalculator();

        $loan = new \App\Loan(123, 5, $calculator);
        $loan2 = new \App\Loan(433, 2, $calculator);

        $this->assertEquals(18.45, $loan->getInterest());
        $this->assertNotEquals($this->any(), $loan2->getTotalSum());
    }

    public function testLoanSerialize()
    {
        $calculator = $this->getInterestCalculator();

        $loan = new \App\Loan(123, 5, $calculator);

        $this->assertEquals(
            '{"sum":123,"days":5,"interest":18.45,"totalSum":141.45,"token":"testApp"}',
            json_encode($loan)
        );
    }

    /**
     * @return \App\Contracts\InterestCalculator
     */
    public function getInterestCalculator()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->getMockBuilder('App\Contracts\InterestCalculator')
            ->getMock();

        // Configure the stub.
        $stub->method('getInterestSum')
            ->willReturnMap([
                [123, 5, 18.45]
            ]);

        return $stub;
    }
}
