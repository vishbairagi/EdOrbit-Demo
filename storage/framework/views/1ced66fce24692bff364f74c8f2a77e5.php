<?php $__env->startSection('header', 'Classes & Sections'); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex items-center text-white font-bold text-base">
            <i data-lucide="layers" class="w-4 h-4 mr-2"></i>
            Classes and Sections
        </div>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
              style="background:rgba(255,255,255,0.18); color:#fff; border:1px solid rgba(255,255,255,0.3);">
            <i data-lucide="calendar" class="w-3 h-3"></i>
            <?php echo e($session->name); ?>

        </span>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('academic.class-sections.export')); ?>"
           class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i>
            Export CSV
        </a>
        <button onclick="document.getElementById('importSectionModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i>
            Import CSV
        </button>
    </div>
</div>


<?php if(session('success')): ?>
<div class="mb-5 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    <?php echo e(session('success')); ?>

</div>
<?php endif; ?>


<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="<?php echo e(route('academic.class-sections.index')); ?>" class="flex flex-col md:flex-row items-center gap-3">
        <div class="w-full md:w-80">
            <label class="block text-xs font-medium text-body mb-1">Search Class Name:</label>
            <div class="relative">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="e.g. Class 1, Class 6..."
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                <?php if(request('search')): ?>
                    <a href="<?php echo e(route('academic.class-sections.index')); ?>"
                       class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex items-end mt-auto gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition bg-navy">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Search Class
            </button>
            <?php if(request('search')): ?>
                <a href="<?php echo e(route('academic.class-sections.index')); ?>"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium text-body hover:bg-gray-50 transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if($classes->isEmpty()): ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="layers" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No classes found.</p>
        <p class="text-sm text-body mt-1">Create classes first from <a href="<?php echo e(route('sm-school.classes.index')); ?>" class="text-navy underline">SM School → Classes</a>.</p>
    </div>
<?php else: ?>
    
    
    <div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('academic.class-sections.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete(event)">
            <?php echo csrf_field(); ?>
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-3 w-10 text-center">
                        <input type="checkbox" id="selectAllClasses" class="rounded border-gray-300 text-navy focus:ring-navy cursor-pointer" onclick="toggleAllClasses(this)">
                    </th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Class</th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Number of Sections</th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Total Students</th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 transition-colors" id="class-row-<?php echo e($class->id); ?>">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" value="<?php echo e($class->id); ?>" class="class-checkbox rounded border-gray-300 text-navy focus:ring-navy cursor-pointer" onchange="updateBulkDeleteButton()">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button onclick="toggleSections(<?php echo e($class->id); ?>)"
                                        class="w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 bg-navy" style="min-width:20px;">
                                    <i data-lucide="plus" class="w-3 h-3" id="toggle-icon-<?php echo e($class->id); ?>"></i>
                                </button>
                                <span class="font-semibold text-heading"><?php echo e($class->name); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-navy font-semibold"><?php echo e($class->sections->count()); ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold text-white bg-navy">
                                    Male: <?php echo e($class->male_students); ?>

                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold text-white" style="background:#1a9090;">
                                    Female: <?php echo e($class->female_students); ?>

                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold text-white" style="background:#7a4bca;">
                                    Total: <?php echo e($class->total_students); ?>

                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button onclick="openAddSection(<?php echo e($class->id); ?>, '<?php echo e($class->name); ?>')"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white rounded-md hover:opacity-90 transition-opacity bg-navy">
                                    Add New / Manage
                                </button>
                                <button onclick="toggleSections(<?php echo e($class->id); ?>)"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white rounded-md hover:opacity-90 transition-opacity"
                                        style="background:#1a7a9a;">
                                    View
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr id="sections-<?php echo e($class->id); ?>" class="hidden">
                        <td colspan="5" class="px-6 py-3 bg-gray-50">
                            <?php if($class->sections->isEmpty()): ?>
                                <p class="text-xs text-gray-400 italic">No sections yet. Add one above.</p>
                            <?php else: ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-md px-3 py-1.5 text-xs">
                                            <input type="checkbox" value="<?php echo e($section->id); ?>" class="section-checkbox rounded border-gray-300 text-navy focus:ring-navy cursor-pointer" onchange="updateBulkDeleteButton()">
                                            <span class="font-medium text-heading"><?php echo e($section->name); ?></span>
                                            <?php if($section->capacity): ?>
                                                <span class="text-gray-400">(cap: <?php echo e($section->capacity); ?>)</span>
                                            <?php endif; ?>
                                            <form method="POST" action="<?php echo e(route('academic.class-sections.sections.destroy', $section)); ?>"
                                                  onsubmit="return confirm('Delete section <?php echo e($section->name); ?>?')"
                                                  class="inline">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-red-400 hover:text-red-600 ml-1">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<div id="addSectionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading">Add Section to <span id="modalClassName" class="text-navy"></span></h3>
            <button onclick="closeAddSection()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="addSectionForm" method="POST">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Section Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. A, B, Rose"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Capacity (optional)</label>
                    <input type="number" name="capacity" min="1" placeholder="e.g. 40"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit"
                        class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:bg-primary-dark transition-colors">
                    Add Section
                </button>
                <button type="button" onclick="closeAddSection()"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAllClasses(source) {
        document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = source.checked);
        document.querySelectorAll('.section-checkbox').forEach(cb => cb.checked = source.checked);
        updateBulkDeleteButton();
    }

    function updateBulkDeleteButton() {
        const checkedClasses = document.querySelectorAll('.class-checkbox:checked').length;
        const checkedSections = document.querySelectorAll('.section-checkbox:checked').length;
        const total = checkedClasses + checkedSections;
        const toolbar = document.getElementById('bulkToolbar');
        const countEl = document.getElementById('selectedCount');
        
        if (total > 0) {
            toolbar.classList.remove('hidden');
            if(countEl) countEl.textContent = total + ' selected';
        } else {
            toolbar.classList.add('hidden');
        }
    }
    
    function clearSelection() {
        document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.section-checkbox').forEach(cb => cb.checked = false);
        const sa = document.getElementById('selectAllClasses');
        if (sa) sa.checked = false;
        updateBulkDeleteButton();
    }

    function confirmBulkDelete(e) {
        if (!confirm('Are you sure you want to delete the selected items?')) {
            e.preventDefault();
            return false;
        }
        
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        
        document.querySelectorAll('.class-checkbox:checked').forEach(cb => {
            container.insertAdjacentHTML('beforeend', `<input type="hidden" name="class_ids[]" value="${cb.value}">`);
        });
        
        document.querySelectorAll('.section-checkbox:checked').forEach(cb => {
            container.insertAdjacentHTML('beforeend', `<input type="hidden" name="section_ids[]" value="${cb.value}">`);
        });
        
        return true;
    }

    function toggleSections(classId) {
        const row = document.getElementById('sections-' + classId);
        const icon = document.getElementById('toggle-icon-' + classId);
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    }

    function openAddSection(classId, className) {
        document.getElementById('modalClassName').textContent = className;
        document.getElementById('addSectionForm').action = '/academic/class-sections/' + classId + '/sections';
        document.getElementById('addSectionModal').classList.remove('hidden');
    }

    function closeAddSection() {
        document.getElementById('addSectionModal').classList.add('hidden');
    }

    function handleSectionDrop(e) {
        e.preventDefault();
        document.getElementById('sectionDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('sectionCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('sectionFileName').textContent = file.name;
        }
    }
    
    document.getElementById('importSectionModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>


<div id="importSectionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background:#fff;">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Class Sections from CSV
            </div>
            <button onclick="document.getElementById('importSectionModal').classList.add('hidden')"
                    class="text-white opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-sm font-semibold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-xs mt-0.5" style="color:#a07040;">Use this template to prepare your CSV</p>
                </div>
                <a href="<?php echo e(route('academic.class-sections.import-demo')); ?>"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Sample CSV
                </a>
            </div>

            
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-semibold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <span>1. Class Name *</span>
                    <span>2. Section Name *</span>
                    <span>3. Capacity</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Class must already exist in this session. Existing sections matched by name will be updated.</p>
            </div>

            
            <form method="POST" action="<?php echo e(route('academic.class-sections.import')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label class="block text-sm font-medium mb-2 text-gray-800">Select CSV File</label>
                <div id="sectionDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('sectionCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleSectionDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-sm font-medium" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="sectionFileName" class="text-xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="sectionCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('sectionFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importSectionModal').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border"
                            style="border-color:#ddd; color:#555; background:#fff;">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-white bg-navy">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-academic/class-sections/index.blade.php ENDPATH**/ ?>