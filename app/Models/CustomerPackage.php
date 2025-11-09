<?php
// app/Models/CustomerPackage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CustomerPackage extends Model
{
    use HasFactory;

    protected $primaryKey = 'cp_id';
    protected $table = 'customer_to_packages';

    protected $fillable = [
        'c_id',
        'p_id',
        'assign_date',
        'billing_cycle_months',
        'status',
        'is_active',
    ];

    protected $casts = [
        'assign_date' => 'date',
        'due_date' => 'date',
        'billing_cycle_months' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_total_amount',
        'formatted_monthly_price',
        'billing_cycle_text',
        'is_expired',
        'days_until_due',
        'is_due_soon',
    ];

    // ==================== RELATIONSHIPS ====================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'c_id', 'c_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'p_id', 'p_id');
    }

    // ==================== ACCESSORS ====================

    public function getPackagePriceAttribute(): float
    {
        return $this->package ? (float) $this->package->monthly_price : 0.0;
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->package_price * $this->billing_cycle_months;
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return '৳' . number_format($this->total_amount, 2);
    }

    public function getFormattedMonthlyPriceAttribute(): string
    {
        return '৳' . number_format($this->package_price, 2);
    }

    public function getBillingCycleTextAttribute(): string
    {
        return match ($this->billing_cycle_months) {
            1 => 'Monthly',
            3 => 'Quarterly',
            6 => 'Half-Yearly',
            12 => 'Annual',
            default => $this->billing_cycle_months . ' Month(s)'
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->due_date && Carbon::parse($this->due_date)->isPast();
    }

    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }
        
        $days = Carbon::parse($this->due_date)->diffInDays(now(), false);
        return $days <= 0 ? abs($days) : 0;
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->isActive() && $this->days_until_due <= 7;
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return '<span class="badge bg-secondary">Inactive</span>';
        }

        return match ($this->status) {
            'active' => $this->is_expired 
                ? '<span class="badge bg-warning">Overdue</span>'
                : ($this->is_due_soon
                    ? '<span class="badge bg-info">Due Soon</span>'
                    : '<span class="badge bg-success">Active</span>'),
            'pending' => '<span class="badge bg-info">Pending</span>',
            'expired' => '<span class="badge bg-danger">Expired</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>'
        };
    }

    public function getPackageNameAttribute(): string
    {
        return $this->package ? $this->package->name : 'Unknown Package';
    }

    public function getPackageTypeAttribute(): string
    {
        return $this->package ? $this->package->package_type : 'unknown';
    }

    // ==================== METHODS ====================

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_active;
    }

    public function isExpired(): bool
    {
        return $this->is_expired;
    }

    public function activate(): bool
    {
        return $this->update([
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function deactivate(): bool
    {
        return $this->update([
            'status' => 'expired',
            'is_active' => false,
        ]);
    }

    // Add scope for active packages
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', 1);
    }
}