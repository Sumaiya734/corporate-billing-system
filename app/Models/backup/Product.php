<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'p_id';

    protected $fillable = [
        'name',
        'product_type_id', // Changed back to product_type_id
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

    protected $appends = [
        'product_type',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_to_products', 'p_id', 'c_id')
                    ->withPivot(
                        'product_price',
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

    // Helper methods to check product type
    public function isRegular(): bool
    {
        return $this->type && $this->type->name === 'regular';
    }

    public function isSpecial(): bool
    {
        return $this->type && $this->type->name === 'special';
    }

    // Accessor for product type name
    public function getProductTypeAttribute(): string
    {
        return $this->type ? $this->type->name : '';
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

    public function getCustomerCount(): int
    {
        return $this->activeCustomers()->count();
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->customers()->sum('customer_to_products.total_amount');
    }
}