@extends('layouts.app')

@section('header', 'Schools')

@section('content')
{{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('school-management.schools.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-3 bg-white">
        <form method="GET" action="{{ route('school-management.schools.index') }}" class="flex w-full md:w-auto gap-2">
            <div class="relative flex-1 md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy" placeholder="Search schools...">
            </div>
            <select name="status" class="block py-2 px-3 border border-gray-300 bg-white text-gray-900 rounded-md text-sm focus:outline-none focus:ring-navy focus:border-navy">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-navy">
                Filter
            </button>
            @if(request()->has('search') || request()->has('status'))
                <a href="{{ route('school-management.schools.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-red-600 bg-white hover:bg-red-50">
                    Clear
                </a>
            @endif
        </form>
        
        <a href="{{ route('school-management.schools.create') }}" class="inline-flex items-center px-4 py-2 bg-navy hover:bg-primary-dark text-white text-sm font-medium rounded-md shadow-sm transition-colors w-full md:w-auto justify-center">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add School
        </a>
    </div>
    
    {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('school-management.schools.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
        {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('school-management.schools.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">School Name</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Contact Details</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Reg Number</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Created At</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($schools as $school)
                    <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $school->id }}">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="{{ $school->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="text-sm font-bold text-heading">{{ $school->name }}</div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="text-xs text-heading"><i data-lucide="phone" class="w-3 h-3 inline mr-1 text-gray-400"></i> {{ $school->phone ?? 'N/A' }}</div>
                            <div class="text-xs text-body"><i data-lucide="mail" class="w-3 h-3 inline mr-1 text-gray-400"></i> {{ $school->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="text-xs text-heading">{{ $school->registration_number ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @if($school->status === 'active')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-xs text-body">
                            {{ $school->created_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('school-management.schools.show', $school) }}" class="text-navy hover:text-primary-dark transition-colors" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('school-management.schools.edit', $school) }}" class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('school-management.schools.destroy', $school) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this school?');">
                                    @csrf
                                    @method('DELETE')
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
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="school" class="w-12 h-12 text-gray-300 mb-3"></i>
                                <p class="text-lg font-medium text-heading">No schools found.</p>
                                <p class="mt-1 text-sm text-body">Create your first school to get started.</p>
                                <a href="{{ route('school-management.schools.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-navy hover:bg-primary-dark text-white text-sm font-medium rounded-md shadow-sm transition-colors">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add School
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($schools->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            {{ $schools->links() }}
        </div>
    @endif
</div>

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
