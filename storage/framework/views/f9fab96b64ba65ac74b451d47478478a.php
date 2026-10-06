<?php $__env->startSection('header', 'Administrator – Staff ID Cards'); ?>
<?php $__env->startSection('content'); ?>
<div class="w-full">

    
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Staff ID Cards
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="printSelectedBtn" onclick="printSelectedStaff()"
                    class="px-4 py-2.5 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                🖨 Print Selected (<span id="selectedCount">0</span>)
            </button>
            <button type="button" onclick="printCards('<?php echo e(route('sm-administrator.id-cards.print')); ?>?all=1')"
                    class="px-4 py-2.5 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#4F7D5A,#6aae78);">
                🖨 Print All Cards
            </button>
        </div>
    </div>

    <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">

        
        <div class="px-6 py-3 flex flex-wrap items-center justify-between gap-3" style="border-bottom:1px solid #F0EAE3; background:#FAF7F3;">
            <p class="text-sm font-semibold" style="color:#211B17;">
                Staff List <span class="font-normal" style="color:#9e8f84;">(<?php echo e($staff->count()); ?> active)</span>
            </p>
            <input type="text" id="staffSearch" placeholder="Search name, ID, role, phone..."
                   class="w-full sm:w-72 rounded-lg text-sm px-3 py-2 focus:outline-none"
                   style="border:1px solid #E8E0D8;">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="staffTable">
                <thead>
                    <tr style="background:#F5F1EB; border-bottom:1px solid #E8E0D8;">
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" id="selectAll" style="accent-color:#B47A42; width:16px; height:16px;">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">Staff</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">Employee ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">Designation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold" style="color:#6b5d52;">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold" style="color:#6b5d52;">Print</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="staff-row hover:bg-gray-50 transition-colors" style="border-bottom:1px solid #F5F0EA;"
                        data-search="<?php echo e(strtolower($member->name.' '.$member->employee_id.' '.$member->role?->name.' '.$member->designation.' '.$member->phone.' '.$member->email)); ?>">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="staff-check" value="<?php echo e($member->id); ?>"
                                   style="accent-color:#B47A42; width:16px; height:16px;">
                        </td>
                        <td class="px-4 py-3 row-no" style="color:#9e8f84;"><?php echo e($loop->iteration); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <?php if($member->photo): ?>
                                    <img src="<?php echo e(filter_var($member->photo, FILTER_VALIDATE_URL) ? $member->photo : asset('storage/'.ltrim($member->photo, '/'))); ?>"
                                         alt="" class="w-9 h-9 rounded-full object-cover flex-shrink-0"
                                         onerror="this.outerHTML='<div class=&quot;w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold&quot; style=&quot;background:linear-gradient(135deg,#B47A42,#D4A055);&quot;><?php echo e(strtoupper(substr($member->name, 0, 1))); ?></div>'">
                                <?php else: ?>
                                    <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold"
                                         style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                                        <?php echo e(strtoupper(substr($member->name, 0, 1))); ?>

                                    </div>
                                <?php endif; ?>
                                <span class="font-semibold truncate" style="color:#211B17;"><?php echo e($member->name); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3" style="color:#6b5d52;"><?php echo e($member->employee_id ?? '—'); ?></td>
                        <td class="px-4 py-3" style="color:#6b5d52;"><?php echo e($member->role?->name ?? '—'); ?></td>
                        <td class="px-4 py-3" style="color:#6b5d52;"><?php echo e($member->designation ?? '—'); ?></td>
                        <td class="px-4 py-3" style="color:#6b5d52;"><?php echo e($member->phone ?? '—'); ?></td>
                        <td class="px-4 py-3" style="color:#6b5d52;"><?php echo e($member->email ?? '—'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <a href="<?php echo e(route('sm-administrator.id-cards.print', ['staff_ids' => [$member->id]])); ?>"
                               onclick="return printCards(this.href)" title="Print ID Card"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition"
                               style="background:#F5EDE3; color:#B47A42;">
                                🖨
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="text-center py-12 text-sm" style="color:#9e8f84;">No active staff found</td></tr>
                    <?php endif; ?>
                    <tr id="noMatchRow" class="hidden">
                        <td colspan="9" class="text-center py-10 text-sm" style="color:#9e8f84;">No staff match your search</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const selectAll = document.getElementById('selectAll');
const countEl = document.getElementById('selectedCount');
const visibleRows = () => [...document.querySelectorAll('.staff-row')].filter(r => !r.classList.contains('hidden'));
const checks = () => [...document.querySelectorAll('.staff-check')];

function refreshCount() {
    const n = checks().filter(c => c.checked).length;
    countEl.textContent = n;
    const vis = visibleRows().map(r => r.querySelector('.staff-check'));
    selectAll.checked = vis.length > 0 && vis.every(c => c.checked);
    selectAll.indeterminate = !selectAll.checked && vis.some(c => c.checked);
}

selectAll.addEventListener('change', function () {
    visibleRows().forEach(r => r.querySelector('.staff-check').checked = this.checked);
    refreshCount();
});
document.addEventListener('change', e => { if (e.target.classList.contains('staff-check')) refreshCount(); });

document.getElementById('staffSearch').addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    let shown = 0;
    document.querySelectorAll('.staff-row').forEach(r => {
        const match = !q || r.dataset.search.includes(q);
        r.classList.toggle('hidden', !match);
        if (match) { shown++; r.querySelector('.row-no').textContent = shown; }
    });
    document.getElementById('noMatchRow').classList.toggle('hidden', shown > 0 || !document.querySelector('.staff-row'));
    refreshCount();
});

function printSelectedStaff() {
    const ids = checks().filter(c => c.checked).map(c => c.value);
    if (!ids.length) { alert('Please select at least one staff member.'); return; }
    printCards(`<?php echo e(route('sm-administrator.id-cards.print')); ?>?` + ids.map(id => 'staff_ids[]=' + encodeURIComponent(id)).join('&'));
}
refreshCount();
</script>
<?php echo $__env->make('student.print-id-cards._print-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-administrator/id-cards/index.blade.php ENDPATH**/ ?>