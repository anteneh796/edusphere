<x-layouts.app :title="__('School settings')">
    <x-breadcrumb :items="[['label' => __('School settings')]]" />

    <x-page-header :title="__('School settings')" :description="__('Core school profile used across the system.')">
        @cannot('update', App\Domains\Settings\Models\Setting::class)
            <x-badge color="warning">{{ __('Read only') }}</x-badge>
        @endcannot
    </x-page-header>

    <div style="max-width: 880px;">
        <form method="POST" action="{{ route('settings.update') }}" novalidate>
            @csrf
            @method('PUT')

            <x-card :title="__('School profile')" :subtitle="__('Appears on reports, receipts and the public website.')">
                <div class="grid grid-2" style="margin-bottom: var(--space-1);">
                    <x-input name="school_name" :label="__('School name')" :value="$settings['school_name']" required />
                    <x-input name="school_tagline" :label="__('Tagline / slogan')" :value="$settings['school_tagline']" :placeholder="__('Knowledge is Light')" />
                </div>

                <div class="grid grid-2">
                    <x-input name="school_email" type="email" :label="__('School email')" :value="$settings['school_email']" placeholder="info@school.et" />
                    <x-input name="school_phone" :label="__('School phone')" :value="$settings['school_phone']" placeholder="+251 …" />
                </div>

                <x-input name="school_address" :label="__('Address')" :value="$settings['school_address']" :placeholder="__('Kebele, Town, Zone, Region')" />
                <x-input name="school_website" :label="__('Website')" :value="$settings['school_website']" placeholder="https://school.et" />

                <div class="grid grid-2">
                    <x-input name="academic_year" :label="__('Current academic year')" :value="$settings['academic_year']" placeholder="e.g. 2026/2027" :hint="__('Use the format YYYY/YYYY')" />
                    <x-input name="currency" :label="__('Currency code')" :value="$settings['currency']" placeholder="ETB" maxlength="3" />
                </div>
            </x-card>

            <x-card :title="__('Localization')" :subtitle="__('Timezone and default language for the portal.')">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label" for="timezone">{{ __('Timezone') }} <span class="required">*</span></label>
                        <select id="timezone" name="timezone" class="form-select" required>
                            @foreach (timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" @selected($settings['timezone'] === $tz)>{{ str_replace('_', ' ', $tz) }}</option>
                            @endforeach
                        </select>
                        @error('timezone')
                            <div class="form-error"><x-icon name="alert-circle" class="icon-sm" /> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="language">{{ __('Default language') }} <span class="required">*</span></label>
                        <select id="language" name="language" class="form-select" required>
                            <option value="en" @selected($settings['language'] === 'en')>{{ __('English') }}</option>
                            <option value="am" @selected($settings['language'] === 'am')>አማርኛ</option>
                        </select>
                        @error('language')
                            <div class="form-error"><x-icon name="alert-circle" class="icon-sm" /> {{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-card>

            <x-card :title="__('Announcement bar')" :subtitle="__('An important or emergency notice shown at the very top of the public website.')">
                <x-textarea name="announcement_text" rows="2" :label="__('Announcement message')" :placeholder="__('e.g. School is closed on Sunday 1 October for a national holiday.')">{{ $settings['announcement_text'] }}</x-textarea>

                <x-bare-field>
                    <input type="hidden" name="announcement_enabled" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="announcement_enabled" value="1" :checked="(bool) $settings['announcement_enabled']" />
                        <span>{{ __('Show this announcement on the website') }}</span>
                    </label>
                </x-bare-field>
            </x-card>

            <x-card :title="__('Social links')" :subtitle="__('Used in the footer of the public website.')">
                <div class="grid grid-2">
                    <x-input name="social_facebook" type="url" :label="__('Facebook')" :value="$settings['social_facebook']" placeholder="https://facebook.com/school" />
                    <x-input name="social_instagram" type="url" :label="__('Instagram')" :value="$settings['social_instagram']" placeholder="https://instagram.com/school" />
                </div>
                <x-input name="social_telegram" type="url" :label="__('Telegram')" :value="$settings['social_telegram']" placeholder="https://t.me/school" />
            </x-card>

            <x-card :title="__('Login methods')" :subtitle="__('Identifiers users can sign in with. Keep at least one enabled.')">
                @foreach ([
                    'login_method_email' => __('Email address'),
                    'login_method_username' => __('Username'),
                    'login_method_employee_id' => __('Employee ID'),
                    'login_method_student_id' => __('Student number'),
                ] as $key => $label)
                    <x-bare-field>
                        <input type="hidden" name="{{ $key }}" value="0">
                        <label class="checkbox-line">
                            <x-input type="checkbox" name="{{ $key }}" value="1" :checked="(bool) $settings[$key]" />
                            <span>{{ $label }}</span>
                        </label>
                    </x-bare-field>
                @endforeach
            </x-card>

            <x-card :title="__('Password policy')" :subtitle="__('Applied to new passwords and password changes.')">
                <div class="grid grid-2" style="margin-bottom: var(--space-2);">
                    <x-input name="password_min_length" type="number" min="8" max="64" :label="__('Minimum length')" :value="$settings['password_min_length']" />
                    <x-input name="password_history_count" type="number" min="0" max="50" :label="__('Passwords to remember')" :value="$settings['password_history_count']" :hint="__('0 disables reuse checking')" />
                </div>
                <x-input name="password_expiration_days" type="number" min="0" max="365" :label="__('Password expiry (days)')" :value="$settings['password_expiration_days']" :hint="__('Users must change their password after this many days. 0 disables expiry.')" />

                <x-bare-field>
                    <input type="hidden" name="password_require_uppercase" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="password_require_uppercase" value="1" :checked="(bool) $settings['password_require_uppercase']" />
                        <span>{{ __('Require an uppercase letter') }}</span>
                    </label>
                </x-bare-field>
                <x-bare-field>
                    <input type="hidden" name="password_require_lowercase" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="password_require_lowercase" value="1" :checked="(bool) $settings['password_require_lowercase']" />
                        <span>{{ __('Require a lowercase letter') }}</span>
                    </label>
                </x-bare-field>
                <x-bare-field>
                    <input type="hidden" name="password_require_number" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="password_require_number" value="1" :checked="(bool) $settings['password_require_number']" />
                        <span>{{ __('Require a number') }}</span>
                    </label>
                </x-bare-field>
                <x-bare-field>
                    <input type="hidden" name="password_require_symbol" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="password_require_symbol" value="1" :checked="(bool) $settings['password_require_symbol']" />
                        <span>{{ __('Require a special character') }}</span>
                    </label>
                </x-bare-field>
            </x-card>

            <x-card :title="__('Session timeout')" :subtitle="__('Idle time in minutes before users are signed out automatically. Individual roles can be tuned from Role Permissions.')">
                <x-input name="session_timeout_global" type="number" min="1" max="1440" :label="__('Global idle timeout (minutes)')" :value="$settings['session_timeout_global']" />
            </x-card>

            @can('update', App\Domains\Settings\Models\Setting::class)
                <x-card>
                    <div class="card-footer" style="margin-top: 0;">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" class="icon-sm" />
                            {{ __('Save settings') }}
                        </button>
                    </div>
                </x-card>
            @endcan
        </form>
    </div>
</x-layouts.app>