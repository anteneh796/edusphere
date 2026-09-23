<x-layouts.app :title="__('Absence requests')">
    <x-page-header
        :title="__('Absence requests')"
        :description="__('Explain a planned or past absence for your child.')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3" style="align-items:start;">
        <x-card :title="__('Explain an absence')">
            @if ($wards->isEmpty())
                <x-empty-state icon="users" :title="__('No children')" :message="__('Link a child first to explain an absence.')" />
            @else
                <form method="POST" action="{{ route('cms.parent.absence-requests.store') }}" class="grid gap-4">
                    @csrf

                    <x-select
                        name="student"
                        :label="__('Child')"
                        :options="$wards->mapWithKeys(fn ($w) => [$w->getKey() => $w->full_name.' ('.$w->student_number.')'])"
                        :value="old('student', $ward?->getKey())"
                        placeholder="{{ __('Select a child…') }}"
                        required />

                    <x-input
                        name="absence_date"
                        type="date"
                        :label="__('Absence date')"
                        :value="old('absence_date')"
                        required />

                    <x-textarea name="reason" :label="__('Reason')" :value="old('reason')" rows="3" required />

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Submit explanation') }}</button>
                    </div>
                </form>
            @endif
        </x-card>

        <x-card :title="__('Your submissions')" style="grid-column: span 2;">
            @forelse ($requests as $request)
                <div class="list-row">
                    <x-avatar :initials="$request->student?->initials() ?? '–'" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate">{{ $request->student?->full_name ?? '—' }}</div>
                        <div class="text-xs text-light">
                            {{ $request->absence_date?->format('d M Y') }} · {{ $request->reason }}
                        </div>
                    </div>
                    <span class="badge badge-{{ $request->status?->badgeColor() ?? 'neutral' }}">{{ $request->status?->label() ?? ucfirst($request->status ?? '') }}</span>
                </div>
                @if ($request->reviewer_note)
                    <p class="text-xs text-light mt-1 ml-0">
                        {{ __('School note:') }} {{ $request->reviewer_note }}
                    </p>
                @endif
            @empty
                <x-empty-state icon="clipboard-check" :title="__('No submissions yet')" :message="__('Absence explanations you send will be listed here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>