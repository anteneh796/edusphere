<x-layouts.app :title="__('Payroll Profile')">

    <x-page-header :title="__('Payroll Profile')"
        :description="__(':name · :id', ['name' => $employee->full_name, 'id' => $employee->employee_id])">
        <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-secondary">{{ __('Back to profile') }}</a>
    </x-page-header>

    <x-card :title="$profile->exists ? __('Edit payroll details') : __('Create payroll profile')">
        <form method="POST" action="{{ $profile->exists ? route('hr.payroll.update', $employee) : route('hr.payroll.store', $employee) }}">
            @csrf
            @if ($profile->exists)
                @method('PUT')
            @endif

            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <x-input name="salary_grade" label="{{ __('Salary grade') }}" :value="old('salary_grade', $profile->salary_grade)" placeholder="e.g. A1" />
                <x-input name="basic_salary" type="number" step="0.01" label="{{ __('Basic salary (ETB)') }}" :value="old('basic_salary', $profile->basic_salary)" min="0" required placeholder="e.g. 25000.00" />
                <x-input name="bank_name" label="{{ __('Bank name') }}" :value="old('bank_name', $profile->bank_name)" placeholder="e.g. Commercial Bank of Ethiopia" />
                <x-input name="account_number" label="{{ __('Account number') }}" :value="old('account_number', $profile->account_number)" placeholder="e.g. 1000234567890" />
                <x-input name="tax_id" label="{{ __('Tax ID') }}" :value="old('tax_id', $profile->tax_id)" placeholder="e.g. 12345" />
                <x-select name="payment_method" :label="__('Payment method')"
                    :options="['bank_transfer' => __('Bank transfer'), 'cash' => __('Cash'), 'cheque' => __('Cheque')]"
                    :value="old('payment_method', $profile->payment_method)" placeholder="{{ __('Select…') }}" />
            </div>

            <div class="form-group" style="margin-top: var(--space-2);">
                <label class="form-label">{{ __('Allowances') }}</label>
                <div class="flex flex-col" style="gap: var(--space-1);">
                    @php $allowances = old('allowances', $profile->allowances ?: []); @endphp
                    @foreach ($allowances as $entryKey => $entryValue)
                        @php
                            $entryName = is_array($entryValue) ? ($entryValue['name'] ?? '') : $entryKey;
                            $entryAmount = is_array($entryValue) ? ($entryValue['amount'] ?? '') : $entryValue;
                        @endphp
                        <div class="flex gap-1" style="gap: var(--space-1); align-items:center;">
                            <input type="text" name="allowances[][name]" value="{{ $entryName }}" class="form-control" placeholder="{{ __('Allowance name') }}" />
                            <input type="number" step="0.01" min="0" name="allowances[][amount]" value="{{ $entryAmount }}" class="form-control" placeholder="0.00" />
                        </div>
                    @endforeach
                    @for ($i = 0; $i < 2; $i++)
                        <div class="flex gap-1" style="gap: var(--space-1); align-items:center;">
                            <input type="text" name="allowances[][name]" value="" class="form-control" placeholder="{{ __('Allowance name') }}" />
                            <input type="number" step="0.01" min="0" name="allowances[][amount]" value="" class="form-control" placeholder="0.00" />
                        </div>
                    @endfor
                </div>
                <div class="form-hint">{{ __('Add allowance names and amounts (e.g. transport, housing).') }}</div>
            </div>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ $profile->exists ? __('Save changes') : __('Create profile') }}
                </button>
            </div>
        </form>
    </x-card>

    @if ($profile->exists)
        <x-card :title="__('Totals')" style="margin-top: var(--space-3);">
            <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--space-2);">
                <div class="stat-card"><div class="stat-icon primary"><x-icon name="banknote" class="icon-lg" /></div><div style="min-width:0;"><div class="stat-value">ETB {{ number_format((float) $profile->basic_salary, 2) }}</div><div class="stat-label">{{ __('Basic salary') }}</div></div></div>
                <div class="stat-card"><div class="stat-icon accent"><x-icon name="plus" class="icon-lg" /></div><div style="min-width:0;"><div class="stat-value">ETB {{ number_format(array_sum($profile->allowances ?: []), 2) }}</div><div class="stat-label">{{ __('Allowances') }}</div></div></div>
                <div class="stat-card"><div class="stat-icon success"><x-icon name="check-circle" class="icon-lg" /></div><div style="min-width:0;"><div class="stat-value">ETB {{ number_format($profile->totalEarnings(), 2) }}</div><div class="stat-label">{{ __('Total monthly earnings') }}</div></div></div>
            </div>
        </x-card>
    @endif

</x-layouts.app>