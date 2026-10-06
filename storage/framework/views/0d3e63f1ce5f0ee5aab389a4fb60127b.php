<?php $__env->startSection('header', 'Administrator – Staff Leaves'); ?>
<?php $__env->startSection('content'); ?>
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white flex items-center gap-2" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Staff Leaves
        </div>
        <div class="flex items-center gap-3">
            <div class="flex gap-2">
                <?php $__currentLoopData = ['all', 'Pending', 'Processing', 'Approved', 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('sm-administrator.leaves.index')); ?>?status=<?php echo e($s); ?>"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium"
                   style="background:<?php echo e($status === $s ? 'linear-gradient(135deg,#B47A42,#D4A055)' : '#f5ece0'); ?>; color:<?php echo e($status === $s ? '#fff' : '#211B17'); ?>; border:1px solid #DED5CB;">
                    <?php echo e(ucfirst($s)); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button onclick="document.getElementById('addLeaveModal').classList.remove('hidden')" 
                    class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-semibold text-white transition-all"
                    style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Leave
            </button>
        </div>
    </div>

    <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        <div class="overflow-x-auto">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Applicant</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Type</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Leave Type</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">From</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">To</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Days</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Reason</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Status</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                        <td class="px-4 py-3">
                            <div class="font-bold text-sm font-bold" style="color:#211B17;">
                                <?php echo e($leave->applicant_model?->name ?? 'Unknown'); ?>

                            </div>
                            <div class="text-xs font-bold" style="color:#9e8f84;"><?php echo e(ucfirst($leave->applicant_type)); ?></div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-bold"
                                  style="background:<?php echo e($leave->applicant_type === 'staff' ? '#eef7f0' : '#f0e8f8'); ?>; color:<?php echo e($leave->applicant_type === 'staff' ? '#2d5434' : '#6a1b9a'); ?>;">
                                <?php echo e($leave->applicant_type === 'staff' ? 'Staff' : 'Admin/Principal'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($leave->leave_type); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($leave->from_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($leave->to_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#211B17;"><?php echo e($leave->total_days); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63; max-width:180px;"><?php echo e(Str::limit($leave->reason, 50)); ?></td>
                        <td class="px-4 py-3">
                            <?php
                                $statusColors = ['Pending'=>['#fff8e1','#8a6d0b'], 'Processing'=>['#e8f4fd','#1a5276'], 'Approved'=>['#eef7f0','#2d5434'], 'Rejected'=>['#fef0f0','#7a181c']];
                                $sc = $statusColors[$leave->status] ?? ['#f5ece0','#211B17'];
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-bold"
                                  style="background:<?php echo e($sc[0]); ?>; color:<?php echo e($sc[1]); ?>;">
                                <?php echo e($leave->status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button onclick="openLeaveModal(<?php echo e($leave->id); ?>, '<?php echo e($leave->status); ?>', '<?php echo e(addslashes($leave->admin_note ?? '')); ?>')"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold"
                                    style="background:linear-gradient(135deg,#B47A42,#D4A055); color:#fff; border:none; cursor:pointer;">
                                Update Status
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="text-center py-12 text-sm font-bold" style="color:#9e8f84;">No leave applications found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="leaveModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="edit" class="w-5 h-5 text-navy"></i>
                Update Leave Status
            </h3>
            <button type="button" onclick="document.getElementById('leaveModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" id="leaveUpdateForm">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="leaveStatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Admin Note <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <textarea name="admin_note" id="leaveNote" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y" placeholder="Optional note for applicant"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Update Status
                </button>
                <button type="button" onclick="document.getElementById('leaveModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<div id="addLeaveModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5 text-navy"></i>
                Add Staff Leave
            </h3>
            <button type="button" onclick="document.getElementById('addLeaveModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('sm-administrator.leaves.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Applicant <span class="text-red-500">*</span></label>
                    <select name="applicant" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white" required>
                        <option value="">Select User...</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->type); ?>_<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">From Date <span class="text-red-500">*</span></label>
                        <input type="date" name="from_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">To Date <span class="text-red-500">*</span></label>
                        <input type="date" name="to_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Leave Type <span class="text-red-500">*</span></label>
                        <select name="leave_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white" required>
                            <option value="Sick">Sick Leave</option>
                            <option value="Casual">Casual Leave</option>
                            <option value="Maternity">Maternity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white" required>
                            <option value="Approved">Approved</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Reason <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y" required placeholder="Reason for leave"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Admin Note <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <textarea name="admin_note" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y" placeholder="Note..."></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Save Leave
                </button>
                <button type="button" onclick="document.getElementById('addLeaveModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.hover-row:hover { background:#FEFAF6; }
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { width:100%; padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; outline:none; font-family:inherit; }
</style>

<script>
function openLeaveModal(id, status, note) {
    document.getElementById('leaveUpdateForm').action = `/sm-administrator/leaves/${id}`;
    document.getElementById('leaveStatus').value = status;
    document.getElementById('leaveNote').value = note;
    document.getElementById('leaveModal').classList.remove('hidden');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-administrator/leaves/index.blade.php ENDPATH**/ ?>