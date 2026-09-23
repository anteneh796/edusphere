<x-layouts.app :title="__('Settings')">
    <x-page-header
        :title="__('Parent settings')"
        :description="__('Update your contact details and notification preferences.')" />

    @if (! $guardian)
        <x-card>
            <x-empty-state icon="users" :title="__('Account not linked')" :message="__('Ask the registrar to link a guardian profile to your login.')" />
        </x-card>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2" style="align-items:start;">
            <x-card :title="__('Contact details')">
                <form method="POST" action="{{ route('cms.parent.settings.update') }}" class="grid gap-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-2 gap-4">
                        <x-input name="first_name" :label="__('First name')" :value="$guardian->first_name" disabled />
                        <x-input name="last_name" :label="__('Last name')" :value="$guardian->last_name" disabled />
                    </div>

                    <x-input name="phone" :label="__('Phone')" :value="old('phone', $guardian->phone)" />

                    <x-input name="email" :label="__('Email')" type="email" :value="old('email', $guardian->email)" />

                    <x-input name="address" :label="__('Address')" :value="old('address', $guardian->address)" />

                    <x-input name="occupation" :label="__('Occupation')" :value="old('occupation', $guardian->occupation)" />

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Save details') }}</button>
                    </div>
                </form>
            </x-card>

            <x-card :title="__('Notification preferences')">
                <form method="POST" action="{{ route('cms.parent.settings.update') }}" class="grid gap-4">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-3">
                        <x-checkbox
                            name="notify_email"
                            :label="__('Notify me by email')"
                            :checked="(bool) $preferences->notify_email" />
                        <x-checkbox
                            name="notify_sms"
                            :label="__('Notify me by SMS')"
                            :checked="(bool) $preferences->notify_sms" />
                        <x-checkbox
                            name="notify_push"
                            :label="__('Notify me in the portal')"
                            :checked="(bool) $preferences->notify_push" />
                    </div>

                    <p class="text-xs text-light form-hint">
                        {{ __('These control how the school reaches you about attendance, grades, fees and urgent notices.') }}
                    </p>

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Save preferences') }}</button>
                    </div>
                </form>
            </x-card>
        </div>
    @endif
</x-layouts.app>