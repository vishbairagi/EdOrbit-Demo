<?php $__env->startSection('header', 'Manage Exams'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-2 text-white shadow-md" style="background:var(--color-primary);">
    <h1 class="flex items-center gap-2 text-base font-bold"><i data-lucide="clock-3" class="h-4 w-4"></i> Manage Exams</h1>
    <a href="<?php echo e(route('examination.exams.create')); ?>" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-1.5 text-xs font-semibold hover:bg-white/15">
        <i data-lucide="plus-square" class="h-4 w-4"></i> Add New Exam
    </a>
</div>


<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('examination.exams.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
        <?php echo csrf_field(); ?>
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[950px] border-collapse text-xs">
            <thead style="background:var(--color-primary);" class="text-left text-white">
                <tr>
                    <th class="px-3 py-3 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                               class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-3 py-3">Exam Title</th>
                    <th class="px-3 py-3">Class / Sections</th>
                    <th class="px-3 py-3">Exam Center</th>
                    <th class="px-3 py-3">Start Date</th>
                    <th class="px-3 py-3">End Date</th>
                    <th class="px-3 py-3">Exam Group</th>
                    <th class="px-3 py-3">Type</th>
                    <th class="px-3 py-3">Papers</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="align-top hover:bg-gray-50">
                        <td class="px-3 py-3 text-center w-10">
                            <input type="checkbox" value="<?php echo e($exam->id); ?>" onchange="updateBulkToolbar()"
                                   class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->title); ?></td>
                        <td class="px-3 py-3 text-body">
                            <span class="font-bold text-heading"><?php echo e($exam->schoolClass->name); ?></span>
                            <div class="mt-1 text-[11px]"><?php echo e($exam->sections->pluck('name')->join(', ')); ?></div>
                        </td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->exam_center); ?></td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->examGroup?->name ?? '—'); ?></td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->exam_type === 'marks' ? 'Marks and Grade' : 'Grade Only'); ?></td>
                        <td class="px-3 py-3 font-bold text-heading"><?php echo e($exam->papers_count); ?></td>
                        <td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-[10px] font-bold <?php echo e($exam->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>"><?php echo e(ucfirst($exam->status)); ?></span></td>
                        <td class="px-3 py-3"><a href="<?php echo e(route('examination.exams.edit', $exam)); ?>" class="inline-flex items-center gap-1 rounded px-2.5 py-1.5 font-bold text-white" style="background:var(--color-primary);"><i data-lucide="pencil" class="h-3 w-3"></i> Edit</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="11" class="px-4 py-12 text-center text-body">No exams have been created for <?php echo e($session->name); ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($exams->hasPages()): ?>
        <div class="border-t border-gray-200 px-4 py-3"><?php echo e($exams->links()); ?></div>
    <?php endif; ?>
</div>

<script>
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
        if (!confirm('Are you sure you want to delete the selected exams?')) return false;
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/exams/index.blade.php ENDPATH**/ ?>