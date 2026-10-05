@extends('layouts.app')

@section('header', 'Edit Fee Invoice')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div><h2 class="text-lg font-bold text-heading">Edit Fee Invoice</h2><p class="text-xs text-body">{{ $school->name }} · {{ $session->name }}</p></div>
    <a href="{{ route('accounting.fee-invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to invoices</a>
</div>

@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>
@endif

<form action="{{ route('accounting.fee-invoices.update', $feeInvoice) }}" method="POST" class="space-y-5">
    @csrf
    @method('PUT')
    
    <section class="rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Student Information</h3></div>
        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
            <div><label class="mb-1 block text-xs font-medium text-body">Student</label><input type="text" value="{{ $feeInvoice->student->full_name }}" disabled class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500"></div>
            <div><label class="mb-1 block text-xs font-medium text-body">Admission No</label><input type="text" value="{{ $feeInvoice->student->admission_no }}" disabled class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500"></div>
            <div><label class="mb-1 block text-xs font-medium text-body">Class</label><input type="text" value="{{ $feeInvoice->student->schoolClass->name }}" disabled class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500"></div>
        </div>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Invoice Details</h3></div>
        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="md:col-span-2"><label for="invoiceTitle" class="mb-1 block text-xs font-medium text-body">Invoice Title <span class="text-red-600">*</span></label><input id="invoiceTitle" name="invoice_title" value="{{ old('invoice_title', $feeInvoice->invoice_title) }}" required maxlength="150" placeholder="e.g. Examination Fee" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="totalAmount" class="mb-1 block text-xs font-medium text-body">Total Amount <span class="text-red-600">*</span></label><input id="totalAmount" name="total_amount" type="number" min="{{ $feeInvoice->amount_paid }}" step="0.01" value="{{ old('total_amount', $feeInvoice->total_amount) }}" required placeholder="0.00" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="dueAmount" class="mb-1 block text-xs font-medium text-body">Due Date Amount</label><input id="dueAmount" name="due_amount" type="number" min="0" step="0.01" value="{{ old('due_amount', $feeInvoice->due_amount) }}" placeholder="Defaults to total" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="issuedAt" class="mb-1 block text-xs font-medium text-body">Date Issued <span class="text-red-600">*</span></label><input id="issuedAt" name="issued_at" type="date" value="{{ old('issued_at', $feeInvoice->issued_at->format('Y-m-d')) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div><label for="dueAt" class="mb-1 block text-xs font-medium text-body">Due Date</label><input id="dueAt" name="due_at" type="date" value="{{ old('due_at', $feeInvoice->due_at?->format('Y-m-d')) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
            <div class="flex items-center md:col-span-2"><label class="flex items-center gap-2 text-sm text-heading"><input type="checkbox" name="allow_partial_payment" value="1" {{ old('allow_partial_payment', $feeInvoice->allow_partial_payment) ? 'checked' : '' }} class="rounded accent-navy">Allow partial payments</label></div>
            <div class="md:col-span-2 xl:col-span-4"><label for="invoiceDescription" class="mb-1 block text-xs font-medium text-body">Description</label><textarea id="invoiceDescription" name="description" rows="3" maxlength="2000" placeholder="Optional note shown on the invoice" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">{{ old('description', $feeInvoice->description) }}</textarea></div>
        </div>
    </section>

    <div class="flex justify-end gap-2"><a href="{{ route('accounting.fee-invoices.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Cancel</a><button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="save" class="h-4 w-4"></i>Save Changes</button></div>
</form>
@endsection
