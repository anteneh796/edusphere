<x-layouts.app :title="__('Page sections')">
    <x-page-header
        :title="__('Page sections')"
        :description="__('Every section of the public website is editable here — hero banners, statistics, programmes, facilities, FAQ, departments and more.')">
        <a href="{{ route('cms.content-blocks.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            {{ __('New section') }}
        </a>
    </x-page-header>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div style="display:grid; gap: var(--space-4);">
        @forelse ($groups as $label => $blocks)
            <x-card :title="$label" :subtitle="trans_choice(':count section(s)', $blocks->count())">
                <x-slot name="actions">
                    <span class="text-xs text-muted">{{ __('Sort order: lower numbers appear first.') }}</span>
                </x-slot>

                <form method="POST" action="{{ route('cms.content-blocks.reorder') }}">
                    @csrf

                    @foreach ($blocks as $block)
                        <div class="list-row">
                            <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0; flex:1;">
                                <input type="number" name="orders[{{ $block->getKey() }}]" value="{{ $block->sort_order }}"
                                    class="form-control" style="width: 72px;" min="0" max="999"
                                    aria-label="{{ __('Sort order') }}">
                                <div style="min-width:0;">
                                    <div class="truncate" style="font-weight: var(--weight-semibold);">
                                        {{ $block->key_label }}
                                        <span class="text-xs text-light">· {{ $block->key }}</span>
                                    </div>
                                    <div class="text-xs text-muted truncate">
                                        {!! $block->eyebrow ? e($block->eyebrow).' — ' : '' !!}{{ $block->title }}
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap: var(--space-1);">
                                <span class="badge {{ $block->published ? 'badge-success' : 'badge-warning' }}">
                                    {{ $block->published ? __('published') : __('hidden') }}
                                </span>
                                <a href="{{ route('cms.content-blocks.edit', $block) }}" class="btn btn-ghost btn-sm">
                                    <x-icon name="pencil" class="icon-sm" />
                                    {{ __('Edit') }}
                                </a>
                                <form method="POST" action="{{ route('cms.content-blocks.destroy', $block) }}" onsubmit="return confirm(@js(__('Delete this section and its content? This cannot be undone.')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost-danger btn-sm">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="card-footer-actions" style="margin-top: var(--space-2);">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <x-icon name="save" class="icon-sm" />
                            {{ __('Save order') }}
                        </button>
                    </div>
                </form>
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="layers" :title="__('No sections yet')" :message="__('Create your first section to start editing the public website.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>