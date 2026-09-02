<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
   protected $fillable = [
        'platform_name',
        'support_email',
        'support_phone',
        'address',
        'website',
        'facebook',
        'instagram',
        'logo',
        'favicon',
        'maintenance_mode',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
    ];
}
