<?php $__env->startSection('header', 'Admit Cards'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 rounded-lg px-4 py-2 text-center text-base font-bold text-white shadow-md" style="background:var(--color-primary);">Manage Exam Admit Cards</div>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-xs">
            <thead class="text-left text-white" style="background:var(--color-primary);">
                <tr><th class="p-3">Exam Title</th><th class="p-3">Class</th><th class="p-3">Exam Center</th><th class="p-3">Start Date</th><th class="p-3">End Date</th><th class="p-3">Generated Cards</th><th class="p-3">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-bold text-heading"><?php echo e($exam->title); ?></td>
                        <td class="p-3"><?php echo e($exam->schoolClass->name); ?></td>
                        <td class="p-3"><?php echo e($exam->exam_center); ?></td>
                        <td class="p-3"><?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="p-3"><?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></td>
                        <td class="p-3"><?php echo e($exam->admit_cards_count); ?></td>
                        <td class="p-3"><div class="flex flex-wrap gap-2"><a href="<?php echo e(route('examination.admit-cards.generate-form', $exam)); ?>" class="inline-flex items-center gap-1 rounded px-3 py-2 font-bold text-white" style="background:var(--color-primary);"><i data-lucide="contact-round" class="h-3.5 w-3.5"></i> Generate Admit Card</a><?php if($exam->admit_cards_count > 0): ?><a href="<?php echo e(route('examination.admit-cards.view', $exam)); ?>" class="inline-flex items-center gap-1 rounded border border-gray-300 px-3 py-2 font-bold text-heading"><i data-lucide="eye" class="h-3.5 w-3.5"></i> View</a><?php endif; ?></div></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="p-10 text-center text-body">No exams found for <?php echo e($session->name); ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($exams->hasPages()): ?><div class="border-t border-gray-200 px-4 py-3"><?php echo e($exams->links()); ?></div><?php endif; ?>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/admit-cards/index.blade.php ENDPATH**/ ?>