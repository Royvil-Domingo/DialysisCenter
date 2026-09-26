<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected array $keys = [
        'clinic_name', 'clinic_address', 'clinic_contact_number',
        'clinic_email', 'operating_hours',
    ];

    public function edit()
    {
        $settings = collect($this->keys)->mapWithKeys(fn ($key) => [$key => Setting::get($key, '')]);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => 'nullable|string|max:150',
            'clinic_address' => 'nullable|string|max:255',
            'clinic_contact_number' => 'nullable|string|max:20',
            'clinic_email' => 'nullable|email|max:150',
            'operating_hours' => 'nullable|string|max:150',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Settings updated.');
    }
}
