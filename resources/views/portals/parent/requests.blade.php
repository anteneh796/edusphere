<x-layouts.app :title="__('Service requests')">
    <x-page-header
        :title="__('Service requests')"
        :description="__('Request documents and administrative services from the school.')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @php
        $requestTypeOptions = collect(\App\Support\Enums\ParentRequestType::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()]);
    @endphp

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3" style="align-items:start;">
        <x-card :title="__('New request')">
            <form method="POST" action="{{ route('cms.parent.requests.store') }}" class="grid gap-4">
                @csrf

                <x-select
                    name="type"
                    :label="__('Request type')"
                    :options="$requestTypeOptions"
                    :value="old('type', \App\Support\Enums\ParentRequestType::DocumentRequest->value)"
                    required />

                <x-select
                    name="student"
                    :label="__('Child (optional)')"
                    :options="$wards->mapWithKeys(fn ($w) => [$w->getKey() => $w->full_name.' ('.$w->student_number.')'])"
                    :value="old('student', $ward?->getKey())"
                    placeholder="{{ __('No child in particular') }}" />

                <x-input name="subject" :label="__('Subject')" :value="old('subject')" required />

                <x-textarea name="description" :label="__('Details (optional)')" :value="old('description')" rows="3" />

                <div>
                    <button type="submit" class="btn btn-primary">{{ __('Submit request') }}</button>
                </div>
            </form>
        </x-card>

        <x-card :title="__('Your requests')" style="grid-column: span 2;">
            @forelse ($requests as $request)
                <div class="list-row">
                    <x-icon name="inbox" class="icon-sm text-light" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate">
                            <span class="font-medium">{{ $request->reference_number }}</span> — {{ $request->subject }}
                        </div>
                        <div class="text-xs text-light">
                            {{ $request->type?->label() ?? ucfirst($request->type ?? '') }}
                            @if ($request->student) · {{ $request->student->full_name }} @endif
                            · {{ $request->submitted_at?->format('d M Y') ?? '—' }}
                        </div>
                        @if ($request->resolution)
                            <div class="text-xs text-light mt-1">{{ __('School note:') }} {{ $request->resolution }}</div>
                        @endif
                    </div>
                    <span class="badge badge-{{ $request->status?->badgeColor() ?? 'neutral' }}">{{ $request->status?->label() ?? ucfirst($request->status ?? '') }}</span>
                </div>
            @empty
                <x-empty-state icon="inbox" :title="__('No requests yet')" :message="__('Service requests you submit will be tracked here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>