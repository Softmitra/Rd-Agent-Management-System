<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'payment_date',
        'payment_type', 
        'amount',
        'note',
        'agent_id',
        'customer_id',
        'rd_account_id',
        'lot_id',
        'lot_status',
        'months_paid',
        'receipt_number',
        'status'
    ];

    protected $dates = [
        'date',
        'payment_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the agent that made this collection.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the customer for this collection.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the RD account for this collection.
     */
    public function rdAccount(): BelongsTo
    {
        return $this->belongsTo(RdAccount::class, 'rd_account_id');
    }

    /**
     * Get the lot this collection is assigned to.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Scope for collections not assigned to any lot.
     */
    public function scopeNotInLot($query)
    {
        return $query->where('lot_status', 'not_in_lot');
    }

    /**
     * Scope for collections assigned to a lot.
     */
    public function scopeAssignedToLot($query)
    {
        return $query->where('lot_status', 'assigned_to_lot');
    }

    /**
     * Scope for collections by agent.
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Assign this collection to a lot.
     */
    public function assignToLot(Lot $lot): bool
    {
        if ($this->lot_status !== 'not_in_lot') {
            return false;
        }

        $this->update([
            'lot_id' => $lot->id,
            'lot_status' => 'assigned_to_lot'
        ]);

        return true;
    }

    /**
     * Remove this collection from its lot.
     */
    public function removeFromLot(): bool
    {
        if ($this->lot_status === 'not_in_lot') {
            return false;
        }

        $this->update([
            'lot_id' => null,
            'lot_status' => 'not_in_lot'
        ]);

        return true;
    }

    /**
     * Get lot status badge color.
     */
    public function getLotStatusBadgeColor(): string
    {
        return match($this->lot_status) {
            'not_in_lot' => 'bg-gray-100 text-gray-800',
            'assigned_to_lot' => 'bg-blue-100 text-blue-800',
            'processed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
