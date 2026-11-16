@extends('layouts.app')

@section('header', 'Manage Medium')

@section('content')

{{-- Top Header Section (Matching /academic/class-teachers and /academic/class-sections) --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Manage Mediums</h3>
        <p class="text-xs text-gray-500 mt-0.5">Total Mediums: {{ $mediums->count() }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('academic.mediums.export') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i> Export CSV
        </a>
        <button type="button" onclick="document.getElementById('importMediumModal').classList.remove('hidden')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i> Import CSV
        </button>
        <button type="button" onclick="document.getElementById('addMediumModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Add Medium
        </button>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#fdeded; color:#5f2120; border:1px solid #f5c2c7;">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
    {{ session('error') }}
</div>
@endif

{{-- Main Table Card --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    {{-- Search Toolbar --}}
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50">
        <form method="GET" action="{{ route('academic.mediums.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search medium by name or description..."
                       class="w-full pl-8 pr-8 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                @if(request('search'))
                    <a href="{{ route('academic.mediums.index') }}"
                       class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white text-xs font-semibold bg-navy hover:bg-primary-dark transition shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Filter
            </button>

            @if(request('search'))
                <a href="{{ route('academic.mediums.index') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 text-xs font-medium hover:bg-gray-100 text-red-600 transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Bulk Delete Toolbar --}}
    <div id="bulkToolbar" class="hidden flex items-center gap-3 bg-red-50 border-b border-red-200 px-4 py-2.5">
        <span id="selectedCount" class="text-xs font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('academic.mediums.bulk-destroy') }}" onsubmit="return confirmBulkDelete(event)">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-2xs">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
            </button>
        </form>
        <button type="button" onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>

    {{-- Mediums Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead>
                <tr class="bg-navy">
                    <th class="px-3 py-2.5 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">#</th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Medium Name</th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Description</th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2.5 text-right text-white font-bold text-xs uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($mediums as $index => $medium)
                    <tr class="hover:bg-gray-50/80 transition-colors" data-id="{{ $medium->id }}">
                        <td class="px-3 py-2.5 text-center w-10">
                            <input type="checkbox" value="{{ $medium->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-2.5 text-xs text-gray-500 font-medium">{{ $index + 1 }}</td>
                        <td class="px-4 py-2.5">
                            <span class="font-bold text-xs text-gray-950">{{ $medium->name }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-gray-700 font-medium">{{ $medium->description ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <form action="{{ route('academic.mediums.update-status', $medium) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <select name="is_active" onchange="this.form.submit()"
                                        class="text-2xs font-semibold rounded-full px-2.5 py-0.5 border cursor-pointer focus:outline-none focus:ring-1 focus:ring-navy {{ $medium->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                    <option value="1" {{ $medium->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ ! $medium->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick="openEditMedium({{ $medium->id }}, '{{ addslashes($medium->name) }}', '{{ addslashes($medium->description ?? '') }}', {{ $medium->is_active ? 1 : 0 }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                        title="Edit">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-600"></i>
                                    <span>Edit</span>
                                </button>
                                <form method="POST" action="{{ route('academic.mediums.destroy', $medium) }}"
                                      onsubmit="return confirm('Delete medium {{ $medium->name }}?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 bg-white border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 transition-colors shadow-2xs"
                                            title="Delete">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i data-lucide="languages" class="w-10 h-10 text-gray-300 mx-auto mb-2.5"></i>
                            <p class="text-sm font-semibold text-gray-700">No mediums found.</p>
                            <p class="text-xs text-gray-500 mt-0.5">Add mediums like Hindi, English, Urdu, etc.</p>
                            <button type="button" onclick="document.getElementById('addMediumModal').classList.remove('hidden')"
                                    class="mt-3.5 inline-flex items-center gap-1 px-3 py-1.5 bg-navy text-white text-xs font-semibold rounded-lg hover:bg-primary-dark transition shadow-2xs">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add First Medium
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Medium Modal --}}
<div id="addMediumModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-heading flex items-center gap-2">
                <i data-lucide="languages" class="w-4 h-4 text-navy"></i>
                Add New Medium
            </h3>
            <button type="button" onclick="document.getElementById('addMediumModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('academic.mediums.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-heading mb-1">Medium Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Hindi, English, Urdu"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                </div>
                <div>
                    <label class="block text-xs font-medium text-heading mb-1">Description (optional)</label>
                    <input type="text" name="description" placeholder="Short description"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="flex-1 bg-navy text-white text-xs font-semibold py-2 rounded-lg hover:bg-primary-dark transition-colors shadow-2xs">
                    Create Medium
                </button>
                <button type="button" onclick="document.getElementById('addMediumModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-xs font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Medium Modal --}}
<div id="editMediumModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-heading flex items-center gap-2">
                <i data-lucide="pencil" class="w-4 h-4 text-navy"></i>
                Edit Medium
            </h3>
            <button type="button" onclick="document.getElementById('editMediumModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form id="editMediumForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-heading mb-1">Medium Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editMediumName" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                </div>
                <div>
                    <label class="block text-xs font-medium text-heading mb-1">Description (optional)</label>
                    <input type="text" id="editMediumDesc" name="description"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="editMediumActive" name="is_active" value="1" class="rounded border-gray-300 text-navy focus:ring-navy">
                    <label for="editMediumActive" class="text-xs font-medium text-heading">Active</label>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="flex-1 bg-navy text-white text-xs font-semibold py-2 rounded-lg hover:bg-primary-dark transition-colors shadow-2xs">
                    Update Medium
                </button>
                <button type="button" onclick="document.getElementById('editMediumModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-xs font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="importMediumModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden bg-white">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Mediums from CSV
            </div>
            <button type="button" onclick="document.getElementById('importMediumModal').classList.add('hidden')"
                    class="text-white opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            {{-- Sample download --}}
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-xs font-bold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-2xs mt-0.5" style="color:#a07040;">Use this template to prepare your CSV</p>
                </div>
                <a href="{{ route('academic.mediums.import-demo') }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy hover:bg-primary-dark transition shadow-2xs">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Sample CSV
                </a>
            </div>

            {{-- Column legend --}}
            <div class="mb-5 p-3 rounded-lg text-2xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-bold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <span>1. Medium Name *</span>
                    <span>2. Description</span>
                    <span>3. Status</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Status: Active or Inactive (default Active). Existing mediums matched by name will be updated.</p>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('academic.mediums.import') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-xs font-semibold mb-2 text-gray-800">Select CSV File</label>
                <div id="mediumDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-7 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('mediumCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleMediumDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-xs font-semibold" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="mediumFileName" class="text-2xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="mediumCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('mediumFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importMediumModal').classList.add('hidden')"
                            class="flex-1 py-2 rounded-lg text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 rounded-lg text-xs font-semibold text-white bg-navy hover:bg-primary-dark transition shadow-2xs">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditMedium(id, name, desc, isActive) {
        document.getElementById('editMediumForm').action = '/academic/mediums/' + id;
        document.getElementById('editMediumName').value = name;
        document.getElementById('editMediumDesc').value = desc;
        document.getElementById('editMediumActive').checked = isActive === 1;
        document.getElementById('editMediumModal').classList.remove('hidden');
    }

    function handleMediumDrop(e) {
        e.preventDefault();
        document.getElementById('mediumDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('mediumCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('mediumFileName').textContent = file.name;
        }
    }

    document.getElementById('importMediumModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // Bulk Select JS
    function toggleSelectAll(cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = cb.checked;
            r.closest('tr').classList.toggle('bg-red-50/70', cb.checked);
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
            r.closest('tr').classList.toggle('bg-red-50/70', r.checked);
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
            r.closest('tr').classList.remove('bg-red-50/70');
        });
        var sa = document.getElementById('selectAll');
        if (sa) { sa.checked = false; sa.indeterminate = false; }
        var toolbar = document.getElementById('bulkToolbar');
        if(toolbar) toolbar.classList.add('hidden');
    }

    function confirmBulkDelete(e) {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            if (e) e.preventDefault();
            return false;
        }
        if (!confirm(checked.length + ' medium(s) permanently delete honge. Sure hain?')) {
            if (e) e.preventDefault();
            return false;
        }
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }
</script>
@endsection
