@extends('layouts.app')

@section('header', 'Create Transport Invoice')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Create Transport Invoice</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
    <a href="{{ route('accounting.transport-invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>Back to invoices
    </a>
</div>

{{-- Step 1: Filter Students --}}
<section class="mb-5 rounded-lg border border-gray-200 bg-white p-5">
    <h3 class="mb-4 text-sm font-bold text-heading">1. Select Target Group</h3>
    <form action="{{ route('accounting.transport-invoices.create') }}" method="GET" class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">
        <div>
            <label for="class_id" class="mb-1 block text-sm font-medium text-body">Class <span class="text-red-500">*</span></label>
            <select name="class_id" id="class_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">Select Class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="section_id" class="mb-1 block text-sm font-medium text-body">Section <span class="text-red-500">*</span></label>
            <select name="section_id" id="section_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">Select Section</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" data-class-id="{{ $section->school_class_id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="transport_route_id" class="mb-1 block text-sm font-medium text-body">Transport Route <span class="text-red-500">*</span></label>
            <select name="transport_route_id" id="transport_route_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">Select Route</option>
                @foreach($routes as $route)
                    <option value="{{ $route->id }}" data-amount="{{ $route->route_fare }}" {{ request('transport_route_id') == $route->id ? 'selected' : '' }}>
                        {{ $route->route_name }} ({{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ $route->route_fare }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                <i data-lucide="search" class="h-4 w-4"></i> Search Students
            </button>
        </div>
    </form>
</section>

{{-- Step 2: Invoice Details & Students --}}
@if(request('class_id') && request('section_id') && request('transport_route_id'))
<section class="rounded-lg border border-gray-200 bg-white">
    <form action="{{ route('accounting.transport-invoices.store') }}" method="POST">
        @csrf
        {{-- Hidden fields to preserve selection --}}
        <input type="hidden" name="class_id" value="{{ request('class_id') }}">
        <input type="hidden" name="section_id" value="{{ request('section_id') }}">
        <input type="hidden" name="transport_route_id" value="{{ request('transport_route_id') }}">
        
        <div class="p-5 border-b border-gray-200">
            <h3 class="mb-4 text-sm font-bold text-heading">2. Invoice Details</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="invoice_title" class="mb-1 block text-sm font-medium text-body">Invoice Heading <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_title" id="invoice_title" value="{{ old('invoice_title') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="e.g. November Transport Fee">
                    @error('invoice_title')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="amount" class="mb-1 block text-sm font-medium text-body">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    @error('amount')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="issued_at" class="mb-1 block text-sm font-medium text-body">Invoice Date <span class="text-red-500">*</span></label>
                    <input type="date" name="issued_at" id="issued_at" value="{{ old('issued_at', date('Y-m-d')) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    @error('issued_at')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="start_date" class="mb-1 block text-sm font-medium text-body">Starting Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>

                <div>
                    <label for="end_date" class="mb-1 block text-sm font-medium text-body">Ending Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
            </div>
        </div>

        <div class="p-0">
            <div class="flex items-center justify-between px-5 py-4">
                <h3 class="text-sm font-bold text-heading">3. Select Students</h3>
                <span class="text-xs text-body">{{ count($students) }} students found</span>
            </div>
            
            @error('student_ids')
                <div class="px-5 pb-3 text-sm text-red-500">{{ $message }}</div>
            @enderror

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead>
                        <tr class="bg-navy">
                            <th scope="col" class="px-4 py-3 font-semibold text-white w-12 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-navy focus:ring-navy" checked>
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Admission No</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Student Name</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Class (Section)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-cb rounded border-gray-300 text-navy focus:ring-navy" checked>
                                </td>
                                <td class="px-4 py-3 text-body">{{ $student->admission_no }}</td>
                                <td class="px-4 py-3 font-semibold text-heading">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="px-4 py-3 text-body">{{ $student->schoolClass->name ?? '' }} ({{ $student->section->name ?? '' }})</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-body">
                                    No students found for this class, section, and route combination.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($students) > 0)
        <div class="flex justify-end gap-2 border-t border-gray-200 p-5 bg-gray-50 rounded-b-lg">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                <i data-lucide="file-plus-2" class="h-4 w-4"></i> Create Invoices
            </button>
        </div>
        @endif
    </form>
</section>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Class/Section cascading logic
        const classSelect = document.getElementById('class_id');
        const sectionSelect = document.getElementById('section_id');
        
        if (classSelect && sectionSelect) {
            const originalSectionOptions = Array.from(sectionSelect.options);

            function filterSections() {
                const selectedClassId = classSelect.value;
                const currentSectionValue = sectionSelect.value;
                
                sectionSelect.innerHTML = '';
                
                originalSectionOptions.forEach(option => {
                    if (option.value === '' || !selectedClassId || option.getAttribute('data-class-id') === selectedClassId) {
                        sectionSelect.appendChild(option);
                    }
                });
                
                if (Array.from(sectionSelect.options).some(opt => opt.value === currentSectionValue)) {
                    sectionSelect.value = currentSectionValue;
                } else {
                    sectionSelect.value = '';
                }
            }

            classSelect.addEventListener('change', filterSections);
            filterSections();
        }

        // Auto-fill amount logic on page load based on selected route
        const routeSelect = document.getElementById('transport_route_id');
        const amountInput = document.getElementById('amount');
        if (routeSelect && amountInput && !amountInput.value) {
            const selectedOption = routeSelect.options[routeSelect.selectedIndex];
            if (selectedOption && selectedOption.getAttribute('data-amount')) {
                amountInput.value = selectedOption.getAttribute('data-amount');
            }
        }
        
        // Select All checkboxes logic
        const selectAllCb = document.getElementById('selectAll');
        if (selectAllCb) {
            selectAllCb.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.student-cb');
                checkboxes.forEach(cb => cb.checked = selectAllCb.checked);
            });
        }
    });
</script>
@endsection
