<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'account_number',
        'monthly_amount',
        'total_deposited',
        'opening_date',
        'registered_phone',
        'half_month_period',
        'installments_paid',
        'note',
        'status',
        'created_by',
        'updated_by',
        'start_date',
        'duration_months',
        'maturity_date',
        'maturity_amount',
        'interest_rate'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'maturity_date' => 'date',
        'monthly_deposit_amount' => 'decimal:2',
        'maturity_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
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
}
