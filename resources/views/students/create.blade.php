<x-layouts.app :title="__('Register student')">
    <x-breadcrumb :items="[
        ['label' => __('Students'), 'url' => route('students.index')],
        ['label' => __('Register student')],
    ]" />

    <x-page-header :title="__('Register student')" :description="__('Create a student record with placement and primary guardian.')" />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('students.store') }}" novalidate enctype="multipart/form-data">
                @csrf
                @include('students._form', ['currentYearName' => $currentYearName ?? \App\Domains\Academics\Models\AcademicYear::current()->first()?->name])

                <div class="card-footer">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Register student') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>