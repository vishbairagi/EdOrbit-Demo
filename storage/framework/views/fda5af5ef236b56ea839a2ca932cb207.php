<?php $__env->startSection('header', 'Administrator – Staff List'); ?>
<?php $__env->startSection('content'); ?>
<div class="w-full">
    
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white flex items-center gap-2"
             style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Staff
        </div>
        <div class="flex gap-2 flex-wrap">
            
            <a href="<?php echo e(route('sm-administrator.staff.export')); ?>"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border transition-all"
               style="border-color:#B47A42; color:#B47A42; background:#fff;"
               onmouseover="this.style.background='#fdf6ed'" onmouseout="this.style.background='#fff'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
            
            <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border transition-all"
                    style="border-color:#2d5434; color:#2d5434; background:#fff;"
                    onmouseover="this.style.background='#eef7f0'" onmouseout="this.style.background='#fff'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import CSV
            </button>
            
            <a href="<?php echo e(route('sm-administrator.staff.create')); ?>"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white"
               style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                + Add New Staff
            </a>
        </div>
    </div>

    
    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
         style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>
    <?php if(session('import_errors') && count(session('import_errors'))): ?>
    <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
        <p class="font-semibold mb-1">Some rows were skipped:</p>
        <ul class="list-disc ml-4 space-y-0.5">
            <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($err); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    
    <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('sm-administrator.staff.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
        <?php echo csrf_field(); ?>
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
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Name</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Phone</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Email</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Salary</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Designation</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Role</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Login</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Username</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Joining</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Status</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <?php if($member->photo): ?>
                                    <img src="<?php echo e(asset('storage/'.$member->photo)); ?>" class="w-9 h-9 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold"
                                         style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                                        <?php echo e(strtoupper(substr($member->name, 0, 1))); ?>

                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-bold text-sm font-bold" style="color:#211B17;"><?php echo e($member->name); ?></div>
                                    <div class="text-xs font-bold" style="color:#9e8f84;"><?php echo e($member->employee_id ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($member->phone ?? '—'); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($member->login_email ?? $member->email ?? '—'); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#211B17;">
                            <?php echo e($member->salary ? '₹'.number_format($member->salary, 2) : '—'); ?>

                        </td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($member->designation ?? '—'); ?></td>
                        <td class="px-4 py-3">
                            <?php if($member->role): ?>
                            <span class="px-2 py-1 rounded-full text-xs font-bold" style="background:#eef7f0; color:#2d5434;">
                                <?php echo e($member->role->name); ?>

                            </span>
                            <?php else: ?>
                            <span class="text-xs font-bold" style="color:#9e8f84;">No role</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($member->login_email ?? $member->login_phone ?? '—'); ?></td>
                        <td class="px-4 py-3 text-sm font-mono font-bold" style="color:#211B17;"><?php echo e($member->username); ?></td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;"><?php echo e($member->joining_date?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) ?? '—'); ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-bold"
                                  style="background:<?php echo e($member->status === 'Active' ? '#eef7f0' : '#fef0f0'); ?>; color:<?php echo e($member->status === 'Active' ? '#2d5434' : '#7a181c'); ?>;">
                                <?php echo e($member->status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?php echo e(route('sm-administrator.staff.edit', $member)); ?>"
                                   class="p-1.5 rounded-lg" style="background:#f5ece0; color:#B47A42;" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <a href="<?php echo e(route('sm-administrator.payroll.index')); ?>?staff_id=<?php echo e($member->id); ?>"
                                   class="p-1.5 rounded-lg" style="background:#eef7f0; color:#2d5434;" title="Payroll">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </a>
                                <form method="POST" action="<?php echo e(route('sm-administrator.staff.destroy', $member)); ?>"
                                      onsubmit="return confirm('Delete <?php echo e($member->name); ?>?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg" style="background:#fef0f0; color:#B4232A; border:none;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="100%" class="text-center py-12" style="color:#9e8f84;">
                            <p class="font-bold">No staff added yet</p>
                            <a href="<?php echo e(route('sm-administrator.staff.create')); ?>" class="text-sm mt-1 inline-block font-bold" style="color:#B47A42;">Add first staff member →</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
         style="background:#fff; border:1px solid #E8E0D8;">
        
        <div class="flex items-center justify-between px-6 py-4"
             style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            <div class="flex items-center gap-2 text-white font-semibold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Staff from CSV
            </div>
            <button onclick="document.getElementById('importModal').classList.add('hidden')"
                    class="text-white opacity-80 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        
        <div class="px-6 py-5">
            
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-sm font-semibold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-xs mt-0.5" style="color:#a07040;">Use this template to prepare your CSV</p>
                </div>
                <a href="<?php echo e(route('sm-administrator.staff.import-demo')); ?>"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap"
                   style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Sample CSV
                </a>
            </div>

            
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#766C63; border:1px solid #eee;">
                <p class="font-semibold mb-1.5" style="color:#211B17;">CSV Columns (in order):</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-0.5">
                    <span>1. Employee ID</span><span>2. Name *</span>
                    <span>3. Gender</span><span>4. Date of Birth</span>
                    <span>5. Phone</span><span>6. Email</span>
                    <span>7. Designation</span><span>8. Joining Date</span>
                    <span>9. Salary</span><span>10. Salary Type</span>
                    <span>11. Role (name)</span><span>12. Username *</span>
                    <span>13. Login Email</span><span>14. Login Phone</span>
                    <span>15. Password *</span><span>16. Status</span>
                    <span>17. Address</span><span>18. Note</span>
                    <span class="text-amber-600 font-medium">19. Doc Type</span><span class="text-amber-600 font-medium">20. Doc Number</span>
                </div>
                <p class="mt-2" style="color:#B47A42;">* Required for new staff. Password optional when updating existing.</p>
                <p class="mt-1" style="color:#9e8f84;">Doc Type &amp; Doc Number are optional. Accepted types: Aadhaar Card, PAN Card, Driving License, Passport, Voter ID, Other.</p>

            </div>

            
            <form method="POST" action="<?php echo e(route('sm-administrator.staff.import')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label class="block text-sm font-medium mb-2" style="color:#211B17;">Select CSV File</label>
                <div id="dropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#D4A055; background:#fffdf9;"
                     onclick="document.getElementById('csvFileInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#fdf6ed';"
                     ondragleave="this.style.background='#fffdf9';"
                     ondrop="handleDrop(event)">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D4A055" stroke-width="1.5" class="mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p class="text-sm font-medium" style="color:#B47A42;">Click or drag &amp; drop your CSV here</p>
                    <p id="fileName" class="text-xs mt-1" style="color:#9e8f84;">No file chosen</p>
                    <input type="file" id="csvFileInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('fileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importModal').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border"
                            style="border-color:#ddd; color:#766C63; background:#fff;">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-white"
                            style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>.hover-row:hover { background:#FEFAF6; }</style>
<script>
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.background = '#fffdf9';
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('csvFileInput');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        document.getElementById('fileName').textContent = file.name;
    }
}
document.getElementById('importModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>

    
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-administrator/staff/index.blade.php ENDPATH**/ ?>