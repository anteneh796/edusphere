<x-layouts.app :title="__('Change password')">
    <x-breadcrumb :items="[
        ['label' => __('My profile'), 'url' => route('profile.index')],
        ['label' => __('Change password')],
    ]" />

    <x-page-header :title="__('Change password')" :description="__('Keep your account secure with a strong password.')" />

    <div style="max-width: 720px;">
        <x-card :title="__('Update password')" :subtitle="__('You will need your current password to continue.')">
            <form method="POST" action="{{ route('profile.password') }}" novalidate>
                @csrf
                @method('PUT')

                <x-input name="current_password" type="password" :label="__('Current password')" placeholder="••••••••" required autofocus />
                <x-input name="new_password" type="password" :label="__('New password')" :placeholder="__('Minimum 8 characters')" required autocomplete="new-password" />
                <x-input name="new_password_confirmation" type="password" :label="__('Confirm new password')" :placeholder="__('Repeat new password')" required autocomplete="new-password" />

                <div class="card-footer" style="margin-top: var(--space-3);">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="lock" class="icon-sm" />
                        {{ __('Update password') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>