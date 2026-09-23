<x-layouts.app :title="__('Calendar')">
    <x-page-header
        :title="__('Calendar')"
        :description="__('School events, homework deadlines, assessments and meetings.')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @php
        $typeBadge = [
            'event' => ['success', __('School event')],
            'homework' => ['info', __('Homework due')],
            'assessment' => ['warning', __('Assessment')],
            'meeting' => ['accent', __('Meeting')],
        ];
    @endphp

    <div class="grid gap-4">
        @forelse ($entries as $entry)
            @php
                $badge = $typeBadge[$entry['type']] ?? ['neutral', ucfirst($entry['type'])];
            @endphp
            <x-card hover>
                <div class="list-row">
                    <div class="text-center min-w-[64px]">
                        <div class="text-sm-semibold">{{ \Illuminate\Support\Carbon::parse($entry['date'])->format('d') }}</div>
                        <div class="text-xs text-light">{{ \Illuminate\Support\Carbon::parse($entry['date'])->format('M') }}</div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate">{{ $entry['title'] }}</div>
                        @if ($entry['context'])
                            <div class="text-xs text-light">{{ $entry['context'] }}</div>
                        @endif
                    </div>
                    <span class="badge badge-{{ $badge[0] }}">{{ $badge[1] }}</span>
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="calendar" :title="__('Nothing scheduled')" :message="__('Upcoming events, deadlines and meetings for the next 30 days will appear here.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>