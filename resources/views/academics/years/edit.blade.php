<x-layouts.app :title="__('Edit academic year')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Academic years'), 'url' => route('academics.years.index')],
        ['label' => __('Edit academic year')],
    ]" />

    <x-page-header :title="__('Edit academic year')" :description="$year->name" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.years.update', $year) }}" novalidate>
                @csrf
                @method('PUT')
                @include('academics.years._form')

                <div class="card-footer">
                    <a href="{{ route('academics.years.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>