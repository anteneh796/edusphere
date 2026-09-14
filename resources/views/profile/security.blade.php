<x-layouts.app :title="'Change password'">
    <x-breadcrumb :items="[
        ['label' => 'My profile', 'url' => route('profile.index')],
        ['label' => 'Change password'],
    ]" />

    <x-page-header title="Change password" description="Keep your account secure with a strong password." />

    <div style="max-width: 720px;">
        <x-card title="Update password" subtitle="You will need your current password to continue.">
            <form method="POST" action="{{ route('profile.password') }}" novalidate>
                @csrf
                @method('PUT')

                <x-input name="current_password" type="password" label="Current password" placeholder="••••••••" required autofocus />
                <x-input name="new_password" type="password" label="New password" placeholder="Minimum 8 characters" required autocomplete="new-password" />
                <x-input name="new_password_confirmation" type="password" label="Confirm new password" placeholder="Repeat new password" required autocomplete="new-password" />

                <div class="card-footer" style="margin-top: var(--space-3);">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="lock" class="icon-sm" />
                        Update password
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>