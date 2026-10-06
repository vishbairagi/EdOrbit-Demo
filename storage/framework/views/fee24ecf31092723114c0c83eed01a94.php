<?php $__env->startSection('header', 'Print ID Cards'); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center">
        <i data-lucide="id-card" class="w-5 h-5 text-white mr-2.5"></i>
        <span class="text-white font-bold text-base tracking-wide">Print ID Cards</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('sm-student.print-id-cards.index')); ?>"
           class="px-3 py-1.5 text-xs font-semibold rounded-md transition <?php echo e($type === 'student' ? 'bg-white' : 'bg-white/20 text-white hover:bg-white/30'); ?>"
           <?php if($type === 'student'): ?> style="color:#211B17;" <?php endif; ?>>Students</a>
        <a href="<?php echo e(route('sm-student.print-id-cards.index', ['type' => 'staff'])); ?>"
           class="px-3 py-1.5 text-xs font-semibold rounded-md transition <?php echo e($type === 'staff' ? 'bg-white' : 'bg-white/20 text-white hover:bg-white/30'); ?>"
           <?php if($type === 'staff'): ?> style="color:#211B17;" <?php endif; ?>>Staff</a>
        <button id="printSelectedBtn" onclick="printSelected()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-xs font-semibold rounded-md hover:bg-gray-100 transition"
                style="color:#211B17;">
            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
            Print Selected Cards
        </button>
    </div>
</div>


<?php if($type === 'student'): ?>
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 mb-5">
    <form method="GET" action="<?php echo e(route('sm-student.print-id-cards.index')); ?>">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-xs text-body block mb-1">Class:</label>
                <select name="class_id" id="classFilter"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Class</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e(request('class_id') == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-body block mb-1">Section:</label>
                <select name="section_id"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Sections</option>
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($section->id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                            <?php echo e($section->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-body block mb-1">ID Card Template:</label>
                <select class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option>Default Layout</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-white text-sm font-medium hover:opacity-90 transition"
                        style="background:#B47A42;">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Get Students
                </button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>


<?php if($type === 'staff'): ?>
    <?php if($staffMembers->isNotEmpty()): ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <p class="text-sm text-body"><?php echo e($staffMembers->count()); ?> staff member(s) found</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100" style="background:#F5F1EB;">
                        <th class="px-4 py-3 text-left w-8"><input type="checkbox" id="selectAll" class="rounded accent-navy"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Employee ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Designation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__currentLoopData = $staffMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3"><input type="checkbox" class="student-cb rounded accent-navy" value="<?php echo e($member->id); ?>"></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($member->name); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($member->employee_id ?? '—'); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($member->role?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($member->designation ?? '—'); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($member->phone ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('sm-student.print-id-cards.staff.print', $member)); ?>"
                                   onclick="return printCards(this.href)"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded bg-green-50 text-green-600 hover:bg-green-100 transition"
                                   title="Print ID Card">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-12 text-center">
        <p class="text-body text-sm">No staff members found.</p>
    </div>
    <?php endif; ?>
<?php endif; ?>


<?php if($type === 'student' && $students->isNotEmpty()): ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <p class="text-sm text-body"><?php echo e($students->count()); ?> student(s) found</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100" style="background:#F5F1EB;">
                        <th class="px-4 py-3 text-left w-8">
                            <input type="checkbox" id="selectAll" class="rounded accent-navy">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Student Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Admission Number</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Section</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Enrollment Number</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Father's Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="student-cb rounded accent-navy" value="<?php echo e($student->id); ?>">
                            </td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->full_name); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->admission_no); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->schoolClass?->name); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->section?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->roll_number ?? '—'); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->father_name ?? '—'); ?></td>
                            <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->phone ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('sm-student.print-id-cards.print', $student)); ?>"
                                   onclick="return printCards(this.href)"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded bg-green-50 text-green-600 hover:bg-green-100 transition"
                                   title="Print ID Card">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php elseif($type === 'student' && request('class_id')): ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-12 text-center">
        <i data-lucide="users" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-body text-sm">No students found for the selected class/section.</p>
    </div>
<?php endif; ?>

<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.student-cb').forEach(cb => cb.checked = this.checked);
});

function printSelected() {
    const ids = [...document.querySelectorAll('.student-cb:checked')].map(cb => cb.value);
    if (!ids.length) {
        alert('Please select at least one <?php echo e($type === 'staff' ? 'staff member' : 'student'); ?>.');
        return;
    }
    printCards(`<?php echo e(route('sm-student.print-id-cards.selected')); ?>?type=<?php echo e($type); ?>&ids=${ids.join(',')}`);
}
</script>
<?php echo $__env->make('student.print-id-cards._print-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/student/print-id-cards/index.blade.php ENDPATH**/ ?>