<?php $__env->startSection('header', 'Fee Invoices'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div><h2 class="text-lg font-bold text-heading">Fee Invoices</h2><p class="text-xs text-body"><?php echo e($school->name); ?> · <?php echo e($session->name); ?></p></div>
    <div class="flex flex-wrap gap-2">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-gray-50"><i data-lucide="upload" class="h-4 w-4"></i>Import</button>
        <a href="<?php echo e(route('accounting.fee-invoices.export', request()->all())); ?>" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-gray-50"><i data-lucide="download" class="h-4 w-4"></i>Export</a>

        <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-2 rounded-md bg-navy px-3 py-2 text-xs font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Create Invoice</button>
    </div>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white p-4">
    <form method="GET" class="grid grid-cols-1 items-end gap-3 md:grid-cols-4">
        <div><label for="invoiceKeyword" class="mb-1 block text-xs font-medium text-body">Search Student / Admission No.</label><input id="invoiceKeyword" name="keyword" value="<?php echo e(request('keyword')); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Search"></div>
        <div><label for="invoiceClassFilter" class="mb-1 block text-xs font-medium text-body">Class</label><select id="invoiceClassFilter" name="class_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><option value="">All classes</option><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($class->id); ?>" <?php echo e(request('class_id') == $class->id ? 'selected' : ''); ?>><?php echo e($class->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label for="invoiceStatusFilter" class="mb-1 block text-xs font-medium text-body">Status</label><select id="invoiceStatusFilter" name="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><option value="">All statuses</option><?php $__currentLoopData = ['unpaid' => 'Unpaid', 'partial' => 'Partially paid', 'paid' => 'Paid']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php echo e(request('status') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="flex gap-2"><button class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white">Filter</button><a href="<?php echo e(route('accounting.fee-invoices.index')); ?>" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body">Reset</a></div>
    </form>
</section>

<?php if($errors->any()): ?>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($message); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
<?php endif; ?>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Invoices</h3><span class="text-xs text-body"><?php echo e($invoices->total()); ?> records</span></div>    
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkPrintForm" method="POST" action="<?php echo e(route('accounting.bulk-invoice-print.print')); ?>" target="hidden-print-iframe" class="hidden">
        <?php echo csrf_field(); ?>
        <div id="bulkPrintIdsContainer"></div>
    </form>
    <button type="button" onclick="bulkPrintSelected()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition-colors shadow-sm">
        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Selected
    </button>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('accounting.fee-invoices.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
        <?php echo csrf_field(); ?>
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead><tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Father's Name</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Class</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Invoice</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Issued</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Due</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Total</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Received</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Due Amount</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Status</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors" data-id="<?php echo e($invoice->id); ?>">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="<?php echo e($invoice->id); ?>" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-3"><span class="block font-bold text-gray-900"><?php echo e($invoice->student->full_name); ?></span><span class="mt-0.5 block font-medium text-gray-700"><?php echo e($invoice->student->admission_no); ?></span></td>
                        <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($invoice->student->father_name ?? '-'); ?></td>
                        <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($invoice->student->schoolClass->name); ?></td>
                        <td class="px-4 py-3"><span class="block font-bold text-gray-900"><?php echo e($invoice->invoice_title); ?></span><span class="mt-0.5 block font-medium text-gray-700">#<?php echo e(str_pad((string)$invoice->id, 5, '0', STR_PAD_LEFT)); ?></span></td>
                        <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($invoice->issued_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($invoice->due_at?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) ?? '—'); ?></td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format((float) $invoice->total_amount, 2)); ?></td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format((float) $invoice->amount_paid, 2)); ?></td>
                        <td class="px-4 py-3 text-right font-bold text-red-600"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format(max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid), 2)); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col items-start gap-1">
                                <span class="rounded-full px-2 py-1 text-[11px] font-bold <?php echo e($invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')); ?>"><?php echo e(ucfirst($invoice->status)); ?></span>
                                <?php if($invoice->status !== 'paid'): ?>
                                    <button type="button" onclick="openPaymentModal(<?php echo e($invoice->id); ?>, <?php echo e(max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid)); ?>, <?php echo e($invoice->allow_partial_payment ? 'true' : 'false'); ?>)" class="text-[11px] font-bold text-blue-700 hover:underline">Collect Payment</button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="printDocument('<?php echo e(route('accounting.fee-invoices.print', $invoice)); ?>')" title="Print Invoice" class="inline-flex items-center rounded-md border border-gray-300 p-1.5 text-navy hover:bg-gray-50"><i data-lucide="printer" class="h-4 w-4"></i></button>
                                <button type="button" onclick="openEditModal(JSON.parse(this.dataset.invoice))" data-invoice="<?php echo e(json_encode([
                                    'id' => $invoice->id,
                                    'invoice_title' => $invoice->invoice_title,
                                    'total_amount' => $invoice->total_amount,
                                    'due_amount' => $invoice->due_amount,
                                    'issued_at' => $invoice->issued_at?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')),
                                    'due_at' => $invoice->due_at?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')),
                                    'allow_partial_payment' => $invoice->allow_partial_payment,
                                    'description' => $invoice->description,
                                    'amount_paid' => $invoice->amount_paid,
                                    'student' => [
                                        'full_name' => $invoice->student->full_name,
                                        'admission_no' => $invoice->student->admission_no,
                                        'class' => $invoice->student->schoolClass->name
                                    ]
                                ])); ?>" title="Edit Invoice" class="inline-flex items-center rounded-md border border-gray-300 p-1.5 text-blue-600 hover:bg-blue-50"><i data-lucide="edit-3" class="h-4 w-4"></i></button>
                                <form action="<?php echo e(route('accounting.fee-invoices.destroy', $invoice)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice? This cannot be undone.');" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" title="Delete Invoice" class="inline-flex items-center rounded-md border border-gray-300 p-1.5 text-red-600 hover:bg-red-50"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="100%" class="px-4 py-12 text-center text-sm text-body">No invoices found for this session.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="border-t border-gray-100 px-4 py-3"><?php echo e($invoices->links()); ?></div>
</section>


<div id="invoiceModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b pb-3">
            <h3 id="modalTitle" class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5 text-navy"></i> Create Invoice
            </h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="invoiceForm" action="<?php echo e(route('accounting.fee-invoices.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            
            <div id="editStudentInfo" class="hidden mb-5">
                <h4 class="text-xs font-bold text-heading mb-3 uppercase">Student Information</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Student</label>
                        <input type="text" id="editStudentName" disabled class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Admission No</label>
                        <input type="text" id="editStudentAdm" disabled class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Class</label>
                        <input type="text" id="editStudentClass" disabled class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                    </div>
                </div>
            </div>

            
            <div id="invoiceStep1">
                <h4 class="text-xs font-bold text-heading mb-3 uppercase">Invoice Details</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="invoiceTitle" class="mb-1 block text-xs font-medium text-body">Invoice Title <span class="text-red-600">*</span></label>
                        <input id="invoiceTitle" name="invoice_title" required maxlength="150" placeholder="e.g. Examination Fee" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                    <div>
                        <label for="totalAmount" class="mb-1 block text-xs font-medium text-body">Total Amount <span class="text-red-600">*</span></label>
                        <input id="totalAmount" name="total_amount" type="number" min="0.01" step="0.01" required placeholder="0.00" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                    <div>
                        <label for="dueAmount" class="mb-1 block text-xs font-medium text-body">Due Date Amount</label>
                        <input id="dueAmount" name="due_amount" type="number" min="0" step="0.01" placeholder="Defaults to total" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                    <div>
                        <label for="issuedAt" class="mb-1 block text-xs font-medium text-body">Date Issued <span class="text-red-600">*</span></label>
                        <input id="issuedAt" name="issued_at" type="date" value="<?php echo e(now()->toDateString()); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                    <div>
                        <label for="dueAt" class="mb-1 block text-xs font-medium text-body">Due Date</label>
                        <input id="dueAt" name="due_at" type="date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                    <div class="flex items-center md:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-heading">
                            <input type="checkbox" id="allowPartial" name="allow_partial_payment" value="1" class="rounded accent-navy">
                            Allow partial payments
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label for="invoiceDescription" class="mb-1 block text-xs font-medium text-body">Description</label>
                        <textarea id="invoiceDescription" name="description" rows="3" maxlength="2000" placeholder="Optional note shown on the invoice" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex flex-wrap justify-end gap-3 border-t pt-4" id="step1Actions">
                    <button type="button" onclick="closeModal()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-heading hover:bg-gray-200 transition-colors">Cancel</button>
                    <button type="button" id="nextBtn" onclick="goToStep(2)" class="inline-flex items-center gap-2 rounded-lg bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark transition-colors">Next <i data-lucide="arrow-right" class="h-4 w-4"></i></button>
                    <button type="submit" id="updateBtn" class="hidden inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition-opacity" style="background:linear-gradient(135deg,#B47A42,#D4A055);"><i data-lucide="save" class="h-4 w-4"></i> Update</button>
                </div>
            </div>

            
            <div id="invoiceStep2" class="hidden">
                <h4 class="text-xs font-bold text-heading mb-3 uppercase">Recipients</h4>
                <p class="mb-4 text-xs text-body">Choose a class, optionally narrow to a section, then select one or more students.</p>
                <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                    <div>
                        <label for="invoiceClass" class="mb-1 block text-xs font-medium text-body">Class <span class="text-red-600">*</span></label>
                        <select id="invoiceClass" name="school_class_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <option value="">Select class</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="invoiceSection" class="mb-1 block text-xs font-medium text-body">Section</label>
                        <select id="invoiceSection" name="section_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <option value="">All sections</option>
                        </select>
                    </div>
                </div>

                <div class="border rounded-md border-gray-200 p-4 bg-gray-50">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-heading">Students <span class="text-red-600">*</span></span>
                        <div class="flex items-center gap-4">
                            <span id="selectedStudentCount" class="text-xs text-body">0 selected</span>
                            <label class="flex items-center gap-2 text-xs text-body font-medium">
                                <input id="selectAllStudents" type="checkbox" class="rounded accent-navy"> Select All
                            </label>
                        </div>
                    </div>
                    <div id="invoiceStudents" class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3 max-h-[30vh] overflow-y-auto">
                        <p class="text-sm italic text-body">Choose a class to load students.</p>
                    </div>
                </div>
                
                <div class="mt-6 flex flex-wrap justify-between gap-3 border-t pt-4">
                    <button type="button" onclick="goToStep(1)" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-heading hover:bg-gray-200 transition-colors inline-flex items-center gap-2"><i data-lucide="arrow-left" class="h-4 w-4"></i> Back</button>
                    <div class="flex gap-3">
                        <button type="submit" name="action_type" value="save" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition-opacity" style="background:linear-gradient(135deg,#B47A42,#D4A055);"><i data-lucide="save" class="h-4 w-4"></i> Save</button>
                        <button type="submit" name="action_type" value="save_and_next" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"><i data-lucide="arrow-right-circle" class="h-4 w-4"></i> Save & Next</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Collect Payment Modal -->
<div id="paymentModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center bg-gray-900/50 backdrop-blur-sm transition-all">
    <div class="w-full max-w-sm rounded-xl bg-white shadow-2xl overflow-hidden p-6">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-sm font-bold text-heading">Collect Payment</h3>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
        
        <form id="paymentForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            
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
                    <select name="payment_method_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="">Select Method</option>
                        <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pm->id); ?>"><?php echo e($pm->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Authorized By <span class="text-red-600">*</span></label>
                    <select name="authorized_by" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="">Select Authorizer</option>
                        <?php $__currentLoopData = $authorizers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-xs font-bold text-gray-900">Payment Date <span class="text-red-600">*</span></label>
                    <input type="date" name="paid_on" value="<?php echo e(now()->toDateString()); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-bold text-gray-900 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
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

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-sm transition-all">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-heading">Import Fee Invoices</h3>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-5 w-5"></i></button>
            </div>
            <form action="<?php echo e(route('accounting.fee-invoices.import')); ?>" method="POST" enctype="multipart/form-data" class="p-6">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <p class="mb-2 text-sm text-body">Download the sample file to see the required format.</p>
                    <a href="<?php echo e(route('accounting.fee-invoices.sample')); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800"><i data-lucide="download" class="h-4 w-4"></i>Download Sample CSV</a>
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-heading">Select CSV File</label>
                    <input type="file" name="file" accept=".csv,.txt" required class="block w-full text-sm text-body file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-heading hover:file:bg-gray-200">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-body hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const classSelect = document.getElementById('invoiceClass');
    const sectionSelect = document.getElementById('invoiceSection');
    const studentsPanel = document.getElementById('invoiceStudents');
    const selectAll = document.getElementById('selectAllStudents');
    const countLabel = document.getElementById('selectedStudentCount');
    const sectionsUrl = <?php echo json_encode(url('accounting/fee-invoices/classes'), 15, 512) ?> + '/:classId/sections';
    const studentsUrl = <?php echo json_encode(url('accounting/fee-invoices/classes'), 15, 512) ?> + '/:classId/students';
    
    function updateCount() {
        const all = [...studentsPanel.querySelectorAll('input[name="student_ids[]"]')];
        const selected = all.filter((input) => input.checked).length;
        countLabel.textContent = `${selected} of ${all.length} students selected`;
        selectAll.checked = all.length > 0 && selected === all.length;
        selectAll.indeterminate = selected > 0 && selected < all.length;
    }

    async function loadStudents() {
        const classId = classSelect.value;
        if (!classId) {
            studentsPanel.innerHTML = '<p class="text-sm italic text-body">Choose a class to load students.</p>';
            updateCount();
            return;
        }
        const sectionQuery = sectionSelect.value ? `?section_id=${encodeURIComponent(sectionSelect.value)}` : '';
        studentsPanel.innerHTML = '<p class="text-sm italic text-body">Loading students...</p>';
        const response = await fetch(studentsUrl.replace(':classId', classId) + sectionQuery, { headers: { Accept: 'application/json' } });
        if (!response.ok) {
            studentsPanel.innerHTML = '<p class="text-sm text-red-700">Students could not be loaded.</p>';
            return;
        }
        const students = await response.json();
        studentsPanel.replaceChildren();
        if (!students.length) {
            studentsPanel.innerHTML = '<p class="text-sm italic text-body">No students in this selection.</p>';
            updateCount();
            return;
        }
        students.forEach((student) => {
            const label = document.createElement('label');
            label.className = 'flex min-w-0 items-start gap-2 rounded-md border border-gray-200 p-3 text-xs hover:bg-gray-50 bg-white';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'student_ids[]';
            checkbox.value = student.id;
            checkbox.className = 'mt-0.5 rounded accent-navy';
            checkbox.addEventListener('change', updateCount);
            const text = document.createElement('span');
            text.className = 'min-w-0';
            const name = document.createElement('strong');
            name.className = 'block truncate text-heading';
            name.textContent = student.name;
            const admissionNo = document.createElement('span');
            admissionNo.className = 'mt-0.5 block text-body';
            admissionNo.textContent = student.admission_no;
            text.append(name, admissionNo);
            label.append(checkbox, text);
            studentsPanel.append(label);
        });
        updateCount();
    }

    async function loadSections() {
        const classId = classSelect.value;
        sectionSelect.replaceChildren(new Option('All sections', ''));
        studentsPanel.innerHTML = '<p class="text-sm italic text-body">Choose a class to load students.</p>';
        updateCount();
        if (!classId) return;
        const response = await fetch(sectionsUrl.replace(':classId', classId), { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        (await response.json()).forEach((section) => sectionSelect.add(new Option(section.name, section.id)));
        loadStudents();
    }

    classSelect.addEventListener('change', loadSections);
    sectionSelect.addEventListener('change', loadStudents);
    selectAll.addEventListener('change', () => {
        studentsPanel.querySelectorAll('input[name="student_ids[]"]').forEach((input) => input.checked = selectAll.checked);
        updateCount();
    });

    function goToStep(step) {
        if (step === 2) {
            const title = document.getElementById('invoiceTitle').value;
            const amount = document.getElementById('totalAmount').value;
            const issued = document.getElementById('issuedAt').value;
            
            if (!title || !amount || !issued) {
                alert('Please fill out all required fields (Title, Total Amount, Date Issued) before proceeding.');
                return;
            }
            
            document.getElementById('invoiceStep1').classList.add('hidden');
            document.getElementById('invoiceStep2').classList.remove('hidden');
        } else {
            document.getElementById('invoiceStep2').classList.add('hidden');
            document.getElementById('invoiceStep1').classList.remove('hidden');
        }
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerHTML = '<i data-lucide="plus" class="w-5 h-5 text-navy"></i> Create Invoice';
        document.getElementById('invoiceForm').action = "<?php echo e(route('accounting.fee-invoices.store')); ?>";
        document.getElementById('formMethod').value = "POST";
        document.getElementById('invoiceForm').reset();
        
        document.getElementById('editStudentInfo').classList.add('hidden');
        document.getElementById('nextBtn').classList.remove('hidden');
        document.getElementById('updateBtn').classList.add('hidden');
        
        classSelect.required = true;
        
        goToStep(1);
        document.getElementById('invoiceModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditModal(invoice) {
        document.getElementById('modalTitle').innerHTML = '<i data-lucide="edit" class="w-5 h-5 text-navy"></i> Edit Invoice';
        document.getElementById('invoiceForm').action = `/accounting/fee-invoices/${invoice.id}`;
        document.getElementById('formMethod').value = "PUT";
        
        document.getElementById('invoiceTitle').value = invoice.invoice_title;
        document.getElementById('totalAmount').value = invoice.total_amount;
        document.getElementById('totalAmount').min = invoice.amount_paid;
        document.getElementById('dueAmount').value = invoice.due_amount;
        document.getElementById('issuedAt').value = invoice.issued_at;
        document.getElementById('dueAt').value = invoice.due_at || '';
        document.getElementById('allowPartial').checked = !!invoice.allow_partial_payment;
        document.getElementById('invoiceDescription').value = invoice.description || '';
        
        document.getElementById('editStudentName').value = invoice.student.full_name;
        document.getElementById('editStudentAdm').value = invoice.student.admission_no;
        document.getElementById('editStudentClass').value = invoice.student.class;
        
        document.getElementById('editStudentInfo').classList.remove('hidden');
        document.getElementById('nextBtn').classList.add('hidden');
        document.getElementById('updateBtn').classList.remove('hidden');
        
        classSelect.required = false;
        
        goToStep(1);
        document.getElementById('invoiceModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeModal() {
        document.getElementById('invoiceModal').classList.add('hidden');
    }

    <?php if(session('reopen_add_modal')): ?>
        document.addEventListener('DOMContentLoaded', () => {
            openAddModal();
        });
    <?php endif; ?>

    // Payment Modal Functions
    function openPaymentModal(invoiceId, balance, allowPartial) {
        const form = document.getElementById('paymentForm');
        form.action = `/accounting/fee-invoices/${invoiceId}/payments`;
        form.reset();

        const amtInput = document.getElementById('paymentAmount');
        amtInput.value = balance;
        amtInput.max = balance;
        
        if (!allowPartial) {
            amtInput.readOnly = true;
            amtInput.classList.add('bg-gray-50', 'text-gray-500');
            document.getElementById('paymentAmountHelp').classList.remove('hidden');
        } else {
            amtInput.readOnly = false;
            amtInput.classList.remove('bg-gray-50', 'text-gray-500');
            document.getElementById('paymentAmountHelp').classList.add('hidden');
        }

        goPaymentStep(1);
        document.getElementById('paymentModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function goPaymentStep(step) {
        if (step === 2) {
            if (!document.getElementById('paymentAmount').value || document.getElementById('paymentAmount').value <= 0) {
                alert('Please enter a valid amount');
                return;
            }
            document.getElementById('paymentStep1').classList.add('hidden');
            document.getElementById('paymentStep2').classList.remove('hidden');
        } else {
            document.getElementById('paymentStep2').classList.add('hidden');
            document.getElementById('paymentStep1').classList.remove('hidden');
        }
    }
</script>

    
    <script>
    (function() {
        function toggleSelectAll(cb) {
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.checked = cb.checked;
                r.closest('tr').classList.toggle('bg-red-50', cb.checked);
            });
            updateBulkToolbar();
        }
        function updateBulkToolbar() {
            var checked = document.querySelectorAll('.row-checkbox:checked');
            var all     = document.querySelectorAll('.row-checkbox');
            var toolbar = document.getElementById('bulkToolbar');
            var countEl = document.getElementById('selectedCount');
            var sa      = document.getElementById('selectAll');
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.closest('tr').classList.toggle('bg-red-50', r.checked);
            });
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
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.checked = false;
                r.closest('tr').classList.remove('bg-red-50');
            });
            var sa = document.getElementById('selectAll');
            if (sa) { sa.checked = false; sa.indeterminate = false; }
            var toolbar = document.getElementById('bulkToolbar');
            if(toolbar) toolbar.classList.add('hidden');
        }
        function confirmBulkDelete() {
            var checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return false;
            if (!confirm(checked.length + ' item(s) permanently delete honge. Sure hain?')) return false;
            var c = document.getElementById('bulkIdsContainer');
            c.innerHTML = '';
            checked.forEach(function(cb) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
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
                inp.type = 'hidden'; inp.name = 'invoice_ids[]'; inp.value = cb.value;
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
        window.toggleSelectAll   = toggleSelectAll;
        window.updateBulkToolbar = updateBulkToolbar;
        window.clearSelection    = clearSelection;
        window.confirmBulkDelete = confirmBulkDelete;
        window.bulkPrintSelected = bulkPrintSelected;
        
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
    })();
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/accounting/invoices/index.blade.php ENDPATH**/ ?>