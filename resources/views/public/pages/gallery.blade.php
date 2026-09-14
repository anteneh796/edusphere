<x-layouts.public title="Photo Gallery">
    <div class="public-page-head">
        <h1>Photo Gallery</h1>
        <p>Moments from around {{ \App\Domains\Settings\Models\Setting::schoolName() }}.</p>
    </div>

    @if ($items->isEmpty())
        <x-empty-state icon="image" title="Nothing here yet" message="Gallery photos will appear once they've been published." />
    @else
        <div class="public-grid gallery-grid">
            @foreach ($items as $item)
                <figure class="gallery-tile">
                    @if ($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->caption ?? $item->title }}" loading="lazy">
                    @endif
                    @if ($item->caption)
                        <figcaption>{{ $item->caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>

        @if (method_exists($items, 'links'))
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @endif
    @endif
</x-layouts.public>
