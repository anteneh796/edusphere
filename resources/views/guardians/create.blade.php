<x-layouts.app :title="'Register guardian'">
    <x-breadcrumb :items="[
        ['label' => 'Guardians', 'url' => route('guardians.index')],
        ['label' => 'Register guardian'],
    ]" />

    <x-page-header title="Register guardian" description="Create a parent/guardian record for the school." />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('guardians.store') }}" novalidate>
                @csrf
                @include('guardians._form')

                <div class="card-footer">
                    <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Register guardian
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>