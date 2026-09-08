<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'latitude',
        'longitude',
        'principal_name',
        'logo',
        'status',
    ];

    public function getSchoolNameAttribute(): string
    {
        return $this->attributes['school_name'] ?? $this->attributes['name'] ?? '';
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['trialing', 'active']);
    }
}
