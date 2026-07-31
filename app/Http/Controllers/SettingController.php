<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SettingController extends Controller
{
    private const ALLOWED_SETTINGS = [
        'app_name',
        'email',
        'phone',
        'address',
        'timezone',
        'date_format',
        'theme_primary_color',
        'theme_secondary_color',
    ];

    public function index()
    {
        return view('system_settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'timezone' => ['nullable', 'timezone'],
            'date_format' => ['nullable', Rule::in(['Y-m-d', 'd-m-Y', 'm/d/Y'])],
            'theme_primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,ico', 'max:2048'],
        ]);

        foreach (self::ALLOWED_SETTINGS as $key) {
            if (array_key_exists($key, $validated)) {
                Setting::updateOrCreate(['name' => $key], ['value' => $validated[$key]]);
            }
        }

        foreach (['logo', 'favicon'] as $key) {
            if ($request->hasFile($key)) {
                Setting::updateOrCreate(
                    ['name' => $key],
                    ['value' => uploadFile($request->file($key), 'settings')],
                );
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
