<x-layouts.app :title="'Edit · '.$exam->name">
    <x-breadcrumb :items="[
        ['label' => 'Exams', 'url' => route('exams.index')],
        ['label' => $exam->name, 'url' => route('exams.show', $exam)],
        ['label' => 'Edit'],
    ]" />

    <x-page-header :title="'Edit '.$exam->name" description="Update exam details. Publishing happens from the exam page." />

    <div style="max-width: 860px;">
        <x-card>
            <form method="POST" action="{{ route('exams.update', $exam) }}">
                @csrf
                @method('PUT')
                @include('exams._form')

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save changes
                    </button>
                    <a href="{{ route('exams.show', $exam) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>