<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'product_id',
        'description',
        'status',
        'department',
        'contact_email',
        'contact_name',
        'contact_phone',
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the customer that owns the support ticket.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'c_id');
    }

    /**
     * Get the product associated with the support ticket.
     */
    public function product()
    {
        return $this->belongsTo(CustomerProduct::class, 'product_id', 'cp_id');
    }

    /**
     * Get the product name through relationship.
     */
    public function getProductNameAttribute()
    {
        return $this->product->product->name ?? 'N/A';
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityBadgeAttribute()
    {
        $colors = [
            'low' => 'bg-secondary',
            'medium' => 'bg-info',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger',
        ];

        return $colors[$this->priority] ?? 'bg-secondary';
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'open' => 'bg-warning',
            'in_progress' => 'bg-info',
            'resolved' => 'bg-success',
            'closed' => 'bg-secondary',
        ];

        return $colors[$this->status] ?? 'bg-secondary';
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include resolved tickets.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Generate a unique ticket number.
     */
    public static function generateTicketNumber()
    {
        do {
            $number = 'TICKET-' . strtoupper(uniqid());
        } while (self::where('ticket_number', $number)->exists());

        return $number;
    }
}