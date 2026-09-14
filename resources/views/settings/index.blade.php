<x-layouts.app :title="'School settings'">
    <x-breadcrumb :items="[['label' => 'School settings']]" />

    <x-page-header title="School settings" description="Core school profile used across the system.">
        @cannot('update', App\Domains\Settings\Models\Setting::class)
            <x-badge color="warning">Read only</x-badge>
        @endcannot
    </x-page-header>

    <div style="max-width: 880px;">
        <x-card title="School profile" subtitle="Appears on reports, receipts and the public website.">
            <form method="POST" action="{{ route('settings.update') }}" novalidate>
                @csrf
                @method('PUT')

                <div class="grid grid-2" style="margin-bottom: var(--space-1);">
                    <x-input name="school_name" label="School name" :value="$settings['school_name']" required />
                    <x-input name="school_tagline" label="Tagline / slogan" :value="$settings['school_tagline']" placeholder="Knowledge is Light" />
                </div>

                <div class="grid grid-2">
                    <x-input name="school_email" type="email" label="School email" :value="$settings['school_email']" placeholder="info@school.et" />
                    <x-input name="school_phone" label="School phone" :value="$settings['school_phone']" placeholder="+251 …" />
                </div>

                <x-input name="school_address" label="Address" :value="$settings['school_address']" placeholder="Kebele, Town, Zone, Region" />
                <x-input name="school_website" label="Website" :value="$settings['school_website']" placeholder="https://school.et" />

                <div class="grid grid-2">
                    <x-input name="academic_year" label="Current academic year" :value="$settings['academic_year']" placeholder="e.g. 2026/2027" hint="Use the format YYYY/YYYY" />
                    <x-input name="currency" label="Currency code" :value="$settings['currency']" placeholder="ETB" maxlength="3" />
                </div>

                @can('update', App\Domains\Settings\Models\Setting::class)
                    <div class="card-footer" style="margin-top: var(--space-3);">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" class="icon-sm" />
                            Save settings
                        </button>
                    </div>
                @endcan
            </form>
        </x-card>
    </div>
</x-layouts.app>