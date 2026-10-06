<?php $__env->startSection('header', 'Transport Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Students Transport Report</h2>
        <p class="text-xs text-body"><?php echo e($school->name ?? 'School'); ?> · <?php echo e($session->name ?? 'Session'); ?></p>
    </div>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white p-4">
    <form action="<?php echo e(route('transport.report')); ?>" method="GET" class="grid grid-cols-1 items-end gap-3 md:grid-cols-4">
        <div>
            <label for="class_id" class="mb-1 block text-xs font-medium text-body">Class</label>
            <select name="class_id" id="class_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">All Classes</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($class->id); ?>" <?php echo e(request('class_id') == $class->id ? 'selected' : ''); ?>>
                        <?php echo e($class->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        
        <div>
            <label for="section_id" class="mb-1 block text-xs font-medium text-body">Section</label>
            <select name="section_id" id="section_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">All Sections</option>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($section->id); ?>" data-class-id="<?php echo e($section->school_class_id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                        <?php echo e($section->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        
        <div>
            <label for="route_id" class="mb-1 block text-xs font-medium text-body">Transport Route <span class="text-red-500">*</span></label>
            <select name="route_id" id="route_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <option value="">Select Route</option>
                <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($route->id); ?>" <?php echo e(request('route_id') == $route->id ? 'selected' : ''); ?>>
                        <?php echo e($route->route_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        
        <div class="flex gap-2">
            <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">Get Report</button>
            <a href="<?php echo e(route('transport.report')); ?>" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Reset</a>
        </div>
    </form>
</section>

<?php if($request->has('route_id')): ?>
<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Assigned Students</h3>
        <span class="text-xs text-body"><?php echo e(count($students)); ?> records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead>
                <tr class="bg-navy">
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Route Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Admission No</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Class</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Section</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Father's Name</th>
                    <th scope="col" class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Father's Phone</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-heading">
                            <?php $__currentLoopData = $student->transportRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($sr->id == request('route_id')): ?>
                                    <?php echo e($sr->route_name); ?>

                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                        <td class="px-4 py-3 text-heading"><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
                        <td class="px-4 py-3 text-body"><?php echo e($student->admission_no); ?></td>
                        <td class="px-4 py-3 text-body"><?php echo e($student->schoolClass ? $student->schoolClass->name : '—'); ?></td>
                        <td class="px-4 py-3 text-body"><?php echo e($student->section ? $student->section->name : '—'); ?></td>
                        <td class="px-4 py-3 text-body"><?php echo e($student->father_name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-body"><?php echo e($student->father_phone ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-body">No students found for this selection.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        const sectionSelect = document.getElementById('section_id');
        
        if (classSelect && sectionSelect) {
            const originalSectionOptions = Array.from(sectionSelect.options);

            function filterSections() {
                const selectedClassId = classSelect.value;
                const currentSectionValue = sectionSelect.value;
                
                // Clear current options
                sectionSelect.innerHTML = '';
                
                // Add options that match the selected class (or "All Sections")
                originalSectionOptions.forEach(option => {
                    if (option.value === '' || !selectedClassId || option.getAttribute('data-class-id') === selectedClassId) {
                        sectionSelect.appendChild(option);
                    }
                });
                
                // Try to keep the previously selected value if it's still available
                if (Array.from(sectionSelect.options).some(opt => opt.value === currentSectionValue)) {
                    sectionSelect.value = currentSectionValue;
                } else {
                    sectionSelect.value = '';
                }
            }

            classSelect.addEventListener('change', filterSections);
            // Run on load to set initial state if class is already selected
            filterSections();
        }
    });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/transport/report.blade.php ENDPATH**/ ?>