@extends('layouts.app')

@section('header', 'Routes')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Transport Routes</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('transport.routes.create') }}" class="inline-flex items-center gap-2 rounded-md bg-navy px-3 py-2 text-xs font-semibold text-white hover:bg-primary-dark">
            <i data-lucide="plus" class="h-4 w-4"></i> Add New Route
        </a>
    </div>
</div>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Route List</h3>
        <span class="text-xs text-body">{{ count($routes) }} records</span>
    </div>
    {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('transport.routes.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
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
    <form id="bulkDeleteForm" method="POST" action="{{ route('transport.routes.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Route Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Transport Vehicles</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Period</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Fare</th>
                    <th scope="col" class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($routes as $route)
                    <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $route->id }}">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="{{ $route->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-3 font-bold text-heading">{{ $route->route_name }}</td>
                        <td class="px-4 py-3 font-bold text-heading">
                            @foreach($route->vehicles as $vehicle)
                                <span class="inline-block bg-gray-100 rounded px-2 py-1 text-[10px] mr-1">{{ $vehicle->vehicle_number }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 font-bold text-heading">{{ $route->period ? $route->period->name : '—' }}</td>
                        <td class="px-4 py-3 font-bold text-heading">{{ \App\Models\SchoolSetting::getCurrencySymbol() }}{{ number_format($route->route_fare, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('transport.routes.edit', $route) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-2.5 py-1.5 font-bold text-navy hover:bg-gray-50"><i data-lucide="edit" class="h-3.5 w-3.5"></i> Edit</a>
                                <form action="{{ route('transport.routes.destroy', $route) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this route?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-red-200 px-2.5 py-1.5 font-bold text-red-600 hover:bg-red-50">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td colspan="100%" class="px-4 py-12 text-center text-sm text-body font-bold">No routes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

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
