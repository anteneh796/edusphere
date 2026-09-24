<x-layouts.app :title="__('Documents')">
    <x-page-header
        :title="__('Documents')"
        :description="$ward ? __('Verified documents for :name', ['name' => $ward->full_name]) : __('Documents')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $ward)
        <x-card>
            <x-empty-state icon="users" :title="__('No child selected')" :message="__('Link or select a child to view documents.')" />
        </x-card>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2" style="align-items:start;">
            <x-card :title="__('Verified documents')">
                @forelse ($documents as $document)
                    <div class="list-row">
                        <x-icon name="file-text" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $document->name }}</div>
                            <div class="text-xs text-light">
                                {{ $document->categoryLabel() }}
                                · {{ $document->size ? number_format($document->size / 1024, 1).' KB' : '' }}
                                · {{ $document->verified_at?->format('d M Y') ?? '' }}
                            </div>
                        </div>
                        <span class="badge badge-success">{{ __('Verified') }}</span>
                        @if ($document->path)
                            <a href="{{ url('storage/'.$document->path) }}" target="_blank" class="btn btn-ghost btn-sm">
                                <x-icon name="download" class="icon-sm" />
                            </a>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="file-text" :title="__('No documents')" :message="__('Verified documents such as birth certificates and transcripts will appear here.')" />
                @endforelse
            </x-card>

        </div>
    @endif
</x-layouts.app>