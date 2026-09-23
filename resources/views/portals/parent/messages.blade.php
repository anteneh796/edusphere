<x-layouts.app :title="__('Messages')">
    <x-page-header
        :title="__('Messages')"
        :description="__('Talk to your child\u2019s teachers and the school office.')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3" style="align-items:start;">
        <x-card :title="__('Compose a message')">
            @if ($recipients->isEmpty())
                <x-empty-state icon="mail" :title="__('No recipients')" :message="__('Your child\u2019s teachers and school office contacts will appear here.')" />
            @else
                <form method="POST" action="{{ route('cms.parent.messages.store') }}" class="grid gap-4">
                    @csrf

                    <x-select
                        name="recipient_uid"
                        :label="__('Recipient')"
                        :options="$recipients->mapWithKeys(fn ($r) => [$r->getKey() => $r->full_name.' — '.\App\Support\Enums\RoleName::tryFrom($r->roles->first()?->name)?->label() ?? __('staff')])"
                        placeholder="{{ __('Select a recipient…') }}"
                        required />

                    <x-input name="subject" :label="__('Subject')" :value="old('subject')" required />

                    <x-textarea name="body" :label="__('Message')" :value="old('body')" rows="4" required />

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Send message') }}</button>
                    </div>
                </form>
            @endif
        </x-card>

        <x-card :title="__('Conversations')" style="grid-column: span 2;">
            @forelse ($conversations as $message)
                <div class="py-3 border-b border-border last:border-0">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-medium">{{ $message->subject }}</p>
                        <span class="text-xs text-foreground-muted">
                            {{ __('With :name', ['name' => ($message->sender_id === auth()->id() ? $message->recipient?->full_name : $message->sender?->full_name) ?? '—']) }}
                        </span>
                    </div>

                    <div class="text-sm mt-2">
                        <p>
                            <span class="text-light">{{ ($message->sender_id === auth()->id() ? __('You') : $message->sender?->full_name) ?? '—' }}:</span>
                            {{ $message->body }}
                        </p>
                        @if ($message->replies->isNotEmpty())
                            @foreach ($message->replies as $reply)
                                <p class="mt-1">
                                    <span class="text-light">{{ ($reply->sender_id === auth()->id() ? __('You') : $reply->sender?->full_name) ?? '—' }}:</span>
                                    {{ $reply->body }}
                                </p>
                            @endforeach
                        @endif
                    </div>

                    <p class="text-xs text-foreground-muted mt-1">{{ $message->created_at?->diffForHumans() ?? '—' }}</p>

                    @unless ($message->sender_id === auth()->id())
                        <details class="mt-2">
                            <summary class="text-sm cursor-pointer text-primary">{{ __('Reply') }}</summary>
                            <form method="POST" action="{{ route('cms.parent.messages.store') }}" class="grid gap-3 mt-2">
                                @csrf
                                <input type="hidden" name="recipient_uid" value="{{ $message->sender_id }}">
                                <input type="hidden" name="subject" value="{{ $message->subject }}">
                                <input type="hidden" name="reply_to_id" value="{{ $message->getKey() }}">
                                <x-textarea name="body" :label="__('Your reply')" rows="2" required />
                                <div>
                                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Send reply') }}</button>
                                </div>
                            </form>
                        </details>
                    @endunless
                </div>
            @empty
                <x-empty-state icon="mail" :title="__('No conversations yet')" :message="__('Messages you exchange with teachers and the office will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>