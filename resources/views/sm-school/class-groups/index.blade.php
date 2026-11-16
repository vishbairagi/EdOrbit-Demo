@extends('layouts.app')

@section('header', 'Class Groups — ' . $school->name . ' (' . $session->name . ')')

@section('content')

{{-- Add / Edit Modal --}}
<div id="groupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 id="modalTitle" class="text-base font-bold text-heading">Add Class Group</h3>
            <button onclick="closeGroupModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="groupForm" method="POST" action="{{ route('sm-school.class-groups.store') }}" class="p-6 space-y-4">
            @csrf
            <span id="methodField"></span>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Group Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="groupName" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="e.g. Primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" name="description" id="groupDesc" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="Optional description">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="groupStatus" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeGroupModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="importGroupModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background:#fff;">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Class Groups from CSV
            </div>
            <button onclick="document.getElementById('importGroupModal').classList.add('hidden')"
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
                <a href="{{ route('sm-school.class-groups.import-demo') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Sample CSV
                </a>
            </div>

            {{-- Column legend --}}
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-semibold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <span>1. Group Name *</span>
                    <span>2. Description</span>
                    <span>3. Status</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Status: Active or Inactive (default Active). Existing groups matched by name will be updated.</p>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('sm-school.class-groups.import') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-sm font-medium mb-2 text-gray-800">Select CSV File</label>
                <div id="groupDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('groupCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleGroupDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-sm font-medium" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="groupFileName" class="text-xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="groupCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('groupFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importGroupModal').classList.add('hidden')"
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

{{-- Flash Message --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
</div>
@endif

{{-- Page Toolbar --}}
<div class="mb-3 flex justify-between items-center flex-wrap gap-2">
    <div>
        <h3 class="text-lg font-bold text-heading">Class Groups</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Groups: {{ $classGroups->total() }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('sm-school.class-groups.export') }}"
           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i>
            Export CSV
        </a>
        <button onclick="document.getElementById('importGroupModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i>
            Import CSV
        </button>
        <button onclick="openGroupModal()" class="inline-flex items-center px-3 py-1.5 bg-navy hover:bg-primary-dark text-white text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5"></i> Add Class Group
        </button>
    </div>
</div>

{{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('sm-school.class-groups.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
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
        <form method="GET" action="{{ route('sm-school.class-groups.index') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1 sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" id="groupSearchInput" name="search" value="{{ request('search') }}"
                       autocomplete="off"
                       class="block w-full pl-9 pr-8 py-1.5 border border-gray-300 rounded-lg text-sm bg-white text-heading placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy"
                       placeholder="Search class groups...">
                @if(request('search'))
                    <a href="{{ route('sm-school.class-groups.index', request()->except('search', 'page')) }}"
                       class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-red-500" title="Clear search">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="py-1.5 px-3 border border-gray-300 bg-white rounded-lg text-sm text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy cursor-pointer">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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

            @if(request()->filled('search') || request()->filled('status') || (request()->filled('per_page') && request('per_page') != 10))
                <a href="{{ route('sm-school.class-groups.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-300 text-xs font-semibold rounded-lg text-red-600 bg-white hover:bg-red-50 transition-colors">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-gray-500 whitespace-nowrap self-end md:self-center">
            Total Groups: {{ $classGroups->total() }}
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Group Name</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Description</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($classGroups as $group)
                    <tr class="hover:bg-gray-50 transition-colors group-row" data-id="{{ $group->id }}" data-name="{{ strtolower($group->name) }}" data-desc="{{ strtolower($group->description ?? '') }}">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="{{ $group->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-heading">{{ $group->name }}</td>
                        <td class="px-4 py-2 text-xs text-body">{{ $group->description ?? '—' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <form action="{{ route('sm-school.class-groups.update-status', $group) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="text-xs font-semibold rounded-full px-2 py-0.5 border cursor-pointer focus:outline-none focus:ring-1 focus:ring-navy {{ $group->status === 'active' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    <option value="active" {{ $group->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $group->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button onclick="editGroup({{ $group->id }}, '{{ addslashes($group->name) }}', '{{ addslashes($group->description ?? '') }}', '{{ $group->status }}')" class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('sm-school.class-groups.destroy', $group) }}" method="POST" onsubmit="return confirm('Delete this group?');">
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
                        <td colspan="100%" class="px-6 py-12 text-center">
                            <i data-lucide="layers" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                            @if(request()->filled('search') || request()->filled('status'))
                                <p class="font-medium text-heading">No class groups found matching your search.</p>
                                <p class="text-sm text-body mt-1">Try adjusting your keywords or filters.</p>
                                <a href="{{ route('sm-school.class-groups.index') }}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-lg text-red-600 bg-white hover:bg-red-50">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Clear Search Filters
                                </a>
                            @else
                                <p class="font-medium text-heading">No class groups yet for {{ $session->name }}.</p>
                                <p class="text-sm text-body mt-1">Add your first group or import from CSV.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
                <tr id="clientEmptyRow" style="display:none;">
                    <td colspan="100%" class="px-6 py-8 text-center text-sm text-body">
                        No matching groups on this page. Press <strong>Search</strong> or hit Enter to search all pages.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($classGroups->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            {{ $classGroups->links() }}
        </div>
    @else
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
            <span>Showing <strong class="text-gray-700 font-semibold">{{ $classGroups->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $classGroups->total() }}</strong> {{ Str::plural('group', $classGroups->total()) }}</span>
            <span class="text-gray-400">Page 1 of 1</span>
        </div>
    @endif
</div>

<script>
    function openGroupModal(title = 'Add Class Group', action = '{{ route('sm-school.class-groups.store') }}', method = 'POST', name = '', desc = '', status = 'active') {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('groupForm').action = action;
        document.getElementById('methodField').innerHTML = method !== 'POST' ? '<input type="hidden" name="_method" value="' + method + '">' : '';
        document.getElementById('groupName').value = name;
        document.getElementById('groupDesc').value = desc;
        document.getElementById('groupStatus').value = status;
        document.getElementById('groupModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function editGroup(id, name, desc, status) {
        openGroupModal('Edit Class Group', '/sm-school/class-groups/' + id, 'PUT', name, desc, status);
    }

    function closeGroupModal() {
        document.getElementById('groupModal').classList.add('hidden');
    }

    function handleGroupDrop(e) {
        e.preventDefault();
        document.getElementById('groupDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('groupCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('groupFileName').textContent = file.name;
        }
    }

    document.getElementById('groupModal').addEventListener('click', function(e) {
        if (e.target === this) closeGroupModal();
    });

    document.getElementById('importGroupModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // Real-time client-side search while typing
    const searchInput = document.getElementById('groupSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.group-row');
            let visibleCount = 0;
            rows.forEach(function(row) {
                const name = row.getAttribute('data-name') || '';
                const desc = row.getAttribute('data-desc') || '';
                const match = !query || name.includes(query) || desc.includes(query);
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
