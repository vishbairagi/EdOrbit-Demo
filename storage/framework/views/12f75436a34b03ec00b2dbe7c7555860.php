<?php $__env->startSection('header', 'Assign Students to Transport Routes'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Assign Students</h2>
        <p class="text-xs text-body"><?php echo e($school->name ?? 'School'); ?> · <?php echo e($session->name ?? 'Session'); ?></p>
    </div>
</div>


<div class="mb-5 rounded-lg border border-gray-200 bg-white p-5">
    <form method="GET" action="<?php echo e(route('transport.assign-students.index')); ?>" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="class_id" class="mb-1 block text-sm font-medium text-body">Class</label>
            <select name="class_id" id="class_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy w-48" onchange="this.form.submit()">
                <option value="">All Classes</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" <?php echo e(request('class_id') == $c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label for="section_id" class="mb-1 block text-sm font-medium text-body">Section</label>
            <select name="section_id" id="section_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy w-48" onchange="this.form.submit()">
                <option value="">All Sections</option>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($section->id); ?>" data-class-id="<?php echo e($section->school_class_id); ?>" <?php echo e(request('section_id') == $section->id ? 'selected' : ''); ?>>
                        <?php echo e($section->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                <i data-lucide="filter" class="h-4 w-4"></i> Filter
            </button>
            <a href="<?php echo e(route('transport.assign-students.index')); ?>" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-gray-50">
                Clear
            </a>
        </div>
    </form>
</div>

<div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
    
    <div id="bulkToolbar" class="hidden mb-3 mx-5 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('transport.assign-students.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
            <?php echo csrf_field(); ?>
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear Transport Assignment
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear Selection</button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-3 text-center w-10">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Admission No</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Enrollment Number</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Student Name</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Class Section</th>
                    <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">Current Route</th>
                    <th class="px-4 py-3 text-right font-semibold text-white uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-center w-10">
                        <input type="checkbox" value="<?php echo e($student->id); ?>" onchange="updateBulkToolbar()"
                            class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                    </td>
                    <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->admission_no); ?></td>
                    <td class="px-4 py-3 font-bold text-heading"><?php echo e($student->roll_number ?? '—'); ?></td>
                    <td class="px-4 py-3 font-bold text-heading">
                        <?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?>

                    </td>
                    <td class="px-4 py-3 font-bold text-heading">
                        <?php echo e($student->schoolClass->name ?? 'N/A'); ?> 
                        <?php if($student->section): ?>
                            (<?php echo e($student->section->name); ?>)
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-bold text-heading">
                        <?php if($student->transportRoutes->count() > 0): ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                <?php echo e($student->transportRoutes->first()->route_name); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-gray-400 italic">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="openAssignModal(<?php echo e($student->id); ?>, <?php echo e($student->transportRoutes->first() ? $student->transportRoutes->first()->id : 'null'); ?>)" class="inline-flex items-center gap-1 rounded-md bg-navy px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-dark">
                            <i data-lucide="bus" class="h-3 w-3"></i> Assign Route
                        </button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-body">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="users" class="mb-2 h-8 w-8 text-gray-300"></i>
                            <p>No students found for this session.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="border-t border-gray-200 px-5 py-4">
        <?php echo e($students->links()); ?>

    </div>
</div>


<div id="assignModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeAssignModal()">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-visible" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-heading">Assign Transport Route</h3>
            <button type="button" onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="assignForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label for="modal_route_id" class="block text-sm font-medium text-body mb-1">Select Route</label>
                    <select id="modal_route_id" name="route_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                        <option value="">None (Unassign)</option>
                        <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($route->id); ?>"><?php echo e($route->route_name); ?> (Fare: <?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e($route->route_fare); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeAssignModal()" class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-200 hover:bg-gray-50 text-body transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-navy hover:opacity-90 transition-opacity">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAssignModal(studentId, currentRouteId) {
        let url = "<?php echo e(route('transport.assign-students.assign', 'ID_PLACEHOLDER')); ?>";
        document.getElementById('assignForm').action = url.replace('ID_PLACEHOLDER', studentId);
        let select = document.getElementById('modal_route_id');
        if (currentRouteId) {
            select.value = currentRouteId;
        } else {
            select.value = "";
        }
        
        document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        const sectionSelect = document.getElementById('section_id');
        
        if (classSelect && sectionSelect) {
            const originalSectionOptions = Array.from(sectionSelect.options);

            function filterSections() {
                const selectedClassId = classSelect.value;
                const currentSectionValue = sectionSelect.value;
                
                sectionSelect.innerHTML = '';
                
                originalSectionOptions.forEach(option => {
                    if (option.value === '' || !selectedClassId || option.getAttribute('data-class-id') === selectedClassId) {
                        sectionSelect.appendChild(option);
                    }
                });
                
                if (Array.from(sectionSelect.options).some(opt => opt.value === currentSectionValue)) {
                    sectionSelect.value = currentSectionValue;
                } else {
                    sectionSelect.value = '';
                }
            }

            classSelect.addEventListener('change', function(e) {
                // Because onchange="this.form.submit()" is on the element, 
                // it might submit before filtering, but since it reloads the page, 
                // the filtering on DOMContentLoaded is enough to show correct options.
            });
            
            filterSections();
        }
    });

    // Bulk Select JS
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
        if (!confirm(checked.length + ' student(s) ko unassign kiya jayega aur inki transport invoice (agar hai) delete ho jayegi. Sure hain?')) return false;
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/transport/assign/index.blade.php ENDPATH**/ ?>