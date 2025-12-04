<?php
// app/Models/MonthlyBillingSummary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyBillingSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_month',
        'display_month',
        'total_customers',
        'total_amount',
        'received_amount',
        'due_amount',
        'status',
        'notes',
        'is_locked',
        'created_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'is_locked' => 'boolean',
        'total_customers' => 'integer'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}