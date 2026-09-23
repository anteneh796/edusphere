<x-layouts.app :title="__('Edit grade')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Grades'), 'url' => route('academics.grades.index')],
        ['label' => __('Edit grade')],
    ]" />

    <x-page-header :title="__('Edit grade')" :description="$grade->name" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.grades.update', $grade) }}" novalidate>
                @csrf
                @method('PUT')
                @include('academics.grades._form')

                <div class="card-footer">
                    <a href="{{ route('academics.grades.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>