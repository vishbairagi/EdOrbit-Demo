<?php $__env->startSection('header', 'Generate Exam Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background:var(--color-primary);">
    <div><h1 class="text-base font-bold"><?php echo e($exam->title); ?></h1><p class="mt-1 text-xs"><?php echo e($exam->schoolClass->name); ?> · <?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?> to <?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></p></div>
    <div class="flex gap-2"><a href="<?php echo e(route('examination.results.index')); ?>" class="rounded border border-white/70 px-3 py-2 text-xs font-semibold">Exam List</a><?php if($exam->results_count > 0): ?><a href="<?php echo e(route('examination.results.view', $exam)); ?>" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold"><i data-lucide="eye" class="h-3.5 w-3.5"></i> View Results</a><?php endif; ?></div>
</div>

<?php if($errors->any()): ?><div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800"><ul class="list-inside list-disc"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>

<section class="mb-5 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
        <div><h2 class="text-sm font-bold text-heading">Students Pending Results</h2><p class="mt-1 text-xs text-body">Students with saved results are hidden from this list.</p></div>
        <form method="GET" action="<?php echo e(route('examination.results.generate-form', $exam)); ?>" class="flex items-center gap-2 text-xs">
            <label for="sort-by" class="font-semibold">Ascending Order</label>
            <select id="sort-by" name="sort_by" onchange="this.form.submit()" class="rounded border border-gray-300 bg-white px-3 py-2"><option value="roll_number" <?php if(request('sort_by', 'roll_number') === 'roll_number'): echo 'selected'; endif; ?>>Main Roll Number</option><option value="name" <?php if(request('sort_by') === 'name'): echo 'selected'; endif; ?>>Name Alphabetically</option></select>
        </form>
    </div>
    <?php if($students->isNotEmpty()): ?>
        <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-xs"><thead class="bg-gray-50 text-left"><tr><th class="p-3">Main Roll No.</th><th class="p-3">Student Name</th><th class="p-3">Section</th><th class="p-3">Subjects in Exam</th><th class="p-3">Action</th></tr></thead><tbody class="divide-y divide-gray-200">
            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td class="p-3"><?php echo e($student->roll_number ?? '—'); ?></td><td class="p-3 font-semibold text-heading"><?php echo e($student->full_name); ?></td><td class="p-3"><?php echo e($student->section?->name ?? '—'); ?></td><td class="p-3"><?php echo e($student->activeSubjects->whereIn('id', $exam->papers->pluck('subject_id'))->pluck('name')->join(', ')); ?></td><td class="p-3"><a href="<?php echo e(route('examination.results.generate-form', ['exam' => $exam->id, 'student_id' => $student->id, 'sort_by' => request('sort_by', 'roll_number')])); ?>#result-entry" class="inline-flex items-center gap-1 rounded px-3 py-1.5 font-semibold text-white" style="background:var(--color-primary);"><i data-lucide="square-pen" class="h-3.5 w-3.5"></i> Enter Result</a></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody></table></div>
    <?php else: ?>
        <p class="p-8 text-center text-xs text-body">All eligible students already have results, or no students match this exam’s sections and subjects.</p>
    <?php endif; ?>
</section>

<?php if($selectedStudent && $papers->isNotEmpty()): ?>
    <section id="result-entry" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3"><div><h2 class="text-sm font-bold text-heading"><?php echo e($selectedStudent->full_name); ?></h2><p class="mt-1 text-xs text-body"><?php echo e($selectedStudent->schoolClass->name); ?> · <?php echo e($selectedStudent->section?->name ?? '—'); ?> · Roll <?php echo e($selectedStudent->roll_number ?? '—'); ?></p></div><span class="text-xs font-semibold text-body"><?php echo e($exam->title); ?></span></div>
        <form method="POST" action="<?php echo e(route('examination.results.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="exam_id" value="<?php echo e($exam->id); ?>"><input type="hidden" name="student_id" value="<?php echo e($selectedStudent->id); ?>">
            <div class="overflow-x-auto"><table class="w-full min-w-[520px] text-xs"><thead class="border-b border-gray-200 text-left"><tr><th class="p-3">Paper Code</th><th class="p-3">Subject</th><th class="p-3">Subject Type</th><?php if($exam->exam_type === 'marks'): ?><th class="p-3">Maximum Marks</th><th class="p-3">Obtained Marks</th><?php else: ?><th class="p-3">Grade</th><?php endif; ?></tr></thead><tbody class="divide-y divide-gray-200">
                <?php $__currentLoopData = $papers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paper): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td class="p-3"><?php echo e($paper->paper_code ?? '—'); ?></td><td class="p-3 font-semibold text-heading"><?php echo e($paper->subject_name); ?></td><td class="p-3"><?php echo e($paper->subject_type ?? '—'); ?></td>
                        <?php if($exam->exam_type === 'marks'): ?>
                            <td class="p-3"><?php echo e($paper->maximum_marks); ?></td><td class="p-2"><label class="sr-only" for="mark-<?php echo e($paper->id); ?>">Obtained marks for <?php echo e($paper->subject_name); ?></label><input id="mark-<?php echo e($paper->id); ?>" name="marks[<?php echo e($paper->id); ?>]" type="number" min="0" max="<?php echo e($paper->maximum_marks); ?>" step="0.01" required value="<?php echo e(old('marks.'.$paper->id, $existingResult?->items->firstWhere('exam_paper_id', $paper->id)?->obtained_marks)); ?>" placeholder="Enter marks" class="w-full rounded border border-gray-300 px-3 py-2"></td>
                        <?php else: ?>
                            <td class="p-2"><label class="sr-only" for="grade-<?php echo e($paper->id); ?>">Grade for <?php echo e($paper->subject_name); ?></label><select id="grade-<?php echo e($paper->id); ?>" name="grades[<?php echo e($paper->id); ?>]" required class="w-full rounded border border-gray-300 bg-white px-3 py-2"><option value="">Select Grade</option><?php $__currentLoopData = $exam->grade_criteria ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gradeRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($gradeRow['grade']); ?>" <?php if(old('grades.'.$paper->id, $existingResult?->items->firstWhere('exam_paper_id', $paper->id)?->grade) === $gradeRow['grade']): echo 'selected'; endif; ?>><?php echo e($gradeRow['grade']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody></table></div>
            <div class="mt-4 flex justify-end"><button class="rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);"><?php echo e(isset($existingResult) ? 'Update Result' : 'Generate Result'); ?></button></div>
        </form>
    </section>
<?php elseif($selectedStudent): ?>
    <div id="result-entry" class="rounded border border-sky-200 bg-sky-50 p-4 text-center text-xs text-sky-900">This student has no active subjects included in this exam.</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/results/generate.blade.php ENDPATH**/ ?>