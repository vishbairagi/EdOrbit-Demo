@extends('layouts.app')

@section('header', 'Assign Students to Transport Routes')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Assign Students</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="mb-5 rounded-lg border border-gray-200 bg-white p-5">
    <form method="GET" action="{{ route('transport.assign-students.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="class_id" class="mb-1 block text-sm font-medium text-body">Class</label>
            <select name="class_id" id="class_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy w-48" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="section_id" class="mb-1 block text-sm font-medium text-body">Section</label>
            <select name="section_id" id="section_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy w-48" onchange="this.form.submit()">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" data-class-id="{{ $section->school_class_id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                <i data-lucide="filter" class="h-4 w-4"></i> Filter
            </button>
            <a href="{{ route('transport.assign-students.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-gray-50">
                Clear
            </a>
        </div>
    </form>
</div>

<div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
    {{-- ══ Bulk Delete Toolbar ══ --}}
    <div id="bulkToolbar" class="hidden mb-3 mx-5 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('transport.assign-students.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear Transport Assignment
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear Selection</button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-3 text-center w-10">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Admission No</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Enrollment Number</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Student Name</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Class Section</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Current Route</th>
                    <th class="px-4 py-3 text-right font-semibold text-white uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-center w-10">
                        <input type="checkbox" value="{{ $student->id }}" onchange="updateBulkToolbar()"
                            class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                    </td>
                    <td class="px-4 py-3 font-bold text-heading">{{ $student->admission_no }}</td>
                    <td class="px-4 py-3 font-bold text-heading">{{ $student->roll_number ?? '—' }}</td>
                    <td class="px-4 py-3 font-bold text-heading">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </td>
                    <td class="px-4 py-3 font-bold text-heading">
                        {{ $student->schoolClass->name ?? 'N/A' }} 
                        @if($student->section)
                            ({{ $student->section->name }})
                        @endif
                    </td>
                    <td class="px-4 py-3 font-bold text-heading">
                        @if($student->transportRoutes->count() > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                {{ $student->transportRoutes->first()->route_name }}
                            </span>
                        @else
                            <span class="text-gray-400 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="openAssignModal({{ $student->id }}, {{ $student->transportRoutes->first() ? $student->transportRoutes->first()->id : 'null' }})" class="inline-flex items-center gap-1 rounded-md bg-navy px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-dark">
                            <i data-lucide="bus" class="h-3 w-3"></i> Assign Route
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-body">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="users" class="mb-2 h-8 w-8 text-gray-300"></i>
                            <p>No students found for this session.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="border-t border-gray-200 px-5 py-4">
        {{ $students->links() }}
    </div>
</div>

{{-- Assign Route Modal --}}
<div id="assignModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeAssignModal()">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-visible" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-heading">Assign Transport Route</h3>
            <button type="button" onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="assignForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label for="modal_route_id" class="block text-sm font-medium text-body mb-1">Select Route</label>
                    <select id="modal_route_id" name="route_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                        <option value="">None (Unassign)</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->route_name }} (Fare: {{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ $route->route_fare }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeAssignModal()" class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-200 hover:bg-gray-50 text-body transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-navy hover:opacity-90 transition-opacity">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAssignModal(studentId, currentRouteId) {
        let url = "{{ route('transport.assign-students.assign', 'ID_PLACEHOLDER') }}";
        document.getElementById('assignForm').action = url.replace('ID_PLACEHOLDER', studentId);
        let select = document.getElementById('modal_route_id');
        if (currentRouteId) {
            select.value = currentRouteId;
        } else {
            select.value = "";
        }
        
        document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
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

            classSelect.addEventListener('change', function(e) {
                // Because onchange="this.form.submit()" is on the element, 
                // it might submit before filtering, but since it reloads the page, 
                // the filtering on DOMContentLoaded is enough to show correct options.
            });
            
            filterSections();
        }
    });

    // Bulk Select JS
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
        if (!confirm(checked.length + ' student(s) ko unassign kiya jayega aur inki transport invoice (agar hai) delete ho jayegi. Sure hain?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }
</script>
@endsection
