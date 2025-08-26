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
        'is_complete',
        'completed_at',
        'completion_notes',
        'data_source',
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
        'is_complete' => 'boolean',
        'completed_at' => 'datetime',
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

    /**
     * Check if customer information is complete
     */
    public function isInfoComplete(): bool
    {
        return !empty($this->mobile_number) &&
               !str_starts_with($this->mobile_number, '999') &&
               !empty($this->address) &&
               !empty($this->name) &&
               !empty($this->cif_id);
    }

    /**
     * Mark customer as complete
     */
    public function markAsComplete(string $notes = null): bool
    {
        return $this->update([
            'is_complete' => true,
            'completed_at' => now(),
            'completion_notes' => $notes,
        ]);
    }

    /**
     * Mark customer as incomplete
     */
    public function markAsIncomplete(string $notes = null): bool
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
     * Scope for incomplete customers
     */
    public function scopeIncomplete($query)
    {
        return $query->where(function ($q) {
            $q->where('is_complete', false)
              ->orWhere(function ($subQ) {
                  $subQ->whereNull('mobile_number')
                       ->orWhere('mobile_number', 'like', '999%')
                       ->orWhereNull('address')
                       ->orWhere('address', '')
                       ->orWhereNull('cif_id')
                       ->orWhere('cif_id', '');
              });
        });
    }

    /**
     * Scope for complete customers
     */
    public function scopeComplete($query)
    {
        return $query->where('is_complete', true);
    }

    /**
     * Scope for customers imported from Excel
     */
    public function scopeFromExcel($query)
    {
        return $query->where('data_source', 'excel_import');
    }
}
