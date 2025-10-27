<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'price', 'description', 'is_active'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Scope for active packages
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRegular($query)
    {
        return $query->where('type', 'regular');
    }

    public function scopeSpecial($query)
    {
        return $query->where('type', 'special');
    }
}