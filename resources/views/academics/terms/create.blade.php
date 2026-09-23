<x-layouts.app :title="__('New term')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Terms'), 'url' => route('academics.terms.index')],
        ['label' => __('New term')],
    ]" />

    <x-page-header :title="__('New term')" :description="__('Add a term or semester to the current academic year.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.terms.store') }}" novalidate>
                @csrf
                @include('academics.terms._form', ['currentYearId' => $currentYearId])

                <div class="card-footer">
                    <a href="{{ route('academics.terms.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create term') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>