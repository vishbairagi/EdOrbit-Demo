@extends('layouts.app')
@section('header', 'Administrator – Roles')
@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white flex items-center gap-2"
             style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Roles
        </div>
        <a href="{{ route('sm-administrator.roles.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white"
           style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Role
        </a>
    </div>

    <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('sm-administrator.roles.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
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
                    <tr style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">#</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Role Name</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Staff Members</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Permissions</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Description</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Status</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $i => $role)
                    <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                        <td class="px-4 py-3 text-sm" style="color:#766C63;">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-sm" style="color:#211B17;">{{ $role->name }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center" style="color:#766C63;">
                            <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:#f5ece0; color:#B47A42;">
                                {{ $role->staff_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center" style="color:#766C63;">
                            <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:#eef7f0; color:#2d5434;">
                                {{ $role->permissions()->count() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm" style="color:#766C63; max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $role->description }}">
                            {{ Str::limit($role->description, 50) ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium"
                                  style="background:{{ $role->status === 'Active' ? '#eef7f0' : '#fef0f0' }}; color:{{ $role->status === 'Active' ? '#2d5434' : '#7a181c' }};">
                                {{ $role->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('sm-administrator.roles.edit', $role) }}"
                                   class="p-1.5 rounded-lg transition-all"
                                   style="background:#f5ece0; color:#B47A42;"
                                   title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('sm-administrator.roles.destroy', $role) }}"
                                      onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg cursor-pointer" style="background:#fef0f0; color:#B4232A; border:none;" title="Delete">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="text-center py-12" style="color:#9e8f84;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mx-auto mb-3" style="opacity:.4;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <p class="font-medium">No roles created yet</p>
                            <a href="{{ route('sm-administrator.roles.create') }}" class="text-sm mt-1 inline-block" style="color:#B47A42;">Create your first role →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
