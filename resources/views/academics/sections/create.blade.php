<x-layouts.app :title="__('New section')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Sections'), 'url' => route('academics.sections.index')],
        ['label' => __('New section')],
    ]" />

    <x-page-header :title="__('New section')" :description="__('Add a section letter for classes in the current year.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.sections.store') }}" novalidate>
                @csrf
                @include('academics.sections._form', ['currentYearId' => $currentYearId])

                <div class="card-footer">
                    <a href="{{ route('academics.sections.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create section') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>