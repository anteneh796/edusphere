<x-layouts.app :title="__('Register guardian')">
    <x-breadcrumb :items="[
        ['label' => __('Guardians'), 'url' => route('guardians.index')],
        ['label' => __('Register guardian')],
    ]" />

    <x-page-header :title="__('Register guardian')" :description="__('Create a parent/guardian record for the school.')" />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('guardians.store') }}" novalidate>
                @csrf
                @include('guardians._form')

                <div class="card-footer">
                    <a href="{{ route('guardians.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Register guardian') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>