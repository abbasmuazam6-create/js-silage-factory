<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'color',
        'available_in', // 'silage', 'wanda', 'both'
    ];

    protected $casts = [
        'available_in' => 'string',
    ];
}