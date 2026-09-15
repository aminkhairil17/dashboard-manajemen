<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSnapshot extends Model
{
    protected $fillable = ['date', 'category', 'kpi_key', 'value', 'unit', 'meta'];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:4',
        'meta' => 'array',
    ];
}
