<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    private const EDITABLE = [
        'site_announcement' => ['nullable', 'string', 'max:500'],
        'registration_open' => ['required', 'in:0,1'],
    ];

    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => [
                'site_announcement' => Setting::get('site_announcement', ''),
                'registration_open' => Setting::get('registration_open', '1'),
            ],
        ]);
    }

    public function update(Request $request, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate(self::EDITABLE);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        $audit->log('admin.settings_updated', 'admin', context: $validated);

        return back()->with('status', 'Settings saved.');
    }
}
