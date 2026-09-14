<x-layouts.app :title="'New class'">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Classes', 'url' => route('academics.classes.index')],
        ['label' => 'New class'],
    ]" />

    <x-page-header title="New class" description="Create a class section for a grade level." />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.classes.store') }}" novalidate>
                @csrf
                @include('academics.classes._form', ['currentYearId' => $currentYearId])

                <div class="card-footer">
                    <a href="{{ route('academics.classes.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Create class
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>