<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status'
    ];

    protected $dates = [
        'date',
        'payment_date',
        'created_at',
        'updated_at'
    ];

    public function agent()
    {
        return $this->belongsTo(\App\Models\Agent::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function rdAccount()
    {
        return $this->belongsTo(\App\Models\RDAccount::class, 'customer_id', 'customer_id');
    }
}
