<?php

namespace App\Domains\Settings\Controllers;

use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Requests\UpdateSchoolProfileRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $this->authorize('view', Setting::class);

        return view('settings.index', ['settings' => $this->allSettings()]);
    }

    public function update(UpdateSchoolProfileRequest $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $fields = $request->validated();

        foreach ($fields as $key => $value) {
            Setting::set($key, $value ?? '', 'school');
        }

        Cache::forget('settings');
        ActivityLogger::log('updated school settings', 'settings');

        return back()->with('status', 'School settings updated successfully.');
    }

    private function allSettings(): array
    {
        $defaults = [
            'school_name' => config('app.name', 'EduSphere'),
            'school_tagline' => 'Knowledge is Light',
            'school_email' => '',
            'school_phone' => '',
            'school_address' => '',
            'school_website' => '',
            'academic_year' => '',
            'currency' => 'ETB',
        ];

        $stored = Setting::pluck('value', 'key')->all();

        return array_replace($defaults, $stored);
    }
}
