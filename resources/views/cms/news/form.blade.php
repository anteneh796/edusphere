<x-layouts.app :title="$item ? __('Edit post') : __('New post')">
    <x-page-header
        :title="$item ? __('Edit post') : __('New post')"
        :description="__('Compose a news post for the public website.')">
        <a href="{{ route('cms.news.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to news') }}
        </a>
    </x-page-header>

    <x-card :title="__('Post details')">
        <form method="POST"
            action="{{ $item ? route('cms.news.update', $item) : route('cms.news.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item) @method('PUT') @endif

            <x-bare-field :label="__('Title *')" for="title">
                <x-input name="title" id="title" :value="old('title', $item?->title)" required />
                <x-error for="title" />
            </x-bare-field>

            <x-bare-field :label="__('Category')" for="category">
                <x-select
                    name="category"
                    id="category"
                    :options="App\Domains\Cms\Models\NewsItem::CATEGORIES"
                    :value="old('category', $item?->category)"
                    :placeholder="__('Select a category')" />
                <x-error for="category" />
            </x-bare-field>

            <x-bare-field :label="__('Excerpt')" for="excerpt">
                <x-textarea name="excerpt" id="excerpt" rows="2">{{ old('excerpt', $item?->excerpt) }}</x-textarea>
                <x-error for="excerpt" />
            </x-bare-field>

            <x-bare-field :label="__('Content *')" for="body">
                <x-textarea name="body" id="body" rows="10" class="monospace">{{ old('body', $item?->body) }}</x-textarea>
                <x-error for="body" />
                <div class="form-hint">{{ __('HTML is allowed.') }}</div>
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field :label="__('Featured image')" for="featured_image">
                    <x-input type="file" name="featured_image" id="featured_image" />
                    @if ($item?->image_path)
                        <div class="form-hint">{{ __('Current') }}: {{ $item->image_path }} — {{ __('upload a new file to replace it.') }}</div>
                    @endif
                    <x-error for="featured_image" />
                </x-bare-field>

                <x-bare-field :label="__('Publish on')" for="published_at">
                    <x-input type="datetime-local" name="published_at" id="published_at" :value="old('published_at', $item?->published_at?->format('Y-m-d\TH:i'))" />
                    <x-error for="published_at" />
                </x-bare-field>
            </div>

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
                    {{ $item ? __('Update post') : __('Create post') }}
                </button>
                <a href="{{ route('cms.news.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
