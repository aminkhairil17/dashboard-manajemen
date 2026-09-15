<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTarget extends Model
{
    protected $fillable = ['kpi_key', 'label', 'min_value', 'max_value'];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
    ];
}
