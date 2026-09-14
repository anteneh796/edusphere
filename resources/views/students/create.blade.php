<x-layouts.app :title="'Register student'">
    <x-breadcrumb :items="[
        ['label' => 'Students', 'url' => route('students.index')],
        ['label' => 'Register student'],
    ]" />

    <x-page-header title="Register student" description="Create a student record with placement and primary guardian." />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('students.store') }}" novalidate>
                @csrf
                @include('students._form', ['currentYearName' => $currentYearName ?? \App\Domains\Academics\Models\AcademicYear::current()->first()?->name])

                <div class="card-footer">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Register student
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>