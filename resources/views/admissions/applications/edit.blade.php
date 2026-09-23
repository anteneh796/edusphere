<x-layouts.app :title="__('Edit application')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Applications'), 'url' => route('admissions.applications.index')],
        ['label' => $application->full_name, 'url' => route('admissions.applications.show', $application)],
        ['label' => __('Edit')],
    ]" />

    <x-page-header :title="__('Edit application')" :description="$application->application_number.' — '.$application->full_name" />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('admissions.applications.update', $application) }}" novalidate>
                @csrf
                @method('PUT')
                @include('admissions.applications._form')

                <div class="card-footer">
                    <a href="{{ route('admissions.applications.show', $application) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>