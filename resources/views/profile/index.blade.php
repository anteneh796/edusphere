<x-layouts.app :title="__('My profile')">
    <x-breadcrumb :items="[['label' => __('My profile')]]" />

    <x-page-header :title="__('My profile')" :description="__('Update your personal information.')" />

    <div style="max-width: 720px;">
        <x-card>
            <form method="POST" action="{{ route('profile.update') }}" novalidate>
                @csrf
                @method('PUT')

                <div class="grid grid-2" style="margin-bottom: var(--space-1);">
                    <x-input name="first_name" :label="__('First name')" :value="$user->first_name" required />
                    <x-input name="last_name" :label="__('Last name')" :value="$user->last_name" required />
                </div>

                <x-input name="email" type="email" :label="__('Email address')" :value="$user->email" required />
                <x-input name="phone" :label="__('Phone number')" :value="$user->phone" placeholder="+251 9xx xxx xxx" />

                <div class="card-footer" style="margin-top: var(--space-3);">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save profile') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>