<x-layouts.app :title="'Classes & Subjects'">
    <x-breadcrumb :items="[['label' => 'Academics']]" />

    <x-page-header title="Classes & Subjects" description="Structure your school's academic organization." />

    <div class="grid grid-stats">
        <x-stat-card :label="'Classes · '.$currentYear->name" :value="$stats['Classes']" icon="clipboard-check" color="primary" />
        <x-stat-card label="Grade levels" :value="$stats['Grades']" icon="graduation" color="info" />
        <x-stat-card label="Subjects" :value="$stats['Subjects']" icon="book-open" color="success" />
        <x-stat-card label="Teachers" :value="$stats['Teachers']" icon="users-large" color="warning" />
    </div>

    <div class="grid grid-cards">
        @foreach ($sections as $section)
            <a href="{{ $section['route'] }}" class="card card-hover" style="text-decoration:none;">
                <div class="flex flex-col" style="gap: var(--space-2); min-height: 130px;">
                    <div class="stat-icon primary"><x-icon name="{{ $section['icon'] }}" class="icon-lg" /></div>
                    <h3 style="font-size: var(--text-base); color: var(--color-text);">{{ $section['title'] }}</h3>
                    <p class="text-sm text-muted" style="flex:1;">{{ $section['description'] }}</p>
                    <span class="text-sm" style="color: var(--primary); font-weight: var(--weight-medium);">Open &rarr;</span>
                </div>
            </a>
        @endforeach
    </div>
</x-layouts.app>