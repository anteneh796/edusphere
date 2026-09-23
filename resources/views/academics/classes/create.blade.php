<x-layouts.app :title="__('New class')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Classes'), 'url' => route('academics.classes.index')],
        ['label' => __('New class')],
    ]" />

    <x-page-header :title="__('New class')" :description="__('Create a class section for a grade level.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.classes.store') }}" novalidate>
                @csrf
                @include('academics.classes._form', ['currentYearId' => $currentYearId])

                <div class="card-footer">
                    <a href="{{ route('academics.classes.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create class') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>