<x-layouts.app :title="$item ? __('Edit event') : __('New event')">
    <x-page-header
        :title="$item ? __('Edit event') : __('New event')"
        :description="__('Add or update an event for the public website calendar.')">
        <a href="{{ route('cms.events.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to events') }}
        </a>
    </x-page-header>

    <x-card :title="__('Event details')">
        <form method="POST"
            action="{{ $item ? route('cms.events.update', $item) : route('cms.events.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item) @method('PUT') @endif

            <x-bare-field :label="__('Title *')" for="title">
                <x-input name="title" id="title" :value="old('title', $item?->title)" required />
                <x-error for="title" />
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field :label="__('Starts at *')" for="starts_at">
                    <x-input type="datetime-local" name="starts_at" id="starts_at" :value="old('starts_at', $item?->starts_at?->format('Y-m-d\TH:i'))" required />
                    <x-error for="starts_at" />
                </x-bare-field>

                <x-bare-field :label="__('Ends at')" for="ends_at">
                    <x-input type="datetime-local" name="ends_at" id="ends_at" :value="old('ends_at', $item?->ends_at?->format('Y-m-d\TH:i'))" />
                    <x-error for="ends_at" />
                </x-bare-field>
            </div>

            <div class="form-grid-2">
                <x-bare-field :label="__('Location')" for="location">
                    <x-input name="location" id="location" :value="old('location', $item?->location)" :placeholder="__('e.g. Main Hall, Sports Field')" />
                    <x-error for="location" />
                </x-bare-field>

                <x-bare-field :label="__('Cover image')" for="cover_image">
                    <x-input type="file" name="cover_image" id="cover_image" />
                    @if ($item?->cover_url)
                        <div class="form-hint">{{ __('Current') }}: {{ $item->cover_path }} — {{ __('upload a new file to replace it.') }}</div>
                    @endif
                    <x-error for="cover_image" />
                </x-bare-field>
            </div>

            <x-bare-field :label="__('Description')" for="description">
                <x-textarea name="description" id="description" rows="6">{{ old('description', $item?->description) }}</x-textarea>
                <x-error for="description" />
            </x-bare-field>

            <x-bare-field :label="__('Slug')" for="slug">
                <x-input name="slug" id="slug" :value="old('slug', $item?->slug)" :placeholder="__('leave blank to generate from the title')" />
                <x-error for="slug" />
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field>
                    <input type="hidden" name="featured" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="featured" value="1" :checked="old('featured', $item?->featured ?? false)" />
                        <span>{{ __('Featured (highlighted on the calendar)') }}</span>
                    </label>
                </x-bare-field>

                <x-bare-field>
                    <input type="hidden" name="published" value="0">
                    <label class="checkbox-line">
                        <x-input type="checkbox" name="published" value="1" :checked="old('published', $item?->published ?? false)" />
                        <span>{{ __('Published (visible on the public site)') }}</span>
                    </label>
                </x-bare-field>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ $item ? __('Update event') : __('Create event') }}
                </button>
                <a href="{{ route('cms.events.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>