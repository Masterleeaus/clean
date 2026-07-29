<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        return view('system_settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:500'],
            'timezone' => ['nullable', Rule::in(['Australia/Sydney', 'UTC', 'Asia/Dhaka', 'America/New_York', 'Europe/London'])],
            'date_format' => ['nullable', Rule::in(['Y-m-d', 'd-m-Y', 'm/d/Y'])],
            'theme_primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico', 'max:1024'],
            'ai_provider' => ['nullable', Rule::in(['gemini', 'openrouter', 'openai'])],
            'ai_api_key' => ['nullable', 'string', 'max:2048'],
            'clear_ai_api_key' => ['nullable', 'boolean'],
            'ai_model' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
        ]);

        $plainKeys = [
            'app_name', 'email', 'phone', 'address', 'timezone', 'date_format',
            'theme_primary_color', 'theme_secondary_color', 'ai_provider', 'ai_model',
        ];

        foreach ($plainKeys as $key) {
            if (array_key_exists($key, $validated)) {
                Setting::query()->updateOrCreate(['name' => $key], ['value' => $validated[$key] ?: null]);
            }
        }

        foreach (['logo', 'favicon'] as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $old = setting($key);
            $path = uploadFile($request->file($key), 'settings', null, null, $old);
            if (! $path) {
                return back()->withErrors([$key => 'The image could not be processed.'])->withInput();
            }

            Setting::query()->updateOrCreate(['name' => $key], ['value' => $path]);
        }

        if ($request->boolean('clear_ai_api_key')) {
            Setting::query()->where('name', 'ai_api_key')->delete();
        } elseif (filled($validated['ai_api_key'] ?? null)) {
            Setting::query()->updateOrCreate([
                'name' => 'ai_api_key',
            ], [
                'value' => 'encrypted:'.Crypt::encryptString((string) $validated['ai_api_key']),
            ]);
        }

        return back()->with('success', 'Settings updated successfully');
    }
}
