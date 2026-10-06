<?php $__env->startSection('header', 'Classes — ' . $school->name . ' (' . $session->name . ')'); ?>

<?php $__env->startSection('content'); ?>


<div id="classModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 id="classModalTitle" class="text-base font-bold text-heading">Add Class</h3>
            <button onclick="closeClassModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="classForm" method="POST" action="<?php echo e(route('sm-school.classes.store')); ?>" class="p-6 space-y-4">
            <?php echo csrf_field(); ?>
            <span id="classMethodField"></span>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="className" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="e.g. Class 1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class Group</label>
                <select name="class_group_id" id="classGroup" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                    <option value="">— No Group —</option>
                    <?php $__currentLoopData = $classGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                <input type="number" name="order" id="classOrder" value="0" min="0" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeClassModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>


<div id="sectionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Add Section</h3>
            <button onclick="closeSectionModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="sectionForm" method="POST" action="" class="p-6 space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Section Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="e.g. A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                <input type="number" name="capacity" min="1" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy" placeholder="Optional">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeSectionModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Add Section</button>
            </div>
        </form>
    </div>
</div>


<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Classes</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="<?php echo e(route('sm-school.classes.import')); ?>" enctype="multipart/form-data" class="p-6 space-y-4">
            <?php echo csrf_field(); ?>
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="<?php echo e(route('sm-school.classes.import-demo')); ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i> Sample CSV
                </a>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload CSV File <span class="text-red-500">*</span></label>
                <input type="file" name="csv_file" accept=".csv" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Import Data</button>
            </div>
        </form>
    </div>
</div>

<div class="mb-3 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-bold text-heading">Classes</h3>
        <p class="text-xs text-body mt-0.5">Session: <?php echo e($session->name); ?> &nbsp;·&nbsp; <?php echo e($school->name); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('sm-school.classes.export')); ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1.5"></i> Export
        </a>
        <button onclick="openImportModal()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1.5"></i> Import
        </button>
        <button onclick="openClassModal()" class="inline-flex items-center px-3 py-1.5 bg-navy hover:bg-primary-dark text-white text-xs font-medium rounded-lg shadow-sm transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5"></i> Add Class
        </button>
    </div>
</div>


<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('sm-school.classes.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
        <?php echo csrf_field(); ?>
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 text-red-600 cursor-pointer accent-red-600"
                            title="Select All">
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Class Name</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Class Group</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Sections</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors row-item" data-id="<?php echo e($class->id); ?>">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="<?php echo e($class->id); ?>" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 text-red-600 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-heading"><?php echo e($class->name); ?></td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <?php if($class->classGroup): ?>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-primary/10 text-navy border border-primary/20"><?php echo e($class->classGroup->name); ?></span>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex flex-wrap gap-1.5 items-center">
                                <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-blue-50 text-blue-700 rounded border border-blue-200">
                                        <?php echo e($section->name); ?>

                                        <form action="<?php echo e(route('sm-school.sections.destroy', $section)); ?>" method="POST" class="inline" onsubmit="return confirm('Delete this section?');">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-blue-400 hover:text-red-500 transition-colors ml-0.5">
                                                <i data-lucide="x" class="w-2.5 h-2.5"></i>
                                            </button>
                                        </form>
                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <button onclick="openSectionModal(<?php echo e($class->id); ?>)" class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-navy border border-dashed border-navy rounded hover:bg-navy hover:text-white transition-colors">
                                    <i data-lucide="plus" class="w-3 h-3 mr-0.5"></i> Add Section
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button onclick="editClass(<?php echo e($class->id); ?>, '<?php echo e(addslashes($class->name)); ?>', <?php echo e($class->class_group_id ?? 'null'); ?>)" class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form action="<?php echo e(route('sm-school.classes.destroy', $class)); ?>" method="POST" onsubmit="return confirm('Delete this class?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                            <p class="font-medium text-heading">No classes yet for this session.</p>
                            <p class="text-sm text-body mt-1">Classes are isolated per school and session.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // ── Bulk Select Logic ──────────────────────────────────────────
    function toggleSelectAll(masterCb) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = masterCb.checked;
            cb.closest('tr').classList.toggle('bg-red-50', masterCb.checked);
        });
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const toolbar  = document.getElementById('bulkToolbar');
        const countEl  = document.getElementById('selectedCount');
        const selectAll = document.getElementById('selectAll');
        const all      = document.querySelectorAll('.row-checkbox');

        // Highlight selected rows
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.closest('tr').classList.toggle('bg-red-50', cb.checked);
        });

        if (checked.length > 0) {
            toolbar.classList.remove('hidden');
            countEl.textContent = checked.length + ' selected';
        } else {
            toolbar.classList.add('hidden');
        }

        // Update master checkbox state
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
        selectAll.checked = checked.length === all.length && all.length > 0;
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('tr').classList.remove('bg-red-50');
        });
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAll').indeterminate = false;
        document.getElementById('bulkToolbar').classList.add('hidden');
    }

    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;

        if (!confirm(checked.length + ' class(es) delete ho jaayenge. Kya aap sure hain?')) return false;

        // Populate hidden IDs into form
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        return true;
    }
    // ──────────────────────────────────────────────────────────────

    function openClassModal(title = 'Add Class', action = '<?php echo e(route('sm-school.classes.store')); ?>', method = 'POST', name = '', groupId = null) {
        document.getElementById('classModalTitle').textContent = title;
        document.getElementById('classForm').action = action;
        document.getElementById('classMethodField').innerHTML = method !== 'POST' ? '<input type="hidden" name="_method" value="' + method + '">' : '';
        document.getElementById('className').value = name;
        if (groupId) document.getElementById('classGroup').value = groupId;
        document.getElementById('classModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function editClass(id, name, groupId) {
        openClassModal('Edit Class', '/sm-school/classes/' + id, 'PUT', name, groupId);
    }

    function closeClassModal() {
        document.getElementById('classModal').classList.add('hidden');
    }

    function openSectionModal(classId) {
        document.getElementById('sectionForm').action = '/sm-school/classes/' + classId + '/sections';
        document.getElementById('sectionModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.add('hidden');
    }

    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    document.getElementById('classModal').addEventListener('click', function(e) { if (e.target === this) closeClassModal(); });
    document.getElementById('sectionModal').addEventListener('click', function(e) { if (e.target === this) closeSectionModal(); });
    document.getElementById('importModal').addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-school/classes/index.blade.php ENDPATH**/ ?>