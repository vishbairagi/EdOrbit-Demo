<?php $__env->startSection('header', 'Subject Types'); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <div class="flex items-center text-white font-bold text-base">
            <i data-lucide="tag" class="w-4 h-4 mr-2"></i>
            Subject Types
        </div>
        
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
              style="background:rgba(255,255,255,0.18); color:#fff; border:1px solid rgba(255,255,255,0.3);">
            <i data-lucide="calendar" class="w-3 h-3"></i>
            <?php echo e($session->name); ?>

        </span>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('academic.subject-types.export')); ?>"
           class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i>
            Export CSV
        </a>
        <button onclick="document.getElementById('importTypeModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i>
            Import CSV
        </button>
        <button onclick="document.getElementById('addTypeModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1.5 bg-white text-navy text-xs font-semibold rounded-lg hover:bg-gray-100 transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
            Add Type
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


<?php if($subjectTypes->isEmpty()): ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="tag" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No subject types found for <span class="text-navy"><?php echo e($session->name); ?></span>.</p>
        <p class="text-sm text-body mt-1">Add types like Theory, Practical, Language, Co-curricular etc. for this session.</p>
        <button onclick="document.getElementById('addTypeModal').classList.remove('hidden')"
                class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i> Add First Type
        </button>
    </div>
<?php else: ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Type Name</th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-white font-semibold text-xs uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__currentLoopData = $subjectTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 transition-colors" data-id="<?php echo e($type->id); ?>">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="<?php echo e($type->id); ?>" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-3 text-body"><?php echo e($index + 1); ?></td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-heading"><?php echo e($type->name); ?></span>
                        </td>
                        <td class="px-4 py-3 text-body text-xs font-bold"><?php echo e($type->description ?? '—'); ?></td>
                        <td class="px-4 py-3">
                            <?php if($type->is_active): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openEditType(<?php echo e($type->id); ?>, '<?php echo e(addslashes($type->name)); ?>', '<?php echo e(addslashes($type->description ?? '')); ?>', <?php echo e($type->is_active ? 1 : 0); ?>)"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-white rounded-md hover:opacity-90"
                                        style="background:#1a4a6e;">
                                    <i data-lucide="pencil" class="w-3 h-3 mr-1"></i> Edit
                                </button>
                                <form method="POST" action="<?php echo e(route('academic.subject-types.destroy', $type)); ?>"
                                      onsubmit="return confirm('Delete type <?php echo e($type->name); ?>? Subjects using this type will be unlinked.')" class="inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-white rounded-md hover:opacity-90"
                                            style="background:#c0392b;">
                                        <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<div id="addTypeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="tag" class="w-5 h-5 text-navy"></i>
                Add Subject Type
            </h3>
            <button onclick="document.getElementById('addTypeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('academic.subject-types.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Type Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Theory, Practical, Language"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Description (optional)</label>
                    <input type="text" name="description" placeholder="Short description"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                    Create Type
                </button>
                <button type="button" onclick="document.getElementById('addTypeModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<div id="editTypeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="pencil" class="w-5 h-5 text-navy"></i>
                Edit Subject Type
            </h3>
            <button onclick="document.getElementById('editTypeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editTypeForm" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editTypeName" name="name" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Description (optional)</label>
                    <input type="text" id="editTypeDesc" name="description"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="editTypeActive" name="is_active" value="1" class="rounded">
                    <label for="editTypeActive" class="text-sm font-medium text-heading">Active</label>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                    Update Type
                </button>
                <button type="button" onclick="document.getElementById('editTypeModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditType(id, name, desc, isActive) {
        document.getElementById('editTypeForm').action = '/academic/subject-types/' + id;
        document.getElementById('editTypeName').value = name;
        document.getElementById('editTypeDesc').value = desc;
        document.getElementById('editTypeActive').checked = isActive === 1;
        document.getElementById('editTypeModal').classList.remove('hidden');
    }

    function handleTypeDrop(e) {
        e.preventDefault();
        document.getElementById('typeDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('typeCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('typeFileName').textContent = file.name;
        }
    }
    document.getElementById('importTypeModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>


<div id="importTypeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background:#fff;">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Subject Types from CSV
            </div>
            <button onclick="document.getElementById('importTypeModal').classList.add('hidden')"
                    class="text-white opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-sm font-semibold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-xs mt-0.5" style="color:#a07040;">Use this template to prepare your CSV</p>
                </div>
                <a href="<?php echo e(route('academic.subject-types.import-demo')); ?>"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Sample CSV
                </a>
            </div>

            
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-semibold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <span>1. Type Name *</span>
                    <span>2. Description</span>
                    <span>3. Status</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Status: Active or Inactive (default Active). Existing types matched by name will be updated.</p>
            </div>

            
            <form method="POST" action="<?php echo e(route('academic.subject-types.import')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label class="block text-sm font-medium mb-2 text-gray-800">Select CSV File</label>
                <div id="typeDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('typeCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleTypeDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-sm font-medium" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="typeFileName" class="text-xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="typeCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('typeFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importTypeModal').classList.add('hidden')"
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-academic/subject-types/index.blade.php ENDPATH**/ ?>