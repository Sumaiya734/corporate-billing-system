<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'cp_id', // Links to customer_to_products (each invoice is for one product)
        'issue_date',
        'previous_due',
        'service_charge',
        'vat_percentage',
        'vat_amount',
        'subtotal',
        'total_amount',
        'received_amount',
        'next_due',
        'status',
        'is_closed',
        'closed_at',
        'closed_by',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'previous_due' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'next_due' => 'decimal:2',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'due_amount',
        'is_overdue',
        'payment_status',
        'formatted_total_amount',
        'formatted_received_amount',
        'formatted_due_amount',
        'days_overdue',
        'payment_progress',
    ];

    // Invoice status constants
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_CANCELLED = 'cancelled';

    // ==================== RELATIONSHIPS ====================

    // FIXED: Updated customer relationship to correctly reference customer_to_products
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProduct::class, 'cp_id', 'cp_id')
            ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id');
    }

    // FIXED: Added relationship to customer_to_products
    public function customerProduct(): BelongsTo
    {
        return $this->belongsTo(CustomerProduct::class, 'cp_id', 'cp_id');
    }

    public function invoiceProducts(): HasMany
    {
        return $this->hasMany(InvoiceProduct::class, 'invoice_id', 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // ==================== SCOPES ====================

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL])
                    ->where('total_amount', '>', DB::raw('COALESCE(received_amount, 0)'));
    }

    // FIXED: Updated scope to use cp_id
    public function scopeByCustomer(Builder $query, int $customerId): Builder
    {
        return $query->whereHas('customerProduct', function ($q) use ($customerId) {
            $q->where('c_id', $customerId);
        });
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeIssuedBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    public function scopeDueBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate])
                    ->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL]);
    }

    public function scopeWithDueAmount(Builder $query): Builder
    {
        return $query->whereRaw('total_amount > COALESCE(received_amount, 0)');
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('issue_date', '>=', now()->subDays($days));
    }

    // ==================== ACCESSORS ====================

    public function getDueAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->received_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_UNPAID, self::STATUS_PARTIAL]) 
            && $this->due_amount > 0;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->status === self::STATUS_PAID) {
            return 'Paid';
        } elseif ($this->status === self::STATUS_CANCELLED) {
            return 'Cancelled';
        } elseif ($this->received_amount > 0) {
            return 'Partial';
        } else {
            return 'Unpaid';
        }
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return '৳' . number_format((float) $this->total_amount, 2);
    }

    public function getFormattedReceivedAmountAttribute(): string
    {
        return '৳' . number_format((float) $this->received_amount, 2);
    }

    public function getFormattedDueAmountAttribute(): string
    {
        return '৳' . number_format($this->due_amount, 2);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return Carbon::parse($this->issue_date)->diffInDays(now());
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return ($this->received_amount / $this->total_amount) * 100;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => '<span class="badge bg-success">Paid</span>',
            self::STATUS_PARTIAL => '<span class="badge bg-warning">Partial</span>',
            self::STATUS_UNPAID => $this->days_overdue > 30 
                ? '<span class="badge bg-danger">Overdue</span>'
                : '<span class="badge bg-info">Unpaid</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-secondary">Cancelled</span>',
            'confirmed' => '<span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Confirmed</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>'
        };
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->customer ? $this->customer->name : 'Unknown Customer';
    }

    // ==================== METHODS ====================

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isUnpaid(): bool
    {
        return $this->status === self::STATUS_UNPAID;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => self::STATUS_PAID,
            'received_amount' => $this->total_amount,
            'next_due' => 0,
        ]);
    }

    public function addPayment(float $amount, string $method = 'cash', string $transactionId = null): Payment
    {
        $payment = $this->payments()->create([
            'c_id' => $this->customerProduct->c_id ?? null,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'transaction_id' => $transactionId,
        ]);

        $this->refresh();

        $newReceivedAmount = $this->received_amount + $amount;
        $newStatus = $this->calculateStatus($newReceivedAmount);

        $this->update([
            'received_amount' => $newReceivedAmount,
            'next_due' => max(0, $this->total_amount - $newReceivedAmount),
            'status' => $newStatus,
        ]);

        return $payment;
    }

    public function calculateStatus($receivedAmount): string
    {
        // Normalize incoming value to float (handles null, decimal string, etc.)
        $receivedAmount = (float) ($receivedAmount ?? 0);

        if ($receivedAmount >= (float) $this->total_amount) {
            return self::STATUS_PAID;
        } elseif ($receivedAmount > 0.0) {
            return self::STATUS_PARTIAL;
        } else {
            return self::STATUS_UNPAID;
        }
    }

    public function cancelInvoice(): bool
    {
        // Refund any payments if needed
        if ($this->received_amount > 0) {
            // Handle refund logic here
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'next_due' => 0,
        ]);
    }

    public function getProductNames(): string
    {
        return $this->invoiceProducts->map(function ($invoiceProduct) {
            return $invoiceProduct->product->name ?? 'Unknown Product';
        })->implode(', ');
    }

    public function getTotalProductAmount(): float
    {
        return $this->invoiceProducts->sum('total_product_amount');
    }

    public function recalculateTotals(): bool
    {
        $productTotal = $this->getTotalProductAmount();
        $subtotal = $productTotal + $this->previous_due + $this->service_charge;
        $vatAmount = $subtotal * ($this->vat_percentage / 100);
        $totalAmount = $subtotal + $vatAmount;

        return $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'next_due' => max(0, $totalAmount - $this->received_amount),
            'status' => $this->calculateStatus($this->received_amount),
        ]);
    }

    // ==================== STATIC METHODS ====================

    public static function generateInvoiceNumber($issueDate = null): string
    {
        // Use provided date or current date
        $date = $issueDate ? \Carbon\Carbon::parse($issueDate) : \Carbon\Carbon::now();
        
        // Format: INV-YY-MM-XXXX (e.g., INV-25-01-0001)
        $year = $date->format('y'); // 2-digit year
        $month = $date->format('m'); // 2-digit month
        $prefix = 'INV-' . $year . '-' . $month . '-';
        
        // Find the highest invoice number for this month
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            // Extract the numeric part and increment
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            // Start from 1 for this month
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getTotalRevenue(): float
    {
        return (float) self::where('status', '!=', self::STATUS_CANCELLED)
                          ->sum('total_amount');
    }

    public static function getTotalCollected(): float
    {
        return (float) self::where('status', '!=', self::STATUS_CANCELLED)
                          ->sum('received_amount');
    }

    public static function getTotalDue(): float
    {
        return (float) self::whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL])
                          ->sum(DB::raw('total_amount - COALESCE(received_amount, 0)'));
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Set issue_date first if not set
            if (empty($invoice->issue_date)) {
                $invoice->issue_date = now()->toDateString();
            }
            
            // Generate invoice number based on issue_date
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->issue_date);
            }

            // Initialize amounts if not set
            $invoice->received_amount = $invoice->received_amount ?? 0.00;
            $invoice->next_due = $invoice->next_due ?? $invoice->total_amount ?? 0.00;
        });

        static::created(function ($invoice) {
            // Auto-set status based on received amount
            if ($invoice->status === null) {
                $invoice->update([
                    'status' => $invoice->calculateStatus($invoice->received_amount)
                ]);
            }
        });
    }
}