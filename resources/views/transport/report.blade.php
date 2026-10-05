@extends('layouts.app')

@section('header', 'Transport Report')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Students Transport Report</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white p-4">
    <form action="{{ route('transport.report') }}" method="GET" class="grid grid-cols-1 items-end gap-3 md:grid-cols-4">
        <div>
            <label for="class_id" class="mb-1 block text-xs font-medium text-body">Class</label>
            <select name="class_id" id="class_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="section_id" class="mb-1 block text-xs font-medium text-body">Section</label>
            <select name="section_id" id="section_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" data-class-id="{{ $section->school_class_id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="route_id" class="mb-1 block text-xs font-medium text-body">Transport Route <span class="text-red-500">*</span></label>
            <select name="route_id" id="route_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">Select Route</option>
                @foreach($routes as $route)
                    <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                        {{ $route->route_name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="flex gap-2">
            <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">Get Report</button>
            <a href="{{ route('transport.report') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Reset</a>
        </div>
    </form>
</section>

@if($request->has('route_id'))
<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Assigned Students</h3>
        <span class="text-xs text-body">{{ count($students) }} records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead>
                <tr class="bg-navy">
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Route Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Admission No</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Class</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Section</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Father's Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Father's Phone</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-heading">
                            @foreach($student->transportRoutes as $sr)
                                @if($sr->id == request('route_id'))
                                    {{ $sr->route_name }}
                                @endif
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-heading">{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->admission_no }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->schoolClass ? $student->schoolClass->name : '—' }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->section ? $student->section->name : '—' }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->father_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->father_phone ?? '—' }}</td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-body">No students found for this selection.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        const sectionSelect = document.getElementById('section_id');
        
        if (classSelect && sectionSelect) {
            const originalSectionOptions = Array.from(sectionSelect.options);

            function filterSections() {
                const selectedClassId = classSelect.value;
                const currentSectionValue = sectionSelect.value;
                
                // Clear current options
                sectionSelect.innerHTML = '';
                
                // Add options that match the selected class (or "All Sections")
                originalSectionOptions.forEach(option => {
                    if (option.value === '' || !selectedClassId || option.getAttribute('data-class-id') === selectedClassId) {
                        sectionSelect.appendChild(option);
                    }
                });
                
                // Try to keep the previously selected value if it's still available
                if (Array.from(sectionSelect.options).some(opt => opt.value === currentSectionValue)) {
                    sectionSelect.value = currentSectionValue;
                } else {
                    sectionSelect.value = '';
                }
            }

            classSelect.addEventListener('change', filterSections);
            // Run on load to set initial state if class is already selected
            filterSections();
        }
    });
</script>

@endsection
