@extends('layouts.app')

@section('header', 'Transport Invoices')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Transport Invoices</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('accounting.transport-invoices.create') }}" class="inline-flex items-center gap-2 rounded-md bg-navy px-3 py-2 text-xs font-semibold text-white hover:bg-primary-dark">
            <i data-lucide="plus" class="h-4 w-4"></i> Create Invoice
        </a>
    </div>
</div>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div id="bulkActionsToolbar" class="hidden items-center justify-between border-b border-gray-200 bg-indigo-50 px-5 py-3">
        <div class="flex items-center gap-3">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-navy text-[11px] font-bold text-white" id="selectedCount">0</span>
            <span class="text-sm font-semibold text-navy">Invoices Selected</span>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="clearSelection()" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Clear</button>
            <form id="bulkPrintForm" method="POST" action="{{ route('accounting.transport-invoices.bulk-print') }}" target="hidden-print-iframe" class="hidden">
                @csrf
                <div id="bulkPrintInputs"></div>
            </form>
            <button type="button" onclick="bulkPrintSelected()" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                <i data-lucide="printer" class="h-3.5 w-3.5"></i> Print Selected
            </button>
            <form id="bulkDeleteForm" method="POST" action="{{ route('accounting.transport-invoices.bulk-delete') }}" onsubmit="return confirmBulkDelete()">
                @csrf
                <div id="bulkDeleteInputs" class="hidden"></div>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Delete Selected
                </button>
            </form>
        </div>
    </div>
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Invoice List</h3>
        <span class="text-xs text-body">{{ count($invoices) }} records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead>
                <tr class="bg-navy">
                    <th scope="col" class="px-4 py-2 w-10">
                        <input type="checkbox" id="selectAllCb" onchange="toggleSelectAll(this)" class="h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy">
                    </th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Father's Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Route</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Invoice</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Issued</th>
                    <th scope="col" class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Received</th>
                    <th scope="col" class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Due Amount</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" value="{{ $invoice->id }}" class="row-checkbox h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy" onchange="updateBulkToolbar()">
                        </td>
                        <td class="px-4 py-3">
                            <span class="block font-bold text-gray-900">{{ $invoice->student->first_name }} {{ $invoice->student->last_name }}</span>
                            <span class="mt-0.5 block font-bold text-gray-900">#{{ $invoice->student->admission_no }}</span>
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-900">{{ $invoice->student->father_name ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold text-gray-900">{{ $invoice->transportRoute->route_name ?? '—' }}</td>
                        <td class="px-4 py-3 font-bold text-gray-900">{{ $invoice->invoice_title }}</td>
                        <td class="px-4 py-3 font-bold text-gray-900">{{ $invoice->issued_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $invoice->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $invoice->amount_paid, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format(max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid), 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-[11px] font-bold {{ $invoice->status === 'paid' ? 'bg-green-50 text-green-700' : ($invoice->status === 'partial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="printDocument('{{ route('accounting.transport-invoices.print', $invoice) }}')" title="Print Invoice" class="inline-flex items-center rounded-md border border-gray-300 p-1.5 text-navy hover:bg-gray-50"><i data-lucide="printer" class="h-4 w-4"></i></button>
                            </div>
                            <div class="mt-1">
                                @if($invoice->status !== 'paid')
                                    <button type="button" onclick="openPaymentModal({{ $invoice->id }}, {{ max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid) }}, true)" class="text-[11px] font-bold text-blue-700 hover:underline">Collect Payment</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td colspan="11" class="px-4 py-12 text-center text-sm font-bold text-gray-900">No transport invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- Collect Payment Modal -->
<div id="paymentModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center bg-gray-900/50 backdrop-blur-sm transition-all">
    <div class="w-full max-w-sm rounded-xl bg-white shadow-2xl overflow-hidden p-6">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-sm font-bold text-heading">Collect Payment</h3>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
        
        <form id="paymentForm" method="POST" action="">
            @csrf
            
            <div id="paymentStep1">
                <div class="mb-4">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Amount <span class="text-red-600">*</span></label>
                    <input type="number" id="paymentAmount" name="amount" step="0.01" min="0.01" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <p id="paymentAmountHelp" class="mt-1 text-[10px] text-red-600 font-bold hidden">Partial payment not allowed, you must pay the full amount.</p>
                </div>
                <div class="mb-5">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Transaction ID</label>
                    <input type="text" name="transaction_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closePaymentModal()" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" onclick="goPaymentStep(2)" class="rounded-md px-3 py-1.5 text-xs font-bold text-white hover:opacity-90" style="background:#B47A42;">Next</button>
                </div>
            </div>

            <div id="paymentStep2" class="hidden">
                <div class="mb-3">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Payment Method <span class="text-red-600">*</span></label>
                    <select name="payment_method" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="">Select Method</option>
                        @foreach($paymentMethods ?? [] as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Authorized By <span class="text-red-600">*</span></label>
                    <select name="authorized_by" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="">Select Authorizer</option>
                        @foreach($authorizers ?? [] as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Payment Date <span class="text-red-600">*</span></label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
                <div class="mb-5">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Additional Note</label>
                    <textarea name="note" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></textarea>
                </div>
                
                <div class="flex justify-between gap-2">
                    <button type="button" onclick="goPaymentStep(1)" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50">Back</button>
                    <button type="submit" class="rounded-md px-3 py-1.5 text-xs font-bold text-white hover:opacity-90" style="background:#B47A42;">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(invoiceId, dueAmount, allowPartial) {
        const form = document.getElementById('paymentForm');
        form.action = `/accounting/transport-invoices/${invoiceId}/payments`;
        
        const amountInput = document.getElementById('paymentAmount');
        const helpText = document.getElementById('paymentAmountHelp');
        
        amountInput.max = dueAmount;
        amountInput.value = dueAmount;
        
        if (!allowPartial) {
            amountInput.readOnly = true;
            helpText.classList.remove('hidden');
        } else {
            amountInput.readOnly = false;
            helpText.classList.add('hidden');
        }
        
        goPaymentStep(1);
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentForm').reset();
    }
    
    function goPaymentStep(step) {
        if(step === 2) {
            const amt = document.getElementById('paymentAmount');
            if(!amt.checkValidity()) {
                amt.reportValidity();
                return;
            }
            document.getElementById('paymentStep1').classList.add('hidden');
            document.getElementById('paymentStep2').classList.remove('hidden');
        } else {
            document.getElementById('paymentStep2').classList.add('hidden');
            document.getElementById('paymentStep1').classList.remove('hidden');
        }
    }
    
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateBulkToolbar();
    }
    
    function updateBulkToolbar() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const toolbar = document.getElementById('bulkActionsToolbar');
        const countSpan = document.getElementById('selectedCount');
        if(checked.length > 0) {
            countSpan.innerText = checked.length;
            toolbar.classList.remove('hidden');
            toolbar.classList.add('flex');
        } else {
            toolbar.classList.add('hidden');
            toolbar.classList.remove('flex');
        }
    }
    
    function clearSelection() {
        document.getElementById('selectAllCb').checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        updateBulkToolbar();
    }
    
    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if(checked.length === 0) return false;
        if(!confirm('Are you sure you want to delete ' + checked.length + ' selected invoices?')) return false;
        
        const form = document.getElementById('bulkDeleteForm');
        const c = document.getElementById('bulkDeleteInputs');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }
    
    function bulkPrintSelected() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if(checked.length === 0) return;
        
        const c = document.getElementById('bulkPrintInputs');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
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
