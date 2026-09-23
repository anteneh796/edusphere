<x-layouts.app :title="__('New academic year')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Academic years'), 'url' => route('academics.years.index')],
        ['label' => __('New academic year')],
    ]" />

    <x-page-header :title="__('New academic year')" :description="__('Create a school calendar year.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.years.store') }}" novalidate>
                @csrf
                @include('academics.years._form')

                <div class="card-footer">
                    <a href="{{ route('academics.years.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create year') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>