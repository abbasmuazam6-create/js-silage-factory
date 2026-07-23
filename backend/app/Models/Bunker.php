<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bunker extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'location',
        'season_id',
        'threshold_percentage',
        'status',
        'notes',
        'is_sealed',
        'total_cost',
        'total_kg',
        'cost_per_kg',
    ];

    protected $casts = [
        'threshold_percentage' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_kg' => 'decimal:2',
        'is_sealed' => 'boolean',
    ];
    
    protected $attributes = [
        'status' => 'active',
        'threshold_percentage' => 10,
        'total_cost' => 0,
        'total_kg' => 0,
        'cost_per_kg' => 0,
    ];

    // ========== RELATIONSHIPS ==========

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Purchases directly linked to this bunker
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(SilagePurchase::class);
    }

    /**
     * Sales linked to this bunker
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Expenses linked to this bunker
     */
    public function expenses(): HasMany
{
    return $this->hasMany(Expense::class);
}

    /**
     * Moisture loss / verification records
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(BunkerVerification::class);
    }

    // ========== CALCULATIONS ==========

    /**
     * Total purchased weight from all purchases
     */
    public function getTotalPurchasedKgAttribute(): float
    {
        return $this->purchases()->sum('weight_kg');
    }

    /**
     * Total cost from all purchases
     */
    public function getTotalPurchaseCostAttribute(): float
    {
        return $this->purchases()->sum('cost');
    }

    /**
     * Total expenses linked to this bunker
     */
    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Total cost = purchase costs + expenses
     */
    public function getTotalCostAttribute(): float
    {
        return $this->total_purchase_cost + $this->total_expenses;
    }

    /**
     * Total sold weight from sales
     */
    public function getTotalSoldKgAttribute(): float
    {
        return $this->saleItems()->sum('weight_kg');
    }

    /**
     * Total sales revenue
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->saleItems()->sum('total_price');
    }

    /**
     * Total shrinkage (moisture loss)
     */
    public function getTotalShrinkageAttribute(): float
    {
        return $this->verifications()->sum('shrinkage_kg');
    }

    /**
     * Current available weight
     */
    public function getAvailableWeightAttribute(): float
    {
        $purchased = $this->total_purchased_kg;
        $sold = $this->total_sold_kg;
        $shrinkage = $this->total_shrinkage;
        return max(0, $purchased - $sold - $shrinkage);
    }

    /**
     * Total Profit = Total Revenue - Total Cost
     */
    public function getTotalProfitAttribute(): float
    {
        return $this->total_revenue - $this->total_cost;
    }
    public function getShrinkageKgAttribute()
{
    return $this->verifications->sum('shrinkage_kg') ?? 0;
}

public function getShrinkagePercentageAttribute()
{
    $total = $this->total_purchased_kg;
    return $total > 0 ? ($this->shrinkage_kg / $total) * 100 : 0;
}

    /**
     * Recalculate and save total cost, total kg, and cost per kg.
     * Called after adding/updating/deleting purchases or expenses.
     * (No lock – always recalculates)
     */
    public function recalculateCost(): void
    {
        $totalKg = $this->total_purchased_kg;
        $totalCost = $this->total_cost;

        $this->total_kg = $totalKg;
        $this->total_cost = $totalCost;

        if ($totalKg > 0) {
            $this->cost_per_kg = $totalCost / $totalKg;
        } else {
            $this->cost_per_kg = 0;
        }

        $this->save();
    }

    /**
     * Check if sales are allowed
     */
    public function canSell(): bool
    {
        if ($this->status === 'empty') {
            return false;
        }
        if ($this->total_kg <= 0) {
            return false; // No stock
        }
        return $this->available_weight > 0;
    }

    /**
     * Available weight for a specific season (kept for compatibility)
     */
    public function availableWeightForSeason($seasonId = null): float
    {
        return $this->available_weight;
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithStock($query)
    {
        return $query->where('status', '!=', 'empty');
    }
}