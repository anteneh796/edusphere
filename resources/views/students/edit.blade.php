<x-layouts.app :title="'Edit student'">
    <x-breadcrumb :items="[
        ['label' => 'Students', 'url' => route('students.index')],
        ['label' => $student->full_name, 'url' => route('students.show', $student)],
        ['label' => 'Edit'],
    ]" />

    <x-page-header title="Edit student" description="Update the student record and placement." />

    <div style="max-width: 1100px;">
        <x-card>
            <form method="POST" action="{{ route('students.update', $student) }}" novalidate>
                @csrf
                @method('PUT')
                @include('students._form')

                <div class="card-footer">
                    <a href="{{ route('students.show', $student) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save changes
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>