@extends('layouts.app')

@section('header', 'Accounting Dashboard')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">{{ $school->name }}</h2>
        <p class="text-xs text-body">Accounting overview · {{ $session->name }}</p>
    </div>
    <a href="{{ route('accounting.fee-invoices.create') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background:#B47A42;">
        <i data-lucide="plus" class="h-4 w-4"></i> Create Fee Invoice
    </a>
</div>

<div class="mb-5 rounded-lg bg-navy px-5 py-3 text-sm font-semibold text-white">Session Financial Summary <span class="ml-2 text-xs font-normal text-white/70">{{ $session->name }}</span></div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
    @foreach([
        ['label' => 'Generated Invoices', 'value' => $generatedAmount, 'note' => $invoiceCount.' invoices this session', 'icon' => 'files', 'tone' => 'amber'],
        ['label' => 'Payments Collected', 'value' => $collectedAmount, 'note' => 'Recorded transactions', 'icon' => 'circle-dollar-sign', 'tone' => 'green'],
        ['label' => 'Pending Amount', 'value' => $pendingAmount, 'note' => $pendingInvoiceCount.' invoices with balance', 'icon' => 'clock-3', 'tone' => 'red'],
    ] as $metric)
        <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-{{ $metric['tone'] }}-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                    <i data-lucide="{{ $metric['icon'] }}" class="w-4 h-4 text-{{ $metric['tone'] }}-500"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-heading leading-tight">{{ $metric['label'] }}</p>
                    <p class="text-xs text-gray-400">{{ $metric['note'] }}</p>
                </div>
            </div>
            <div class="text-lg font-bold text-heading ml-1">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $metric['value'], 2) }}</div>
        </div>
    @endforeach
</div>

<div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-3">
    <section class="rounded-lg border border-gray-200 bg-white p-5 xl:col-span-2">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-heading">Recent Payments</h3>
                <p class="mt-1 text-xs text-body">Latest collections for {{ $session->name }}.</p>
            </div>
            <a href="{{ route('accounting.fee-invoices.index') }}" class="text-xs font-semibold text-navy hover:underline">View invoices</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="border-y border-gray-200 bg-gray-50 text-body">
                    <tr><th class="px-3 py-2 font-semibold">Student</th><th class="px-3 py-2 font-semibold">Invoice</th><th class="px-3 py-2 font-semibold">Method</th><th class="px-3 py-2 text-right font-semibold">Amount</th><th class="px-3 py-2 font-semibold">Date</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentPayments as $payment)
                        <tr>
                            <td class="px-3 py-3 font-medium text-heading">{{ $payment->invoice->student->full_name }}</td>
                            <td class="px-3 py-3"><a class="text-navy hover:underline" href="{{ route('accounting.fee-invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_title }}</a></td>
                            <td class="px-3 py-3 text-body">{{ $payment->paymentMethod?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-heading">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-3 py-3 text-body">{{ $payment->paid_on->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-10 text-center text-sm text-body">No payments recorded for this session.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="rounded-lg border border-gray-200 bg-white p-5">
        <h3 class="text-sm font-bold text-heading">Accounting</h3>
        <div class="mt-4 divide-y divide-gray-100">
            <a href="{{ route('accounting.fee-types.index') }}" class="flex items-center justify-between py-3 text-sm text-body hover:text-heading"><span class="flex items-center gap-2"><i data-lucide="tags" class="h-4 w-4 text-navy"></i> Fee Types</span><i data-lucide="chevron-right" class="h-4 w-4"></i></a>
            <a href="{{ route('accounting.fee-invoices.index') }}" class="flex items-center justify-between py-3 text-sm text-body hover:text-heading"><span class="flex items-center gap-2"><i data-lucide="receipt-text" class="h-4 w-4 text-navy"></i> Fee Invoices</span><i data-lucide="chevron-right" class="h-4 w-4"></i></a>
            <a href="{{ route('accounting.transport-invoices.index') }}" class="flex items-center justify-between py-3 text-sm text-body hover:text-heading"><span class="flex items-center gap-2"><i data-lucide="bus" class="h-4 w-4 text-navy"></i> Transport Invoices</span><i data-lucide="chevron-right" class="h-4 w-4"></i></a>
            <a href="{{ route('accounting.collect-payment.index') }}" class="flex items-center justify-between py-3 text-sm text-body hover:text-heading"><span class="flex items-center gap-2"><i data-lucide="hand-coins" class="h-4 w-4 text-navy"></i> Collect Payment</span><i data-lucide="chevron-right" class="h-4 w-4"></i></a>

        </div>
    </section>
</div>
@endsection
