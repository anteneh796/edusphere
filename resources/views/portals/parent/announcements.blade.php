<x-layouts.app :title="__('Announcements')">
    <x-page-header
        :title="__('Announcements')"
        :description="__('Latest news and announcements from the school.')" />

    <div class="grid gap-4">
        @forelse ($items as $item)
            <x-card :title="$item->title" :subtitle="$item->published_at?->format('d M Y, H:i')">
                <p class="text-sm">{{ $item->excerpt }}</p>
                @if ($item->category)
                    <span class="badge badge-neutral mt-2">{{ ucfirst($item->category) }}</span>
                @endif
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="newspaper" :title="__('No announcements')" :message="__('School announcements will appear here.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>