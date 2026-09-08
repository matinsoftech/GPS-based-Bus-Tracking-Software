<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'monthly_price',
        'yearly_price',
        'max_buses',
        'max_students',
        'max_parents',
        'max_drivers',
        'max_routes',
        'max_devices',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',

            'max_buses' => 'integer',
            'max_students' => 'integer',
            'max_parents' => 'integer',
            'max_drivers' => 'integer',
            'max_routes' => 'integer',
            'max_devices' => 'integer',

            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}