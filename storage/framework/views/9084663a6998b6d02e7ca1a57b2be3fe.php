<?php $__env->startSection('header', 'School Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-navy text-white">
            <i data-lucide="settings-2" class="h-5 w-5"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold leading-tight text-heading">School Settings</h2>
            <p class="text-xs text-body"><?php echo e($school->name); ?> - <?php echo e($session->name); ?></p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <label for="sessionFilter" class="text-xs font-medium text-body">Session</label>
        <select id="sessionFilter" class="rounded-lg border border-gray-200 py-1.5 pl-3 pr-8 text-xs focus:border-navy focus:ring-1 focus:ring-navy" onchange="window.location.href='?tab=<?php echo e($activeTab); ?>&session_id='+this.value">
            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s->id); ?>" <?php if($s->id === $session->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?php echo e(session('error')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <p><?php echo e($message); ?></p>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
<nav class="col-span-full flex gap-2 overflow-x-auto border-b border-gray-200 pb-2">
    <?php
        $tabs = [
            'general' => 'General',
            'payment-methods' => 'Payment Methods',
            'registration' => 'Registration',
            'dashboard' => 'Dashboard',
            'live-classes' => 'Live Classes',
            'card-background' => 'Card Background',
            'examination' => 'Examination',
        ];
    ?>
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('sm-school.settings.index', ['tab' => $tab, 'session_id' => $session->id])); ?>" class="shrink-0 rounded-md px-3 py-2 text-xs font-semibold <?php echo e($activeTab === $tab ? 'bg-navy text-white' : 'text-body hover:bg-gray-100'); ?>"><?php echo e($label); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>

<?php if($activeTab === 'registration'): ?>
<form action="<?php echo e(route('sm-school.settings.update')); ?>" method="POST" class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white mb-6">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Registration Settings</h3>
    </div>
    <?php echo csrf_field(); ?>
    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Category:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_student_category" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_student_category', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Student Category Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Type:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_student_type" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_student_type', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Student Type Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Middle Name:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_middle_name" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_middle_name', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Middle Name Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Blood Group:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_blood_group" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_blood_group', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Blood Group Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Religion:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_religion" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_religion', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Religion Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Caste / Sub Caste:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_caste" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_caste', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Caste Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Medium:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_medium" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_medium', '1') == '1' ? 'checked' : ''); ?>> <span class="text-sm">Show Medium Field</span>
            </div>
        </div>
        <div class="flex justify-end pt-4">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Options</button>
        </div>
    </div>
</form>
<div class="col-span-full grid grid-cols-1 gap-5 xl:grid-cols-2">
    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Current Category Options</h3>
            <p class="mt-1 text-xs text-body">Student categories available for <?php echo e($school->name); ?>.</p>
        </div>
        <div class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <i data-lucide="grip-vertical" class="h-4 w-4 shrink-0 text-gray-400"></i>
                            <span class="truncate rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold lowercase text-gray-600"><?php echo e(\Illuminate\Support\Str::slug($category->name, '_')); ?></span>
                            <span class="truncate text-sm font-semibold text-heading"><?php echo e($category->name); ?></span>
                            <span class="shrink-0 text-xs text-body"><?php echo e($category->students_count); ?> students</span>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <button type="button" onclick="document.getElementById('edit-category-<?php echo e($category->id); ?>').classList.toggle('hidden')" class="text-blue-600 transition hover:text-blue-800" title="Edit <?php echo e($category->name); ?>">
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </button>
                            <form action="<?php echo e(route('sm-school.settings.categories.destroy', $category)); ?>" method="POST" onsubmit="return confirm('Delete <?php echo e(addslashes($category->name)); ?>?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600 transition hover:text-red-800" title="Delete <?php echo e($category->name); ?>">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <form id="edit-category-<?php echo e($category->id); ?>" action="<?php echo e(route('sm-school.settings.categories.update', $category)); ?>" method="POST" class="mt-3 hidden rounded-md bg-gray-50 p-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <label for="category-name-<?php echo e($category->id); ?>" class="mb-1 block text-xs font-medium text-body">Display Label</label>
                        <div class="flex flex-wrap gap-2">
                            <input type="hidden" name="academic_session_id" value="<?php echo e($session->id); ?>">
                            <input id="category-name-<?php echo e($category->id); ?>" type="text" name="name" value="<?php echo e($category->name); ?>" required maxlength="100" class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-navy px-3 py-2 text-xs font-semibold text-white hover:bg-primary-dark">
                                <i data-lucide="check" class="h-3.5 w-3.5"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-5 py-12 text-center">
                    <i data-lucide="tags" class="mx-auto mb-2 h-9 w-9 text-gray-300"></i>
                    <p class="text-sm font-medium text-heading">No categories configured.</p>
                    <p class="mt-1 text-xs text-body">Add an option to make it available on student forms.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Add New Option</h3>
            <p class="mt-1 text-xs text-body">Create a category shown in student admission and edit forms.</p>
        </div>
        <form action="<?php echo e(route('sm-school.settings.categories.store')); ?>" method="POST" class="space-y-4 p-5">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="new-category-name" class="mb-1 block text-xs font-medium text-body">Display Label</label>
                    <input id="new-category-name" type="text" name="name" value="<?php echo e(old('name')); ?>" required maxlength="100" placeholder="e.g., OBC-A" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
                <div>
                    <label for="new-category-session" class="mb-1 block text-xs font-medium text-body">Session</label>
                    <select id="new-category-session" name="academic_session_id" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" required>
                        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>" <?php if($s->id === $session->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Add Option
            </button>
        </form>
    </section>
</div>

<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Student Types</h3>
        <p class="mt-1 text-xs text-body">Manage the options shown in student admission and edit forms for <?php echo e($school->name); ?>.</p>
    </div>
    <form action="<?php echo e(route('sm-school.settings.student-types.store')); ?>" method="POST" class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-end">
        <?php echo csrf_field(); ?>
        <div class="flex-1">
            <label for="new-student-type" class="mb-1 block text-xs font-medium text-body">Student Type Name</label>
            <input id="new-student-type" type="text" name="name" value="<?php echo e(old('name')); ?>" required maxlength="100" placeholder="e.g., Day Scholar" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
        </div>
        <div class="w-40">
            <label for="new-student-type-session" class="mb-1 block text-xs font-medium text-body">Session</label>
            <select id="new-student-type-session" name="academic_session_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" required>
                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>" <?php if($s->id === $session->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Add Student Type</button>
    </form>
    <div class="divide-y divide-gray-100">
        <?php $__empty_1 = true; $__currentLoopData = $studentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $studentType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="px-5 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3"><span class="truncate text-sm font-semibold text-heading"><?php echo e($studentType->name); ?></span><span class="shrink-0 text-xs text-body"><?php echo e($studentType->students_count); ?> students</span></div>
                    <div class="flex shrink-0 items-center gap-3">
                        <button type="button" onclick="document.getElementById('edit-student-type-<?php echo e($studentType->id); ?>').classList.toggle('hidden')" class="text-blue-700 hover:text-blue-900" title="Edit student type"><i data-lucide="pencil" class="h-4 w-4"></i></button>
                        <form action="<?php echo e(route('sm-school.settings.student-types.destroy', $studentType)); ?>" method="POST" onsubmit="return confirm('Delete this student type?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit" class="text-red-600 hover:text-red-800" title="Delete student type"><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                    </div>
                </div>
                <form id="edit-student-type-<?php echo e($studentType->id); ?>" action="<?php echo e(route('sm-school.settings.student-types.update', $studentType)); ?>" method="POST" class="mt-3 hidden flex flex-wrap items-end gap-2 rounded-md bg-gray-50 p-3">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <div class="min-w-48 flex-1">
                        <label for="student-type-name-<?php echo e($studentType->id); ?>" class="mb-1 block text-xs font-medium text-body">Student Type Name</label>
                        <input id="student-type-name-<?php echo e($studentType->id); ?>" type="text" name="name" value="<?php echo e($studentType->name); ?>" required maxlength="100" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <input type="hidden" name="academic_session_id" value="<?php echo e($session->id); ?>">
                    </div>
                    <button type="submit" class="rounded-md bg-navy px-4 py-2 text-xs font-semibold text-white hover:bg-primary-dark">Save Changes</button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="px-5 py-10 text-center"><i data-lucide="users-round" class="mx-auto mb-2 h-8 w-8 text-gray-300"></i><p class="text-sm font-medium text-heading">No student types configured.</p><p class="mt-1 text-xs text-body">Add a type to make it available during admission.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Fee Periods</h3>
        <p class="mt-1 text-xs text-body">Configure billing intervals for each academic session.</p>
    </div>
    <form action="<?php echo e(route('sm-school.settings.fee-periods.store')); ?>" method="POST" class="grid grid-cols-1 items-end gap-4 border-b border-gray-200 bg-gray-50 p-5 md:grid-cols-4">
        <?php echo csrf_field(); ?>
        <div><label for="periodName" class="mb-1 block text-xs font-medium text-body">Period Name</label><input id="periodName" name="name" value="<?php echo e(old('name')); ?>" required maxlength="100" placeholder="e.g. Monthly Tuition" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
        <div><label for="periodSession" class="mb-1 block text-xs font-medium text-body">Session</label><select id="periodSession" name="academic_session_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $academicSession): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($academicSession->id); ?>"><?php echo e($academicSession->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label for="periodDuration" class="mb-1 block text-xs font-medium text-body">Period Duration</label><select id="periodDuration" name="duration_months" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><option value="1">Monthly · 1 month</option><option value="3">Quarterly · 3 months</option><option value="4">4 months</option><option value="6">Half-yearly · 6 months</option><option value="12">Annual · 12 months</option></select></div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Add Period</button>
    </form>
    <div class="divide-y divide-gray-100">
        <?php $__empty_1 = true; $__currentLoopData = $feePeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="px-5 py-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-sm font-semibold text-heading"><?php echo e($period->name); ?></p><p class="mt-1 text-xs text-body"><?php echo e($period->academicSession->name); ?> · Every <?php echo e($period->duration_months); ?> month(s)</p></div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="document.getElementById('edit-period-<?php echo e($period->id); ?>').classList.toggle('hidden')" class="text-blue-700 hover:text-blue-900" title="Edit period"><i data-lucide="pencil" class="h-4 w-4"></i></button>
                        <form action="<?php echo e(route('sm-school.settings.fee-periods.destroy', $period)); ?>" method="POST" onsubmit="return confirm('Delete this period?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-red-600 hover:text-red-800" title="Delete period"><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                    </div>
                </div>
                <form id="edit-period-<?php echo e($period->id); ?>" action="<?php echo e(route('sm-school.settings.fee-periods.update', $period)); ?>" method="POST" class="mt-3 hidden grid grid-cols-1 gap-3 rounded-md bg-gray-50 p-3 md:grid-cols-4">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input name="name" value="<?php echo e($period->name); ?>" required maxlength="100" aria-label="Period name" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <select name="academic_session_id" aria-label="Academic session" class="rounded-md border border-gray-300 px-3 py-2 text-sm"><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $academicSession): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($academicSession->id); ?>" <?php echo e($period->academic_session_id === $academicSession->id ? 'selected' : ''); ?>><?php echo e($academicSession->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    <select name="duration_months" aria-label="Duration in months" class="rounded-md border border-gray-300 px-3 py-2 text-sm"><?php $__currentLoopData = [1, 3, 4, 6, 12]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $months): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($months); ?>" <?php echo e($period->duration_months === $months ? 'selected' : ''); ?>><?php echo e($months); ?> month(s)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    <button class="rounded-md bg-navy px-3 py-2 text-sm font-semibold text-white">Save Changes</button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="px-5 py-10 text-center text-sm text-body">No fee periods configured.</p>
        <?php endif; ?>
    </div>
</section>
<?php elseif($activeTab === 'payment-methods'): ?>
<section class="col-span-full space-y-8" id="payment-methods-section">
    <!-- Static Payment Methods (Bank, UPI, Cash, Check) -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Standard Payment Methods</h3>
            <p class="mt-1 text-xs text-body">Configure built-in payment methods.</p>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Method Boxes -->
                <button type="button" onclick="togglePaymentMethod('bank')" id="btn-method-bank" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="building-2" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Bank Transfer</span>
                </button>
                <button type="button" onclick="togglePaymentMethod('upi')" id="btn-method-upi" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="smartphone" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">UPI Transfer</span>
                </button>
                <button type="button" onclick="togglePaymentMethod('cash')" id="btn-method-cash" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="banknote" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Cash</span>
                </button>
                <button type="button" onclick="togglePaymentMethod('check')" id="btn-method-check" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="file-signature" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Check</span>
                </button>
            </div>

            <form action="<?php echo e(route('sm-school.settings.update')); ?>" method="POST" enctype="multipart/form-data" class="bg-gray-50 border border-gray-200 rounded-xl p-5 hidden" id="payment-method-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="academic_session_id" value="<?php echo e($session->id); ?>">
                
                <!-- Bank Form -->
                <div id="method-bank" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Bank Transfer Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Bank Transfer:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_bank_transfer" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_bank_transfer', '0') == '1' ? 'checked' : ''); ?>>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Branch Code:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_branch_code" value="<?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_branch_code')); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Account No.:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_account_no" value="<?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_account_no')); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Name:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_name" value="<?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_name')); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="bank_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"><?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_instructions')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- UPI Form -->
                <div id="method-upi" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">UPI Transfer Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable UPI Transfer:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_upi_transfer" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_upi_transfer', '0') == '1' ? 'checked' : ''); ?>>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">QR Code:</label>
                        <div class="col-span-2">
                            <input type="file" name="upi_qr_code" class="block w-full text-sm">
                            <?php if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_qr_code')): ?>
                            <p class="text-xs text-green-600 mt-1">QR Code already uploaded.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">UPI ID:</label>
                        <div class="col-span-2">
                            <input type="text" name="upi_id" value="<?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_id')); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Name:</label>
                        <div class="col-span-2">
                            <input type="text" name="upi_name" value="<?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_name')); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="upi_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"><?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_instructions')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Cash Form -->
                <div id="method-cash" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Cash Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Cash:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_cash_payment" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_cash_payment', '0') == '1' ? 'checked' : ''); ?>>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="cash_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"><?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'cash_instructions')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Check Form -->
                <div id="method-check" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Check Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Check:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_check_payment" value="1" <?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_check_payment', '0') == '1' ? 'checked' : ''); ?>>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="check_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"><?php echo e(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'check_instructions')); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Standard Methods</button>
                </div>
            </form>
        </div>
    </div>

</section>

<script>
    function togglePaymentMethod(method) {
        const form = document.getElementById('payment-method-form');
        const details = document.querySelectorAll('.payment-method-detail');
        const buttons = document.querySelectorAll('.payment-method-btn');
        
        let currentlyVisible = false;
        
        details.forEach(d => {
            if(d.id === 'method-' + method && d.classList.contains('hidden')) {
                d.classList.remove('hidden');
                currentlyVisible = true;
            } else {
                d.classList.add('hidden');
            }
        });

        buttons.forEach(b => {
            if(b.id === 'btn-method-' + method && currentlyVisible) {
                b.classList.remove('border-gray-200');
                b.classList.add('border-navy', 'ring-1', 'ring-navy');
            } else {
                b.classList.add('border-gray-200');
                b.classList.remove('border-navy', 'ring-1', 'ring-navy');
            }
        });

        if(currentlyVisible) {
            form.classList.remove('hidden');
        } else {
            form.classList.add('hidden');
        }
    }
</script>
<?php elseif($activeTab === 'general'): ?>
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">General Settings</h3>
    </div>
    <form action="<?php echo e(route('sm-school.settings.update')); ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        <?php echo csrf_field(); ?>
    <input type="hidden" name="academic_session_id" value="<?php echo e($session->id); ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Set Currency:</label>
            <div class="col-span-2">
                <?php 
                    $currency = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'currency', 'USD');
                    $currencies = ['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'INR' => 'INR (₹)', 'AUD' => 'AUD ($)', 'CAD' => 'CAD ($)', 'SGD' => 'SGD ($)', 'JPY' => 'JPY (¥)', 'NGN' => 'NGN (₦)', 'ZAR' => 'ZAR (R)'];
                ?>
                <select name="currency" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary sm:text-sm">
                    <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k); ?>" <?php echo e($currency === $k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Invoice Copy:</label>
            <div class="col-span-2">
                <?php $invoiceCopy = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_copy', 'No'); ?>
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="invoice_copy" value="Yes" <?php echo e($invoiceCopy === 'Yes' ? 'checked' : ''); ?>> Yes</label>
                    <label><input type="radio" name="invoice_copy" value="No" <?php echo e($invoiceCopy === 'No' ? 'checked' : ''); ?>> No</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Admin will be able to print school and student copies of invoices.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Registration Invoice Type:</label>
            <div class="col-span-2">
                <?php $regInvType = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'registration_invoice_type', 'Combined Invoice'); ?>
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="registration_invoice_type" value="Combined Invoice" <?php echo e($regInvType === 'Combined Invoice' ? 'checked' : ''); ?>> Combined Invoice</label>
                    <label><input type="radio" name="registration_invoice_type" value="Separate Invoices" <?php echo e($regInvType === 'Separate Invoices' ? 'checked' : ''); ?>> Separate Invoices</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Choose whether to create a single combined invoice with all fee types or separate invoices for each fee type when a new student is registered.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Auto Generate Invoice:</label>
            <div class="col-span-2">
                <?php $autoInv = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'auto_generate_invoice', 'No'); ?>
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="auto_generate_invoice" value="Yes" <?php echo e($autoInv === 'Yes' ? 'checked' : ''); ?>> Yes</label>
                    <label><input type="radio" name="auto_generate_invoice" value="No" <?php echo e($autoInv === 'No' ? 'checked' : ''); ?>> No</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">New - Monthly invoices will be auto generated.</p>
                <?php if($autoInv === 'Yes'): ?>
                <p class="text-xs font-semibold text-green-600 mt-1">Next cron job will run on: <?php echo e(now()->addMonth()->startOfMonth()->format('d M Y')); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload School Logo:</label>
            <div class="col-span-2">
                <input type="file" name="school_logo" class="block w-full text-sm">
                <?php if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'school_logo')): ?>
                <p class="text-xs text-green-600 mt-1">Logo already uploaded.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload School Signature:</label>
            <div class="col-span-2">
                <input type="file" name="school_signature" class="block w-full text-sm">
                <?php if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'school_signature')): ?>
                <p class="text-xs text-green-600 mt-1">Signature already uploaded.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>

<?php elseif($activeTab === 'dashboard' || $activeTab === 'live-classes'): ?>
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="p-12 text-center text-gray-500 text-sm font-semibold">
        <?php echo e(ucfirst(str_replace('-', ' ', $activeTab))); ?> settings are blank/upcoming.
    </div>
</section>

<?php elseif($activeTab === 'card-background'): ?>
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Card Background Settings</h3>
    </div>
    <form action="<?php echo e(route('sm-school.settings.update')); ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        <?php echo csrf_field(); ?>
    <input type="hidden" name="academic_session_id" value="<?php echo e($session->id); ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Id Card Background:</label>
            <div class="col-span-2">
                <input type="file" name="id_card_background" class="block w-full text-sm">
                <?php if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'id_card_background')): ?>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-green-600">Background already uploaded.</p>
                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_id_card_background" value="1"> Remove
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Invoice Background:</label>
            <div class="col-span-2">
                <input type="file" name="invoice_background" class="block w-full text-sm">
                <?php if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_background')): ?>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-green-600">Background already uploaded.</p>
                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_invoice_background" value="1"> Remove
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Examination background image ( admit card and result ) :</label>
            <div class="col-span-2">
                <input type="file" name="examination_background" class="block w-full text-sm">
                <?php if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'examination_background')): ?>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-green-600">Background already uploaded.</p>
                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_examination_background" value="1"> Remove
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>

<?php elseif($activeTab === 'examination'): ?>
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Examination Settings</h3>
    </div>
    <form action="<?php echo e(route('sm-school.settings.update')); ?>" method="POST" class="p-6 space-y-6">
        <?php echo csrf_field(); ?>
    <input type="hidden" name="academic_session_id" value="<?php echo e($session->id); ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Admit Card Photo:</label>
            <div class="col-span-2">
                <?php $admitPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'student_admit_card_photo', '0') == '1'; ?>
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="student_admit_card_photo" value="1" <?php echo e($admitPhoto ? 'checked' : ''); ?>> Yes</label>
                    <label><input type="radio" name="student_admit_card_photo" value="0" <?php echo e(!$admitPhoto ? 'checked' : ''); ?>> No</label>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Exam Result Photo:</label>
            <div class="col-span-2">
                <?php $resultPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exam_result_photo', '0') == '1'; ?>
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="exam_result_photo" value="1" <?php echo e($resultPhoto ? 'checked' : ''); ?>> Yes</label>
                    <label><input type="radio" name="exam_result_photo" value="0" <?php echo e(!$resultPhoto ? 'checked' : ''); ?>> No</label>
                </div>
            </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\intel\Documents\edu_verse\resources\views/sm-school/settings/index.blade.php ENDPATH**/ ?>