<x-layouts.app :title="__('New Exam')">
    <x-breadcrumb :items="[
        ['label' => __('Exams'), 'url' => route('exams.index')],
        ['label' => __('New exam')],
    ]" />

    <x-page-header :title="__('New exam')" :description="__('Create an examination window, then assign papers to classes.')" />

    <div style="max-width: 860px;">
        <x-card>
            <form method="POST" action="{{ route('exams.store') }}">
                @csrf
                @include('exams._form')

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create exam') }}
                    </button>
                    <a href="{{ route('exams.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>