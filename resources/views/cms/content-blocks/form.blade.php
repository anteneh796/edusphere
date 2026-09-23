@php
    use App\Domains\Cms\Models\ContentBlock;

    $item = $contentBlock;

    $initialItems = [];

    if (old('payload') !== null) {
        $initialItems = json_decode((string) old('payload'), true)['items'] ?? [];
    } elseif ($item) {
        $initialItems = $item->items;
    }

    $editorItems = collect($initialItems)->map(fn ($i) => [
        'icon' => (string) ($i['icon'] ?? ''),
        'title' => (string) ($i['title'] ?? ''),
        'text' => (string) ($i['text'] ?? ''),
        'detail' => (string) ($i['detail'] ?? ''),
        'points_text' => implode("\n", $i['points'] ?? []),
    ])->values()->all();
@endphp

<x-layouts.app :title="$item ? __('Edit section') : __('New section')">
    <x-page-header
        :title="$item ? __('Edit section') : __('New section')"
        :description="__('Sections are the blocks that make up each public website page.')">
        <a href="{{ route('cms.content-blocks.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to sections') }}
        </a>
        @if ($item)
            <a href="{{ route('public.home') }}" class="btn btn-outline" target="_blank">
                <x-icon name="eye" class="icon-sm" />
                {{ __('Preview site') }}
            </a>
        @endif
    </x-page-header>

    <x-card :title="$item ? __('Section content') : __('New section')">
        <form method="POST" action="{{ $item ? route('cms.content-blocks.update', $item) : route('cms.content-blocks.store') }}">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            <div class="form-grid-2">
                <x-bare-field :label="__('Page *')" for="page_slug">
                    <x-select name="page_slug" id="page_slug" :options="\App\Domains\Cms\Models\ContentBlock::PAGE_LABELS" :value="old('page_slug', $item?->page_slug)" required />
                    <x-error for="page_slug" />
                </x-bare-field>

                <x-bare-field :label="__('Section key *')" for="key">
                    <x-input name="key" id="key" :value="old('key', $item?->key)" list="content-block-keys"
                        :placeholder="__('e.g. hero, programmes, faq')" required />
                    <datalist id="content-block-keys">
                        @foreach (\App\Domains\Cms\Models\ContentBlock::KEY_LABELS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </datalist>
                    <x-error for="key" />
                    <div class="form-hint">{{ $item?->key_label }} — lowercase letters, numbers and dashes only.</div>
                </x-bare-field>
            </div>

            <div class="form-grid-2">
                <x-bare-field :label="__('Eyebrow / small label')" for="eyebrow">
                    <x-input name="eyebrow" id="eyebrow" :value="old('eyebrow', $item?->eyebrow)" :placeholder="__('e.g. Why families choose us')" />
                    <x-error for="eyebrow" />
                </x-bare-field>

                <x-bare-field :label="__('Title / heading')" for="title">
                    <x-input name="title" id="title" :value="old('title', $item?->title)" :placeholder="__('e.g. A path for every age')" />
                    <x-error for="title" />
                </x-bare-field>
            </div>

            <x-bare-field :label="__('Lead / intro text')" for="lead">
                <x-textarea name="lead" id="lead" rows="2">{{ old('lead', $item?->lead) }}</x-textarea>
                <x-error for="lead" />
            </x-bare-field>

            <x-bare-field :label="__('Body (HTML)')" for="body">
                <x-textarea name="body" id="body" rows="5" class="monospace">{{ old('body', $item?->body) }}</x-textarea>
                <x-error for="body" />
                <div class="form-hint">{{ __('HTML is allowed. Use :school to insert the school name.') }}</div>
            </x-bare-field>

            <x-bare-field :label="__('List items')">
                <div class="form-hint" style="margin-bottom: var(--space-2);">
                    {{ __('Used by cards, statistics, FAQ, age table and department grids. Each item may carry an icon, title, text, detail and bullet points (one per line).') }}
                </div>

                <div
                    x-data="blockItems(@json($editorItems))">
                    <template x-for="(item, index) in items" :key="index">
                        <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-3); margin-bottom: var(--space-2);">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label" x-bind:for="'item-icon-'+index">Icon</label>
                                    <input class="form-control" type="text" x-model="items[index].icon" x-bind:id="'item-icon-'+index" placeholder="e.g. beaker" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" x-bind:for="'item-title-'+index">Title</label>
                                    <input class="form-control" type="text" x-model="items[index].title" x-bind:id="'item-title-'+index" placeholder="e.g. STEM &amp; Innovation" />
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label" x-bind:for="'item-text-'+index">Text</label>
                                    <textarea class="form-control" rows="2" x-model="items[index].text" x-bind:id="'item-text-'+index"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" x-bind:for="'item-detail-'+index">Detail / sub-label</label>
                                    <input class="form-control" type="text" x-model="items[index].detail" x-bind:id="'item-detail-'+index" placeholder="e.g. Grades 1 &ndash; 6" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" x-bind:for="'item-points-'+index">Bullet points (one per line)</label>
                                <textarea class="form-control monospace" rows="2" x-model="items[index].points_text" x-bind:id="'item-points-'+index"></textarea>
                            </div>
                            <div style="display:flex; gap: var(--space-1); margin-top: var(--space-1);">
                                <button type="button" class="btn btn-ghost btn-sm" x-on:click="move(index, -1)" :disabled="index === 0">
                                    <x-icon name="chevron-up" class="icon-sm" /> {{ __('Up') }}
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm" x-on:click="move(index, 1)" :disabled="index === items.length - 1">
                                    <x-icon name="chevron-down" class="icon-sm" /> {{ __('Down') }}
                                </button>
                                <button type="button" class="btn btn-ghost-danger btn-sm" x-on:click="remove(index)">
                                    <x-icon name="trash" class="icon-sm" /> {{ __('Remove item') }}
                                </button>
                            </div>
                        </div>
                    </template>

                    <button type="button" class="btn btn-outline btn-sm" x-on:click="add()">
                        <x-icon name="plus" class="icon-sm" />
                        {{ __('Add item') }}
                    </button>

                    <input type="hidden" name="payload" x-bind:value="payloadJson" />
                </div>

                <x-error for="payload" />
            </x-bare-field>

            <div class="form-grid-2">
                <x-bare-field :label="__('Sort order')" for="sort_order">
                    <x-input type="number" name="sort_order" id="sort_order" :value="old('sort_order', $item?->sort_order ?? 0)" min="0" max="999" />
                    <x-error for="sort_order" />
                </x-bare-field>

                <x-bare-field>
                    <label class="checkbox-line" style="align-self: end;">
                        <input type="hidden" name="published" value="0">
                        <x-input type="checkbox" name="published" value="1" :checked="old('published', $item?->published ?? true)" />
                        <span>{{ __('Published (visible on the public site)') }}</span>
                    </label>
                    <x-error for="published" />
                </x-bare-field>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ $item ? __('Update section') : __('Create section') }}
                </button>
                <a href="{{ route('cms.content-blocks.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>

    <script>
        function blockItems(seedItems) {
            return {
                items: Array.isArray(seedItems) ? seedItems : [],
                get payloadJson() {
                    return JSON.stringify({ items: this.items });
                },
                add() {
                    this.items.push({ icon: '', title: '', text: '', detail: '', points_text: '' });
                },
                remove(index) {
                    this.items.splice(index, 1);
                },
                move(index, offset) {
                    const target = index + offset;
                    if (target < 0 || target >= this.items.length) {
                        return;
                    }
                    const [moved] = this.items.splice(index, 1);
                    this.items.splice(target, 0, moved);
                },
            };
        }
    </script>
</x-layouts.app>