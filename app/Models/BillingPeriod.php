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
        // Disallow future months
        if ($month > Carbon::now()->format('Y-m')) {
            return false;
        }

        // Allow access to any current or past month. Previous business logic
        // required the previous month to be closed before accessing the next
        // month; to let admins view/close historical months directly we
        // remove that restriction here.
        return true;
    }

    /**
     * Get display name for the month
     */
    public function getDisplayNameAttribute()
    {
        return Carbon::createFromFormat('Y-m', $this->billing_month)->format('F Y');
    }
}
