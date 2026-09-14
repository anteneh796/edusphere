<x-layouts.app :title="'New subject'">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Subjects', 'url' => route('academics.subjects.index')],
        ['label' => 'New subject'],
    ]" />

    <x-page-header title="New subject" description="Add a subject to the school curriculum." />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.subjects.store') }}" novalidate>
                @csrf
                <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                    <x-input name="name" label="Name" placeholder="e.g. General Science" required />
                    <x-input name="code" label="Code" placeholder="e.g. GSCI" hint="Short code, letters only." required />
                </div>
                <x-input name="description" label="Description" :value="old('description')" placeholder="Optional description of the subject's scope." />
                <x-input name="sort_order" type="number" label="Sort order" :value="old('sort_order', 0)" hint="Lower values appear first." />

                <div class="card-footer">
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Create subject
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>