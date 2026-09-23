@props([
    'ward' => null,
    'wards' => [],
])

@if ($wards->count() > 1)
    <div class="dropdown" x-data="dropdown" @click.outside="open = false" :class="{ open: open }">
        <button type="button" class="btn btn-ghost" @click="toggle">
            <x-icon name="users" class="icon-sm" />
            {{ $ward?->full_name ?? __('Select a child') }}
            <x-icon name="chevron-down" class="icon-sm" />
        </button>

        <div class="dropdown-menu" x-show="open" x-cloak x-transition style="right: 0; min-width: 240px;">
            <div class="dropdown-header">
                <b>{{ __('Switch child') }}</b>
            </div>
            <form method="POST" action="{{ route('cms.parent.wards.switch') }}" class="p-1">
                @csrf
                <div class="px-2 pb-1">
                    <select name="student" class="form-select" required @change="this.form.submit()">
                        @foreach ($wards as $option)
                            <option value="{{ $option->getKey() }}" @selected($option->getKey() === $ward?->getKey())>
                                {{ $option->full_name }} — {{ $option->classRoom?->name ?? __('No class') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>
@elseif ($ward)
    <span class="badge badge-accent">
        <x-icon name="user" class="icon-sm" />
        {{ $ward->full_name }}
    </span>
@endif