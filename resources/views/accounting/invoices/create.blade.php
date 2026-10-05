@extends('layouts.app')

@section('header', 'Create Fee Invoice')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div><h2 class="text-lg font-bold text-heading">Create Fee Invoice</h2><p class="text-xs text-body">{{ $school->name }} · {{ $session->name }}</p></div>
    <a href="{{ route('accounting.fee-invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to invoices</a>
</div>

@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>
@endif

<form action="{{ route('accounting.fee-invoices.store') }}" method="POST" class="space-y-5">
    @csrf
    <section class="rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Recipients</h3><p class="mt-1 text-xs text-body">Choose a class, optionally narrow to a section, then select one or more students.</p></div>
        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            <div><label for="invoiceClass" class="mb-1 block text-xs font-medium text-body">Class <span class="text-red-600">*</span></label><select id="invoiceClass" name="school_class_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><option value="">Select class</option>@foreach($classes as $class)<option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>@endforeach</select></div>
            <div><label for="invoiceSection" class="mb-1 block text-xs font-medium text-body">Section</label><select id="invoiceSection" name="section_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><option value="">All sections</option></select></div>
            <div class="flex items-end"><span id="selectedStudentCount" class="text-xs text-body">Select a class to load students.</span></div>
        </div>
        <div class="border-t border-gray-100 p-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2"><span class="text-xs font-semibold text-heading">Students <span class="text-red-600">*</span></span><label class="flex items-center gap-2 text-xs text-body"><input id="selectAllStudents" type="checkbox" class="rounded accent-navy">Select all shown</label></div>
            <div id="invoiceStudents" class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3"><p class="text-sm italic text-body">Choose a class to load students.</p></div>
        </div>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Invoice Details</h3></div>
        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="md:col-span-2"><label for="invoiceTitle" class="mb-1 block text-xs font-medium text-body">Invoice Title <span class="text-red-600">*</span></label><input id="invoiceTitle" name="invoice_title" value="{{ old('invoice_title') }}" required maxlength="150" placeholder="e.g. Examination Fee" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="totalAmount" class="mb-1 block text-xs font-medium text-body">Total Amount <span class="text-red-600">*</span></label><input id="totalAmount" name="total_amount" type="number" min="0.01" step="0.01" value="{{ old('total_amount') }}" required placeholder="0.00" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="dueAmount" class="mb-1 block text-xs font-medium text-body">Due Date Amount</label><input id="dueAmount" name="due_amount" type="number" min="0" step="0.01" value="{{ old('due_amount') }}" placeholder="Defaults to total" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="issuedAt" class="mb-1 block text-xs font-medium text-body">Date Issued <span class="text-red-600">*</span></label><input id="issuedAt" name="issued_at" type="date" value="{{ old('issued_at', now()->toDateString()) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="dueAt" class="mb-1 block text-xs font-medium text-body">Due Date</label><input id="dueAt" name="due_at" type="date" value="{{ old('due_at') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div class="flex items-center md:col-span-2"><label class="flex items-center gap-2 text-sm text-heading"><input type="checkbox" name="allow_partial_payment" value="1" {{ old('allow_partial_payment') ? 'checked' : '' }} class="rounded accent-navy">Allow partial payments</label></div>
            <div class="md:col-span-2 xl:col-span-4"><label for="invoiceDescription" class="mb-1 block text-xs font-medium text-body">Description</label><textarea id="invoiceDescription" name="description" rows="3" maxlength="2000" placeholder="Optional note shown on the invoice" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">{{ old('description') }}</textarea></div>
        </div>
    </section>

    <div class="flex justify-end gap-2"><a href="{{ route('accounting.fee-invoices.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Cancel</a><button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="file-plus-2" class="h-4 w-4"></i>Create Invoices</button></div>
</form>

<script>
(() => {
    const classSelect = document.getElementById('invoiceClass');
    const sectionSelect = document.getElementById('invoiceSection');
    const studentsPanel = document.getElementById('invoiceStudents');
    const selectAll = document.getElementById('selectAllStudents');
    const countLabel = document.getElementById('selectedStudentCount');
    const sectionsUrl = @json(url('accounting/fee-invoices/classes')) + '/:classId/sections';
    const studentsUrl = @json(url('accounting/fee-invoices/classes')) + '/:classId/students';
    const previousStudentIds = @json(old('student_ids', []));
    const previousSectionId = @json(old('section_id', ''));

    function updateCount() {
        const all = [...studentsPanel.querySelectorAll('input[name="student_ids[]"]')];
        const selected = all.filter((input) => input.checked).length;
        countLabel.textContent = `${selected} of ${all.length} students selected`;
        selectAll.checked = all.length > 0 && selected === all.length;
        selectAll.indeterminate = selected > 0 && selected < all.length;
    }

    async function loadStudents() {
        const classId = classSelect.value;
        if (!classId) return;
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
            label.className = 'flex min-w-0 items-start gap-2 rounded-md border border-gray-200 p-3 text-xs hover:bg-gray-50';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'student_ids[]';
            checkbox.value = student.id;
            checkbox.className = 'mt-0.5 rounded accent-navy';
            checkbox.checked = previousStudentIds.length > 0 && previousStudentIds.map(Number).includes(Number(student.id));
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
        if (!classId) return;
        const response = await fetch(sectionsUrl.replace(':classId', classId), { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        (await response.json()).forEach((section) => sectionSelect.add(new Option(section.name, section.id, false, String(section.id) === String(previousSectionId))));
        loadStudents();
    }

    classSelect.addEventListener('change', loadSections);
    sectionSelect.addEventListener('change', loadStudents);
    selectAll.addEventListener('change', () => {
        studentsPanel.querySelectorAll('input[name="student_ids[]"]').forEach((input) => input.checked = selectAll.checked);
        updateCount();
    });
    if (classSelect.value) loadSections();
})();
</script>
@endsection
