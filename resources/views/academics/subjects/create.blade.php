<x-layouts.app :title="__('New subject')">
    <x-breadcrumb :items="[
        ['label' => __('Academics'), 'url' => route('academics.index')],
        ['label' => __('Subjects'), 'url' => route('academics.subjects.index')],
        ['label' => __('New subject')],
    ]" />

    <x-page-header :title="__('New subject')" :description="__('Add a subject to the school curriculum.')" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.subjects.store') }}" novalidate>
                @csrf
                <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                    <x-input name="name" :label="__('Name')" :placeholder="__('e.g. General Science')" required />
                    <x-input name="code" :label="__('Code')" :placeholder="__('e.g. GSCI')" :hint="__('Short code, letters only.')" required />
                </div>
                <x-input name="description" :label="__('Description')" :value="old('description')" :placeholder="__('Optional description of the subject's scope.')" />
                <x-input name="sort_order" type="number" :label="__('Sort order')" :value="old('sort_order', 0)" :hint="__('Lower values appear first.')" />

                <div class="card-footer">
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create subject') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>