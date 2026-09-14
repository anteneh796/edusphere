<x-layouts.app :title="$item ? 'Edit post' : 'New post'">
    <x-page-header
        :title="$item ? 'Edit post' : 'New post'"
        description="Compose a news post for the public website.">
        <a href="{{ route('cms.news.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            Back to news
        </a>
    </x-page-header>

    <x-card title="Post details">
        <form method="POST"
            action="{{ $item ? route('cms.news.update', $item) : route('cms.news.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item) @method('PUT') @endif

            <x-bare-field label="Title *" for="title">
                <x-input name="title" id="title" :value="old('title', $item?->title)" required />
                <x-error for="title" />
            </x-bare-field>

            <x-bare-field label="Excerpt" for="excerpt">
                <x-textarea name="excerpt" id="excerpt" rows="2">{{ old('excerpt', $item?->excerpt) }}</x-textarea>
                <x-error for="excerpt" />
            </x-bare-field>

            <x-bare-field label="Content *" for="content">
                <x-textarea name="content" id="content" rows="10" class="monospace">{{ old('content', $item?->content) }}</x-textarea>
                <x-error for="content" />
                <div class="form-hint">HTML is allowed.</div>
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field label="Featured image" for="featured_image">
                    <x-input type="file" name="featured_image" id="featured_image" />
                    <x-error for="featured_image" />
                </x-bare-field>

                <x-bare-field label="Publish on" for="published_at">
                    <x-input type="datetime-local" name="published_at" id="published_at" :value="old('published_at', $item?->published_at?->format('Y-m-d\TH:i'))" />
                    <x-error for="published_at" />
                </x-bare-field>
            </div>

            <x-bare-field>
                <label class="checkbox-line">
                    <x-input type="checkbox" name="published" value="1" :checked="old('published', $item?->published ?? false)" />
                    <span>Published (visible on the public site)</span>
                </label>
            </x-bare-field>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ $item ? 'Update post' : 'Create post' }}
                </button>
                <a href="{{ route('cms.news.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
