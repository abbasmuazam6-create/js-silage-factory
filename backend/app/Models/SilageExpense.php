<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SilageExpense extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'expense_code',
        'bunker_id',
        'category',
        'amount',
        'expense_date',
        'notes',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function bunker(): BelongsTo
    {
        return $this->belongsTo(Bunker::class);
    }
}
