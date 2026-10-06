<?php $__env->startSection('header', 'Study Materials'); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center text-white font-bold text-base">
        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
        Study Materials
        <span class="ml-2 text-white/70 text-xs font-normal">Session: <?php echo e($session->name); ?></span>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('academic.study-materials.export', request()->all())); ?>"
           class="inline-flex items-center px-3 py-1.5 bg-white/10 text-white border border-white/20 text-xs font-semibold rounded-lg hover:bg-white/20 transition-colors" title="Export to CSV">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1.5"></i> Export CSV
        </a>
        <button onclick="document.getElementById('importMaterialModal').classList.remove('hidden')"
           class="inline-flex items-center px-3 py-1.5 bg-white/10 text-white border border-white/20 text-xs font-semibold rounded-lg hover:bg-white/20 transition-colors" title="Import from CSV">
            <i data-lucide="upload-cloud" class="w-3.5 h-3.5 mr-1.5"></i> Import CSV
        </button>
        <button onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
           class="inline-flex items-center px-3 py-1.5 bg-white text-navy text-xs font-semibold rounded-lg hover:bg-gray-100 transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
            Add Material
        </button>
    </div>
</div>

<?php if(session('import_errors')): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-r-xl shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Some rows were skipped during import:</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="<?php echo e(route('academic.study-materials.index')); ?>" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
            
            <div class="w-full md:w-56">
                <label class="block text-xs font-medium text-body mb-1">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Classes</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" <?php echo e(request('class_id') == $c->id ? 'selected' : ''); ?>>
                            <?php echo e($c->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="w-full md:w-44">
                <label class="block text-xs font-medium text-body mb-1">Material Type:</label>
                <select name="type" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Types</option>
                    <option value="pdf" <?php echo e(request('type') === 'pdf' ? 'selected' : ''); ?>>PDF</option>
                    <option value="image" <?php echo e(request('type') === 'image' ? 'selected' : ''); ?>>Image</option>
                    <option value="video" <?php echo e(request('type') === 'video' ? 'selected' : ''); ?>>Video</option>
                    <option value="description" <?php echo e(request('type') === 'description' ? 'selected' : ''); ?>>Notes / Description</option>
                </select>
            </div>

            
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-body mb-1">Search Material:</label>
                <div class="relative">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Book name or description..."
                           class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                    <?php if(request('search')): ?>
                        <a href="<?php echo e(route('academic.study-materials.index', array_filter(['class_id' => request('class_id'), 'type' => request('type')]))); ?>"
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
                    Filter
                </button>
                <?php if(request('class_id') || request('type') || request('search')): ?>
                    <a href="<?php echo e(route('academic.study-materials.index')); ?>"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium text-body hover:bg-gray-50 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg self-end md:self-center">
            <button type="button" onclick="switchView('table')" id="btnViewTable"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy">
                <i data-lucide="list" class="w-3.5 h-3.5 inline-block mr-1"></i> Single Table
            </button>
            <button type="button" onclick="switchView('grouped')" id="btnViewGrouped"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 inline-block mr-1"></i> Grouped by Class
            </button>
        </div>
    </form>
</div>


<?php if($materials->isEmpty()): ?>
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="file-text" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No study materials found.</p>
        <p class="text-sm text-body mt-1">Try adjusting your filters or upload new materials.</p>
        <button onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
           class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i> Add Material
        </button>
    </div>
<?php else: ?>

    
    <div id="viewTableContainer" class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between" style="background:#F5F1EB;">
            <span class="text-xs font-semibold text-body">Total Materials: <strong><?php echo e($materials->count()); ?></strong></span>
        </div>
        
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('academic.study-materials.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
        <?php echo csrf_field(); ?>
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Book / Material Name</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Class</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Section</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-center text-white font-semibold text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 transition-colors" data-id="<?php echo e($material->id); ?>">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="<?php echo e($material->id); ?>" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                            <td class="px-4 py-3 text-body"><?php echo e($index + 1); ?></td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-heading"><?php echo e($material->book_name); ?></p>
                                <?php if($material->title): ?>
                                    <p class="text-xs text-body mt-0.5"><?php echo e($material->title); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i data-lucide="graduation-cap" class="w-3 h-3 mr-1"></i>
                                    <?php echo e($material->schoolClass?->name ?? '—'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-body"><?php echo e($material->section?->name ?? 'All Sections'); ?></td>
                            <td class="px-4 py-3">
                                <?php
                                    $typeColors = [
                                        'pdf'         => 'bg-red-100 text-red-700',
                                        'image'       => 'bg-green-100 text-green-700',
                                        'video'       => 'bg-purple-100 text-purple-700',
                                        'description' => 'bg-blue-100 text-blue-700',
                                    ];
                                    $typeIcons = ['pdf' => 'file-text', 'image' => 'image', 'video' => 'video', 'description' => 'align-left'];
                                ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold <?php echo e($typeColors[$material->type] ?? 'bg-gray-100 text-gray-600'); ?>">
                                    <i data-lucide="<?php echo e($typeIcons[$material->type] ?? 'file'); ?>" class="w-3 h-3"></i>
                                    <?php echo e(ucfirst($material->type)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-body text-xs truncate max-w-[150px]" title="<?php echo e($material->description); ?>">
                                <?php echo e($material->description ?: '—'); ?>

                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <?php if($material->file_path): ?>
                                        <?php $url = str_starts_with($material->file_path, 'http') ? $material->file_path : Storage::url($material->file_path); ?>
                                        <a href="<?php echo e($url); ?>" target="_blank"
                                           class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded-md hover:opacity-90 bg-navy">
                                            <i data-lucide="eye" class="w-3 h-3 mr-1"></i> View
                                        </a>
                                    <?php endif; ?>
                                    <button onclick='openEditMaterialModal(<?php echo json_encode($material, 15, 512) ?>)'
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded-md hover:opacity-90"
                                            style="background:#3498db;">
                                        <i data-lucide="edit" class="w-3 h-3 mr-1"></i> Edit
                                    </button>
                                    <form method="POST" action="<?php echo e(route('academic.study-materials.destroy', $material)); ?>"
                                          onsubmit="return confirm('Delete this study material?')" class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded-md hover:opacity-90"
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
    </div>

    
    <div id="viewGroupedContainer" class="hidden space-y-6 mb-6">
        <?php $__currentLoopData = $materialsByClass; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className => $classMaterials): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between" style="background:#F5F1EB;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-navy flex items-center justify-center text-white text-xs font-bold">
                            <i data-lucide="book" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-base text-heading"><?php echo e($className); ?></h3>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                            <?php echo e($classMaterials->count()); ?> material(s)
                        </span>
                    </div>
                    <button onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
                       class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white rounded-md hover:opacity-90 transition bg-navy">
                        <i data-lucide="plus" class="w-3 h-3"></i> Add Material
                    </button>
                </div>

                
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('academic.study-materials.bulk-destroy')); ?>" onsubmit="return confirmBulkDelete()">
        <?php echo csrf_field(); ?>
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                                <th class="px-4 py-2.5 text-left w-12">#</th>
                                <th class="px-4 py-2.5 text-left">Book / Material Name</th>
                                <th class="px-4 py-2.5 text-left">Section</th>
                                <th class="px-4 py-2.5 text-left">Type</th>
                                <th class="px-4 py-2.5 text-left">Description</th>
                                <th class="px-4 py-2.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $classMaterials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-3 text-body text-xs font-medium"><?php echo e($idx + 1); ?></td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-heading"><?php echo e($material->book_name); ?></p>
                                        <?php if($material->title): ?>
                                            <p class="text-xs text-body mt-0.5"><?php echo e($material->title); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-body text-xs"><?php echo e($material->section?->name ?? 'All Sections'); ?></td>
                                    <td class="px-4 py-3">
                                        <?php
                                            $typeColors = [
                                                'pdf'         => 'bg-red-100 text-red-700',
                                                'image'       => 'bg-green-100 text-green-700',
                                                'video'       => 'bg-purple-100 text-purple-700',
                                                'description' => 'bg-blue-100 text-blue-700',
                                            ];
                                            $typeIcons = ['pdf' => 'file-text', 'image' => 'image', 'video' => 'video', 'description' => 'align-left'];
                                        ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold <?php echo e($typeColors[$material->type] ?? 'bg-gray-100 text-gray-600'); ?>">
                                            <i data-lucide="<?php echo e($typeIcons[$material->type] ?? 'file'); ?>" class="w-3 h-3"></i>
                                            <?php echo e(ucfirst($material->type)); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-body text-xs truncate max-w-[150px]" title="<?php echo e($material->description); ?>">
                                        <?php echo e($material->description ?: '—'); ?>

                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <?php if($material->file_path): ?>
                                                <?php $url = str_starts_with($material->file_path, 'http') ? $material->file_path : Storage::url($material->file_path); ?>
                                                <a href="<?php echo e($url); ?>" target="_blank"
                                                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded hover:opacity-90 bg-navy">
                                                    <i data-lucide="eye" class="w-3 h-3 mr-1"></i> View
                                                </a>
                                            <?php endif; ?>
                                            <button onclick='openEditMaterialModal(<?php echo json_encode($material, 15, 512) ?>)'
                                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded hover:opacity-90"
                                                    style="background:#3498db;">
                                                <i data-lucide="edit" class="w-3 h-3 mr-1"></i> Edit
                                            </button>
                                            <form method="POST" action="<?php echo e(route('academic.study-materials.destroy', $material)); ?>"
                                                  onsubmit="return confirm('Delete this study material?')" class="inline">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded hover:opacity-90"
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
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

<?php endif; ?>


<div id="addMaterialModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5 text-navy"></i>
                Add Study Material
            </h3>
            <button type="button" onclick="closeAddMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" action="<?php echo e(route('academic.study-materials.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Book Name <span class="text-red-500">*</span></label>
                    <input type="text" name="book_name" required placeholder="e.g. Mathematics Part-1"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Title / Topic (optional)</label>
                    <input type="text" name="title" placeholder="e.g. Chapter 1 - Number System"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Class <span class="text-red-500">*</span></label>
                        <select name="school_class_id" required onchange="loadSections(this.value)"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— Select Class —</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Section (optional)</label>
                        <select name="section_id" id="modalSectionSelect"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— All Sections —</option>
                        </select>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Material Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1.5">
                        <?php $__currentLoopData = ['pdf' => 'PDF', 'image' => 'Image', 'video' => 'Video', 'description' => 'Notes/Text']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-navy has-[:checked]:bg-navy/5">
                                <input type="radio" name="type" value="<?php echo e($value); ?>" onchange="handleTypeChange('<?php echo e($value); ?>')"
                                       <?php echo e($value === 'pdf' ? 'checked' : ''); ?>

                                       class="text-navy">
                                <span class="text-sm font-medium text-heading"><?php echo e($label); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div id="fileSection">
                    <label class="block text-sm font-medium text-heading mb-1">Upload File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" id="fileInput"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    <p class="text-xs text-body mt-1" id="fileHint">Max 50MB. PDF files only.</p>
                </div>

                
                <div id="descriptionSection">
                    <label class="block text-sm font-medium text-heading mb-1">Notes / Description <span class="text-body font-normal text-xs">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Write your notes or description here..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Upload Material
                </button>
                <button type="button" onclick="closeAddMaterialModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<div id="importMaterialModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-navy"></i>
                Import Study Materials
            </h3>
            <button type="button" onclick="closeImportMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" action="<?php echo e(route('academic.study-materials.import')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            
            <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded-lg mb-4 border border-blue-100">
                <p class="font-semibold mb-1"><i data-lucide="info" class="w-3.5 h-3.5 inline mr-1"></i> Instructions:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-[11px]">
                    <li>Upload a CSV file with headers.</li>
                    <li>Required columns: <b>Book Name, Class Name, Material Type</b>.</li>
                    <li><b>Material Type</b> must be one of: <i>pdf, image, video, description</i>.</li>
                    <li><b>File URL</b> can be used instead of uploading physical files.</li>
                </ul>
                <div class="mt-2 pt-2 border-t border-blue-200">
                    <a href="<?php echo e(route('academic.study-materials.import-demo')); ?>" class="inline-flex items-center font-semibold hover:underline">
                        <i data-lucide="download" class="w-3 h-3 mr-1"></i> Download Sample CSV
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Select CSV File <span class="text-red-500">*</span></label>
                    <input type="file" name="csv_file" required accept=".csv,.txt"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Import Data
                </button>
                <button type="button" onclick="closeImportMaterialModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<div id="editMaterialModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="edit" class="w-5 h-5 text-navy"></i>
                Edit Study Material
            </h3>
            <button type="button" onclick="closeEditMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" id="editMaterialForm" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="space-y-4">
                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Book Name <span class="text-red-500">*</span></label>
                    <input type="text" name="book_name" id="edit_book_name" required placeholder="e.g. Mathematics Part-1"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Title / Topic (optional)</label>
                    <input type="text" name="title" id="edit_title" placeholder="e.g. Chapter 1 - Number System"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Class <span class="text-red-500">*</span></label>
                        <select name="school_class_id" id="edit_school_class_id" required onchange="loadSections(this.value, 'editModalSectionSelect')"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— Select Class —</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Section (optional)</label>
                        <select name="section_id" id="editModalSectionSelect"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— All Sections —</option>
                        </select>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Material Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1.5">
                        <?php $__currentLoopData = ['pdf' => 'PDF', 'image' => 'Image', 'video' => 'Video', 'description' => 'Notes/Text']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-navy has-[:checked]:bg-navy/5">
                                <input type="radio" name="type" value="<?php echo e($value); ?>" id="edit_type_<?php echo e($value); ?>" onchange="handleTypeChange('<?php echo e($value); ?>', 'edit')"
                                       class="text-navy">
                                <span class="text-sm font-medium text-heading"><?php echo e($label); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div id="edit_fileSection">
                    <label class="block text-sm font-medium text-heading mb-1">Update File <span class="text-xs text-gray-400 font-normal">(leave blank to keep current)</span></label>
                    <input type="file" name="file" id="editFileInput"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    <p class="text-xs text-body mt-1" id="edit_fileHint">Max 50MB.</p>
                </div>

                
                <div id="edit_descriptionSection">
                    <label class="block text-sm font-medium text-heading mb-1">Notes / Description <span class="text-body font-normal text-xs">(optional)</span></label>
                    <textarea name="description" id="edit_description" rows="3" placeholder="Write your notes or description here..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Save Changes
                </button>
                <button type="button" onclick="closeEditMaterialModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeAddMaterialModal() {
        document.getElementById('addMaterialModal').classList.add('hidden');
    }

    function closeImportMaterialModal() {
        document.getElementById('importMaterialModal').classList.add('hidden');
    }

    function closeEditMaterialModal() {
        document.getElementById('editMaterialModal').classList.add('hidden');
    }

    function handleTypeChange(type, prefix = '') {
        const fileSection = document.getElementById(prefix ? prefix + '_fileSection' : 'fileSection');
        const fileHint    = document.getElementById(prefix ? prefix + '_fileHint' : 'fileHint');

        if (type === 'description') {
            fileSection.classList.add('hidden');
        } else {
            fileSection.classList.remove('hidden');
            if (type === 'pdf') fileHint.textContent = 'Max 50MB. PDF files only.';
            else if (type === 'image') fileHint.textContent = 'Max 50MB. JPG, PNG, GIF etc.';
            else if (type === 'video') fileHint.textContent = 'Max 50MB. MP4, AVI etc.';
        }
    }

    function loadSections(classId, selectId = 'modalSectionSelect', selectedSectionId = null) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">— All Sections —</option>';
        if (!classId) return;

        fetch('/academic/study-materials/sections/' + classId)
            .then(r => r.json())
            .then(sections => {
                sections.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    if (selectedSectionId == s.id) opt.selected = true;
                    select.appendChild(opt);
                });
            });
    }

    function openEditMaterialModal(material) {
        document.getElementById('editMaterialForm').action = '/academic/study-materials/' + material.id;
        document.getElementById('edit_book_name').value = material.book_name || '';
        document.getElementById('edit_title').value = material.title || '';
        document.getElementById('edit_description').value = material.description || '';
        
        document.getElementById('edit_school_class_id').value = material.school_class_id;
        loadSections(material.school_class_id, 'editModalSectionSelect', material.section_id);

        document.getElementById('edit_type_' + material.type).checked = true;
        handleTypeChange(material.type, 'edit');

        document.getElementById('editMaterialModal').classList.remove('hidden');
    }

    function switchView(mode) {
        const table = document.getElementById('viewTableContainer');
        const grouped = document.getElementById('viewGroupedContainer');
        const btnTable = document.getElementById('btnViewTable');
        const btnGrouped = document.getElementById('btnViewGrouped');

        if (mode === 'grouped') {
            table?.classList.add('hidden');
            grouped?.classList.remove('hidden');
            btnGrouped.className = "px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy";
            btnTable.className = "px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all";
            localStorage.setItem('materials_view_mode', 'grouped');
        } else {
            table?.classList.remove('hidden');
            grouped?.classList.add('hidden');
            btnTable.className = "px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy";
            btnGrouped.className = "px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all";
            localStorage.setItem('materials_view_mode', 'table');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const pref = localStorage.getItem('materials_view_mode');
        if (pref === 'grouped') {
            switchView('grouped');
        }
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-academic/study-materials/index.blade.php ENDPATH**/ ?>