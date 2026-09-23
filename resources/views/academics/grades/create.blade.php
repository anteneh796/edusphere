<x-layouts.app :title="__('New grade')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Grades'), 'url' => route('academics.grades.index')],
        ['label' => __('New grade')],
    ]" />

    <x-page-header :title="__('New grade')" :description="__('Add a Kindergarten through Grade 8 level.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.grades.store') }}" novalidate>
                @csrf
                @include('academics.grades._form')

                <div class="card-footer">
                    <a href="{{ route('academics.grades.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create grade') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>