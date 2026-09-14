<x-layouts.app :title="'Edit guardian'">
    <x-breadcrumb :items="[
        ['label' => 'Guardians', 'url' => route('guardians.index')],
        ['label' => $guardian->full_name, 'url' => route('guardians.show', $guardian)],
        ['label' => 'Edit'],
    ]" />

    <x-page-header title="Edit guardian" description="Update the guardian record." />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('guardians.update', $guardian) }}" novalidate>
                @csrf
                @method('PUT')
                @include('guardians._form')

                <div class="card-footer">
                    <a href="{{ route('guardians.show', $guardian) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save changes
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>