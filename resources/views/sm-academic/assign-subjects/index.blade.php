@extends('layouts.app')
{{-- View updated to clear cache --}}

@section('header', 'Assign Subject')

@section('content')

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Subject Assignments</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('academic.assign-subjects.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="{{ route('academic.assign-subjects.import-demo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
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

{{-- Page Header --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center text-white font-bold text-base">
        <i data-lucide="users" class="w-4 h-4 mr-2"></i>
        Assign Subject
        <span class="ml-2 text-white/70 text-xs font-normal">Session: {{ $session->name }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('academic.assign-subjects.export') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white border border-white/20 hover:bg-white/10 flex items-center gap-2 transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i> Export
        </a>
        <button onclick="openImportModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white border border-white/20 hover:bg-white/10 flex items-center gap-2 transition-colors">
            <i data-lucide="upload" class="w-4 h-4"></i> Import
        </button>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
</div>
@endif

{{-- Search & Class Filter Bar --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('academic.assign-subjects.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
            {{-- Class Dropdown --}}
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-body mb-1">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Keyword Search --}}
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-body mb-1">Search Subject / Code:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject name or code..."
                           class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('academic.assign-subjects.index', array_filter(['class_id' => request('class_id')])) }}"
                           class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-end mt-auto gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition bg-navy">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request('class_id') || request('search'))
                    <a href="{{ route('academic.assign-subjects.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium text-body hover:bg-gray-50 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Empty State --}}
@if($subjects->isEmpty())
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="book-open" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No subjects found.</p>
        <p class="text-sm text-body mt-1">Try adjusting your filters or add subjects first.</p>
    </div>
@else
    {{-- ══ Bulk Delete Toolbar ══ --}}
    <div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('academic.assign-subjects.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear Assigned Teachers
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between" style="background:#F5F1EB;">
            <span class="text-xs font-semibold text-body">Total Subjects: <strong>{{ $subjects->count() }}</strong></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy">
                        <th class="px-4 py-2 w-10 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                        </th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Subject Name</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Subject Code</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Subject Type</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Class</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Teachers</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subjects as $subject)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 text-center w-10">
                                <input type="checkbox" value="{{ $subject->id }}" onchange="updateBulkToolbar()"
                                    class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-3 font-bold text-heading flex items-center gap-2">
                                <i data-lucide="plus-circle" class="w-4 h-4 text-navy"></i>
                                {{ $subject->name }}
                            </td>
                            <td class="px-4 py-3">
                                @if($subject->code)
                                    <span class="text-sm text-body font-bold">{{ $subject->code }}</span>
                                @else
                                    <span class="text-body text-xs font-bold">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-body font-bold">
                                {{ $subject->type }}
                            </td>
                            <td class="px-4 py-3 text-sm text-body font-bold">
                                {{ $subject->schoolClass?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($subject->teachers->count() > 0)
                                    <a href="{{ route('academic.assign-subjects.teachers', $subject) }}" class="text-navy font-bold hover:underline">
                                        {{ $subject->teachers->count() }}
                                    </a>
                                @else
                                    <a href="{{ route('academic.assign-subjects.teachers', $subject) }}" class="text-navy text-sm font-bold hover:underline">
                                        Assign Teachers
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<script>
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }
    document.getElementById('importModal')?.addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });

    // ══ Bulk Select JS ══
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
        if (!confirm('Are you sure you want to clear assigned teachers from the selected subjects?')) return false;
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
