@extends('layouts.app')

@section('header', 'Invoice Details')

@section('content')
@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>
@endif
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div><h2 class="text-lg font-bold text-heading">{{ $feeInvoice->invoice_title }}</h2><p class="text-xs text-body">{{ $feeInvoice->student->full_name }} · {{ $feeInvoice->student->admission_no }}</p></div>
    <a href="{{ route('accounting.fee-invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Invoices</a>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="space-y-5 xl:col-span-2">
        <section class="rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Invoice Detail</h3></div>
            <dl class="grid grid-cols-2 gap-x-5 gap-y-4 p-5 text-xs sm:grid-cols-3">
                <div><dt class="text-body">Student</dt><dd class="mt-1 font-semibold text-heading">{{ $feeInvoice->student->full_name }}</dd></div>
                <div><dt class="text-body">Class / Section</dt><dd class="mt-1 font-semibold text-heading">{{ $feeInvoice->student->schoolClass->name }} / {{ $feeInvoice->student->section?->name ?? '—' }}</dd></div>
                <div><dt class="text-body">Session</dt><dd class="mt-1 font-semibold text-heading">{{ $feeInvoice->academicSession->name }}</dd></div>
                <div><dt class="text-body">Date Issued</dt><dd class="mt-1 font-semibold text-heading">{{ $feeInvoice->issued_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</dd></div>
                <div><dt class="text-body">Due Date</dt><dd class="mt-1 font-semibold text-heading">{{ $feeInvoice->due_at?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) ?? '—' }}</dd></div>
                <div><dt class="text-body">Status</dt><dd class="mt-1 font-semibold text-heading">{{ ucfirst($feeInvoice->status) }}</dd></div>
                <div class="col-span-full"><dt class="text-body">Description</dt><dd class="mt-1 text-heading">{{ $feeInvoice->description ?: '—' }}</dd></div>
            </dl>
            <div class="grid grid-cols-1 divide-y divide-gray-100 border-t border-gray-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <div class="p-4"><p class="text-xs text-body">Total Amount</p><p class="mt-1 text-lg font-bold text-heading">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $feeInvoice->total_amount, 2) }}</p></div>
                <div class="p-4"><p class="text-xs text-body">Amount Paid</p><p class="mt-1 text-lg font-bold text-green-700">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $feeInvoice->amount_paid, 2) }}</p></div>
                <div class="p-4"><p class="text-xs text-body">Outstanding</p><p class="mt-1 text-lg font-bold text-red-700">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($outstanding, 2) }}</p></div>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Payment History</h3></div>
            <div class="overflow-x-auto"><table class="min-w-full text-left text-xs"><thead><tr class="bg-navy"><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Date</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Method</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Transaction ID</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Authorized By</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Amount</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Note</th><th class="px-4 py-2 text-center font-semibold text-white uppercase tracking-wider">Receipt</th></tr></thead><tbody class="divide-y divide-gray-100 bg-white">
                @forelse($feeInvoice->payments as $payment)
                    <tr class="hover:bg-gray-50 transition-colors"><td class="px-4 py-3 text-body">{{ $payment->paid_on->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td><td class="px-4 py-3 text-heading">{{ $payment->paymentMethod?->name ?? '—' }}</td><td class="px-4 py-3 text-body">{{ $payment->transaction_id ?? '—' }}</td><td class="px-4 py-3 text-body">{{ $payment->authorized_by }}</td><td class="px-4 py-3 text-right font-semibold text-heading">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $payment->amount, 2) }}</td><td class="px-4 py-3 text-body">{{ $payment->note ?? '—' }}</td><td class="px-4 py-3 text-center"><button type="button" onclick="printDocument('{{ route('accounting.collect-payment.receipt', $payment) }}')" title="Print payment receipt" aria-label="Print payment receipt" class="inline-flex h-7 w-7 items-center justify-center rounded border border-gray-300 text-navy hover:bg-gray-50"><i data-lucide="printer" class="h-3.5 w-3.5"></i></button></td></tr>
                @empty
                    <tr class="hover:bg-gray-50 transition-colors"><td colspan="7" class="px-4 py-8 text-center text-sm text-body">No payments collected yet.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>

    <section class="h-fit rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Collect Payment</h3><p class="mt-1 text-xs text-body">Outstanding balance: {{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($outstanding, 2) }}</p></div>
        @if($outstanding > 0)
            @if($paymentMethods->isEmpty())
                <div class="p-5 text-sm text-body">Add an active payment method in <a href="{{ route('sm-school.settings.index', ['tab' => 'payment-methods']) }}" class="font-semibold text-navy underline">School Settings</a> before collecting payment.</div>
            @elseif(empty($authorizers))
                <div class="p-5 text-sm text-body">Add an active administrator or staff user before collecting payment.</div>
            @else
                <form action="{{ route('accounting.fee-invoices.payments.store', $feeInvoice) }}" method="POST" class="space-y-4 p-5">
                    @csrf
                    <div><label for="paymentAmount" class="mb-1 block text-xs font-medium text-body">Amount</label><input id="paymentAmount" name="amount" type="number" min="0.01" max="{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($outstanding, 2, '.', '') }}" step="0.01" value="{{ old('amount', number_format($outstanding, 2, '.', '')) }}" {{ $feeInvoice->allow_partial_payment ? '' : 'readonly' }} required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><p class="mt-1 text-[11px] text-body">{{ $feeInvoice->allow_partial_payment ? 'Partial payments are enabled.' : 'This invoice requires the full remaining balance.' }}</p></div>
                    <div><label for="paymentMethod" class="mb-1 block text-xs font-medium text-body">Payment Method</label><select id="paymentMethod" name="payment_method_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">@foreach($paymentMethods as $method)<option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>@endforeach</select></div>
                    <div><label for="transactionId" class="mb-1 block text-xs font-medium text-body">Transaction ID</label><input id="transactionId" name="transaction_id" value="{{ old('transaction_id') }}" maxlength="150" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Optional"></div>
                    <div><label for="authorizedBy" class="mb-1 block text-xs font-medium text-body">Authorized By</label><select id="authorizedBy" name="authorized_by" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><option value="">Select staff / admin</option>@foreach($authorizers as $value => $label)<option value="{{ $value }}" {{ old('authorized_by') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="paidOn" class="mb-1 block text-xs font-medium text-body">Payment Date</label><input id="paidOn" name="paid_on" type="date" value="{{ old('paid_on', now()->toDateString()) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label for="paymentNote" class="mb-1 block text-xs font-medium text-body">Additional Note</label><textarea id="paymentNote" name="note" rows="2" maxlength="2000" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ old('note') }}</textarea></div>
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="circle-dollar-sign" class="h-4 w-4"></i>Collect Payment</button>
                </form>
            @endif
        @else
            <p class="p-5 text-sm font-medium text-green-700">This invoice is fully paid.</p>
        @endif
    </section>
</div>

<script>
    window.printDocument = function(url) {
        let iframe = document.getElementById('hidden-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'hidden-print-iframe';
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
    };
</script>
@endsection
