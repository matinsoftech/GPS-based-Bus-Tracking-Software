<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function get(): Setting
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([
                'platform_name' => config('app.name'),
                'maintenance_mode' => false,
            ]);
        }

        return $setting;
    }
}
