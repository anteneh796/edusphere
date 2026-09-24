<x-layouts.app :title="__('Parent service requests')">
    <x-page-header
        :title="__('Parent service requests')"
        :description="__('Administrative requests submitted by parents.')" />

    <div class="grid gap-4">
        @forelse ($requests as $request)
            <x-card :title="$request->reference_number" :subtitle="$request->subject">
                <div class="list-row">
                    <x-avatar :initials="$request->parent?->initials() ?? '–'" />
                    <div class="min-w-0 flex-1">
                        <div class="text-sm">
                            {{ $request->parent?->full_name ?? '—' }}
                            @if ($request->student)
                                <span class="text-light">· {{ $request->student->full_name }} ({{ $request->student->student_number }})</span>
                            @endif
                        </div>
                        <div class="text-xs text-light">
                            {{ $request->type?->label() ?? ucfirst($request->type ?? '') }}
                            · {{ $request->submitted_at?->format('d M Y') ?? '—' }}
                        </div>
                        @if ($request->description)
                            <div class="text-xs mt-1">{{ $request->description }}</div>
                        @endif
                        @if ($request->resolution)
                            <div class="text-xs text-light mt-1">{{ __('Resolution:') }} {{ $request->resolution }}</div>
                        @endif
                    </div>
                    <span class="badge badge-{{ $request->status?->badgeColor() ?? 'neutral' }}">{{ $request->status?->label() ?? ucfirst($request->status ?? '') }}</span>
                </div>

                @if (in_array($request->status?->value ?? null, ['submitted', 'processing'], true))
                    <details class="mt-3">
                        <summary class="text-sm cursor-pointer text-primary">{{ __('Process request') }}</summary>
                        <div class="mt-2">
                            <form method="POST" action="{{ route('parent-services.requests.process', $request) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <x-input name="resolution" :label="__('Resolution note')" placeholder="{{ __('e.g. certificate ready for pickup') }}" />
                                <input type="hidden" name="status" value="{{ \App\Support\Enums\ParentRequestStatus::Completed->value }}">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Mark completed') }}</button>
                            </form>
                            <form method="POST" action="{{ route('parent-services.requests.process', $request) }}" class="flex flex-wrap items-end gap-3 mt-2">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Support\Enums\ParentRequestStatus::Processing->value }}">
                                <button type="submit" class="btn btn-ghost btn-sm">{{ __('Mark in progress') }}</button>
                            </form>
                        </div>
                    </details>
                @endif
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="inbox" :title="__('No requests')" :message="__('Parent service requests will appear here.')" />
            </x-card>
        @endforelse
    </div>
</x-layouts.app>