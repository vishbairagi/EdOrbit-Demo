@extends('layouts.app')

@section('header', 'Manage Exam Groups')

@section('content')
@php
    $editing = $editingGroup;
    $showGroupForm = $editing !== null || $errors->any() || request()->boolean('create');
    $criteria = old('grade_criteria', $editing?->grade_criteria ?? [
        ['percentage_from' => 0, 'percentage_to' => 40, 'grade' => 'F', 'status' => 'Fail', 'remark' => 'Needs Improvement'],
        ['percentage_from' => 41, 'percentage_to' => 50, 'grade' => 'D', 'status' => 'Pass', 'remark' => 'Satisfactory'],
        ['percentage_from' => 51, 'percentage_to' => 60, 'grade' => 'C', 'status' => 'Pass', 'remark' => 'Satisfactory'],
        ['percentage_from' => 61, 'percentage_to' => 70, 'grade' => 'B', 'status' => 'Pass', 'remark' => 'Good'],
        ['percentage_from' => 71, 'percentage_to' => 80, 'grade' => 'B+', 'status' => 'Pass', 'remark' => 'Good'],
        ['percentage_from' => 81, 'percentage_to' => 90, 'grade' => 'A', 'status' => 'Pass', 'remark' => 'Very Good'],
        ['percentage_from' => 91, 'percentage_to' => 100, 'grade' => 'A+', 'status' => 'Pass', 'remark' => 'Excellent'],
    ]);
    if (is_array($criteria) && array_key_exists('percentage_from', $criteria)) {
        $criteria = collect($criteria['percentage_from'])->map(fn ($from, $index) => [
            'percentage_from' => $from,
            'percentage_to' => $criteria['percentage_to'][$index] ?? '',
            'grade' => $criteria['grade'][$index] ?? '',
            'status' => $criteria['status'][$index] ?? 'Pass',
            'remark' => $criteria['remark'][$index] ?? '',
        ])->all();
    }
    $criteria = collect($criteria)->map(function (array $row): array {
        if (in_array(strtolower((string) ($row['status'] ?? '')), ['active', 'inactive'], true)) {
            $row['status'] = ($row['grade'] ?? '') === 'F' ? 'Fail' : 'Pass';
        }

        return $row;
    })->all();
    $thresholds = old('result_thresholds', $editing?->result_thresholds ?? []);
@endphp

<div class="mb-5 rounded-lg px-4 py-2 text-center text-base font-bold text-white shadow-md" style="background:var(--color-primary);">Manage Exam Groups</div>
@if($errors->any())<div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="flex flex-col gap-4">
<section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm {{ $showGroupForm ? '' : 'hidden' }}" id="group-form" style="order:3;">
    <h2 class="mb-4 text-sm font-bold text-heading">{{ $editing ? 'Edit Exam Group' : 'Create Exam Group' }}</h2>
    <form method="POST" action="{{ $editing ? route('examination.groups.update', $editing) : route('examination.groups.store') }}" class="space-y-5">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="max-w-xl">
            <label for="group-name" class="mb-1 block text-xs font-semibold text-heading">Group Name</label>
            <input id="group-name" name="name" value="{{ old('name', $editing?->name) }}" required maxlength="100" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between rounded px-3 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">
                <span>Grade Criteria</span>
                <button type="button" id="add-grade-row" class="rounded border border-white/70 px-2 py-1 text-xs">Add row</button>
            </div>
            {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('examination.groups.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-xs">
                    <thead class="bg-gray-50 text-left"><tr><th class="p-2">Percentage &gt;=</th><th class="p-2">Percentage &lt;=</th><th class="p-2">Assign Grade</th><th class="p-2">Status</th><th class="p-2">Remark</th><th class="p-2">Action</th></tr></thead>
                    <tbody id="grade-rows" class="divide-y divide-gray-200">
                        @foreach($criteria as $row)
                            <tr>
                                <td class="p-1"><input name="grade_criteria[percentage_from][]" type="number" min="0" max="100" step="0.01" value="{{ $row['percentage_from'] }}" required class="w-full rounded border border-gray-300 px-2 py-2"></td>
                                <td class="p-1"><input name="grade_criteria[percentage_to][]" type="number" min="0" max="100" step="0.01" value="{{ $row['percentage_to'] }}" required class="w-full rounded border border-gray-300 px-2 py-2"></td>
                                <td class="p-1"><input name="grade_criteria[grade][]" value="{{ $row['grade'] }}" maxlength="20" required class="w-full rounded border border-gray-300 px-2 py-2"></td>
                                <td class="p-1"><input name="grade_criteria[status][]" type="text" value="{{ $row['status'] ?? (($row['grade'] ?? '') === 'F' ? 'Fail' : 'Pass') }}" maxlength="100" placeholder="Pass, Fail, etc." class="w-full rounded border border-gray-300 px-2 py-2"></td>
                                <td class="p-1"><input name="grade_criteria[remark][]" type="text" value="{{ $row['remark'] ?? '' }}" maxlength="150" placeholder="Good, Excellent, etc." class="w-full rounded border border-gray-300 px-2 py-2"></td>
                                <td class="p-1 text-center"><button type="button" class="remove-grade-row px-2 py-1 font-bold text-red-600" aria-label="Remove grade row">×</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h3 class="mb-3 rounded px-3 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Result Thresholds</h3>
            <div class="grid gap-4 md:grid-cols-3">
                <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="result_thresholds[show_subject_status]" value="0"><input type="checkbox" name="result_thresholds[show_subject_status]" value="1" @checked($thresholds['show_subject_status'] ?? false)> Show Subject-wise Result Status</label>
                <label class="text-xs">Subject Pass Threshold (%)<input name="result_thresholds[subject_pass_threshold]" type="number" min="0" max="100" step="0.01" value="{{ $thresholds['subject_pass_threshold'] ?? 0 }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Subject Status Column Header<input name="result_thresholds[subject_status_header]" value="{{ $thresholds['subject_status_header'] ?? 'Status' }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Subject Pass Label<input name="result_thresholds[subject_pass_label]" value="{{ $thresholds['subject_pass_label'] ?? 'Pass' }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Subject Fail Label<input name="result_thresholds[subject_fail_label]" value="{{ $thresholds['subject_fail_label'] ?? 'Fail' }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="result_thresholds[show_overall_status]" value="0"><input type="checkbox" name="result_thresholds[show_overall_status]" value="1" @checked($thresholds['show_overall_status'] ?? false)> Show Overall Result Status</label>
                <label class="text-xs">Overall Pass Threshold (%)<input name="result_thresholds[overall_pass_threshold]" type="number" min="0" max="100" step="0.01" value="{{ $thresholds['overall_pass_threshold'] ?? 0 }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Max Failed Subjects Allowed<input name="result_thresholds[max_failed_subjects]" type="number" min="0" value="{{ $thresholds['max_failed_subjects'] ?? 0 }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Overall Result Row Header<input name="result_thresholds[overall_row_header]" value="{{ $thresholds['overall_row_header'] ?? 'Overall Result' }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Overall Pass Label<input name="result_thresholds[overall_pass_label]" value="{{ $thresholds['overall_pass_label'] ?? 'Pass' }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
                <label class="text-xs">Overall Fail Label<input name="result_thresholds[overall_fail_label]" value="{{ $thresholds['overall_fail_label'] ?? 'Fail' }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button class="rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);">{{ $editing ? 'Save Group' : 'Create Group' }}</button>
            @if($editing)<a href="{{ route('examination.groups.index') }}" class="rounded border border-gray-300 px-4 py-2 text-xs font-semibold text-body">Cancel</a>@endif
        </div>
    </form>
</section>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" style="order:1;">
    {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('examination.groups.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-xs">
            <thead class="text-left text-white" style="background:var(--color-primary);"><tr><th class="p-3">Group Name</th><th class="p-3">Grade Bands</th><th class="p-3">Exams</th><th class="p-3">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($groups as $group)
                    <tr><td class="p-3 font-semibold text-heading">{{ $group->name }}</td><td class="p-3">{{ collect($group->grade_criteria)->pluck('grade')->join(', ') }}</td><td class="p-3">{{ $group->exams_count }}</td><td class="p-3"><div class="flex gap-2"><a href="{{ route('examination.groups.index', ['edit' => $group->id]) }}" class="rounded px-3 py-1.5 font-semibold text-white" style="background:var(--color-primary);">Edit</a><form method="POST" action="{{ route('examination.groups.destroy', $group) }}" onsubmit="return confirm('Delete this exam group?')">@csrf @method('DELETE')<button class="rounded border border-red-300 px-3 py-1.5 font-semibold text-red-700">Delete</button></form></div></td></tr>
                @empty
                    <tr><td colspan="100%" class="p-8 text-center text-body">No exam groups created for this session.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="flex justify-end" style="order:2;">
    @if($editing)
        <a href="{{ route('examination.groups.index', ['create' => 1]) }}" class="inline-flex items-center gap-1 rounded px-4 py-2 text-xs font-semibold text-white" style="background:var(--color-primary);"><i data-lucide="plus" class="h-3.5 w-3.5"></i> Add Group</a>
    @else
        <button type="button" id="show-group-form" class="inline-flex items-center gap-1 rounded px-4 py-2 text-xs font-semibold text-white" style="background:var(--color-primary);"><i data-lucide="plus" class="h-3.5 w-3.5"></i> Add Group</button>
    @endif
</div>
</div>

<script>
document.getElementById('show-group-form')?.addEventListener('click', () => {
    document.getElementById('group-form').classList.remove('hidden');
    document.getElementById('group-name').focus();
    document.getElementById('group-form').scrollIntoView({behavior: 'smooth', block: 'start'});
});
document.getElementById('add-grade-row')?.addEventListener('click', () => {
    document.getElementById('grade-rows').insertAdjacentHTML('beforeend', `<tr><td class="p-1"><input name="grade_criteria[percentage_from][]" type="number" min="0" max="100" step="0.01" required class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[percentage_to][]" type="number" min="0" max="100" step="0.01" required class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[grade][]" maxlength="20" required class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[status][]" type="text" value="Pass" maxlength="100" placeholder="Pass, Fail, etc." class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[remark][]" type="text" maxlength="150" placeholder="Good, Excellent, etc." class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1 text-center"><button type="button" class="remove-grade-row px-2 py-1 font-bold text-red-600" aria-label="Remove grade row">×</button></td></tr>`);
});
document.addEventListener('click', event => {
    if (event.target.closest('.remove-grade-row')) event.target.closest('tr').remove();
});
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