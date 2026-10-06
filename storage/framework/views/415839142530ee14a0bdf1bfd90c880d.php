<?php $__env->startSection('header', 'Academic Report'); ?>

<?php $__env->startSection('content'); ?>
<style>
    @media print {
        @page { size: A4 portrait; margin: 15mm; }
        body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        /* Hide UI */
        body > aside, body > main > header, .print-hide { display: none !important; }
        
        /* Ensure main container takes full width for print */
        #print-container { display: block !important; width: 100% !important; margin: 0; padding: 0; }
        
        /* Print rules */
        .report-page { page-break-after: always; padding: 0 !important; margin: 0 0 20px 0 !important; border: none !important; box-shadow: none !important; }
        .report-page:last-child { page-break-after: auto; }
        .page-break-inside-avoid { break-inside: avoid; }
        
        /* Aesthetics for print */
        .school-header { border-bottom: 3px solid #111827; padding-bottom: 15px; margin-bottom: 20px; text-align: center; }
        .student-details { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 2px solid #e5e7eb; padding: 15px; border-radius: 8px; margin-bottom: 25px; background: #f9fafb; }
    }
</style>

<div class="print-hide mb-5 rounded-lg px-4 py-2 text-center text-base font-bold text-white shadow-md" style="background:var(--color-primary);">Academic Report</div>

<section class="print-hide mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form method="GET" action="<?php echo e(route('examination.reports.academic')); ?>" class="grid items-end gap-3 md:grid-cols-4">
        <label class="text-xs font-semibold">Class
            <select name="class_id" onchange="this.form.section_id.value='';this.form.student_id.value='';this.form.submit()" class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal focus:ring-1 focus:ring-navy">
                <option value="">Select Class</option><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($class->id); ?>" <?php if($schoolClass?->id === $class->id): echo 'selected'; endif; ?>><?php echo e($class->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <label class="text-xs font-semibold">Section
            <select name="section_id" onchange="this.form.student_id.value='';this.form.submit()" <?php if(!$schoolClass): echo 'disabled'; endif; ?> class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal focus:ring-1 focus:ring-navy">
                <option value="">Select Section</option><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($item->id); ?>" <?php if($section?->id === $item->id): echo 'selected'; endif; ?>><?php echo e($item->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <label class="text-xs font-semibold">Student
            <select name="student_id" <?php if(!$section): echo 'disabled'; endif; ?> class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal focus:ring-1 focus:ring-navy">
                <option value="">Select Student</option><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($item->id); ?>" <?php if($student?->id === $item->id): echo 'selected'; endif; ?>><?php echo e($item->full_name); ?> · <?php echo e($item->admission_no); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <button class="rounded px-4 py-2 text-xs font-bold text-white transition-opacity hover:opacity-90" style="background:var(--color-primary);">Fetch Report</button>
    </form>
</section>

<?php if($student): ?>
    <div class="print-hide mb-4 flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <div>
            <h1 class="text-sm font-bold text-heading"><?php echo e($student->full_name); ?></h1>
            <p class="mt-1 text-xs text-body"><?php echo e($student->schoolClass->name); ?> · <?php echo e($student->section?->name ?? '—'); ?> · Admission No. <?php echo e($student->admission_no); ?></p>
        </div>
        <?php if($results->isNotEmpty()): ?>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded px-4 py-2 text-xs font-bold text-white transition-opacity hover:opacity-90 shadow-sm" style="background:var(--color-primary);">
                <i data-lucide="printer" class="h-4 w-4"></i> Print Full Report
            </button>
        <?php endif; ?>
    </div>

    <?php if($results->isEmpty()): ?>
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-8 text-center text-sm text-sky-900 font-medium">
            No exam results found for this student in the <?php echo e($session->name); ?> session yet. <br>
            <span class="text-xs font-normal mt-2 block">Generate results for this student in the Exam Results module first.</span>
        </div>
    <?php else: ?>
        <div id="print-container">
            
            <div class="report-page bg-white p-6 border border-gray-200 rounded-xl shadow-sm">
                
                <div class="school-header">
                    <h1 class="text-3xl font-black uppercase text-gray-900 tracking-wide"><?php echo e($student->school->name ?? 'School Name'); ?></h1>
                    <p class="text-gray-700 mt-2 text-sm"><?php echo e($student->school->address ?? ''); ?></p>
                    <p class="text-gray-700 text-sm font-medium mt-1"><?php echo e($student->school->phone ?? ''); ?> &nbsp;|&nbsp; <?php echo e($student->school->email ?? ''); ?></p>
                    <div class="mt-6 inline-block bg-gray-900 text-white px-6 py-2 rounded-full">
                        <h2 class="text-lg font-bold uppercase tracking-widest">Annual Academic Report</h2>
                    </div>
                    <p class="text-sm font-bold text-gray-600 mt-3 uppercase tracking-wide">Session: <?php echo e($session->name); ?></p>
                </div>

                
                <div class="student-details text-sm">
                    <div>
                        <table class="w-full">
                            <tr><td class="py-1.5 font-bold text-gray-600 w-32">Student Name:</td><td class="text-gray-900 font-black uppercase text-base"><?php echo e($student->full_name); ?></td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Class & Section:</td><td class="text-gray-900 font-bold"><?php echo e($student->schoolClass->name); ?> - <?php echo e($student->section?->name ?? ''); ?></td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Father's Name:</td><td class="text-gray-900"><?php echo e($student->father_name ?? '—'); ?></td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Mother's Name:</td><td class="text-gray-900"><?php echo e($student->mother_name ?? '—'); ?></td></tr>
                        </table>
                    </div>
                    <div>
                        <table class="w-full">
                            <tr><td class="py-1.5 font-bold text-gray-600 w-32">Admission No:</td><td class="text-gray-900 font-mono font-bold"><?php echo e($student->admission_no); ?></td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Date of Birth:</td><td class="text-gray-900"><?php echo e($student->dob ? $student->dob->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) : '—'); ?></td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Blood Group:</td><td class="text-gray-900"><?php echo e($student->blood_group ?? '—'); ?></td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Contact:</td><td class="text-gray-900"><?php echo e($student->phone ?? '—'); ?></td></tr>
                        </table>
                    </div>
                </div>

                
                <div class="mt-8 mb-4">
                    <h3 class="text-lg font-black uppercase text-gray-800 border-b-2 border-gray-800 pb-2 mb-4">Performance Overview</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $exam = $result->exam;
                                $items = $result->items->filter(fn ($item) => $item->paper !== null)->values();
                                $totalMaximum = $items->sum(fn ($item) => (float) ($item->paper->maximum_marks ?? 0));
                                $totalObtained = $items->sum(fn ($item) => (float) ($item->obtained_marks ?? 0));
                                $percentage = $totalMaximum > 0 ? ($totalObtained / $totalMaximum) * 100 : 0;
                            ?>
                            <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?php echo e($exam->title); ?></p>
                                <?php if($exam->exam_type === 'marks'): ?>
                                    <div class="text-2xl font-black text-gray-900"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format($percentage, 1)); ?>%</div>
                                    <div class="text-xs font-medium text-gray-500 mt-1"><?php echo e($totalObtained); ?> / <?php echo e($totalMaximum); ?></div>
                                <?php else: ?>
                                    <div class="text-2xl font-black text-gray-900">Grades</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            
            <div class="report-page bg-white p-6 border border-gray-200 rounded-xl shadow-sm mt-6">
                <h3 class="text-xl font-black uppercase text-gray-800 border-b-2 border-gray-800 pb-2 mb-6 text-center">Detailed Examination Results</h3>
                
                <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $exam = $result->exam;
                        $resultSettings = $exam->settings ?? [];
                        $resultThresholds = $exam->result_thresholds ?? [];
                        $items = $result->items->filter(fn ($item) => $item->paper !== null)->values();
                        $totalMaximum = $items->sum(fn ($item) => (float) ($item->paper->maximum_marks ?? 0));
                        $totalObtained = $items->sum(fn ($item) => (float) ($item->obtained_marks ?? 0));
                        $gradePercentages = $items->map(function ($item) use ($exam): float {
                            if ($item->obtained_marks !== null && $item->paper?->maximum_marks) return ((float) $item->obtained_marks / (float) $item->paper->maximum_marks) * 100;
                            return (float) (collect($exam->grade_criteria ?? [])->firstWhere('grade', $item->grade)['percentage_from'] ?? 0);
                        });
                        $overallPercentage = $totalMaximum > 0 ? ($totalObtained / $totalMaximum) * 100 : ($gradePercentages->isNotEmpty() ? $gradePercentages->avg() : 0);
                        $overallGrade = collect($exam->grade_criteria ?? [])->first(fn ($row) => $overallPercentage >= (float) $row['percentage_from'] && $overallPercentage <= (float) $row['percentage_to'])['grade'] ?? null;
                        $failedCount = $items->filter(fn ($item) => strcasecmp((string) $item->status, (string) ($resultThresholds['subject_fail_label'] ?? 'Fail')) === 0)->count();
                        $overallPass = $overallPercentage >= (float) ($resultThresholds['overall_pass_threshold'] ?? 0) && $failedCount <= (int) ($resultThresholds['max_failed_subjects'] ?? 0);
                    ?>
                    
                    <div class="page-break-inside-avoid mb-10 border border-gray-300 rounded-lg overflow-hidden">
                        
                        <div class="bg-gray-100 border-b border-gray-300 px-5 py-3 flex justify-between items-center">
                            <div>
                                <h4 class="text-base font-black text-gray-900 uppercase"><?php echo e($exam->title); ?></h4>
                                <p class="text-xs text-gray-600 font-medium mt-0.5"><?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?> - <?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></p>
                            </div>
                            <?php if($resultSettings['show_ranks'] ?? false): ?>
                                <div class="bg-white border border-gray-300 px-3 py-1.5 rounded text-xs font-bold text-gray-800">
                                    <?php echo e(($resultSettings['rank_criteria'] ?? 'class') === 'section' ? 'Section' : 'Class'); ?> Rank: <?php echo e($result->rank ?? '—'); ?>

                                </div>
                            <?php endif; ?>
                        </div>

                        
                        <table class="w-full text-sm text-left">
                            <thead class="bg-white border-b-2 border-gray-200">
                                <tr>
                                    <th class="p-3 font-bold text-gray-700">Subject</th>
                                    <?php if($resultSettings['subject_code'] ?? false): ?><th class="p-3 font-bold text-gray-700">Code</th><?php endif; ?>
                                    <?php if($resultSettings['subject_type'] ?? true): ?><th class="p-3 font-bold text-gray-700 text-center">Type</th><?php endif; ?> 
                                    <?php if($exam->exam_type === 'marks' && ($resultSettings['total_marks'] ?? true)): ?><th class="p-3 font-bold text-gray-700 text-center">Max Marks</th><?php endif; ?> 
                                    <?php if($exam->exam_type === 'marks' && ($resultSettings['total_obtained_marks'] ?? true)): ?><th class="p-3 font-bold text-gray-700 text-center">Obtained</th><?php endif; ?> 
                                    <?php if($resultSettings['overall_grade'] ?? false): ?><th class="p-3 font-bold text-gray-700 text-center">Grade</th><?php endif; ?> 
                                    <?php if($resultThresholds['show_subject_status'] ?? false): ?><th class="p-3 font-bold text-gray-700 text-center"><?php echo e($resultThresholds['subject_status_header'] ?? 'Status'); ?></th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="p-3 font-bold text-gray-800"><?php echo e($item->paper->subject_name); ?></td>
                                        <?php if($resultSettings['subject_code'] ?? false): ?><td class="p-3 text-gray-600"><?php echo e($item->paper->paper_code ?? '—'); ?></td><?php endif; ?> 
                                        <?php if($resultSettings['subject_type'] ?? true): ?><td class="p-3 text-gray-600 text-center"><?php echo e($item->paper->subject_type ?? '—'); ?></td><?php endif; ?> 
                                        <?php if($exam->exam_type === 'marks' && ($resultSettings['total_marks'] ?? true)): ?><td class="p-3 text-gray-800 text-center font-mono"><?php echo e($item->paper->maximum_marks); ?></td><?php endif; ?> 
                                        <?php if($exam->exam_type === 'marks' && ($resultSettings['total_obtained_marks'] ?? true)): ?><td class="p-3 text-gray-900 text-center font-mono font-bold"><?php echo e($item->obtained_marks ?? '—'); ?></td><?php endif; ?> 
                                        <?php if($resultSettings['overall_grade'] ?? false): ?><td class="p-3 text-gray-900 text-center font-bold"><?php echo e($item->grade ?? '—'); ?></td><?php endif; ?> 
                                        <?php if($resultThresholds['show_subject_status'] ?? false): ?>
                                            <td class="p-3 text-center font-bold">
                                                <?php if(stripos($item->status, 'Fail') !== false): ?>
                                                    <span class="text-red-600">Fail</span>
                                                <?php elseif(stripos($item->status, 'Pass') !== false): ?>
                                                    <span class="text-green-600">Pass</span>
                                                <?php else: ?>
                                                    <span class="text-gray-800"><?php echo e($item->status ?? '—'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <?php if(($exam->exam_type === 'marks' && (($resultSettings['total_marks'] ?? true) || ($resultSettings['total_obtained_marks'] ?? true))) || ($resultSettings['overall_grade'] ?? false)): ?>
                                <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                                    <tr>
                                        <td colspan="<?php echo e(1 + (int) ($resultSettings['subject_code'] ?? false) + (int) ($resultSettings['subject_type'] ?? true)); ?>" class="p-3 text-right font-black uppercase text-gray-800"><?php echo e($exam->exam_type === 'marks' ? 'Grand Total' : 'Overall Grade'); ?></td>
                                        <?php if($exam->exam_type === 'marks' && ($resultSettings['total_marks'] ?? true)): ?><td class="p-3 text-center font-black font-mono text-gray-900"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format($totalMaximum, 2)); ?></td><?php endif; ?> 
                                        <?php if($exam->exam_type === 'marks' && ($resultSettings['total_obtained_marks'] ?? true)): ?><td class="p-3 text-center font-black font-mono text-gray-900"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format($totalObtained, 2)); ?></td><?php endif; ?> 
                                        <?php if($resultSettings['overall_grade'] ?? false): ?><td class="p-3 text-center font-black text-gray-900"><?php echo e($overallGrade ?? '—'); ?></td><?php endif; ?> 
                                        <?php if($resultThresholds['show_subject_status'] ?? false): ?><td></td><?php endif; ?>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>

                        
                        <?php if($resultThresholds['show_overall_status'] ?? false): ?>
                            <div class="bg-white border-t border-gray-300 px-5 py-3 flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-700 uppercase"><?php echo e($resultThresholds['overall_row_header'] ?? 'Overall Result'); ?>:</span>
                                <span class="font-black uppercase <?php echo e($overallPass ? 'text-green-600' : 'text-red-600'); ?>">
                                    <?php echo e($overallPass ? ($resultThresholds['overall_pass_label'] ?? 'Pass') : ($resultThresholds['overall_fail_label'] ?? 'Fail')); ?>

                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(($resultSettings['teacher_signature_enabled'] ?? false) && !empty($resultSettings['teacher_signature_path'])): ?>
                            <div class="bg-white flex justify-end border-t border-gray-200 px-5 py-3">
                                <div class="text-center">
                                    <img src="<?php echo e(asset('storage/'.$resultSettings['teacher_signature_path'])); ?>" alt="Signature" class="h-12 object-contain mb-1">
                                    <div class="text-xs font-semibold text-gray-500 border-t border-gray-300 pt-1 px-4">Class Teacher</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                
                <div class="mt-20 flex justify-between px-10">
                    <div class="text-center w-40 border-t-2 border-gray-800 pt-2 text-sm font-bold text-gray-800">Class Teacher</div>
                    <div class="text-center w-40 border-t-2 border-gray-800 pt-2 text-sm font-bold text-gray-800">Principal</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/reports/academic.blade.php ENDPATH**/ ?>