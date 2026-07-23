<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'start_month', 'start_day', 'end_month', 'end_day', 'color'
    ];
}