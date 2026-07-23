<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BunkerVerification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
    'bunker_id',
    'recorded_remaining_kg',  // ✅ correct
    'actual_remaining_kg',    // ✅ correct
    'shrinkage_kg',
    'shrinkage_percentage',
    'verified_by',
    'date_verified',          // ✅ correct
    'notes',
    'season_id',
];
    protected $casts = [
        'recorded_kg' => 'decimal:2',
        'actual_kg' => 'decimal:2',
        'shrinkage_kg' => 'decimal:2',
        'shrinkage_percentage' => 'decimal:2',
        'date' => 'date',
    ];

    public function bunker()
    {
        return $this->belongsTo(Bunker::class);
    }
}