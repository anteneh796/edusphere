@props([
    'cancelText' => __('Cancel'),
])

<div x-data x-show="$store.confirm.open"
    x-cloak
    class="modal-backdrop"
    x-transition.opacity
    style="align-items:start; padding-top: 12vh;"
    @click.self="$store.confirm.close()">

    <div class="modal modal-sm" x-transition x-cloak role="dialog" aria-modal="true">
        <div class="modal-body" style="padding: var(--space-4); text-align:center;">
            <div class="empty-state-icon" style="margin: 0 auto var(--space-3); background: var(--color-danger-soft); color: var(--color-danger);">
                <x-icon name="alert-triangle" class="icon-lg" />
            </div>

            <h3 class="modal-title" style="font-size: var(--text-lg);" x-text="$store.confirm.title"></h3>
            <p style="color: var(--color-text-muted); font-size: var(--text-sm); margin-top: 8px;" x-text="$store.confirm.message"></p>
        </div>

        <div class="modal-footer" style="gap: var(--space-1);">
            <button type="button" class="btn btn-secondary" @click="$store.confirm.close()">{{ $cancelText }}</button>
            <button type="button" class="btn btn-danger" x-text="$store.confirm.confirmText" @click="$store.confirm.submit()"></button>
        </div>
    </div>
</div>