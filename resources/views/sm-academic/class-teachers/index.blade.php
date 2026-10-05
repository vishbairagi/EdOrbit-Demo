@extends('layouts.app')
@section('header', 'Class Teachers')
@section('content')
{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Class Teachers</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('academic.class-teachers.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="{{ route('academic.class-teachers.import-demo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
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

<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white flex items-center gap-2 bg-navy">
            <i data-lucide="users" class="w-5 h-5"></i>
            Class Teachers
        </div>
        <div class="flex gap-2">
            <a href="{{ route('academic.class-teachers.export') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white border border-white/20 hover:bg-white/10 flex items-center gap-2 bg-navy">
                <i data-lucide="download" class="w-4 h-4"></i> Export
            </a>
            <button onclick="openImportModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white border border-white/20 hover:bg-white/10 flex items-center gap-2 bg-navy">
                <i data-lucide="upload" class="w-4 h-4"></i> Import
            </button>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-navy">
                + Assign Class Teacher
            </button>
        </div>
    </div>

    {{-- Search & Class Filter Bar --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5" style="background:#fff;">
        <form method="GET" action="{{ route('academic.class-teachers.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
                {{-- Class Dropdown --}}
                <div class="w-full md:w-48">
                    <label class="block text-xs font-medium mb-1" style="color:#211B17;">Filter by Class:</label>
                    <select name="class_id" id="filter_class_id" onchange="updateFilterSections(); this.form.submit()" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white" style="color:#211B17;">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section Dropdown --}}
                <div class="w-full md:w-48">
                    <label class="block text-xs font-medium mb-1" style="color:#211B17;">Filter by Section:</label>
                    <select name="section_id" id="filter_section_id" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white" style="color:#211B17;">
                        <option value="">All Sections</option>
                    </select>
                </div>

                {{-- Keyword Search --}}
                <div class="w-full md:w-64">
                    <label class="block text-xs font-medium mb-1" style="color:#211B17;">Search Staff Name:</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search staff..." class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 pr-8 focus:outline-none" style="color:#211B17;">
                        @if(request('search'))
                            <a href="{{ route('academic.class-teachers.index', array_filter(['class_id' => request('class_id'), 'section_id' => request('section_id')])) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex items-end mt-auto gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition bg-navy">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        Filter
                    </button>
                    @if(request('class_id') || request('section_id') || request('search'))
                        <a href="{{ route('academic.class-teachers.index') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium hover:bg-gray-50 transition" style="color:#211B17;">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('academic.class-teachers.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Staff</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Class / Section</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classTeachers as $ct)
                    <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" value="{{ $ct->id }}" class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" onchange="updateBulkToolbar()">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-sm" style="color:#211B17;">{{ $ct->staff->name }}</div>
                            <div class="font-bold text-xs" style="color:#9e8f84;">{{ $ct->staff->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold" style="background:#f5ece0; color:#211B17;">
                                {{ $ct->schoolClass->name }}{{ $ct->section ? ' / '.$ct->section->name : '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" onclick="openEditModal({{ $ct->id }}, {{ $ct->staff_id }}, {{ $ct->class_id }}, {{ $ct->section_id ?? 'null' }})" class="p-1.5 mr-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Edit">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.class-teachers.destroy', $ct) }}" class="inline" onsubmit="return confirm('Remove assignment?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Remove">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="text-center py-12 text-sm" style="color:#9e8f84;">No class teachers assigned for this session</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Assign Modal --}}
<div id="addModal" class="fixed inset-0 z-50 {{ $errors->any() ? '' : 'hidden' }} flex items-center justify-center" style="background:rgba(0,0,0,.5);">
    <div class="rounded-xl p-6 w-full max-w-md mx-4" style="background:#fff;">
        <h3 class="font-bold text-lg mb-4" style="color:#211B17;">Assign Class Teacher</h3>
        <form method="POST" action="{{ route('academic.class-teachers.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1" style="color:#211B17;">Select Staff</label>
                <select name="staff_id" required class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none">
                    <option value="">Select...</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="assignmentClassId" class="block text-sm font-semibold mb-1" style="color:#211B17;">Select Class</label>
                <select id="assignmentClassId" name="class_id" required class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
                @error('class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="mb-5">
                <label for="assignmentSectionId" class="block text-sm font-semibold mb-1" style="color:#211B17;">Select Section</label>
                <select id="assignmentSectionId" name="section_id" data-selected-section="{{ old('section_id') }}" required disabled class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none">
                    <option value="">Select class first...</option>
                </select>
                <select id="availableSections" class="hidden" aria-hidden="true" tabindex="-1">
                    @foreach($classes as $class)
                        @foreach($class->sections as $section)
                            <option value="{{ $section->id }}" data-class-id="{{ $class->id }}">{{ $section->name }}</option>
                        @endforeach
                    @endforeach
                </select>
                @error('section_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border" style="background:#f5ece0; color:#211B17; border-color:#DED5CB;">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white bg-navy">Assign</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,.5);">
    <div class="rounded-xl p-6 w-full max-w-md mx-4" style="background:#fff;">
        <h3 class="font-bold text-lg mb-4" style="color:#211B17;">Edit Class Teacher</h3>
        <form method="POST" id="editForm" action="">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1" style="color:#211B17;">Select Staff</label>
                <select name="staff_id" id="edit_staff_id" required class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none">
                    <option value="">Select...</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_class_id" class="block text-sm font-semibold mb-1" style="color:#211B17;">Select Class</label>
                <select id="edit_class_id" name="class_id" required class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-5">
                <label for="edit_section_id" class="block text-sm font-semibold mb-1" style="color:#211B17;">Select Section</label>
                <select id="edit_section_id" name="section_id" required class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none">
                    <option value="">Select class first...</option>
                </select>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg text-sm border" style="background:#f5ece0; color:#211B17; border-color:#DED5CB;">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white bg-navy">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        lucide.createIcons();
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }
    document.getElementById('importModal')?.addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });

    const assignmentClassSelect = document.getElementById('assignmentClassId');
    const assignmentSectionSelect = document.getElementById('assignmentSectionId');
    const availableSections = document.getElementById('availableSections');
    let selectedSectionId = assignmentSectionSelect.dataset.selectedSection || '';

    function updateAssignmentSections() {
        assignmentSectionSelect.replaceChildren(new Option('Select section...', ''));
        let hasSections = false;

        for (const sectionOption of availableSections.options) {
            if (sectionOption.dataset.classId === assignmentClassSelect.value) {
                assignmentSectionSelect.add(new Option(sectionOption.textContent, sectionOption.value));
                hasSections = true;
            }
        }

        assignmentSectionSelect.disabled = assignmentClassSelect.value === '';

        if (assignmentClassSelect.value !== '' && !hasSections) {
            assignmentSectionSelect.add(new Option('No sections available for this class', ''));
        }

        assignmentSectionSelect.value = selectedSectionId;
        selectedSectionId = '';
    }

    assignmentClassSelect.addEventListener('change', updateAssignmentSections);
    updateAssignmentSections();

    // Filter logic
    const filterClassSelect = document.getElementById('filter_class_id');
    const filterSectionSelect = document.getElementById('filter_section_id');
    const selectedFilterSectionId = '{{ request('section_id') }}';

    function updateFilterSections() {
        filterSectionSelect.replaceChildren(new Option('All Sections', ''));
        
        if (filterClassSelect.value === '') {
            return;
        }

        for (const sectionOption of availableSections.options) {
            if (sectionOption.dataset.classId === filterClassSelect.value) {
                let opt = new Option(sectionOption.textContent, sectionOption.value);
                if (sectionOption.value === selectedFilterSectionId) {
                    opt.selected = true;
                }
                filterSectionSelect.add(opt);
            }
        }
    }
    
    updateFilterSections();

    // Edit logic
    const editClassSelect = document.getElementById('edit_class_id');
    const editSectionSelect = document.getElementById('edit_section_id');

    function updateEditSections(selectedSectionId = '') {
        editSectionSelect.replaceChildren(new Option('Select section...', ''));
        let hasSections = false;

        for (const sectionOption of availableSections.options) {
            if (sectionOption.dataset.classId === editClassSelect.value) {
                editSectionSelect.add(new Option(sectionOption.textContent, sectionOption.value));
                hasSections = true;
            }
        }

        editSectionSelect.disabled = editClassSelect.value === '';

        if (editClassSelect.value !== '' && !hasSections) {
            editSectionSelect.add(new Option('No sections available for this class', ''));
        }

        if(selectedSectionId) {
            editSectionSelect.value = selectedSectionId;
        }
    }

    editClassSelect.addEventListener('change', () => updateEditSections(''));

    function openEditModal(id, staff_id, class_id, section_id) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editForm').action = "/academic/class-teachers/" + id;
        document.getElementById('edit_staff_id').value = staff_id;
        document.getElementById('edit_class_id').value = class_id;
        updateEditSections(section_id);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>

<style>
.hover-row:hover { background:#FEFAF6; }
</style>

    {{-- ══ Bulk Select JS (auto-injected) ══ --}}
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
        window.toggleSelectAll   = toggleSelectAll;
        window.updateBulkToolbar = updateBulkToolbar;
        window.clearSelection    = clearSelection;
        window.confirmBulkDelete = confirmBulkDelete;
    })();
    </script>
@endsection
