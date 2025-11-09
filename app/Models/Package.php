<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';
    protected $primaryKey = 'p_id';

    protected $fillable = [
        'name',
        'package_type',
        'description',
        'monthly_price',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_to_packages', 'p_id', 'c_id')
                    ->withPivot(
                        'package_price',
                        'assign_date',
                        'total_amount',
                        'status',
                        'is_active'
                    )
                    ->withTimestamps();
    }

    public function activeCustomers(): BelongsToMany
    {
        return $this->customers()
                    ->wherePivot('status', 'active')
                    ->wherePivot('is_active', 1);
    }

    public function scopeRegular($query)
    {
        return $query->where('package_type', 'regular');
    }

    public function scopeSpecial($query)
    {
        return $query->where('package_type', 'special');
    }

    public function getFormattedPriceAttribute(): string
    {
        return '৳' . number_format((float) $this->monthly_price, 2);
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->monthly_price;
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return $this->formatted_price;
    }

    public function isRegular(): bool
    {
        return $this->package_type === 'regular';
    }

    public function isSpecial(): bool
    {
        return $this->package_type === 'special';
    }

    public function getCustomerCount(): int
    {
        return $this->activeCustomers()->count();
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->customers()->sum('customer_to_packages.total_amount');
    }
}