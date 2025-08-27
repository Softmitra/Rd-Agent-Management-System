<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class RDAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rd_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'agent_id',
        'account_type',
        'is_joint_account',
        'joint_holder_name',
        'joint_holder_2_name',
        'joint_holder_3_name',
        'account_number',
        'aslaas_number',
        'monthly_amount',
        'payment_method',
        'cheque_number',
        'cheque_date',
        'cheque_bank',
        'total_deposited',
        'opening_date',
        'registered_phone',
        'half_month_period',
        'installments_paid',
        'last_paid_month',
        'note',
        'status',
        'created_by',
        'updated_by',
        'start_date',
        'duration_months',
        'maturity_date',
        'maturity_amount',
        'interest_rate',
        'interest_compounding',
        'is_complete',
        'completed_at',
        'completion_notes',
        'data_source',
        'nominee_name',
        'nominee_relation',
        'nominee_phone',
        'previous_post_office',
        'transfer_date',
        'premature_closure_date',
        'premature_closure_amount',
        'loan_availed',
        'loan_amount',
        'loan_date',
        'loan_repayment_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'maturity_date' => 'date',
        'last_paid_month' => 'date',
        'cheque_date' => 'date',
        'transfer_date' => 'date',
        'premature_closure_date' => 'date',
        'loan_date' => 'date',
        'loan_repayment_date' => 'date',
        'monthly_deposit_amount' => 'decimal:2',
        'maturity_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'premature_closure_amount' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'is_complete' => 'boolean',
        'loan_availed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the RD account.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the agent managing this RD account.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the payments for this RD account.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the collections for this RD account.
     */
    public function collections()
    {
        return $this->hasMany(Collection::class, 'rd_account_id');
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function creator()
    {
        return $this->belongsTo(Agent::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Agent::class, 'updated_by');
    }

    public function calculateMaturityAmount()
    {
        // Basic calculation (can be enhanced based on specific requirements)
        return $this->monthly_deposit_amount * $this->tenure_months * (1 + ($this->interest_rate / 100));
    }

    /**
     * Calculate the number of defaulted months based on expected vs paid installments
     */
    public function getMissedMonthsAttribute(): int
    {
        // Calculate months elapsed from start date to current date
        $startDate = $this->start_date ? Carbon::parse($this->start_date) : Carbon::parse($this->created_at);
        $currentDate = Carbon::now();

        // Calculate total months elapsed
        $monthsElapsed = $startDate->diffInMonths($currentDate);

        // Get installments paid (default to 0 if null)
        $installmentsPaid = $this->installments_paid ?? 0;

        // Calculate missed months = expected installments - paid installments
        $missedMonths = max(0, $monthsElapsed - $installmentsPaid);

        return $missedMonths;
    }

    /**
     * Calculate penalty per month (Re. 1 for Rs. 100 denomination, proportional for other amounts)
     */
    public function getPenaltyPerMonthAttribute(): float
    {
        $monthlyAmount = $this->monthly_amount ?? 0;
        // Re. 1 penalty for Rs. 100, proportional for other amounts
        return ($monthlyAmount / 100) * 1.00;
    }

    /**
     * Calculate total penalty amount
     */
    public function getTotalPenaltyAttribute(): float
    {
        return $this->penalty_per_month * $this->missed_months;
    }

    /**
     * Calculate total payable amount (missed deposits + penalties - rebate)
     */
    public function getTotalPayableAttribute(): float
    {
        $missedDeposits = ($this->monthly_amount ?? 0) * $this->missed_months;
        $totalAmount = $missedDeposits + $this->total_penalty;

        // Apply rebate if applicable
        $rebateAmount = $this->calculateRebate();
        return $totalAmount - $rebateAmount;
    }

    /**
     * Calculate rebate amount based on paid installments
     * Rs. 10 for 6 months, Rs. 40 for 12 months
     */
    public function calculateRebate(): float
    {
        $installmentsPaid = $this->installments_paid ?? 0;
        
        // Apply rebate based on the new scheme
        if ($installmentsPaid >= 12) {
            return 40.00; // Rs. 40 for 12 months
        } elseif ($installmentsPaid >= 6) {
            return 10.00; // Rs. 10 for 6 months
        }
        
        return 0.00;
    }
    
    /**
     * Calculate total amount with rebate applied
     */
    public function getTotalAmountWithRebateAttribute(): float
    {
        $totalDeposits = ($this->total_deposited ?? 0);
        $rebateAmount = $this->calculateRebate();
        
        return $totalDeposits - $rebateAmount;
    }
    
    /**
     * Get rebate information
     */
    public function getRebateInfoAttribute(): array
    {
        $installmentsPaid = $this->installments_paid ?? 0;
        $rebateAmount = $this->calculateRebate();
        
        // Calculate next rebate milestone
        $nextRebateMilestone = $installmentsPaid < 6 ? 6 : 12;
        $nextRebateAmount = $installmentsPaid < 6 ? 10.00 : 40.00;
        $rebateRemaining = max(0, $nextRebateMilestone - $installmentsPaid);
        
        return [
            'installments_paid' => $installmentsPaid,
            'rebate_amount' => $rebateAmount,
            'rebate_applied' => $rebateAmount > 0,
            'rebate_threshold' => 6, // Fixed threshold for display
            'next_rebate_milestone' => $nextRebateMilestone,
            'next_rebate_amount' => $nextRebateAmount,
            'rebate_remaining' => $rebateRemaining,
            'total_amount_with_rebate' => $this->total_amount_with_rebate
        ];
    }

    /**
     * Check if account should be marked as discontinued (4+ defaults)
     */
    public function getIsDiscontinuedAttribute(): bool
    {
        return $this->missed_months >= 4;
    }

    /**
     * Get computed status considering discontinuation
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->is_discontinued) {
            return 'discontinued';
        }
        return $this->status;
    }

    /**
     * Check if RD account information is complete
     */
    public function isInfoComplete(): bool
    {
        return !empty($this->account_number) &&
            !empty($this->aslaas_number) &&
            $this->aslaas_number !== 'APPLIED' &&
            !empty($this->monthly_amount) &&
            !empty($this->duration_months) &&
            !empty($this->start_date) &&
            !empty($this->maturity_date) &&
            !empty($this->registered_phone) &&
            !str_starts_with($this->registered_phone, '999');
    }

    /**
     * Mark RD account as complete
     */
    public function markAsComplete(?string $notes = null): bool
    {
        return $this->update([
            'is_complete' => true,
            'completed_at' => now(),
            'completion_notes' => $notes,
        ]);
    }

    /**
     * Mark RD account as incomplete
     */
    public function markAsIncomplete(?string $notes = null): bool
    {
        return $this->update([
            'is_complete' => false,
            'completed_at' => null,
            'completion_notes' => $notes,
        ]);
    }

    /**
     * Get completion status for display
     */
    public function getCompletionStatusAttribute(): string
    {
        if ($this->is_complete) {
            return 'complete';
        }

        if ($this->isInfoComplete()) {
            return 'auto_complete';
        }

        return 'incomplete';
    }

    /**
     * Scope for incomplete RD accounts
     */
    public function scopeIncomplete($query)
    {
        return $query->where(function ($q) {
            $q->where('is_complete', false);
        });
    }

    /**
     * Scope for complete RD accounts
     */
    public function scopeComplete($query)
    {
        return $query->where('is_complete', true);
    }

    /**
     * Scope for RD accounts imported from Excel
     */
    public function scopeFromExcel($query)
    {
        return $query->where('data_source', 'excel_import');
    }

    /**
     * Validate monthly amount meets minimum requirement (Rs. 100 and multiples of Rs. 10)
     */
    public function validateMonthlyAmount(): bool
    {
        $monthlyAmount = $this->monthly_amount ?? 0;
        return $monthlyAmount >= 100 && $monthlyAmount % 10 === 0;
    }

    /**
     * Check if account is eligible for premature closure (after 3 years)
     */
    public function isEligibleForPrematureClosure(): bool
    {
        if (!$this->start_date) {
            return false;
        }

        $startDate = Carbon::parse($this->start_date);
        $threeYearsAgo = Carbon::now()->subYears(3);
        
        return $startDate <= $threeYearsAgo;
    }

    /**
     * Check if account is eligible for loan (after 12 installments and 1 year)
     */
    public function isEligibleForLoan(): bool
    {
        if (!$this->start_date || $this->installments_paid < 12) {
            return false;
        }

        $startDate = Carbon::parse($this->start_date);
        $oneYearAgo = Carbon::now()->subYear();
        
        return $startDate <= $oneYearAgo;
    }

    /**
     * Calculate maximum loan amount (50% of balance credit)
     */
    public function getMaxLoanAmountAttribute(): float
    {
        if (!$this->isEligibleForLoan()) {
            return 0.00;
        }

        $balanceCredit = $this->total_deposited ?? 0;
        return $balanceCredit * 0.5; // 50% of balance
    }

    /**
     * Get all joint holders including primary account holder
     */
    public function getAllJointHoldersAttribute(): array
    {
        $holders = [$this->customer->name ?? 'Primary Holder'];
        
        if ($this->joint_holder_name) {
            $holders[] = $this->joint_holder_name;
        }
        if ($this->joint_holder_2_name) {
            $holders[] = $this->joint_holder_2_name;
        }
        if ($this->joint_holder_3_name) {
            $holders[] = $this->joint_holder_3_name;
        }

        return $holders;
    }

    /**
     * Check if account has nomination facility
     */
    public function hasNomination(): bool
    {
        return !empty($this->nominee_name);
    }

    /**
     * Check if account was transferred from another post office
     */
    public function isTransferred(): bool
    {
        return !empty($this->previous_post_office);
    }

    /**
     * Check if account has availed loan facility
     */
    public function hasLoan(): bool
    {
        return $this->loan_availed && !empty($this->loan_amount);
    }
}
