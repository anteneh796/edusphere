<x-layouts.app :title="'Billing'">
    <x-page-header
        title="Billing &amp; payments"
        description="Fees and invoices linked to your wards.">
        <a href="{{ route('portals.parent.dashboard') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            Back to dashboard
        </a>
    </x-page-header>

    <div class="grid grid-stats">
        @php
            $outstanding = $wards->sum(fn ($w) => (float) ($w->fee_balance ?? 0));
            $due = $wards->sum(fn ($w) => (float) ($w->last_payment_amount ?? 0));
        @endphp
        <x-stat-card label="Outstanding balance" :value="number_format($outstanding, 2)" icon="wallet" color="primary" />
        <x-stat-card label="Latest payment" :value="number_format($due, 2)" icon="arrow-down-right" color="success" />
        <x-stat-card label="Wards billed" :value="$wards->count()" icon="users" color="info" />
    </div>

    <x-card class="mt-4" title="Fee summary">
        @forelse ($wards as $ward)
            <div class="list-row">
                <x-avatar :initials="$ward->initials()" />
                <div class="min-w-0 flex-1">
                    <div class="truncate">{{ $ward->full_name }}</div>
                    <div class="text-xs text-light">{{ $ward->student_number }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm-semibold">{{ number_format((float) ($ward->fee_balance ?? 0), 2) }}</div>
                    <div class="text-xs text-light">balance</div>
                </div>
            </div>
        @empty
            <x-empty-state icon="receipt" title="No billing yet" message="Invoices and payments will appear once linked." />
        @endforelse

        <div class="form-actions mt-3">
            <p class="text-xs text-light form-hint">
                Payments are recorded by the school accountant. Contact the office for receipts or adjustments.
            </p>
        </div>
    </x-card>
</x-layouts.app>
