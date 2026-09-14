<x-layouts.app :title="$item ? 'Edit gallery item' : 'Add gallery item'">
    <x-page-header
        :title="$item ? 'Edit gallery item' : 'Add gallery item'"
        description="Add a photo to the public gallery.">
        <a href="{{ route('cms.gallery.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            Back to gallery
        </a>
    </x-page-header>

    <x-card title="Gallery item">
        <form method="POST"
            action="{{ $item ? route('cms.gallery.update', $item) : route('cms.gallery.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item) @method('PUT') @endif

            <x-bare-field label="Title" for="title">
                <x-input name="title" id="title" :value="old('title', $item?->title)" />
                <x-error for="title" />
            </x-bare-field>

            <x-bare-field label="Caption" for="caption">
                <x-input name="caption" id="caption" :value="old('caption', $item?->caption)" />
                <x-error for="caption" />
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field label="Image" for="image">
                    <x-input type="file" name="image" id="image" />
                    <x-error for="image" />
                </x-bare-field>

                <x-bare-field label="Sort order" for="sort_order">
                    <x-input type="number" name="sort_order" id="sort_order" :value="old('sort_order', $item?->sort_order)" />
                    <x-error for="sort_order" />
                </x-bare-field>
            </div>

            @if ($item?->image_url)
                <div class="form-preview">
                    <img src="{{ $item->image_url }}" alt="" class="thumb thumb-md">
                    <div class="form-hint">Existing image — upload a new one to replace it.</div>
                </div>
            @endif

            <x-bare-field>
                <label class="checkbox-line">
                    <x-input type="checkbox" name="published" value="1" :checked="old('published', $item?->published ?? false)" />
                    <span>Published (visible on the public site)</span>
                </label>
            </x-bare-field>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ $item ? 'Save changes' : 'Add photo' }}
                </button>
                <a href="{{ route('cms.gallery.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
