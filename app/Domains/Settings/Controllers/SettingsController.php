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

        $booleanKeys = [
            'announcement_enabled',
            'login_method_email',
            'login_method_username',
            'login_method_employee_id',
            'login_method_student_id',
            'password_require_uppercase',
            'password_require_lowercase',
            'password_require_number',
            'password_require_symbol',
        ];

        foreach ($request->validated() as $key => $value) {
            $stored = in_array($key, $booleanKeys, true)
                ? ($request->boolean($key) ? '1' : '0')
                : ((string) ($value ?? ''));

            Setting::set($key, $stored, 'school');
        }

        Cache::forget('settings');
        ActivityLogger::log('updated school settings', 'settings');

        return back()->with('status', __('School settings updated successfully.'));
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
            'timezone' => 'Africa/Addis_Ababa',
            'language' => 'en',
            'announcement_enabled' => false,
            'announcement_text' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_telegram' => '',
            'login_method_email' => true,
            'login_method_username' => true,
            'login_method_employee_id' => true,
            'login_method_student_id' => true,
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_lowercase' => true,
            'password_require_number' => true,
            'password_require_symbol' => false,
            'password_history_count' => 5,
            'password_expiration_days' => 0,
            'session_timeout_global' => 60,
        ];

        $stored = Setting::pluck('value', 'key')->all();

        foreach ($stored as $key => $value) {
            if (isset($defaults[$key]) && is_bool($defaults[$key])) {
                $stored[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return array_replace($defaults, $stored);
    }
}
