<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BunkerAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'bunker_id',
        'silage_purchase_id',
        'season_id',
        'source',
        'assigned_weight_kg',
        'date_assigned',
        'notes',
    ];

    protected $casts = [
        'assigned_weight_kg' => 'decimal:2',
        'date_assigned' => 'date',
    ];

    public function bunker()
    {
        return $this->belongsTo(Bunker::class);
    }

    public function silagePurchase()
    {
        return $this->belongsTo(SilagePurchase::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}