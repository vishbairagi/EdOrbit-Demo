@extends('layouts.app')

@section('header', 'Classes & Sections')

@section('content')

{{-- Top Header Section (Matching /academic/class-teachers) --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Classes and Sections</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Classes: {{ $classes->total() }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('academic.class-sections.export') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i> Export CSV
        </a>
        <button onclick="document.getElementById('importSectionModal').classList.remove('hidden')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i> Import CSV
        </button>
    </div>
</div>

{{-- Flash Message --}}
@if(session('success'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
    {{ session('success') }}
</div>
@endif

{{-- Main Unified Table Card --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    {{-- Search & Filter Toolbar --}}
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50">
        <form method="GET" action="{{ route('academic.class-sections.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search class, section or group..."
                       class="w-full pl-8 pr-8 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                @if(request('search'))
                    <a href="{{ route('academic.class-sections.index', request()->except('search', 'page')) }}"
                       class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

            <select name="per_page" onchange="this.form.submit()" class="rounded-lg border border-gray-300 text-xs px-2.5 py-2 focus:outline-none bg-white text-heading">
                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / page</option>
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / page</option>
            </select>

            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white text-xs font-semibold bg-navy hover:bg-primary-dark transition shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Filter
            </button>

            @if(request('search') || (request('per_page') && request('per_page') != 10))
                <a href="{{ route('academic.class-sections.index') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 text-xs font-medium hover:bg-gray-100 text-red-600 transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Bulk Delete Toolbar --}}
    <div id="bulkToolbar" class="hidden flex items-center gap-3 bg-red-50 border-b border-red-200 px-4 py-2.5">
        <span id="selectedCount" class="text-xs font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('academic.class-sections.bulk-destroy') }}" onsubmit="return confirmBulkDelete(event)">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-2xs">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead>
                <tr class="bg-navy">
                    <th class="px-3 py-2.5 w-10 text-center">
                        <input type="checkbox" id="selectAllClasses" class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" onclick="toggleAllClasses(this)" title="Select All">
                    </th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Class</th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Class Group</th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Number of Sections</th>
                    <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Total Students</th>
                    <th class="px-4 py-2.5 text-right text-white font-bold text-xs uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($classes as $class)
                    <tr class="hover:bg-gray-50/80 transition-colors" id="class-row-{{ $class->id }}">
                        <td class="px-3 py-2.5 text-center">
                            <input type="checkbox" value="{{ $class->id }}" class="class-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" onchange="updateBulkDeleteButton()">
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="font-bold text-xs text-gray-950">{{ $class->name }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-xs font-semibold text-gray-800">
                            {{ $class->classGroup->name ?? '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-xs font-bold text-gray-900">
                            {{ $class->sections->count() }}
                        </td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="font-bold text-gray-950">{{ $class->total_students }}</span>
                            <span class="text-2xs text-gray-700 font-semibold ml-1">(M: {{ $class->male_students }} · F: {{ $class->female_students }})</span>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick="openAddSection({{ $class->id }}, '{{ $class->name }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                        title="Add New / Manage Sections">
                                    <i data-lucide="plus" class="w-3.5 h-3.5 text-gray-600"></i>
                                    <span>Add New / Manage</span>
                                </button>
                                <button type="button" onclick="toggleSections({{ $class->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                        title="View Sections">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-600"></i>
                                    <span>View</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{-- Expandable Sections Row --}}
                    <tr id="sections-{{ $class->id }}" class="hidden">
                        <td colspan="6" class="px-6 py-3 bg-gray-50/70 border-t border-b border-gray-100">
                            @if($class->sections->isEmpty())
                                <p class="text-xs text-gray-500 font-medium italic">No sections yet. Click "Add New / Manage" to add sections.</p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach($class->sections as $section)
                                        <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-md px-3 py-1.5 text-xs shadow-2xs">
                                            <input type="checkbox" value="{{ $section->id }}" class="section-checkbox w-3.5 h-3.5 rounded border-gray-300 cursor-pointer accent-red-600" onchange="updateBulkDeleteButton()">
                                            <span class="font-semibold text-gray-900">{{ $section->name }}</span>
                                            @if($section->capacity)
                                                <span class="text-gray-600 text-2xs font-semibold">(cap: {{ $section->capacity }})</span>
                                            @endif
                                            <form method="POST" action="{{ route('academic.class-sections.sections.destroy', $section) }}"
                                                  onsubmit="return confirm('Delete section {{ $section->name }}?')"
                                                  class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 ml-1 transition-colors" title="Delete section">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-xs text-gray-500">
                            <i data-lucide="layers" class="w-8 h-8 text-gray-300 mx-auto mb-1.5"></i>
                            No classes found. Create classes first from <a href="{{ route('sm-school.classes.index') }}" class="text-navy underline">SM School → Classes</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Table Pagination Footer --}}
    @if($classes->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            {{ $classes->links() }}
        </div>
    @else
        <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
            <span>Showing <strong class="text-gray-700 font-semibold">{{ $classes->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $classes->total() }}</strong> classes</span>
            <span class="text-gray-400">Page 1 of 1</span>
        </div>
    @endif
</div>

{{-- Add Section Modal --}}
<div id="addSectionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading">Add Section to <span id="modalClassName" class="text-navy"></span></h3>
            <button onclick="closeAddSection()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="addSectionForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Section Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. A, B, Rose"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Capacity (optional)</label>
                    <input type="number" name="capacity" min="1" placeholder="e.g. 40"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:bg-primary-dark transition-colors">
                    Add Section
                </button>
                <button type="button" onclick="closeAddSection()"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAllClasses(source) {
        document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = source.checked);
        document.querySelectorAll('.section-checkbox').forEach(cb => cb.checked = source.checked);
        updateBulkDeleteButton();
    }

    function updateBulkDeleteButton() {
        const checkedClasses = document.querySelectorAll('.class-checkbox:checked').length;
        const checkedSections = document.querySelectorAll('.section-checkbox:checked').length;
        const total = checkedClasses + checkedSections;
        const toolbar = document.getElementById('bulkToolbar');
        const countEl = document.getElementById('selectedCount');
        
        if (total > 0) {
            toolbar.classList.remove('hidden');
            if(countEl) countEl.textContent = total + ' selected';
        } else {
            toolbar.classList.add('hidden');
        }
    }
    
    function clearSelection() {
        document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.section-checkbox').forEach(cb => cb.checked = false);
        const sa = document.getElementById('selectAllClasses');
        if (sa) sa.checked = false;
        updateBulkDeleteButton();
    }

    function confirmBulkDelete(e) {
        if (!confirm('Are you sure you want to delete the selected items?')) {
            e.preventDefault();
            return false;
        }
        
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        
        document.querySelectorAll('.class-checkbox:checked').forEach(cb => {
            container.insertAdjacentHTML('beforeend', `<input type="hidden" name="class_ids[]" value="${cb.value}">`);
        });
        
        document.querySelectorAll('.section-checkbox:checked').forEach(cb => {
            container.insertAdjacentHTML('beforeend', `<input type="hidden" name="section_ids[]" value="${cb.value}">`);
        });
        
        return true;
    }

    function toggleSections(classId) {
        const row = document.getElementById('sections-' + classId);
        const icon = document.getElementById('toggle-icon-' + classId);
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    }

    function openAddSection(classId, className) {
        document.getElementById('modalClassName').textContent = className;
        document.getElementById('addSectionForm').action = '/academic/class-sections/' + classId + '/sections';
        document.getElementById('addSectionModal').classList.remove('hidden');
    }

    function closeAddSection() {
        document.getElementById('addSectionModal').classList.add('hidden');
    }

    function handleSectionDrop(e) {
        e.preventDefault();
        document.getElementById('sectionDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('sectionCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('sectionFileName').textContent = file.name;
        }
    }
    
    document.getElementById('importSectionModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>

{{-- Import Modal --}}
<div id="importSectionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background:#fff;">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Class Sections from CSV
            </div>
            <button onclick="document.getElementById('importSectionModal').classList.add('hidden')"
                    class="text-white opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            {{-- Sample download --}}
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-sm font-semibold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-xs mt-0.5" style="color:#a07040;">Use this template to prepare your CSV</p>
                </div>
                <a href="{{ route('academic.class-sections.import-demo') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Sample CSV
                </a>
            </div>

            {{-- Column legend --}}
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-semibold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <span>1. Class Name *</span>
                    <span>2. Section Name *</span>
                    <span>3. Capacity</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Class must already exist in this session. Existing sections matched by name will be updated.</p>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('academic.class-sections.import') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-sm font-medium mb-2 text-gray-800">Select CSV File</label>
                <div id="sectionDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('sectionCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleSectionDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-sm font-medium" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="sectionFileName" class="text-xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="sectionCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('sectionFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importSectionModal').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border"
                            style="border-color:#ddd; color:#555; background:#fff;">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-white bg-navy">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
