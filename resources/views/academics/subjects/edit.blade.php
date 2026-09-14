<x-layouts.app :title="'Edit '.$subject->name">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Subjects', 'url' => route('academics.subjects.index')],
        ['label' => $subject->name],
    ]" />

    <x-page-header title="Edit subject" :description="'Update '.$subject->name.' ('.$subject->code.').'" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.subjects.update', $subject) }}" novalidate>
                @csrf
                @method('PUT')
                <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                    <x-input name="name" label="Name" :value="old('name', $subject->name)" required />
                    <x-input name="code" label="Code" :value="old('code', $subject->code)" hint="Short code, letters only." required />
                </div>
                <x-input name="description" label="Description" :value="old('description', $subject->description)" placeholder="Optional description of the subject's scope." />
                <x-input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $subject->sort_order)" hint="Lower values appear first." />

                <div class="card-footer">
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save changes
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>