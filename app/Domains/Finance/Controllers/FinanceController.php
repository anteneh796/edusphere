<?php

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\Enums\PaymentMethod;
use App\Support\Enums\PaymentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceController extends Controller
{
    /* ---------------------------------- Helpers --------------------------------- */

    private function studentOptions(): array
    {
        return Student::query()
            ->active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (Student $student) => [
                $student->getKey() => $student->full_name.' ('.$student->student_number.')',
            ])
            ->all();
    }

    /* --------------------------------- Invoices -------------------------------- */

    public function invoicesIndex(): View
    {
        return view('finance.invoices.index', [
            'invoices' => Invoice::query()
                ->with(['student.classRoom.gradeLevel'])
                ->latest()
                ->get(),
        ]);
    }

    public function invoicesCreate(): View
    {
        return view('finance.invoices.create', [
            'students' => $this->studentOptions(),
        ]);
    }

    public function invoicesStore(): RedirectResponse
    {
        $validated = request()->validate([
            'student_id' => ['required', 'exists:students,id'],
            'description' => ['required', 'string', 'max:190'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:190'],
        ]);

        $invoice = Invoice::create([
            ...$validated,
            'invoice_number' => 'INV-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'status' => 'pending',
            'created_by_id' => auth()->id(),
        ]);

        return to_route('finance.invoices.index')
            ->with('status', __('Invoice :number created.', ['number' => $invoice->invoice_number]));
    }

    /* --------------------------------- Payments -------------------------------- */

    public function paymentsIndex(): View
    {
        return view('finance.payments.index', [
            'payments' => Payment::query()
                ->with(['student.classRoom.gradeLevel', 'invoice'])
                ->latest()
                ->get(),
        ]);
    }

    public function paymentsCreate(): View
    {
        return view('finance.payments.create', [
            'students' => $this->studentOptions(),
            'methods' => PaymentMethod::cases(),
            'invoices' => Invoice::query()->due()->with(['student'])->get(),
        ]);
    }

    public function paymentsStore(): RedirectResponse
    {
        $validated = request()->validate([
            'student_id' => ['required', 'exists:students,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:80'],
            'provider' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::enum(PaymentStatus::class)],
        ]);

        $payment = Payment::create([
            ...$validated,
            'payment_number' => 'PAY-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'paid_at' => $validated['status'] === PaymentStatus::Confirmed->value ? now() : null,
            'recorded_by_id' => auth()->id(),
        ]);

        if ($payment->status === PaymentStatus::Confirmed) {
            $payment->confirm();
        }

        $this->notifyGuardian($payment);

        return to_route('finance.payments.index')->with('status', __('Payment recorded.'));
    }

    public function paymentsConfirm(Payment $payment): RedirectResponse
    {
        $payment->confirm();

        $this->notifyGuardian($payment);

        return to_route('finance.payments.index')->with('status', __('Payment confirmed.'));
    }

    private function notifyGuardian(Payment $payment): void
    {
        $guardians = $payment->student->guardians;

        if ($guardians->isEmpty()) {
            return;
        }

        $payload = [
            'type' => 'payment',
            'category' => 'fee',
            'priority' => 'medium',
            'icon' => 'receipt',
            'title' => __('Payment received'),
            'body' => __('A :amount payment for :student was recorded.', [
                'amount' => number_format((float) $payment->amount, 2),
                'student' => $payment->student->full_name,
            ]),
            'redirect_url' => route('cms.parent.billing'),
            'data' => ['payment_id' => $payment->getKey()],
        ];

        app(NotificationService::class)->sendToMany(
            $guardians->pluck('user_id')->filter()->values()->all(),
            $payload
        );
    }
}
