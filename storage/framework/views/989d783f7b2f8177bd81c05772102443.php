<?php $__env->startSection('header', 'Administrator – Staff Payroll'); ?>
<?php $__env->startSection('content'); ?>
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Staff Payroll
        </div>
    </div>

    
    <form method="GET" action="<?php echo e(route('sm-administrator.payroll.index')); ?>" class="mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="field-label">Month</label>
            <input type="month" name="month" value="<?php echo e($month); ?>" class="field-input" style="width:180px;">
        </div>
        <div>
            <label class="field-label">Session</label>
            <select name="session_id" class="field-input" style="width:180px;">
                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($session->id); ?>" <?php echo e($sessionId == $session->id ? 'selected' : ''); ?>><?php echo e($session->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Load</button>
    </form>

    <form method="POST" action="<?php echo e(route('sm-administrator.payroll.generate')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="month" value="<?php echo e($month); ?>">
        <input type="hidden" name="session_id" value="<?php echo e($sessionId); ?>">

        <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                Payroll for <?php echo e(\Carbon\Carbon::parse($month . '-01')->format('F Y')); ?>

            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5ece0;">
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Staff</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Role</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Basic (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Allowances (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Deductions (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Net Salary (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Payment Status</th>
                            <th class="text-center text-sm font-semibold px-4 py-3" style="color:#211B17;">Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $record = $records[$member->id] ?? null; ?>
                        <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                            <td class="px-4 py-3">
                                <div class="font-bold text-sm font-bold" style="color:#211B17;"><?php echo e($member->name); ?></div>
                                <div class="text-xs font-bold" style="color:#9e8f84;"><?php echo e($member->employee_id); ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($member->role?->name ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <input type="number" name="staff[<?php echo e($member->id); ?>][basic]" min="0" step="0.01"
                                       value="<?php echo e($record?->basic_salary ?? $member->salary ?? 0); ?>"
                                       class="px-2 py-1 rounded-lg border text-sm text-right font-bold" style="width:100px; border-color:#DED5CB;"
                                       oninput="calcNet(<?php echo e($member->id); ?>)">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="staff[<?php echo e($member->id); ?>][allowances]" min="0" step="0.01"
                                       value="<?php echo e($record?->allowances ?? 0); ?>"
                                       class="px-2 py-1 rounded-lg border text-sm text-right font-bold" style="width:100px; border-color:#DED5CB;"
                                       oninput="calcNet(<?php echo e($member->id); ?>)">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="staff[<?php echo e($member->id); ?>][deductions]" min="0" step="0.01"
                                       value="<?php echo e($record?->deductions ?? 0); ?>"
                                       class="px-2 py-1 rounded-lg border text-sm text-right font-bold" style="width:100px; border-color:#DED5CB;"
                                       oninput="calcNet(<?php echo e($member->id); ?>)">
                            </td>
                            <td class="px-4 py-3">
                                <span id="net-<?php echo e($member->id); ?>" class="font-bold text-sm font-bold" style="color:#2d5434;">
                                    <?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format(($record?->net_salary ?? ($member->salary ?? 0)), 2)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <select name="staff[<?php echo e($member->id); ?>][payment_status]" class="px-2 py-1 rounded-lg border text-sm font-bold" style="border-color:#DED5CB;">
                                    <?php $__currentLoopData = ['Pending','Paid','Partial']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ps); ?>" <?php echo e(($record?->payment_status ?? 'Pending') === $ps ? 'selected' : ''); ?>><?php echo e($ps); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($record): ?>
                                <a href="<?php echo e(route('sm-administrator.payroll.slip', [$member, $month])); ?>" target="_blank"
                                   class="px-2 py-1 rounded-lg text-xs font-bold" style="background:#eef7f0; color:#2d5434;">
                                    View Slip
                                </a>
                                <?php else: ?>
                                <span class="text-xs font-bold" style="color:#9e8f84;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center py-12 text-sm font-bold" style="color:#9e8f84;">No active staff found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($staff->count()): ?>
            <div class="px-6 py-4 flex justify-end" style="border-top:1px solid #F0EAE3;">
                <button type="submit" class="px-8 py-2.5 rounded-lg font-semibold text-white"
                        style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                    Generate / Update Payroll
                </button>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<style>
.hover-row:hover { background:#FEFAF6; }
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; outline:none; font-family:inherit; }
</style>

<script>
function calcNet(staffId) {
    const row = document.querySelector(`[name="staff[${staffId}][basic]"]`);
    const rowAllow = document.querySelector(`[name="staff[${staffId}][allowances]"]`);
    const rowDed   = document.querySelector(`[name="staff[${staffId}][deductions]"]`);
    if (!row) return;
    const net = (parseFloat(row.value)||0) + (parseFloat(rowAllow?.value)||0) - (parseFloat(rowDed?.value)||0);
    const netEl = document.getElementById('net-' + staffId);
    if (netEl) netEl.textContent = '₹' + net.toFixed(2);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-administrator/payroll/index.blade.php ENDPATH**/ ?>