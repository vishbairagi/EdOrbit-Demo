@extends('layouts.app')

@section('header', 'Collect Payment')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div><h2 class="text-lg font-bold text-heading">Collect Payment</h2><p class="text-xs text-body">{{ $school->name }} · {{ $session->name }}</p></div>
    <a href="{{ route('accounting.fee-invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="receipt-text" class="h-4 w-4"></i>All invoices</a>
</div>

<section class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4"><div><h3 class="text-sm font-bold text-heading">Outstanding Invoices</h3><p class="mt-1 text-xs text-body">Unpaid and partially paid invoices for this session.</p></div><span class="text-xs text-body">{{ $invoices->total() }} invoices</span></div>
    
    {{-- ══ Bulk Toolbar ══ --}}
    <div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-gray-50 border-b border-gray-200 px-5 py-2.5">
        <span id="selectedCount" class="text-sm font-semibold text-gray-700">0 selected</span>
        <form id="bulkPrintForm" method="POST" action="{{ route('accounting.collect-payment.bulk-print') }}" target="hidden-print-iframe" class="hidden">
            @csrf
            <div id="bulkPrintIdsContainer"></div>
        </form>
        <button type="button" onclick="bulkPrintSelected()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Selected
        </button>
        <form id="bulkDeleteForm" method="POST" action="{{ route('accounting.collect-payment.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            <div id="bulkDeleteIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>
    
    <div class="overflow-x-auto"><table class="min-w-full text-left text-xs"><thead><tr class="bg-navy"><th class="px-4 py-2 w-10 text-center"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded border-gray-300 cursor-pointer" title="Select All"></th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Invoice</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Class</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Total</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Paid</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Balance</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Status</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Action</th></tr></thead><tbody class="divide-y divide-gray-100 bg-white">
        @forelse($invoices as $invoice)
            <tr class="hover:bg-gray-50 transition-colors"><td class="px-4 py-3 text-center"><input type="checkbox" class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer" data-type="{{ $invoice->type }}" value="{{ $invoice->id }}" onchange="updateBulkToolbar()"></td><td class="px-4 py-3"><span class="block font-bold text-gray-900">{{ $invoice->student_name }}</span><span class="block font-bold text-gray-900">#{{ $invoice->admission_no }}</span></td><td class="px-4 py-3 font-bold text-gray-900">{{ $invoice->invoice_title }}</td><td class="px-4 py-3 font-bold text-gray-900">{{ $invoice->class_name }}</td><td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $invoice->total_amount, 2) }}</td><td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $invoice->amount_paid, 2) }}</td><td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($invoice->due_amount, 2) }}</td><td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-[11px] font-bold {{ $invoice->status === 'partial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700' }}">{{ ucfirst($invoice->status) }}</span></td><td class="px-4 py-3 text-right"><a href="{{ $invoice->show_url }}" class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-2.5 py-1.5 font-semibold text-navy hover:bg-gray-50"><i data-lucide="hand-coins" class="h-3.5 w-3.5"></i>Collect</a></td></tr>
        @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-body">No outstanding invoices for this session.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="border-t border-gray-100 px-4 py-3">{{ $invoices->links() }}</div>
</section>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4"><div><h3 class="text-sm font-bold text-heading">Payment History</h3><p class="mt-1 text-xs text-body">Recorded collections for {{ $session->name }}.</p></div><span class="text-xs text-body">{{ $payments->total() }} payments</span></div>
    <div class="overflow-x-auto"><table class="min-w-full text-left text-xs"><thead><tr class="bg-navy"><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Date</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Invoice</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Method</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Transaction ID</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Authorized By</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Amount</th><th class="px-4 py-2 text-center font-semibold text-white uppercase tracking-wider">Receipt</th></tr></thead><tbody class="divide-y divide-gray-100 bg-white">
        @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition-colors"><td class="px-4 py-3 font-bold text-gray-900">{{ \Carbon\Carbon::parse($payment->paid_on)->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td><td class="px-4 py-3 font-bold text-gray-900">{{ $payment->student_name }}</td><td class="px-4 py-3 font-bold text-gray-900"><a href="{{ $payment->invoice_url }}" class="text-navy hover:underline">{{ $payment->invoice_title }}</a></td><td class="px-4 py-3 font-bold text-gray-900">{{ $payment->method }}</td><td class="px-4 py-3 font-bold text-gray-900">{{ $payment->transaction_id }}</td><td class="px-4 py-3 font-bold text-gray-900">{{ $payment->authorized_by }}</td><td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $payment->amount, 2) }}</td><td class="px-4 py-3 text-center">@if($payment->receipt_url) <button type="button" onclick="printDocument('{{ $payment->receipt_url }}')" title="Print payment receipt" aria-label="Print payment receipt" class="inline-flex h-7 w-7 items-center justify-center rounded border border-gray-300 text-navy hover:bg-gray-50"><i data-lucide="printer" class="h-3.5 w-3.5"></i></button> @else - @endif</td></tr>
        @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-body">No payment history for this session yet.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="border-t border-gray-100 px-4 py-3">{{ $payments->links() }}</div>
</section>

<script>
    function toggleSelectAll(source) {
        document.querySelectorAll('.row-checkbox').forEach(function(cb) {
            cb.checked = source.checked;
        });
        updateBulkToolbar();
    }
    
    function updateBulkToolbar() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        var all = document.querySelectorAll('.row-checkbox');
        var toolbar = document.getElementById('bulkToolbar');
        var countEl = document.getElementById('selectedCount');
        var sa = document.getElementById('selectAll');
        
        if (checked.length > 0) {
            if(toolbar) toolbar.classList.remove('hidden');
            if(countEl) countEl.textContent = checked.length + ' selected';
        } else {
            if(toolbar) toolbar.classList.add('hidden');
        }
        
        if (sa) {
            sa.indeterminate = checked.length > 0 && checked.length < all.length;
            sa.checked = all.length > 0 && checked.length === all.length;
        }
    }
    
    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(function(cb) { cb.checked = false; });
        updateBulkToolbar();
    }

    function confirmBulkDelete() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm(checked.length + ' item(s) permanently delete honge. Sure hain?')) return false;
        var c = document.getElementById('bulkDeleteIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; 
            inp.name = cb.dataset.type + '_ids[]'; 
            inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    function bulkPrintSelected() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        var c = document.getElementById('bulkPrintIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; 
            inp.name = cb.dataset.type + '_ids[]'; 
            inp.value = cb.value;
            c.appendChild(inp);
        });
        let iframe = document.getElementById('hidden-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'hidden-print-iframe';
            iframe.name = 'hidden-print-iframe';
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);
        }
        document.getElementById('bulkPrintForm').submit();
    }

    window.printDocument = function(url) {
        let iframe = document.getElementById('hidden-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'hidden-print-iframe';
            iframe.name = 'hidden-print-iframe';
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