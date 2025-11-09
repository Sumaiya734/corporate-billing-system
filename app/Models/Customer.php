<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'has_regular_package',
        'has_special_packages',
        'has_due_payments',
        'status_badge',
        'latest_invoice',
        'package_info',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FIXED: Use HasMany through CustomerToPackage instead of BelongsToMany
     */
    public function customerPackages(): HasMany
    {
        return $this->hasMany(CustomerPackage::class, 'c_id', 'c_id');
    }

    /**
     * FIXED: Get packages through customerPackages relationship
     */
    public function packages()
    {
        return $this->hasManyThrough(
            Package::class,
            CustomerPackage::class,
            'c_id', // Foreign key on customer_to_packages table
            'p_id', // Foreign key on packages table
            'c_id', // Local key on customers table
            'p_id'  // Local key on customer_to_packages table
        );
    }

    /**
     * FIXED: Active customer packages
     */
    public function activeCustomerPackages(): HasMany
    {
        return $this->customerPackages()
            ->where('status', 'active')
            ->where('is_active', true);
    }

    /**
     * FIXED: Active packages through active customer packages
     */
    public function activePackages()
    {
        return $this->hasManyThrough(
            Package::class,
            CustomerPackage::class,
            'c_id',
            'p_id',
            'c_id',
            'p_id'
        )->where('customer_to_packages.status', 'active')
         ->where('customer_to_packages.is_active', true);
    }

    /**
     * FIXED: Regular package
     */
    public function regularPackage(): ?Package
    {
        return $this->activePackages()
            ->where('package_type', 'regular')
            ->first();
    }

    /**
     * FIXED: Special packages
     */
    public function specialPackages()
    {
        return $this->activePackages()
            ->where('package_type', 'special')
            ->get();
    }

    public function mainPackage(): ?Package
    {
        return $this->regularPackage();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'c_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'c_id');
    }

    public function unpaidInvoices(): HasMany
    {
        return $this->invoices()->whereIn('status', ['unpaid', 'partial']);
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
            $q->whereIn('status', ['unpaid', 'partial'])
              ->whereRaw('total_amount > COALESCE(received_amount, 0)');
        });
    }

    /**
     * FIXED: Scope for customers with specific package
     */
    public function scopeWithPackage(Builder $query, int $packageId): Builder
    {
        return $query->whereHas('customerPackages', function ($q) use ($packageId) {
            $q->where('p_id', $packageId)
              ->where('status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * FIXED: Scope for customers with regular package
     */
    public function scopeWithRegularPackage(Builder $query): Builder
    {
        return $query->whereHas('customerPackages.package', function ($q) {
            $q->where('package_type', 'regular');
        })->whereHas('customerPackages', function ($q) {
            $q->where('status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * FIXED: Scope for customers with special packages
     */
    public function scopeWithSpecialPackages(Builder $query): Builder
    {
        return $query->whereHas('customerPackages.package', function ($q) {
            $q->where('package_type', 'special');
        })->whereHas('customerPackages', function ($q) {
            $q->where('status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * FIXED: Scope for customers with no packages
     */
    public function scopeWithNoPackage(Builder $query): Builder
    {
        return $query->whereDoesntHave('customerPackages', function ($q) {
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
        return $this->activeCustomerPackages->sum(function ($customerPackage) {
            return $customerPackage->package->monthly_price ?? 0;
        });
    }

    public function getTotalDueAttribute(): float
    {
        return (float) $this->unpaidInvoices()
            ->sum(DB::raw('total_amount - COALESCE(received_amount, 0)'));
    }

    /**
     * FIXED: Check if has regular package
     */
    public function getHasRegularPackageAttribute(): bool
    {
        return $this->activeCustomerPackages()
            ->whereHas('package', function ($q) {
                $q->where('package_type', 'regular');
            })
            ->exists();
    }

    /**
     * FIXED: Check if has special packages
     */
    public function getHasSpecialPackagesAttribute(): bool
    {
        return $this->activeCustomerPackages()
            ->whereHas('package', function ($q) {
                $q->where('package_type', 'special');
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
     * FIXED: Package info accessor
     */
    public function getPackageInfoAttribute(): string
    {
        $regularPackage = $this->activeCustomerPackages()
            ->whereHas('package', function ($q) {
                $q->where('package_type', 'regular');
            })
            ->with('package')
            ->first();

        if (!$regularPackage) {
            return 'No Package Assigned';
        }

        $specialCount = $this->activeCustomerPackages()
            ->whereHas('package', function ($q) {
                $q->where('package_type', 'special');
            })
            ->count();
        
        return $specialCount > 0 
            ? "{$regularPackage->package->name} + {$specialCount} add-on(s)" 
            : $regularPackage->package->name;
    }

    // ==================== METHODS ====================

    /**
     * FIXED: Assign package method
     */
    public function assignPackage(
        int $packageId,
        int $billingCycleMonths = 1,
        string $status = 'active'
    ): bool {
        // Check if package already assigned and active
        if ($this->hasActivePackage($packageId)) {
            return false;
        }

        CustomerPackage::create([
            'c_id' => $this->c_id,
            'p_id' => $packageId,
            'assign_date' => now()->toDateString(),
            'billing_cycle_months' => $billingCycleMonths,
            'status' => $status,
            'is_active' => true,
        ]);

        return true;
    }

    public function assignPackages(array $packageData): void
    {
        foreach ($packageData as $data) {
            $this->assignPackage(
                $data['package_id'],
                $data['billing_cycle_months'] ?? 1,
                $data['status'] ?? 'active'
            );
        }
    }

    /**
     * FIXED: Update package status
     */
    public function updatePackageStatus(int $cpId, string $status): bool
    {
        return $this->customerPackages()
            ->where('cp_id', $cpId)
            ->update(['status' => $status]) > 0;
    }

    /**
     * FIXED: Deactivate package
     */
    public function deactivatePackage(int $cpId): bool
    {
        return $this->customerPackages()
            ->where('cp_id', $cpId)
            ->update(['is_active' => false]) > 0;
    }

    /**
     * FIXED: Check if has package
     */
    public function hasPackage(int $packageId): bool
    {
        return $this->customerPackages()
            ->where('p_id', $packageId)
            ->exists();
    }

    /**
     * FIXED: Check if has active package
     */
    public function hasActivePackage(int $packageId): bool
    {
        return $this->customerPackages()
            ->where('p_id', $packageId)
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
     * FIXED: Get active package count
     */
    public function getActivePackageCount(): int
    {
        return $this->activeCustomerPackages()->count();
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
            'customerPackages' => function ($query) {
                $query->where('status', 'active')
                      ->where('is_active', true)
                      ->with('package');
            },
            'invoices' => function ($query) {
                $query->latest()->limit(5);
            },
            'user'
        ]);
    }
}