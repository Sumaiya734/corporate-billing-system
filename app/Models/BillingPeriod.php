<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BillingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_month',
        'is_closed',
        'total_amount',
        'received_amount',
        'carried_forward',
        'total_invoices',
        'affected_invoices',
        'closed_at',
        'closed_by',
        'notes'
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'carried_forward' => 'decimal:2',
        'closed_at' => 'datetime'
    ];

    /**
     * Get the user who closed this period
     */
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Check if a specific month is closed
     */
    public static function isMonthClosed($month)
    {
        return self::where('billing_month', $month)
            ->where('is_closed', true)
            ->exists();
    }

    /**
     * Get the last closed month
     */
    public static function getLastClosedMonth()
    {
        return self::where('is_closed', true)
            ->orderBy('billing_month', 'desc')
            ->first();
    }

    /**
     * Check if previous month is closed (required before accessing current month)
     */
    public static function canAccessMonth($month)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        $previousMonth = $monthDate->copy()->subMonth()->format('Y-m');
        
        // Current month is always accessible
        if ($month === Carbon::now()->format('Y-m')) {
            return true;
        }
        
        // Future months are not accessible
        if ($month > Carbon::now()->format('Y-m')) {
            return false;
        }
        
        // For past months, check if previous month is closed
        // First month ever is always accessible
        $firstInvoice = \App\Models\Invoice::orderBy('issue_date')->first();
        if ($firstInvoice) {
            $firstMonth = Carbon::parse($firstInvoice->issue_date)->format('Y-m');
            if ($month === $firstMonth) {
                return true;
            }
        }
        
        // Check if previous month is closed
        return self::isMonthClosed($previousMonth);
    }

    /**
     * Get display name for the month
     */
    public function getDisplayNameAttribute()
    {
        return Carbon::createFromFormat('Y-m', $this->billing_month)->format('F Y');
    }
}
