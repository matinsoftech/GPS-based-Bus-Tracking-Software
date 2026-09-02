<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(SettingService $settingService)
    {
        return view('setting.index', [
            'settings' => $settingService->get(),
        ]);
    }

    public function update(
        Request $request,
        SettingService $settingService
    ) {
        $validated = $request->validate([
            'platform_name' => 'nullable|string|max:255',

            'support_email' => 'nullable|email|max:255',

            'support_phone' => 'nullable|string|max:30',

            'address' => 'nullable|string|max:1000',

            'website' => 'nullable|url|max:255',

            'facebook' => 'nullable|url|max:255',

            'instagram' => 'nullable|url|max:255',

            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:1024',

            'maintenance_mode' => 'nullable|boolean',
        ]);

        $settings = $settingService->get();

        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {

            if (
                $settings->logo &&
                Storage::disk('public')->exists($settings->logo)
            ) {
                Storage::disk('public')->delete($settings->logo);
            }

            $validated['logo'] = $request
                ->file('logo')
                ->store('settings', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Favicon
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('favicon')) {

            if (
                $settings->favicon &&
                Storage::disk('public')->exists($settings->favicon)
            ) {
                Storage::disk('public')->delete($settings->favicon);
            }

            $validated['favicon'] = $request
                ->file('favicon')
                ->store('settings', 'public');
        }

        $validated['maintenance_mode'] =
            $request->boolean('maintenance_mode');

        $settings->update($validated);

        // $settingService->clearCache();

        return back()->with(
            'success',
            'Settings updated successfully.'
        );
    }

    public function deleteLogo(
        SettingService $settingService
    ) {
        $settings = $settingService->get();

        if (
            $settings->logo &&
            Storage::disk('public')->exists($settings->logo)
        ) {
            Storage::disk('public')->delete($settings->logo);
        }

        $settings->update([
            'logo' => null,
        ]);

        $settingService->clearCache();

        return back()->with(
            'success',
            'Logo deleted successfully.'
        );
    }

    public function deleteFavicon(
        SettingService $settingService
    ) {
        $settings = $settingService->get();

        if (
            $settings->favicon &&
            Storage::disk('public')->exists($settings->favicon)
        ) {
            Storage::disk('public')->delete($settings->favicon);
        }

        $settings->update([
            'favicon' => null,
        ]);

        $settingService->clearCache();

        return back()->with(
            'success',
            'Favicon deleted successfully.'
        );
    }
}
