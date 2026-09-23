<x-layouts.app :title="__('New admission application')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Applications'), 'url' => route('admissions.applications.index')],
        ['label' => __('New application')],
    ]" />

    <x-page-header :title="__('New admission application')" :description="__('Record an inquiry walk-in or website application request.')" />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('admissions.applications.store') }}" novalidate>
                @csrf
                @include('admissions.applications._form', ['gradeLevels' => $gradeLevels, 'years' => $years, 'inquiry' => $inquiry])

                <div class="card-footer">
                    <a href="{{ route('admissions.applications.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create application') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>