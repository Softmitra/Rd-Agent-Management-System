<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'phone',
        'agent_id',
        'aadhar_number',
        'pan_number',
        'aadhar_file',
        'pan_file',
        'photo',
        'contact_info',
        'address',
        'date_of_birth',
        'cif_id',
        'savings_account_no',
        'has_savings_account',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contact_info' => 'array',
        'date_of_birth' => 'datetime',
        'has_savings_account' => 'boolean',
    ];

    /**
     * Get the agent that manages this customer.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the RD accounts for the customer.
     */
    public function rdAccounts(): HasMany
    {
        return $this->hasMany(RDAccount::class);
    }

    /**
     * Get the payments made by this customer.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
