<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'delivery_address',
        'contact_person',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // ========== RELATIONSHIPS ==========

    public function sales()
    {
        return $this->hasMany(SaleItem::class, 'customer_id');
    }

    // ========== ACCESSORS ==========

    public function getTotalDuesAttribute()
    {
        return $this->sales()->sum('due_amount');
    }

    public function getTotalSalesAttribute()
    {
        return $this->sales()->sum('total_price');
    }

    public function getTotalPaidAttribute()
    {
        return $this->sales()->sum('paid_amount');
    }
}