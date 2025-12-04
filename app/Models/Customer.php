<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'c_id';

    protected $fillable = [
        'user_id',
        'customer_id',
        'name',
        'email',
        'phone',
        'address',
        'connection_address',
        'id_type',
        'id_number',
        'is_active',
        'profile_picture',
        'id_card_front',
        'id_card_back',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'initial_letter',
        'full_address',
        'total_monthly_charge',
        'total_due',
        'has_regular_product',
        'has_special_products',
        'has_due_payments',
        'status_badge',
        'latest_invoice',
        'product_info',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FIXED: Use HasMany through CustomerToProduct instead of BelongsToMany
     */
    public function customerproducts(): HasMany
    {
        return $this->hasMany(Customerproduct::class, 'c_id', 'c_id');
    }

    /**
     * FIXED: Get products through customerproducts relationship
     */
    public function products()
    {
        return $this->hasManyThrough(
            product::class,
            Customerproduct::class,
            'c_id', // Foreign key on customer_to_products table
            'p_id', // Foreign key on products table
            'c_id', // Local key on customers table
            'p_id'  // Local key on customer_to_products table
        );
    }

    /**
     * FIXED: Active customer products
     */
    public function activeCustomerproducts(): HasMany
    {
        return $this->customerproducts()
            ->where('status', 'active')
            ->where('is_active', true);
    }

    /**
     * FIXED: Active products through active customer products
     */
    public function activeproducts()
    {
        return $this->hasManyThrough(
            product::class,
            Customerproduct::class,
            'c_id',
            'p_id',
            'c_id',
            'p_id'
        )->where('customer_to_products.status', 'active')
         ->where('customer_to_products.is_active', true);
    }

    /**
     * FIXED: Regular product
     */
    public function regularproduct(): ?product
    {
        return $this->activeproducts()
            ->whereHas('type', function($query) {
                $query->where('name', 'regular');
            })
            ->first();
    }

    /**
     * FIXED: Special products
     */
    public function specialproducts()
    {
        return $this->activeproducts()
            ->whereHas('type', function($query) {
                $query->where('name', 'special');
            })
            ->get();
    }

    public function mainproduct(): ?product
    {
        return $this->regularproduct();
    }

    // FIXED: Updated invoices relationship to use customer_to_products as intermediate table
    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            Customerproduct::class,
            'c_id',        // Foreign key on customer_to_products table
            'cp_id',       // Foreign key on invoices table
            'c_id',        // Local key on customers table
            'cp_id'        // Local key on customer_to_products table
        );
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,           // Final model
            CustomerProduct::class,   // Intermediate model
            'c_id',                   // Foreign key on customer_to_products table
            'cp_id',                  // Foreign key on payments table
            'c_id',                   // Local key on customers table
            'cp_id'                   // Local key on customer_to_products table
        );
    }

    public function unpaidInvoices(): HasManyThrough
    {
        return $this->invoices()->whereIn('invoices.status', ['unpaid', 'partial']);
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('customer_id', 'like', "%{$search}%");
        });
    }

    public function scopeWithDuePayments(Builder $query): Builder
    {
        return $query->whereHas('invoices', function ($q) {
            $q->whereIn('invoices.status', ['unpaid', 'partial'])
              ->whereRaw('invoices.total_amount > COALESCE(invoices.received_amount, 0)');
        });
    }

    /**
     * FIXED: Scope for customers with specific product
     */
    public function scopeWithproduct(Builder $query, int $productId): Builder
    {
        return $query->whereHas('customerproducts', function ($q) use ($productId) {
            $q->where('p_id', $productId)
              ->where('status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * FIXED: Scope for customers with regular product
     */
    public function scopeWithRegularproduct(Builder $query): Builder
    {
        return $query->whereHas('customerproducts.product', function ($q) {
            $q->whereHas('type', function($query) {
                $query->where('name', 'regular');
            });
        })->whereHas('customerproducts', function ($q) {
            $q->where('status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * FIXED: Scope for customers with special products
     */
    public function scopeWithSpecialproducts(Builder $query): Builder
    {
        return $query->whereHas('customerproducts.product', function ($q) {
            $q->whereHas('type', function($query) {
                $query->where('name', 'special');
            });
        })->whereHas('customerproducts', function ($q) {
            $q->where('status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * FIXED: Scope for customers with no products
     */
    public function scopeWithNoproduct(Builder $query): Builder
    {
        return $query->whereDoesntHave('customerproducts', function ($q) {
            $q->where('status', 'active')
              ->where('is_active', true);
        });
    }

    // ==================== ACCESSORS ====================

    public function getInitialLetterAttribute(): string
    {
        return strtoupper(substr(trim($this->name), 0, 1));
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->connection_address
        ]);

        return $parts ? implode(' | ', $parts) : 'No address provided';
    }

    /**
     * FIXED: Total monthly charge calculation
     */
    public function getTotalMonthlyChargeAttribute(): float
    {
        return $this->activeCustomerproducts->sum(function ($customerproduct) {
            return $customerproduct->product->monthly_price ?? 0;
        });
    }

    public function getTotalDueAttribute(): float
    {
        return (float) $this->unpaidInvoices()
            ->sum(DB::raw('total_amount - COALESCE(received_amount, 0)'));
    }

    /**
     * FIXED: Check if has regular product
     */
    public function getHasRegularproductAttribute(): bool
    {
        return $this->activeCustomerproducts()
            ->whereHas('product', function ($q) {
                $q->whereHas('type', function($query) {
                    $query->where('name', 'regular');
                });
            })
            ->exists();
    }

    /**
     * FIXED: Check if has special products
     */
    public function getHasSpecialproductsAttribute(): bool
    {
        return $this->activeCustomerproducts()
            ->whereHas('product', function ($q) {
                $q->whereHas('type', function($query) {
                    $query->where('name', 'special');
                });
            })
            ->exists();
    }

    public function getHasDuePaymentsAttribute(): bool
    {
        return $this->total_due > 0;
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return '<span class="badge bg-secondary">Inactive</span>';
        }

        return $this->has_due_payments 
            ? '<span class="badge bg-warning">Active (Due)</span>'
            : '<span class="badge bg-success">Active</span>';
    }

    public function getLatestInvoiceAttribute(): ?Invoice
    {
        return $this->invoices()->latest()->first();
    }

    /**
     * FIXED: product info accessor
     */
    public function getproductInfoAttribute(): string
    {
        $regularproduct = $this->activeCustomerproducts()
            ->whereHas('product', function ($q) {
                $q->whereHas('type', function($query) {
                    $query->where('name', 'regular');
                });
            })
            ->with('product')
            ->first();

        if (!$regularproduct) {
            return 'No product Assigned';
        }

        $specialCount = $this->activeCustomerproducts()
            ->whereHas('product', function ($q) {
                $q->whereHas('type', function($query) {
                    $query->where('name', 'special');
                });
            })
            ->count();
        
        return $specialCount > 0 
            ? "{$regularproduct->product->name} + {$specialCount} add-on(s)" 
            : $regularproduct->product->name;
    }

    // ==================== METHODS ====================

    /**
     * FIXED: Assign product method
     */
    public function assignproduct(
        int $productId,
        int $billingCycleMonths = 1,
        string $status = 'active'
    ): bool {
        // Check if product already assigned and active
        if ($this->hasActiveproduct($productId)) {
            return false;
        }

        Customerproduct::create([
            'c_id' => $this->c_id,
            'p_id' => $productId,
            'assign_date' => now()->toDateString(),
            'billing_cycle_months' => $billingCycleMonths,
            'status' => $status,
            'is_active' => true,
        ]);

        return true;
    }

    public function assignproducts(array $productData): void
    {
        foreach ($productData as $data) {
            $this->assignproduct(
                $data['product_id'],
                $data['billing_cycle_months'] ?? 1,
                $data['status'] ?? 'active'
            );
        }
    }

    /**
     * FIXED: Update product status
     */
    public function updateproductStatus(int $cpId, string $status): bool
    {
        return $this->customerproducts()
            ->where('cp_id', $cpId)
            ->update(['status' => $status]) > 0;
    }

    /**
     * FIXED: Deactivate product
     */
    public function deactivateproduct(int $cpId): bool
    {
        return $this->customerproducts()
            ->where('cp_id', $cpId)
            ->update(['is_active' => false]) > 0;
    }

    /**
     * FIXED: Check if has product
     */
    public function hasproduct(int $productId): bool
    {
        return $this->customerproducts()
            ->where('p_id', $productId)
            ->exists();
    }

    /**
     * FIXED: Check if has active product
     */
    public function hasActiveproduct(int $productId): bool
    {
        return $this->customerproducts()
            ->where('p_id', $productId)
            ->where('status', 'active')
            ->where('is_active', true)
            ->exists();
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function toggleActivation(): bool
    {
        return $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * FIXED: Get active product count
     */
    public function getActiveproductCount(): int
    {
        return $this->activeCustomerproducts()->count();
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->invoices()->sum('total_amount');
    }

    public function getTotalCollected(): float
    {
        return (float) $this->invoices()->sum('received_amount');
    }

    public function getPaymentEfficiency(): float
    {
        $total = $this->getTotalRevenue();
        
        return $total > 0 ? ($this->getTotalCollected() / $total) * 100 : 0;
    }

    // ==================== STATIC METHODS ====================

    public static function generateCustomerId(): string
    {
        $prefix = 'CUST' . date('y');

        do {
            $id = $prefix . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('customer_id', $id)->exists());

        return $id;
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_id)) {
                $customer->customer_id = self::generateCustomerId();
            }
        });

        static::created(function ($customer) {
            // Auto-create user account if email provided
            if (!$customer->user_id && $customer->email) {
                $user = User::firstOrCreate(
                    ['email' => $customer->email],
                    [
                        'name' => $customer->name,
                        'password' => bcrypt(Str::random(12)),
                        'role' => 'customer',
                    ]
                );
                
                $customer->updateQuietly(['user_id' => $user->id]);
            }
        });
    }

    // ==================== QUERY PERFORMANCE ====================

    /**
     * FIXED: Eager load common relationships
     */
    public function scopeWithCommonRelations(Builder $query): Builder
    {
        return $query->with([
            'customerproducts' => function ($query) {
                $query->where('status', 'active')
                      ->where('is_active', true)
                      ->with('product');
            },
            'invoices' => function ($query) {
                $query->latest()->limit(5);
            },
            'user'
        ]);
    }
}