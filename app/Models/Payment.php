<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';
    
    // Ensure the primary key is auto-incrementing
    public $incrementing = true;
    
    // Specify the key type
    protected $keyType = 'int';
    
    protected $fillable = [
        'invoice_id', // Links to the specific product invoice
        'cp_id', // Links to customer_to_products (specific product)
        'amount',
        'payment_method',
        'payment_date',
        'transaction_id',
        'collected_by',
        'status',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'c_id', 'c_id');
    }

    public function customerProduct()
    {
        return $this->belongsTo(CustomerProduct::class, 'cp_id', 'cp_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by', 'id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return '৳ ' . number_format((float)$this->amount, 2);
    }

    public function getFormattedPaymentDateAttribute()
    {
        return \Carbon\Carbon::parse($this->payment_date)->format('M j, Y');
    }
   
    public function getPaymentMethodTextAttribute()
    {
        $methods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'mobile_banking' => 'Mobile Banking',
            'card' => 'Card',
            'online' => 'Online'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

}