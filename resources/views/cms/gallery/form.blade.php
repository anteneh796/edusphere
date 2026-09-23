<x-layouts.app :title="$item ? __('Edit gallery item') : __('Add gallery item')">
    <x-page-header
        :title="$item ? __('Edit gallery item') : __('Add gallery item')"
        :description="__('Add a photo to the public gallery.')">
        <a href="{{ route('cms.gallery.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to gallery') }}
        </a>
    </x-page-header>

    <x-card :title="__('Gallery item')">
        <form method="POST"
            action="{{ $item ? route('cms.gallery.update', $item) : route('cms.gallery.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item) @method('PUT') @endif

            <x-bare-field :label="__('Caption')" for="caption">
                <x-input name="caption" id="caption" :value="old('caption', $item?->caption)" />
                <x-error for="caption" />
            </x-bare-field>

            <x-bare-field :label="__('Album / collection')" for="album">
                <x-input name="album" id="album" :value="old('album', $item?->album)" :placeholder="__('e.g. Sports Day 2026, Primary Graduation')" />
                <x-error for="album" />
                <div class="form-hint">{{ __('Items with the same album are grouped together on the gallery page.') }}</div>
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field :label="__('Media type')" for="media_type">
                    <x-select
                        name="media_type"
                        id="media_type"
                        :options="\App\Domains\Cms\Models\GalleryItem::MEDIA_TYPE_LABELS"
                        :value="old('media_type', $item?->media_type ?? \App\Domains\Cms\Models\GalleryItem::MEDIA_IMAGE)" />
                    <x-error for="media_type" />
                </x-bare-field>

                <x-bare-field :label="__('Sort order')" for="sort_order">
                    <x-input type="number" name="sort_order" id="sort_order" :value="old('sort_order', $item?->sort_order)" />
                    <x-error for="sort_order" />
                </x-bare-field>
            </div>

            <div class="form-grid-2">
                <x-bare-field :label="__('Image')" for="image">
                    <x-input type="file" name="image" id="image" />
                    <x-error for="image" />
                </x-bare-field>

                <x-bare-field :label="__('Video link (YouTube / Vimeo)')" for="video_url">
                    <x-input name="video_url" id="video_url" :value="old('video_url', $item?->video_url)" :placeholder="__('https://www.youtube.com/watch?v=…')" />
                    <x-error for="video_url" />
                    <div class="form-hint">{{ __('Required when media type is Video.') }}</div>
                </x-bare-field>
            </div>

            @if ($item?->image_url)
                <div class="form-preview">
                    <img src="{{ $item->image_url }}" alt="" class="thumb thumb-md">
                    <div class="form-hint">{{ __('Existing image — upload a new one to replace it.') }}</div>
                </div>
            @endif

            <x-bare-field>
                <input type="hidden" name="published" value="0">
                <label class="checkbox-line">
                    <x-input type="checkbox" name="published" value="1" :checked="old('published', $item?->published ?? false)" />
                    <span>{{ __('Published (visible on the public site)') }}</span>
                </label>
            </x-bare-field>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ $item ? __('Save changes') : __('Add photo') }}
                </button>
                <a href="{{ route('cms.gallery.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
