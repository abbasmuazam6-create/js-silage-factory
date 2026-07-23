<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
    'bunker_id',
    'season_id', // ✅ Add this
    'expense_category_id',
    'amount',
    'date',
    'notes',
];
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function bunker()
    {
        return $this->belongsTo(Bunker::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
    public function season()
{
    return $this->belongsTo(Season::class);
}
}