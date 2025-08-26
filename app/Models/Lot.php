<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_reference_number',
        'lot_date',
        'lot_description',
        'agent_id',
        'total_accounts',
        'total_amount',
        'commission_percentage',
        'commission_amount',
        'status',
        'import_file_name',
        'import_errors',
        'notes',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'lot_date' => 'date',
        'total_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'import_errors' => 'array',
        'verified_at' => 'datetime'
    ];

    /**
     * Get the agent that owns this lot.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the collections assigned to this lot.
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get the user who verified this lot.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Calculate and update commission amount.
     */
    public function calculateCommission(): void
    {
        $this->commission_amount = ($this->total_amount * $this->commission_percentage) / 100;
        $this->save();
    }

    /**
     * Update lot totals based on assigned collections.
     */
    public function updateTotals(): void
    {
        $collections = $this->collections();
        $this->total_accounts = $collections->count();
        $this->total_amount = $collections->sum('amount');
        $this->calculateCommission();
    }

    /**
     * Check if lot can be modified.
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['draft', 'processing']);
    }

    /**
     * Get lot status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'verified' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Scope for lots belonging to a specific agent.
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope for lots within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('lot_date', [$startDate, $endDate]);
    }
}
