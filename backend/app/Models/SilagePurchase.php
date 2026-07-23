<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SilagePurchase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'purchase_code',
        'supplier_id',
        'bunker_id',        // ← NEW: direct bunker link
        'purchase_date',
        'area',
        'weight_kg',
        'cost',
        'notes',
        'season_id',
        'status',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'cost' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'available',
    ];

    // ========== RELATIONSHIPS ==========

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Direct bunker relationship (NEW - replaces assignments)
     */
    public function bunker()
    {
        return $this->belongsTo(Bunker::class);
    }

    /**
     * @deprecated - kept for compatibility, but no longer used
     */
    public function bunkerAssignments()
    {
        return $this->hasMany(BunkerAssignment::class);
    }

    /**
     * @deprecated - kept for compatibility, but no longer used
     */
    public function bunkers()
    {
        return $this->belongsToMany(Bunker::class, 'bunker_assignments')
            ->withPivot('assigned_weight_kg', 'notes', 'date_assigned')
            ->withTimestamps();
    }

    // ========== ACCESSORS ==========

    /**
     * Available kg = total weight - assigned to bunkers
     * With direct bunker relationship, this is simply weight_kg (since purchases are always linked to one bunker)
     * But if a purchase is partially assigned, we track it differently.
     * For simplicity: available_kg = weight_kg - sum of assigned_kg from bunker_assignments
     * (kept for backward compatibility)
     */
    public function getAvailableKgAttribute()
    {
        // If using direct bunker relationship, a purchase is fully assigned to one bunker
        // So available_kg is just weight_kg
        // But if there are still assignment records, we need to handle both cases
        $assigned = $this->bunkerAssignments()->sum('assigned_weight_kg');
        return $this->weight_kg - $assigned;
    }

    /**
     * Check if purchase is fully used
     */
    public function getIsFullyUsedAttribute(): bool
    {
        return $this->available_kg <= 0;
    }

    // ========== STATUS MANAGEMENT ==========

    public function updateStatus()
    {
        $available = $this->available_kg;
        if ($available <= 0) {
            $this->status = 'used';
        } elseif ($available < $this->weight_kg) {
            $this->status = 'partial';
        } else {
            $this->status = 'available';
        }
        $this->save();
    }

    // ========== SCOPES ==========

    public function scopeAvailable($query)
    {
        return $query->where('status', '!=', 'used');
    }

    public function scopeForSeason($query, $seasonId)
    {
        return $query->where('season_id', $seasonId);
    }

    public function scopeForBunker($query, $bunkerId)
    {
        return $query->where('bunker_id', $bunkerId);
    }
}