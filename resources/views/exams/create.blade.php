<x-layouts.app :title="'New Exam'">
    <x-breadcrumb :items="[
        ['label' => 'Exams', 'url' => route('exams.index')],
        ['label' => 'New exam'],
    ]" />

    <x-page-header title="New exam" description="Create an examination window, then assign papers to classes." />

    <div style="max-width: 860px;">
        <x-card>
            <form method="POST" action="{{ route('exams.store') }}">
                @csrf
                @include('exams._form')

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Create exam
                    </button>
                    <a href="{{ route('exams.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>