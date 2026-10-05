@extends('layouts.app')

@section('header', 'Invoice Details')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Transport Invoice Details</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('accounting.transport-invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Invoices</a>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="xl:col-span-2 space-y-5">
        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-bold text-heading">Invoice Summary</h3>
                <span class="rounded-full px-2 py-1 text-[11px] font-semibold {{ $transportInvoice->status === 'paid' ? 'bg-green-50 text-green-700' : ($transportInvoice->status === 'partial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                    {{ ucfirst($transportInvoice->status) }}
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-3">
                <div><span class="block text-xs font-medium text-body">Invoice Title</span><span class="font-semibold text-heading">{{ $transportInvoice->invoice_title }}</span></div>
                <div><span class="block text-xs font-medium text-body">Issue Date</span><span class="font-semibold text-heading">{{ $transportInvoice->issued_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</span></div>
                <div><span class="block text-xs font-medium text-body">Student</span><span class="font-semibold text-heading">{{ $transportInvoice->student->first_name }} {{ $transportInvoice->student->last_name }}</span></div>
                <div><span class="block text-xs font-medium text-body">Route</span><span class="font-semibold text-heading">{{ $transportInvoice->transportRoute->route_name ?? '—' }}</span></div>
                <div><span class="block text-xs font-medium text-body">Start Date</span><span class="font-semibold text-heading">{{ $transportInvoice->start_date ? $transportInvoice->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) : '—' }}</span></div>
                <div><span class="block text-xs font-medium text-body">End Date</span><span class="font-semibold text-heading">{{ $transportInvoice->end_date ? $transportInvoice->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) : '—' }}</span></div>
            </div>
            
            <div class="mt-6 rounded-md border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead><tr class="bg-navy">
                            <th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Item</th>
                            <th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-heading">Transport Fare ({{ $transportInvoice->transportRoute->route_name ?? '' }})</td>
                            <td class="px-4 py-3 text-right font-medium text-heading">{{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ number_format($transportInvoice->total_amount, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-3 text-right font-semibold text-heading">Total Amount</td>
                            <td class="px-4 py-3 text-right font-bold text-navy">{{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ number_format($transportInvoice->total_amount, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-right font-semibold text-heading">Amount Paid</td>
                            <td class="px-4 py-3 text-right font-semibold text-green-600">- {{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ number_format($transportInvoice->amount_paid, 2) }}</td>
                        </tr>
                        <tr class="bg-red-50/30">
                            <td class="px-4 py-3 text-right font-semibold text-red-800">Due Amount</td>
                            <td class="px-4 py-3 text-right font-bold text-red-600">{{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ number_format($transportInvoice->due_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        @if($transportInvoice->payments->count() > 0)
        <section class="rounded-lg border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-sm font-bold text-heading">Payment History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead><tr class="bg-navy">
                            <th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Method</th>
                            <th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Note</th>
                            <th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($transportInvoice->payments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-body">{{ $payment->payment_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                            <td class="px-4 py-3 text-body">{{ $paymentMethods->firstWhere('id', (int) $payment->payment_method)?->name ?? $payment->payment_method ?? '—' }}</td>
                            <td class="px-4 py-3 text-body">{{ $payment->note ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-green-600">{{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif
    </div>
    
    <div>
        @if($transportInvoice->due_amount > 0)
        <section class="rounded-lg border border-gray-200 bg-white overflow-hidden">
            <div class="bg-navy px-5 py-4">
                <h3 class="text-sm font-bold text-white">Collect Payment</h3>
            </div>
            <form action="{{ route('accounting.transport-invoices.payments.store', $transportInvoice) }}" method="POST" class="space-y-4 p-5">
                @csrf
                <div>
                    <label for="payment_date" class="mb-1 block text-sm font-medium text-body">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
                <div>
                    <label for="amount" class="mb-1 block text-sm font-medium text-body">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" id="amount" value="{{ $transportInvoice->due_amount }}" max="{{ $transportInvoice->due_amount }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-right font-bold text-red-600 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <p class="text-[11px] text-gray-500 mt-1">You can change the amount to accept partial payment.</p>
                </div>
                <div>
                    <label for="payment_method" class="mb-1 block text-sm font-medium text-body">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="">Select Method</option>
                        @foreach($paymentMethods ?? [] as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="note" class="mb-1 block text-sm font-medium text-body">Note</label>
                    <textarea name="note" id="note" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></textarea>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="check" class="h-4 w-4"></i>Submit Payment</button>
                </div>
            </form>
        </section>
        @else
        <section class="rounded-lg border border-green-200 bg-green-50 p-6 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-3">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-green-800">Fully Paid</h3>
            <p class="text-green-600 mt-1 text-xs">There is no due amount remaining.</p>
        </section>
        @endif
    </div>
</div>
@endsection
