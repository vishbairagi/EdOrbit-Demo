@extends('layouts.app')

@section('header', 'Students')

@section('content')

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Students</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('sm-student.students.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="{{ route('sm-student.students.import-demo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i> Sample CSV
                </a>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload CSV File <span class="text-red-500">*</span></label>
                <input type="file" name="csv_file" accept=".csv" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Import Data</button>
            </div>
        </form>
    </div>
</div>

{{-- Header bar --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center">
        <i data-lucide="users" class="w-5 h-5 text-white mr-2.5"></i>
        <span class="text-white font-bold text-base tracking-wide">Students</span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('sm-student.students.export') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-white/20 text-white text-xs font-semibold rounded-md hover:bg-white/10 transition">
            <i data-lucide="download" class="w-3.5 h-3.5"></i> Export
        </a>
        <button onclick="openImportModal()"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-white/20 text-white text-xs font-semibold rounded-md hover:bg-white/10 transition">
            <i data-lucide="upload" class="w-3.5 h-3.5"></i> Import
        </button>
        <a href="{{ route('sm-student.students.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-xs font-semibold rounded-md hover:bg-gray-100 transition"
           style="color:#211B17;">
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Add Admission
        </a>
    </div>
</div>

{{-- Search & Filter --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 mb-5">
    <form method="GET" action="{{ route('sm-student.students.index') }}" id="searchForm">
        <p class="text-sm font-semibold text-heading mb-3">Search Students</p>
        <div class="flex gap-4 mb-4">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="radio" name="search_type" value="keyword"
                       {{ request('search_type', 'keyword') === 'keyword' ? 'checked' : '' }}
                       class="accent-navy" onchange="toggleSearch(this.value)">
                Search By Keyword
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="radio" name="search_type" value="class"
                       {{ request('search_type') === 'class' ? 'checked' : '' }}
                       class="accent-navy" onchange="toggleSearch(this.value)">
                Search By Class
            </label>
        </div>
        <div id="keywordSearch" class="{{ request('search_type', 'keyword') === 'keyword' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs text-body block mb-1">Search Field:</label>
                    <select name="search_field" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="first_name" {{ request('search_field') === 'first_name' ? 'selected' : '' }}>First Name</option>
                        <option value="last_name"  {{ request('search_field') === 'last_name'  ? 'selected' : '' }}>Last Name</option>
                        <option value="admission_no" {{ request('search_field') === 'admission_no' ? 'selected' : '' }}>Admission No</option>
                        <option value="phone"      {{ request('search_field') === 'phone'      ? 'selected' : '' }}>Phone</option>
                        <option value="email"      {{ request('search_field') === 'email'      ? 'selected' : '' }}>Email</option>
                        <option value="father_name" {{ request('search_field') === 'father_name' ? 'selected' : '' }}>Father Name</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-body block mb-1">Keyword:</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                           placeholder="Enter Search Keyword"
                           class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-sm font-medium hover:opacity-90 transition"
                            style="background:#B47A42;">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> Get Students
                    </button>
                </div>
            </div>
        </div>
        <div id="classSearch" class="{{ request('search_type') === 'class' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs text-body block mb-1">Class:</label>
                    <select name="class_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-sm font-medium hover:opacity-90 transition"
                            style="background:#B47A42;">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> Get Students
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Students Table --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <p class="text-sm text-body">Showing <strong>{{ $students->count() }}</strong> of <strong>{{ $students->total() }}</strong> students</p>
        <div class="flex gap-2">
            <a href="{{ route('sm-student.students.create') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-xs font-medium hover:opacity-90 transition"
               style="background:#B47A42;">
                <i data-lucide="plus" class="w-3 h-3"></i> Add New
            </a>
        </div>
    </div>

    {{-- Bulk Delete Toolbar --}}
    <div id="bulkToolbar" class="hidden mx-5 mt-3 mb-1 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5">
        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 flex-shrink-0"></i>
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('sm-student.students.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline ml-2">Clear Selection</button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100" style="background:#F5F1EB;">
                    <th class="px-4 py-3 text-center text-xs font-semibold text-body w-10">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                               class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                               title="Select / Deselect All">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Student Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Class</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Section</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Enrollment Number</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Admission No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors row-item">
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" value="{{ $student->id }}" onchange="updateBulkToolbar()"
                                   class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('sm-student.students.edit', $student) }}" class="font-bold text-black hover:underline">
                                {{ $student->full_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-heading text-xs font-bold">{{ $student->schoolClass?->name ?? '---' }}</td>
                        <td class="px-4 py-3 text-heading text-xs font-bold">{{ $student->section?->name ?? '---' }}</td>
                        <td class="px-4 py-3 text-heading text-xs font-mono font-bold">{{ $student->roll_number ?? '---' }}</td>
                        <td class="px-4 py-3 text-heading text-xs font-mono font-bold">{{ $student->admission_no }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'Active'      => 'bg-green-50 text-green-700 border-green-200',
                                    'Inactive'    => 'bg-gray-50 text-gray-500 border-gray-200',
                                    'Promoted'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Transferred' => 'bg-amber-50 text-amber-700 border-amber-200',
                                ];
                            @endphp
                            <form action="{{ route('sm-student.students.update-status', $student) }}" method="POST" class="inline-block">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="text-xs font-bold rounded-full px-2 py-0.5 border cursor-pointer focus:outline-none {{ $statusColors[$student->status] ?? 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                    @foreach(['Active', 'Inactive', 'Promoted', 'Transferred'] as $st)
                                        <option value="{{ $st }}" {{ $student->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('sm-student.print-id-cards.print', $student) }}"
                                   onclick="return printCards(this.href)"
                                   title="Print ID Card"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('sm-student.students.edit', $student) }}"
                                   title="Edit Student"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </a>
                                <form method="POST" action="{{ route('sm-student.students.destroy', $student) }}"
                                      onsubmit="return confirm('Delete this student permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete Student"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-14 text-center text-body text-sm font-bold">
                            <div class="flex flex-col items-center gap-3">
                                <i data-lucide="users" class="w-12 h-12 text-gray-200"></i>
                                <p class="text-gray-500">No students found.</p>
                                <a href="{{ route('sm-student.students.create') }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-bold"
                                   style="background:#B47A42;">
                                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Add Admission
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $students->links() }}
        </div>
    @endif
</div>

<script>
function toggleSearch(type) {
    document.getElementById('keywordSearch').classList.toggle('hidden', type !== 'keyword');
    document.getElementById('classSearch').classList.toggle('hidden', type !== 'class');
}
function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}
document.getElementById('importModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeImportModal();
});
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
        toolbar.classList.remove('hidden');
        countEl.textContent = checked.length + ' student(s) selected';
    } else {
        toolbar.classList.add('hidden');
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
    document.getElementById('bulkToolbar').classList.add('hidden');
}
function confirmBulkDelete() {
    var checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) return false;
    if (!confirm(checked.length + ' student(s) permanently delete honge. Sure hain?')) return false;
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
@include('student.print-id-cards._print-script')

@endsection