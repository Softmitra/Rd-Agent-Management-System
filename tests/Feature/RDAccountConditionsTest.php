<?php

namespace Tests\Feature;

use App\Models\RDAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RDAccountConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_monthly_amount_meets_minimum_requirement()
    {
        $account = new RDAccount(['monthly_amount' => 100]);
        $this->assertTrue($account->validateMonthlyAmount());

        $account->monthly_amount = 110;
        $this->assertTrue($account->validateMonthlyAmount());

        $account->monthly_amount = 95;
        $this->assertFalse($account->validateMonthlyAmount());

        $account->monthly_amount = 105; // Not multiple of 10
        $this->assertFalse($account->validateMonthlyAmount());
    }

    /** @test */
    public function it_calculates_rebate_correctly()
    {
        $account = new RDAccount();

        // No rebate for less than 6 months
        $account->installments_paid = 5;
        $this->assertEquals(0.00, $account->calculateRebate());

        // Rs. 10 rebate for 6 months
        $account->installments_paid = 6;
        $this->assertEquals(10.00, $account->calculateRebate());

        // Rs. 10 rebate for 11 months
        $account->installments_paid = 11;
        $this->assertEquals(10.00, $account->calculateRebate());

        // Rs. 40 rebate for 12 months
        $account->installments_paid = 12;
        $this->assertEquals(40.00, $account->calculateRebate());

        // Rs. 40 rebate for more than 12 months
        $account->installments_paid = 18;
        $this->assertEquals(40.00, $account->calculateRebate());
    }

    /** @test */
    public function it_calculates_penalty_correctly()
    {
        $account = new RDAccount(['monthly_amount' => 100]);
        $this->assertEquals(1.00, $account->penalty_per_month); // Re. 1 for Rs. 100

        $account->monthly_amount = 200;
        $this->assertEquals(2.00, $account->penalty_per_month); // Rs. 2 for Rs. 200

        $account->monthly_amount = 50;
        $this->assertEquals(0.50, $account->penalty_per_month); // Rs. 0.50 for Rs. 50
    }

    /** @test */
    public function it_checks_premature_closure_eligibility()
    {
        $account = new RDAccount();
        $account->start_date = Carbon::now()->subYears(4); // 4 years ago
        $this->assertTrue($account->isEligibleForPrematureClosure());

        $account->start_date = Carbon::now()->subYears(2); // 2 years ago
        $this->assertFalse($account->isEligibleForPrematureClosure());

        $account->start_date = null;
        $this->assertFalse($account->isEligibleForPrematureClosure());
    }

    /** @test */
    public function it_checks_loan_eligibility()
    {
        $account = new RDAccount();
        $account->start_date = Carbon::now()->subYears(2); // 2 years ago
        $account->installments_paid = 12; // 12 installments
        $this->assertTrue($account->isEligibleForLoan());

        $account->installments_paid = 11; // Only 11 installments
        $this->assertFalse($account->isEligibleForLoan());

        $account->installments_paid = 12;
        $account->start_date = Carbon::now()->subMonths(6); // Only 6 months ago
        $this->assertFalse($account->isEligibleForLoan());

        $account->start_date = null;
        $this->assertFalse($account->isEligibleForLoan());
    }

    /** @test */
    public function it_calculates_max_loan_amount_correctly()
    {
        $account = new RDAccount();
        $account->start_date = Carbon::now()->subYears(2);
        $account->installments_paid = 12;
        $account->total_deposited = 1200; // 12 * 100

        $this->assertEquals(600.00, $account->max_loan_amount); // 50% of 1200

        // Not eligible for loan
        $account->installments_paid = 11;
        $this->assertEquals(0.00, $account->max_loan_amount);
    }

    /** @test */
    public function it_gets_all_joint_holders_correctly()
    {
        $account = new RDAccount();
        $account->customer = (object)['name' => 'Primary Customer'];
        $account->joint_holder_name = 'Joint Holder 1';
        $account->joint_holder_2_name = 'Joint Holder 2';
        $account->joint_holder_3_name = 'Joint Holder 3';

        $holders = $account->all_joint_holders;
        $this->assertEquals([
            'Primary Customer',
            'Joint Holder 1',
            'Joint Holder 2',
            'Joint Holder 3'
        ], $holders);

        // Test with some null values
        $account->joint_holder_2_name = null;
        $account->joint_holder_3_name = null;
        $holders = $account->all_joint_holders;
        $this->assertEquals([
            'Primary Customer',
            'Joint Holder 1'
        ], $holders);
    }
}
