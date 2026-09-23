<x-layouts.app :title="__('Edit term')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Terms'), 'url' => route('academics.terms.index')],
        ['label' => __('Edit term')],
    ]" />

    <x-page-header :title="__('Edit term')" :description="$term->name" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.terms.update', $term) }}" novalidate>
                @csrf
                @method('PUT')
                @include('academics.terms._form')

                <div class="card-footer">
                    <a href="{{ route('academics.terms.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>