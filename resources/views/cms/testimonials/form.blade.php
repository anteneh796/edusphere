<x-layouts.app :title="$item ? __('Edit testimonial') : __('New testimonial')">
    <x-page-header
        :title="$item ? __('Edit testimonial') : __('New testimonial')"
        :description="__('Share a kind word from a parent or alumnus on the home page.')">
        <a href="{{ route('cms.testimonials.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to testimonials') }}
        </a>
    </x-page-header>

    <x-card :title="__('Testimonial')">
        <form method="POST"
            action="{{ $item ? route('cms.testimonials.update', $item) : route('cms.testimonials.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item) @method('PUT') @endif

            <div class="form-grid-2">
                <x-bare-field :label="__('Full name *')" for="name">
                    <x-input name="name" id="name" :value="old('name', $item?->name)" required />
                    <x-error for="name" />
                </x-bare-field>

                <x-bare-field :label="__('Role / relationship')" for="role">
                    <x-input name="role" id="role" :value="old('role', $item?->role)" :placeholder="__('e.g. Parent of two')" />
                    <x-error for="role" />
                </x-bare-field>
            </div>

            <x-bare-field :label="__('Quote *')" for="quote">
                <x-textarea name="quote" id="quote" rows="4" class="monospace">{{ old('quote', $item?->quote) }}</x-textarea>
                <x-error for="quote" />
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field :label="__('Avatar photo')" for="avatar">
                    <x-input type="file" name="avatar" id="avatar" />
                    @if ($item?->avatar_url)
                        <div class="form-hint">{{ __('Current') }}: {{ $item->avatar_path }} — {{ __('upload a new file to replace it.') }}</div>
                    @endif
                    <x-error for="avatar" />
                </x-bare-field>

                <x-bare-field :label="__('Sort order')" for="sort_order">
                    <x-input type="number" name="sort_order" id="sort_order" :value="old('sort_order', $item?->sort_order)" />
                    <x-error for="sort_order" />
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
                    {{ $item ? __('Update testimonial') : __('Add testimonial') }}
                </button>
                <a href="{{ route('cms.testimonials.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>