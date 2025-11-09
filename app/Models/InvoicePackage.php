<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InvoicePackage extends Pivot
{
    protected $table = 'customer_to_packages';

    protected $primaryKey = 'cp_id'; // Add this line for the primary key
    
    public $incrementing = true; // Add this if cp_id is auto-incrementing

    protected $fillable = [
        'c_id',
        'p_id',
        'package_price',
        'assign_date',
        'billing_cycle_months',
        'total_amount',
        'status',
        'is_active'
    ];

    protected $casts = [
        'assign_date' => 'date',
        'package_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_cycle_months' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'c_id', 'c_id');
    }

    /**
     * Relationship with Package
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'p_id', 'p_id');
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Check if package is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '৳' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted package price
     */
    public function getFormattedPackagePriceAttribute(): string
    {
        return '৳' . number_format($this->package_price, 2);
    }

    /**
     * Get billing cycle text
     */
    public function getBillingCycleTextAttribute(): string
    {
        if ($this->billing_cycle_months === 1) {
            return 'Monthly';
        } elseif ($this->billing_cycle_months === 3) {
            return 'Quarterly';
        } elseif ($this->billing_cycle_months === 6) {
            return 'Half-Yearly';
        } elseif ($this->billing_cycle_months === 12) {
            return 'Annual';
        } else {
            return $this->billing_cycle_months . ' Months';
        }
    }
}