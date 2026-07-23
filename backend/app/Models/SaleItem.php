<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
    'bunker_id',
    'customer_id',
    'payment_type_id',
    'invoice_number',
    'sale_type',
    'weight_kg',
    'units',
    'price_per_kg',
    'total_price',
    'discount',
    'paid_amount',
    'due_amount',
    'date',
    'season_id',
    'cost_per_kg_at_sale',
    'profit',
];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'profit' => 'decimal:2',
        'date' => 'date',
    ];

    public function bunker()
    {
        return $this->belongsTo(Bunker::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
    public function paymentType()
{
    return $this->belongsTo(PaymentType::class);
}
}