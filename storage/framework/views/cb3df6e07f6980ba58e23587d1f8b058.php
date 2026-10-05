<?php $__env->startSection('header', 'Generate Admit Cards'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-2 text-white shadow-md" style="background:var(--color-primary);">
    <div><h1 class="text-base font-bold"><?php echo e($exam->title); ?></h1><p class="mt-1 text-xs"><?php echo e($exam->schoolClass->name); ?> · <?php echo e($exam->exam_center); ?> · <?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?> to <?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></p></div>
    <div class="flex gap-2"><a href="<?php echo e(route('examination.admit-cards.index')); ?>" class="rounded border border-white/70 px-3 py-1.5 text-xs font-semibold">Exam List</a><?php if($exam->admit_cards_count > 0): ?><a href="<?php echo e(route('examination.admit-cards.view', $exam)); ?>" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-1.5 text-xs font-semibold"><i data-lucide="eye" class="h-3.5 w-3.5"></i> View Generated Cards</a><?php endif; ?></div>
</div>

<?php if($errors->any()): ?><div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800"><ul class="list-inside list-disc"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>

<section class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div><h2 class="text-sm font-bold text-heading">Generate Admit Cards</h2><p class="mt-1 text-xs text-body">Only active students with at least one matching exam subject are listed.</p></div>
        <form method="GET" action="<?php echo e(route('examination.admit-cards.generate-form', $exam)); ?>" class="flex items-center gap-2 text-xs">
            <label for="sort-by" class="font-semibold">Ascending Order</label>
            <select id="sort-by" name="sort_by" onchange="this.form.submit()" class="rounded border border-gray-300 bg-white px-3 py-2"><option value="roll_number" <?php if(request('sort_by', 'roll_number') === 'roll_number'): echo 'selected'; endif; ?>>Main Roll Number</option><option value="name" <?php if(request('sort_by') === 'name'): echo 'selected'; endif; ?>>Name Alphabetically</option></select>
        </form>
    </div>

    <?php if($students->isNotEmpty()): ?>
        <form method="POST" action="<?php echo e(route('examination.admit-cards.generate', $exam)); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="sort_by" value="<?php echo e(request('sort_by', 'roll_number')); ?>">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="text-xs font-semibold">Exam Roll Number Prefix<input name="prefix" maxlength="30" value="<?php echo e(old('prefix', $exam->admit_roll_prefix)); ?>" placeholder="e.g. CS" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Starting Count<input name="starting_number" type="number" min="1" required value="<?php echo e(old('starting_number', $exam->admit_roll_start)); ?>" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
            </div>
            <?php $__errorArgs = ['starting_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-700"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div class="overflow-x-auto rounded border border-gray-200">
                <table class="w-full min-w-[620px] text-xs">
                    <thead class="bg-gray-50 text-left"><tr><th class="p-3"><input type="checkbox" id="select-all-students" checked aria-label="Select all students"></th><th class="p-3">Main Roll No.</th><th class="p-3">Student Name</th><th class="p-3">Section</th><th class="p-3">Exam Subjects</th><th class="p-3">Exam Roll No.</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="p-3"><input type="checkbox" name="student_ids[]" value="<?php echo e($student->id); ?>" class="student-choice" checked aria-label="Select <?php echo e($student->full_name); ?>"></td>
                                <td class="p-3"><?php echo e($student->roll_number ?? '—'); ?></td>
                                <td class="p-3 font-semibold text-heading"><?php echo e($student->full_name); ?></td>
                                <td class="p-3"><?php echo e($student->section?->name ?? '—'); ?></td>
                                <td class="p-3"><?php echo e($student->activeSubjects->whereIn('id', $exam->papers->pluck('subject_id'))->pluck('name')->join(', ')); ?></td>
                                <td class="p-3">New card</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <button class="inline-flex items-center gap-1 rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);"><i data-lucide="contact-round" class="h-3.5 w-3.5"></i> Generate Admit Cards</button>
        </form>
        <script>
            document.getElementById('select-all-students').addEventListener('change', event => {
                document.querySelectorAll('.student-choice').forEach(input => input.checked = event.target.checked);
            });
        </script>
    <?php else: ?>
        <div class="rounded border border-sky-200 bg-sky-50 px-4 py-4 text-center text-xs text-sky-900">No eligible students found. Students must be Active and have at least one assigned subject included in this exam.</div>
    <?php endif; ?>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/admit-cards/generate.blade.php ENDPATH**/ ?>