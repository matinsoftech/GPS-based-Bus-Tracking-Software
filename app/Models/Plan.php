<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

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
            'deleted_at' => 'datetime',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) data_get($this->features, $feature, false);
    }
}
