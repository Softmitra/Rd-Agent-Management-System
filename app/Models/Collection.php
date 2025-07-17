<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'payment_type',
        'amount',
        'note',
        'agent_id',
        'customer_id',
        'status'
    ];

    public function agent()
    {
        return $this->belongsTo(\App\Models\Agent::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
