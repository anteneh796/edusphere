<x-layouts.app :title="__('Messages')">
    <x-page-header :title="__('Messages')" :description="__('Send and receive messages with staff.')" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3" style="align-items:start;">
        <x-card :title="__('Compose a message')">
            @if ($recipients->isEmpty())
                <x-empty-state icon="mail" :title="__('No recipients')" :message="__('Other active staff members will appear here.')" />
            @else
                <form method="POST" action="{{ route('cms.teacher.messages.store') }}" class="grid gap-4">
                    @csrf

                    <x-select
                        name="recipient_uid"
                        :label="__('Recipient')"
                        :options="$recipients->mapWithKeys(fn ($r) => [$r->getKey() => $r->full_name . ' (' . ($r->email ?? '—') . ')'])"
                        placeholder="{{ __('Select recipient…') }}"
                        required />

                    <x-input
                        name="subject"
                        :label="__('Subject')"
                        :value="old('subject')"
                        required />

                    <x-textarea name="body" :label="__('Message')" :value="old('body')" required />

                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('Send message') }}</button>
                    </div>
                </form>
            @endif
        </x-card>

        <x-card :title="__('Inbox')">
            @forelse ($inbox as $message)
                <div class="py-3 border-b border-border last:border-0">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-medium">{{ $message->subject }}</p>
                        <span class="badge badge-neutral">{{ $message->message_type }}</span>
                    </div>
                    <p class="text-sm mt-1">{{ $message->body }}</p>
                    <p class="text-xs text-foreground-muted mt-1">
                        {{ __('From :sender', ['sender' => $message->sender?->full_name ?? '—']) }}
                        · {{ $message->created_at?->diffForHumans() ?? '—' }}
                    </p>
                </div>
            @empty
                <x-empty-state icon="inbox" :title="__('No messages received')" :message="__('Messages sent to you will appear here.')" />
            @endforelse
        </x-card>

        <x-card :title="__('Sent')">
            @forelse ($outbox as $message)
                <div class="py-3 border-b border-border last:border-0">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-medium">{{ $message->subject }}</p>
                        <span class="badge badge-neutral">{{ $message->recipient_type }}</span>
                    </div>
                    <p class="text-sm mt-1">{{ $message->body }}</p>
                    <p class="text-xs text-foreground-muted mt-1">
                        {{ __('To :recipient', ['recipient' => $message->recipient?->full_name ?? '—']) }}
                        · {{ $message->created_at?->diffForHumans() ?? '—' }}
                    </p>
                </div>
            @empty
                <x-empty-state icon="send" :title="__('Nothing sent yet')" :message="__('Messages you send will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>