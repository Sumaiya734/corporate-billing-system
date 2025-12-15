<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\RollingBillingHelper;
class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'cp_id', // Links to customer_to_products (each invoice is for one product)
        'issue_date',
        'previous_due',
        'subtotal',
        'total_amount',
        'received_amount',
        'next_due',
        'status',
        'is_closed',
        'closed_at',
        'closed_by',
        'notes',
        'created_by',
        'is_active_rolling',
        'billing_cycle_number',
        'cycle_position',
        'cycle_start_date'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'previous_due' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'next_due' => 'decimal:2',
        'is_closed' => 'boolean',
        'is_active_rolling' => 'boolean',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cycle_start_date' => 'date'
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
        'is_fully_paid',
        'is_advance_payment',
        'advance_amount',
        'is_confirmed'
    ];

    // Invoice status constants
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_CONFIRMED = 'confirmed'; // ✅ User confirmed but due carried forward
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CONVERTED = 'converted';

    // Status options for dropdowns
    public static $statusOptions = [
        self::STATUS_UNPAID => 'Unpaid',
        self::STATUS_PARTIAL => 'Partial',
        self::STATUS_PAID => 'Paid',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_CONVERTED => 'Converted'
    ];

    // ==================== RELATIONSHIPS ====================

    public function customerProduct(): BelongsTo
    {
        return $this->belongsTo(CustomerProduct::class, 'cp_id', 'cp_id');
    }

    public function customer()
    {
        return $this->hasOneThrough(
            Customer::class,
            CustomerProduct::class,
            'cp_id', // Foreign key on customer_to_products table
            'c_id',  // Foreign key on customers table
            'cp_id', // Local key on invoices table
            'c_id'   // Local key on customer_to_products table
        );
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            CustomerProduct::class,
            'cp_id', // Foreign key on customer_to_products table
            'p_id',  // Foreign key on products table
            'cp_id', // Local key on invoices table
            'p_id'   // Local key on customer_to_products table
        );
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

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL, self::STATUS_CONFIRMED])
                    ->where('next_due', '>', 0)
                    ->where('issue_date', '<', now()->subDays(30));
    }

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
                    ->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL, self::STATUS_CONFIRMED]);
    }

    public function scopeWithDueAmount(Builder $query): Builder
    {
        return $query->where('next_due', '>', 0);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('issue_date', '>=', now()->subDays($days));
    }

    public function scopeNotConfirmed(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_CONFIRMED);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('is_closed', true);
    }

    public function scopeNotClosed(Builder $query): Builder
    {
        return $query->where('is_closed', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_CONVERTED]);
    }

    public function scopeBillingMonth(Builder $query): Builder
    {
        return $query->where('subtotal', '>', 0);
    }

    public function scopeCarryForward(Builder $query): Builder
    {
        return $query->where('subtotal', 0)->where('previous_due', '>', 0);
    }

    // ==================== ACCESSORS ====================

    public function getDueAmountAttribute(): float
    {
        return (float) max(0, $this->total_amount - $this->received_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!in_array($this->status, [self::STATUS_UNPAID, self::STATUS_PARTIAL, self::STATUS_CONFIRMED])) {
            return false;
        }
        
        if ($this->next_due <= 0) {
            return false;
        }
        
        // Consider overdue if unpaid for more than 30 days
        return Carbon::parse($this->issue_date)->diffInDays(now()) > 30;
    }

    public function getPaymentStatusAttribute(): string
    {
        return self::$statusOptions[$this->status] ?? ucfirst($this->status);
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return '৳' . number_format((float) $this->total_amount, 0);
    }

    public function getFormattedReceivedAmountAttribute(): string
    {
        return '৳' . number_format((float) $this->received_amount, 0);
    }

    public function getFormattedDueAmountAttribute(): string
    {
        return '৳' . number_format($this->due_amount, 0);
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

        return round(($this->received_amount / $this->total_amount) * 100, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badgeClass = 'badge ';
        $icon = '';
        $text = '';

        switch ($this->status) {
            case self::STATUS_PAID:
                $badgeClass .= 'bg-success';
                $icon = '<i class="fas fa-check-circle me-1"></i>';
                $text = 'Paid';
                break;
                
            case self::STATUS_PARTIAL:
                $badgeClass .= 'bg-warning text-dark';
                $icon = '<i class="fas fa-clock me-1"></i>';
                $text = 'Partial';
                break;
                
            case self::STATUS_CONFIRMED:
                $badgeClass .= 'bg-info';
                $icon = '<i class="fas fa-check-double me-1"></i>';
                $text = 'Confirmed';
                break;
                
            case self::STATUS_UNPAID:
                if ($this->is_overdue) {
                    $badgeClass .= 'bg-danger';
                    $icon = '<i class="fas fa-exclamation-triangle me-1"></i>';
                    $text = 'Overdue';
                } else {
                    $badgeClass .= 'bg-secondary';
                    $icon = '<i class="fas fa-clock me-1"></i>';
                    $text = 'Unpaid';
                }
                break;
                
            case self::STATUS_CANCELLED:
                $badgeClass .= 'bg-dark';
                $icon = '<i class="fas fa-times me-1"></i>';
                $text = 'Cancelled';
                break;
                
            case self::STATUS_CONVERTED:
                $badgeClass .= 'bg-purple';
                $icon = '<i class="fas fa-exchange-alt me-1"></i>';
                $text = 'Converted';
                break;
                
            default:
                $badgeClass .= 'bg-secondary';
                $text = ucfirst($this->status);
        }

        return '<span class="' . $badgeClass . '">' . $icon . $text . '</span>';
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->customer->name ?? 'Unknown Customer';
    }

    public function getProductNameAttribute(): string
    {
        return $this->product->name ?? 'Unknown Product';
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->status === self::STATUS_PAID || 
               ($this->next_due <= 0 && $this->received_amount >= $this->total_amount);
    }

    public function getIsAdvancePaymentAttribute(): bool
    {
        return $this->received_amount > $this->total_amount && $this->total_amount > 0;
    }

    public function getAdvanceAmountAttribute(): float
    {
        return $this->is_advance_payment ? ($this->received_amount - $this->total_amount) : 0;
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function getIsBillingMonthAttribute(): bool
    {
        return $this->subtotal > 0;
    }

    public function getIsCarryForwardMonthAttribute(): bool
    {
        return $this->subtotal == 0 && $this->previous_due > 0;
    }

    // ==================== METHODS ====================

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || $this->is_fully_paid;
    }

    public function isUnpaid(): bool
    {
        return $this->status === self::STATUS_UNPAID;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => self::STATUS_PAID,
            'received_amount' => $this->total_amount,
            'next_due' => 0,
            'is_closed' => true,
            'closed_at' => now(),
        ]);
    }

    public function markAsConfirmed(): bool
    {
        return $this->update([
            'status' => self::STATUS_CONFIRMED,
            'is_closed' => true,
            'closed_at' => now(),
            'notes' => ($this->notes ?? '') . "\n[Confirmed on: " . now()->format('Y-m-d H:i:s') . "]",
        ]);
    }

    public function addPayment(float $amount, string $method = 'cash', string $transactionId = null): Payment
    {
        $payment = Payment::create([
            'invoice_id' => $this->invoice_id,
            'c_id' => $this->customerProduct->c_id ?? null,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'transaction_id' => $transactionId,
            'collected_by' => Auth::id(),
            'status' => 'completed'
        ]);

        // Update invoice amounts
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
        $receivedAmount = (float) ($receivedAmount ?? 0);
        $totalAmount = (float) $this->total_amount;

        if ($receivedAmount >= $totalAmount) {
            return self::STATUS_PAID;
        } elseif ($receivedAmount > 0) {
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

    /**
     * Validate that the invoice amounts are mathematically consistent
     * Ensures: total_amount = subtotal + previous_due and next_due = total_amount - received_amount
     */
    public function validateAmounts(): bool
    {
        $expectedTotal = $this->subtotal + $this->previous_due;
        $expectedNextDue = max(0, $expectedTotal - $this->received_amount);
        
        $isTotalValid = abs($this->total_amount - $expectedTotal) < 0;
        $isNextDueValid = abs($this->next_due - $expectedNextDue) < 0;
        
        return $isTotalValid && $isNextDueValid;
    }

    /**
     * Fix any inconsistencies in the invoice amounts
     * Corrects: total_amount = subtotal + previous_due and next_due = total_amount - received_amount
     */
    public function fixAmounts(): bool
    {
        $correctTotal = $this->subtotal + $this->previous_due;
        $correctNextDue = max(0, $correctTotal - $this->received_amount);
        
        return $this->update([
            'total_amount' => $correctTotal,
            'next_due' => $correctNextDue
        ]);
    }

    /**
     * Recalculate payment amounts based on actual payments in database
     * This ensures next_due = total_amount - received_amount is always accurate
     */
    public function recalculatePaymentAmounts(): bool
    {
        // Get actual sum of payments from database
        $actualReceivedAmount = (float) $this->payments()->sum('amount');
        
        // Calculate correct next_due
        $correctNextDue = max(0, $this->total_amount - $actualReceivedAmount);
        
        // Determine correct status
        if ($actualReceivedAmount >= $this->total_amount) {
            $correctStatus = self::STATUS_PAID;
            $correctNextDue = 0;
        } elseif ($actualReceivedAmount > 0) {
            $correctStatus = self::STATUS_PARTIAL;
        } else {
            $correctStatus = self::STATUS_UNPAID;
        }

        return $this->update([
            'received_amount' => $actualReceivedAmount,
            'next_due' => $correctNextDue,
            'status' => $correctStatus,
        ]);
    }

    /**
     * Confirm this invoice and carry forward remaining amount
     */
    public function confirmAndCarryForward(): array
    {
        $dueAmount = $this->next_due;
        
        // Mark as confirmed
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'is_closed' => true,
            'closed_at' => now(),
            'notes' => ($this->notes ?? '') . "\n[Confirmed: " . now()->format('Y-m-d H:i:s') . 
                      "] Due amount of ৳" . number_format($dueAmount, 0) . " carried forward."
        ]);

        // Get customer product details
        $customerProduct = $this->customerProduct;
        if (!$customerProduct) {
            throw new \Exception("Customer product not found for invoice #{$this->invoice_number}");
        }

        // Calculate next billing date based on billing cycle
        $billingCycle = $customerProduct->billing_cycle_months ?? 1;
        $nextMonth = Carbon::parse($this->issue_date)->addMonths($billingCycle);
        
        // Check if next month invoice already exists
        $existingNextInvoice = Invoice::where('cp_id', $this->cp_id)
            ->whereYear('issue_date', $nextMonth->year)
            ->whereMonth('issue_date', $nextMonth->month)
            ->first();

        $nextInvoice = null;
        
        if ($existingNextInvoice) {
            // Check if this carry-forward amount was already added to prevent duplication
            $notes = $existingNextInvoice->notes ?? '';
            $alreadyAdded = strpos($notes, "Added ৳" . number_format($dueAmount, 0) . " carried forward from invoice #{$this->invoice_number}") !== false;
            
            if (!$alreadyAdded) {
                // Update existing invoice with carried forward amount
                $newPreviousDue = $dueAmount;
                $newTotalAmount = $existingNextInvoice->subtotal + $newPreviousDue;
                $newNextDue = max(0, $newTotalAmount - $existingNextInvoice->received_amount);
                
                $existingNextInvoice->update([
                    'previous_due' => $newPreviousDue,
                    'total_amount' => $newTotalAmount,
                    'next_due' => $newNextDue,
                    'notes' => $notes . "\nAdded ৳" . number_format($dueAmount, 0) . 
                              " carried forward from invoice #{$this->invoice_number}"
                ]);
            }
            $nextInvoice = $existingNextInvoice;
        } else {
            // Create new invoice for next month
            $product = $customerProduct->product;
            
            // Calculate subtotal using the CustomerProduct method
            $subtotal = $customerProduct->getSubtotalForMonth($nextMonth);
            
            $nextInvoice = Invoice::create([
                'cp_id' => $this->cp_id,
                'issue_date' => $nextMonth->format('Y-m-d'),
                'previous_due' => $dueAmount,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal + $dueAmount,
                'received_amount' => 0,
                'next_due' => $subtotal + $dueAmount,
                'status' => self::STATUS_UNPAID,
                'notes' => "Carried forward ৳" . number_format($dueAmount, 0) . 
                          " from confirmed invoice #{$this->invoice_number}",
                'created_by' => Auth::id() ?? 1
            ]);
        }

        // Validate that the next invoice amounts are consistent
        if ($nextInvoice) {
            $nextInvoice->refresh();
            if (!$nextInvoice->validateAmounts()) {
                // Fix any inconsistencies
                $nextInvoice->fixAmounts();
                $nextInvoice->refresh();
            }
        }
        
        return [
            'success' => true,
            'carried_forward_amount' => $dueAmount,
            'next_invoice' => $nextInvoice,
            'current_invoice_status' => self::STATUS_CONFIRMED,
            'next_billing_date' => $nextMonth->format('Y-m-d')
        ];
    }

    /**
     * Check if this invoice can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->status !== self::STATUS_CONFIRMED 
            && !$this->is_closed 
            && $this->next_due > 0
            && $this->status !== self::STATUS_CANCELLED
            && $this->status !== self::STATUS_CONVERTED
            && $this->status !== self::STATUS_PAID;
    }

    /**
     * Close invoice (for month close process)
     */
    public function closeInvoice($closedBy = null): bool
    {
        return $this->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $closedBy ?? Auth::id(),
            'notes' => ($this->notes ?? '') . "\n[Month Closed: " . now()->format('Y-m-d H:i:s') . "]"
        ]);
    }

    /**
     * Get next billing date based on this invoice
     */
    public function getNextBillingDate(): Carbon
    {
        $customerProduct = $this->customerProduct;
        if (!$customerProduct) {
            return Carbon::parse($this->issue_date)->addMonth();
        }
        
        $billingCycle = $customerProduct->billing_cycle_months ?? 1;
        return Carbon::parse($this->issue_date)->addMonths($billingCycle);
    }

    /**
     * Check if next billing cycle invoice should be created
     */
    public function shouldCreateNextBillingCycleInvoice(): bool
    {
        // If invoice is not paid, don't create next one
        if (!$this->isPaid() && !$this->isConfirmed()) {
            return false;
        }
        
        // Check if next billing date has passed
        $nextBillingDate = $this->getNextBillingDate();
        $today = Carbon::today();
        
        return $today >= $nextBillingDate;
    }

    // ==================== STATIC METHODS ====================

    public static function generateInvoiceNumber($issueDate = null): string
    {
        $date = $issueDate ? Carbon::parse($issueDate) : Carbon::now();
        $year = $date->format('y');
        $month = $date->format('m');
        $prefix = 'INV-' . $year . '-' . $month . '-';
        
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getTotalRevenue(): float
    {
        return (float) self::active()->sum('total_amount');
    }

    public static function getTotalCollected(): float
    {
        return (float) self::active()->sum('received_amount');
    }

    public static function getTotalDue(): float
    {
        return (float) self::whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL, self::STATUS_CONFIRMED])
                          ->sum('next_due');
    }

    public static function getConfirmedInvoicesTotal(): float
    {
        return (float) self::confirmed()->sum('next_due');
    }

    /**
     * Get invoices for a specific month
     */
    public static function getInvoicesForMonth($month): \Illuminate\Database\Eloquent\Collection
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        return self::with(['customerProduct.customer', 'customerProduct.product', 'payments'])
            ->whereYear('issue_date', $monthDate->year)
            ->whereMonth('issue_date', $monthDate->month)
            ->where('status', '!=', self::STATUS_CONVERTED)
            ->orderBy('issue_date', 'desc')
            ->orderBy('invoice_id', 'desc')
            ->get();
    }

    /**
     * Create next billing cycle invoice for a customer product
     */
    public static function createNextBillingCycleInvoice($cpId, $currentInvoice): ?Invoice
    {
        $customerProduct = CustomerProduct::find($cpId);
        if (!$customerProduct) {
            return null;
        }
        
        $billingCycle = $customerProduct->billing_cycle_months ?? 1;
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $currentInvoiceDate = Carbon::parse($currentInvoice->issue_date);
        
        // Calculate the correct next billing cycle date based on the original assignment date
        // This ensures that payments in the middle of a cycle don't reset the billing cycle
        $monthsSinceAssign = $assignDate->diffInMonths($currentInvoiceDate);
        $currentCycle = floor($monthsSinceAssign / $billingCycle);
        $nextCycleMonths = ($currentCycle + 1) * $billingCycle;
        $nextMonth = $assignDate->copy()->addMonths($nextCycleMonths);
        
        // Check if invoice already exists for next month
        $existingInvoice = self::where('cp_id', $cpId)
            ->whereYear('issue_date', $nextMonth->year)
            ->whereMonth('issue_date', $nextMonth->month)
            ->first();
            
        if ($existingInvoice) {
            return $existingInvoice;
        }
        
        // Calculate subtotal using the CustomerProduct method
        $subtotal = $customerProduct->getSubtotalForMonth($nextMonth);
        
        // Previous due is any unpaid amount from current invoice
        $previousDue = $currentInvoice->status === self::STATUS_CONFIRMED ? $currentInvoice->next_due : 0;
        
        $invoice = self::create([
            'cp_id' => $cpId,
            'issue_date' => $nextMonth->format('Y-m-d'),
            'previous_due' => $previousDue,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + $previousDue,
            'received_amount' => 0,
            'next_due' => $subtotal + $previousDue,
            'status' => self::STATUS_UNPAID,
            'notes' => "Next billing cycle invoice. " . 
                      ($previousDue > 0 ? "Includes ৳" . number_format($previousDue, 0) . " carried forward." : ""),
            'created_by' => Auth::id() ?? 1
        ]);
        
        return $invoice;
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
            
            // Always calculate next_due properly
            $invoice->next_due = max(0, ($invoice->total_amount ?? 0.00) - ($invoice->received_amount ?? 0.00));
            
            // Auto-set status if not provided
            if (empty($invoice->status)) {
                $invoice->status = $invoice->calculateStatus($invoice->received_amount);
            }
        });

        static::updating(function ($invoice) {
            // Ensure next_due is always correct
            if ($invoice->isDirty(['total_amount', 'received_amount'])) {
                $invoice->next_due = max(0, $invoice->total_amount - $invoice->received_amount);
                
                // Auto-update status if amounts changed
                if ($invoice->isDirty('received_amount')) {
                    $invoice->status = $invoice->calculateStatus($invoice->received_amount);
                }
            }
            
            // When invoice is marked as paid, ensure amounts are correct
            if ($invoice->isDirty('status') && $invoice->status === self::STATUS_PAID) {
                $invoice->received_amount = $invoice->total_amount;
                $invoice->next_due = 0;
                $invoice->is_closed = true;
                $invoice->closed_at = now();
            }
            
            // When invoice is confirmed, keep due amount but mark as closed
            if ($invoice->isDirty('status') && $invoice->status === self::STATUS_CONFIRMED) {
                $invoice->is_closed = true;
                $invoice->closed_at = now();
            }
        });

        static::created(function ($invoice) {
            Log::info("Invoice created: #{$invoice->invoice_number} for CP ID: {$invoice->cp_id}");
        });

        static::updated(function ($invoice) {
            // When invoice is paid or confirmed, check if next billing cycle invoice should be created
            if ($invoice->isDirty('status') && 
                ($invoice->status === self::STATUS_PAID || $invoice->status === self::STATUS_CONFIRMED)) {
                
                // Check if next billing cycle invoice should be created
                if ($invoice->shouldCreateNextBillingCycleInvoice()) {
                    Log::info("Invoice #{$invoice->invoice_number} paid/confirmed. Checking next billing cycle...");
                    
                    // Try to create next billing cycle invoice
                    try {
                        $nextInvoice = self::createNextBillingCycleInvoice($invoice->cp_id, $invoice);
                        if ($nextInvoice) {
                            Log::info("Created next billing cycle invoice: #{$nextInvoice->invoice_number}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to create next billing cycle invoice: " . $e->getMessage());
                    }
                }
            }
        });
    }
    
    // Rolling invoice methods (if needed)
    public static function generateRollingInvoiceNumber($customerId, $productId, $cycleNumber)
    {
        return sprintf('RINV-%04d-%03d-C%02d', 
            $customerId, 
            $productId, 
            $cycleNumber
        );
    }
    
    /**
     * Get or create rolling invoice for a customer product
     */
    public static function getOrCreateRollingInvoice($cpId, Carbon $monthDate)
    {
        // Get customer product details
        $customerProduct = \App\Models\CustomerProduct::with('product')->find($cpId);
        
        if (!$customerProduct) {
            return null;
        }
        
        // Check if invoice already exists for this month
        $existingInvoice = self::where('cp_id', $cpId)
            ->whereYear('issue_date', $monthDate->year)
            ->whereMonth('issue_date', $monthDate->month)
            ->first();
            
        if ($existingInvoice) {
            return $existingInvoice;
        }
        
        // Calculate if this is a billing month
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $monthsSinceAssign = $assignDate->diffInMonths($monthDate);
        $isBillingMonth = ($monthsSinceAssign % $customerProduct->billing_cycle_months) === 0;
        
        // Calculate subtotal using the CustomerProduct method
        $subtotal = $customerProduct->getSubtotalForMonth($monthDate);
        
        // Get previous due amount
        $previousDue = self::where('cp_id', $cpId)
            ->where('status', '!=', 'paid')
            ->where('next_due', '>', 0)
            ->sum('next_due');
        
        $totalAmount = $subtotal + $previousDue;
        
        // Create new invoice
        $invoice = self::create([
            'cp_id' => $cpId,
            'issue_date' => $monthDate->format('Y-m-d'),
            'previous_due' => $previousDue,
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'received_amount' => 0,
            'next_due' => $totalAmount,
            'status' => 'unpaid',
            'is_active_rolling' => 0, // Monthly invoice, not rolling
            'notes' => "Monthly invoice for {$monthDate->format('F Y')} - " . 
                      ($isBillingMonth ? "Billing month" : "Carry forward"),
            'created_by' => \Illuminate\Support\Facades\Auth::id() ?? 1
        ]);
        
        return $invoice;
    }
}