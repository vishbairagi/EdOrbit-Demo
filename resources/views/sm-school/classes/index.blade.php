@extends('layouts.app')

@section('header', 'Classes — ' . $school->name . ' (' . $session->name . ')')

@section('content')

{{-- Add Class Modal --}}
<div id="classModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 id="classModalTitle" class="text-base font-bold text-heading">Add Class</h3>
            <button onclick="closeClassModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="classForm" method="POST" action="{{ route('sm-school.classes.store') }}" class="p-6 space-y-4">
            @csrf
            <span id="classMethodField"></span>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="className" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="e.g. Class 1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class Group</label>
                <select name="class_group_id" id="classGroup" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                    <option value="">— No Group —</option>
                    @foreach($classGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                <input type="number" name="order" id="classOrder" value="0" min="0" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeClassModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Section Modal --}}
<div id="sectionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Add Section</h3>
            <button onclick="closeSectionModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="sectionForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Section Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="e.g. A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                <input type="number" name="capacity" min="1" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="Optional">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeSectionModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Add Section</button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Classes</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('sm-school.classes.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="{{ route('sm-school.classes.import-demo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
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

<div class="mb-3 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-bold text-heading">Classes</h3>
        <p class="text-xs text-body mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; {{ $school->name }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('sm-school.classes.export') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1.5"></i> Export
        </a>
        <button onclick="openImportModal()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1.5"></i> Import
        </button>
        <button onclick="openClassModal()" class="inline-flex items-center px-3 py-1.5 bg-navy hover:bg-primary-dark text-white text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5"></i> Add Class
        </button>
    </div>
</div>

{{-- Bulk Delete Toolbar --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('sm-school.classes.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    {{-- Search & Filter Toolbar --}}
    <div class="px-4 py-3 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-3 bg-white">
        <form method="GET" action="{{ route('sm-school.classes.index') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1 sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" id="classSearchInput" name="search" value="{{ request('search') }}"
                       autocomplete="off"
                       class="block w-full pl-9 pr-8 py-1.5 border border-gray-300 rounded-lg text-sm bg-white text-heading placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy"
                       placeholder="Search by class, group, section...">
                @if(request('search'))
                    <a href="{{ route('sm-school.classes.index', request()->except('search', 'page')) }}"
                       class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-red-500" title="Clear search">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

            <select name="class_group_id" onchange="this.form.submit()"
                    class="py-1.5 px-3 border border-gray-300 bg-white rounded-lg text-sm text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy cursor-pointer">
                <option value="">All Class Groups</option>
                @foreach($classGroups as $group)
                    <option value="{{ $group->id }}" {{ request('class_group_id') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>

            <select name="per_page" onchange="this.form.submit()"
                    class="py-1.5 px-3 border border-gray-300 bg-white rounded-lg text-sm text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy cursor-pointer"
                    title="Entries per page">
                <option value="2" {{ request('per_page') == 2 ? 'selected' : '' }}>2 / page</option>
                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / page</option>
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
            </select>

            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-navy hover:bg-primary-dark text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Search
            </button>

            @if(request()->filled('search') || request()->filled('class_group_id') || (request()->filled('per_page') && request('per_page') != 10))
                <a href="{{ route('sm-school.classes.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-300 text-xs font-semibold rounded-lg text-red-600 bg-white hover:bg-red-50 transition-colors">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-body whitespace-nowrap self-end md:self-center">
            Total: <span class="font-bold text-heading">{{ $classes->total() }}</span> {{ Str::plural('class', $classes->total()) }}
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 text-red-600 cursor-pointer accent-red-600"
                            title="Select All">
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Class Name</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Class Group</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Sections</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($classes as $class)
                    <tr class="hover:bg-gray-50 transition-colors row-item class-row"
                        data-id="{{ $class->id }}"
                        data-name="{{ strtolower($class->name) }}"
                        data-group="{{ strtolower($class->classGroup->name ?? '') }}"
                        data-sections="{{ strtolower($class->sections->pluck('name')->implode(' ')) }}">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="{{ $class->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 text-red-600 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-heading">{{ $class->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @if($class->classGroup)
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-primary/10 text-navy border border-primary/20">{{ $class->classGroup->name }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex flex-wrap gap-1.5 items-center">
                                @foreach($class->sections as $section)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-blue-50 text-blue-700 rounded border border-blue-200">
                                        {{ $section->name }}
                                        <form action="{{ route('sm-school.sections.destroy', $section) }}" method="POST" class="inline" onsubmit="return confirm('Delete this section?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-blue-400 hover:text-red-500 transition-colors ml-0.5">
                                                <i data-lucide="x" class="w-2.5 h-2.5"></i>
                                            </button>
                                        </form>
                                    </span>
                                @endforeach
                                <button onclick="openSectionModal({{ $class->id }})" class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-navy border border-dashed border-navy rounded hover:bg-navy hover:text-white transition-colors">
                                    <i data-lucide="plus" class="w-3 h-3 mr-0.5"></i> Add Section
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button onclick="editClass({{ $class->id }}, '{{ addslashes($class->name) }}', {{ $class->class_group_id ?? 'null' }})" class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('sm-school.classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Delete this class?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                            @if(request()->filled('search') || request()->filled('class_group_id'))
                                <p class="font-medium text-heading">No classes found matching your search.</p>
                                <p class="text-sm text-body mt-1">Try adjusting your keywords or class group filter.</p>
                                <a href="{{ route('sm-school.classes.index') }}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-lg text-red-600 bg-white hover:bg-red-50">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Clear Search Filters
                                </a>
                            @else
                                <p class="font-medium text-heading">No classes yet for this session.</p>
                                <p class="text-sm text-body mt-1">Classes are isolated per school and session.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
                <tr id="clientEmptyRow" style="display:none;">
                    <td colspan="5" class="px-6 py-8 text-center text-sm text-body">
                        No matching classes on this page. Press <strong>Search</strong> or hit Enter to search all pages.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($classes->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            {{ $classes->links() }}
        </div>
    @else
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
            <span>Showing <strong class="text-gray-700 font-semibold">{{ $classes->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $classes->total() }}</strong> {{ Str::plural('class', $classes->total()) }}</span>
            <span class="text-gray-400">Page 1 of 1</span>
        </div>
    @endif
</div>

<script>
    // ── Bulk Select Logic ──────────────────────────────────────────
    function toggleSelectAll(masterCb) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = masterCb.checked;
            cb.closest('tr').classList.toggle('bg-red-50', masterCb.checked);
        });
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const toolbar  = document.getElementById('bulkToolbar');
        const countEl  = document.getElementById('selectedCount');
        const selectAll = document.getElementById('selectAll');
        const all      = document.querySelectorAll('.row-checkbox');

        // Highlight selected rows
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.closest('tr').classList.toggle('bg-red-50', cb.checked);
        });

        if (checked.length > 0) {
            toolbar.classList.remove('hidden');
            countEl.textContent = checked.length + ' selected';
        } else {
            toolbar.classList.add('hidden');
        }

        // Update master checkbox state
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
        selectAll.checked = checked.length === all.length && all.length > 0;
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('tr').classList.remove('bg-red-50');
        });
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAll').indeterminate = false;
        document.getElementById('bulkToolbar').classList.add('hidden');
    }

    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;

        if (!confirm(checked.length + ' class(es) delete ho jaayenge. Kya aap sure hain?')) return false;

        // Populate hidden IDs into form
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        return true;
    }
    // ──────────────────────────────────────────────────────────────

    function openClassModal(title = 'Add Class', action = '{{ route('sm-school.classes.store') }}', method = 'POST', name = '', groupId = null) {
        document.getElementById('classModalTitle').textContent = title;
        document.getElementById('classForm').action = action;
        document.getElementById('classMethodField').innerHTML = method !== 'POST' ? '<input type="hidden" name="_method" value="' + method + '">' : '';
        document.getElementById('className').value = name;
        if (groupId) document.getElementById('classGroup').value = groupId;
        document.getElementById('classModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function editClass(id, name, groupId) {
        openClassModal('Edit Class', '/sm-school/classes/' + id, 'PUT', name, groupId);
    }

    function closeClassModal() {
        document.getElementById('classModal').classList.add('hidden');
    }

    function openSectionModal(classId) {
        document.getElementById('sectionForm').action = '/sm-school/classes/' + classId + '/sections';
        document.getElementById('sectionModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.add('hidden');
    }

    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    document.getElementById('classModal').addEventListener('click', function(e) { if (e.target === this) closeClassModal(); });
    document.getElementById('sectionModal').addEventListener('click', function(e) { if (e.target === this) closeSectionModal(); });
    document.getElementById('importModal').addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });

    // Real-time client-side search across class name, group, and sections
    const classSearchInput = document.getElementById('classSearchInput');
    if (classSearchInput) {
        classSearchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.class-row');
            let visibleCount = 0;
            rows.forEach(function(row) {
                const name = row.getAttribute('data-name') || '';
                const group = row.getAttribute('data-group') || '';
                const sections = row.getAttribute('data-sections') || '';
                const match = !query || name.includes(query) || group.includes(query) || sections.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            const clientEmpty = document.getElementById('clientEmptyRow');
            if (clientEmpty) {
                clientEmpty.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }
        });
    }
</script>
@endsection
