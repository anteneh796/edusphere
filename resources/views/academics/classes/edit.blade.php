<x-layouts.app :title="'Edit '.$classRoom->name">
    <x-breadcrumb :items="[
        ['label' => 'Academics', 'url' => route('academics.index')],
        ['label' => 'Classes', 'url' => route('academics.classes.index')],
        ['label' => $classRoom->name],
    ]" />

    <x-page-header title="Edit class" :description="'Update section '.$classRoom->name.'.'" />

    <div style="max-width: 640px;">
        <x-card>
            <form method="POST" action="{{ route('academics.classes.update', $classRoom) }}" novalidate>
                @csrf
                @method('PUT')
                @include('academics.classes._form', ['currentYearId' => $currentYearId])

                <div class="card-footer">
                    <a href="{{ route('academics.classes.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save changes
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>