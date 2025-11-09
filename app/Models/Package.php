<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Package extends Model
{
    use HasFactory;

    // Table & Primary Key
    protected $table = 'packages';
    protected $primaryKey = 'p_id';

    // Mass Assignment
    protected $fillable = [
        'name',
        'package_type',
        'description',
        'monthly_price',
    ];

    // Casts
    protected $casts = [
        'monthly_price' => 'decimal:2',
    ];

    // Append accessors to JSON/array output
    protected $appends = [
        'formatted_price',
        'formatted_total_price',
    ];

    // -----------------------------------------------------------------
    // RELATIONSHIPS
    // -----------------------------------------------------------------

    /**
     * All customers linked to this package (via pivot table)
     */
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

    /**
     * Only active customers (status = active AND is_active = 1)
     */
    public function activeCustomers(): BelongsToMany
    {
        return $this->customers()
                    ->wherePivot('status', 'active')
                    ->wherePivot('is_active', 1);
    }

    // -----------------------------------------------------------------
    // SCOPES
    // -----------------------------------------------------------------

    public function scopeRegular($query)
    {
        return $query->where('package_type', 'regular');
    }

    public function scopeSpecial($query)
    {
        return $query->where('package_type', 'special');
    }

    // -----------------------------------------------------------------
    // ACCESSORS
    // -----------------------------------------------------------------

    /**
     * ৳1,200.00
     */
    public function getFormattedPriceAttribute(): string
    {
        return '৳' . number_format((float) $this->monthly_price, 2);
    }

    /**
     * Raw float: 1200.00
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->monthly_price;
    }

    /**
     * Same as formatted_price (kept for backward compatibility)
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return $this->formatted_price;
    }

    // -----------------------------------------------------------------
    // HELPER METHODS
    // -----------------------------------------------------------------

    public function isRegular(): bool
    {
        return $this->package_type === 'regular';
    }

    public function isSpecial(): bool
    {
        return $this->package_type === 'special';
    }

    /**
     * Count of active customers
     */
    public function getCustomerCount(): int
    {
        return $this->activeCustomers()->count();
    }

    /**
     * Total revenue from all assigned packages (sum of pivot total_amount)
     */
    public function getTotalRevenue(): float
    {
        return (float) $this->customers()->sum('customer_to_packages.total_amount');
    }
}