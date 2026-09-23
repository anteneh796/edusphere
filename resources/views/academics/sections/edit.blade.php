<x-layouts.app :title="__('Edit section')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Sections'), 'url' => route('academics.sections.index')],
        ['label' => __('Edit section')],
    ]" />

    <x-page-header :title="__('Edit section')" :description="__('Update this section letter.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.sections.update', $section) }}" novalidate>
                @csrf
                @method('PUT')
                @include('academics.sections._form')

                <div class="card-footer">
                    <a href="{{ route('academics.sections.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>