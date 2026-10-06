<?php $__env->startSection('header', 'Generated Exam Results'); ?>

<?php $__env->startSection('content'); ?>
<style>
    @media print {
        @page { size: A4 portrait; margin: 0; }
        body { 
            background: white !important; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
            display: block !important;
            height: auto !important;
            overflow: visible !important;
            margin: 0; padding: 0;
        }
        
        /* Hide everything except print container */
        body > aside, body > main > header, #ui-container { display: none !important; }
        
        /* Show print container */
        #print-container { display: block !important; }

        /* Print single or all logic */
        body.print-single .result-card { display: none !important; }
        body.print-single .result-card.print-selected { display: block !important; page-break-after: always; break-after: page; }
        
        body.print-all .result-card { display: block !important; page-break-after: always; break-after: page; }
        body.print-all .result-card:last-of-type { page-break-after: auto; break-after: auto; }
        
        .result-card { box-shadow: none !important; margin: 0 !important; border: none !important; min-height: 296mm !important; }
        .school-header { border-bottom: 2px solid #211B17; padding-bottom: 10px; margin-bottom: 15px; }
        .student-details { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; border: 1px solid #e5e7eb; padding: 10px; border-radius: 8px; }
    }
</style>


<div id="ui-container">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background:var(--color-primary);">
        <div><h1 class="text-base font-bold"><?php echo e($exam->title); ?></h1><p class="mt-1 text-xs"><?php echo e($exam->schoolClass->name); ?> · <?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?> to <?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></p></div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-print-all-results class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition"><i data-lucide="printer" class="h-3.5 w-3.5"></i> Print All Results</button>
            <a href="<?php echo e(route('examination.results.index')); ?>" class="rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition">Exam List</a>
            <a href="<?php echo e(route('examination.results.generate-form', $exam)); ?>" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition"><i data-lucide="square-pen" class="h-3.5 w-3.5"></i> Generate Pending Results</a>
        </div>
    </div>

    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-5">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <p class="text-sm font-bold text-heading">Student Results</p>
        </div>

        
        <div id="bulkToolbar" class="hidden mx-5 mt-3 mb-1 flex items-center gap-3 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-2.5">
            <i data-lucide="check-square" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
            <span id="selectedCount" class="text-sm font-semibold text-indigo-700">0 selected</span>
            
            <button type="button" onclick="printSelectedResults()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors shadow-sm" style="background-color: #4f46e5; color: #ffffff;">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Selected
            </button>

            <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('examination.results.bulk-destroy', $exam)); ?>" onsubmit="return confirmBulkDelete()" class="ml-2">
                <?php echo csrf_field(); ?>
                <div id="bulkIdsContainer"></div>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors shadow-sm" style="background-color: #dc2626; color: #ffffff;">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
                </button>
            </form>
            <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline ml-2">Clear Selection</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100" style="background:#F5F1EB;">
                        <th class="px-4 py-3 text-center text-xs font-semibold text-body w-10">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                   class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                                   title="Select / Deselect All">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Student Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Section</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Exam Roll No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Admission No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Overall Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-body">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php 
                            $items = $result->items->filter(fn ($item) => $item->paper !== null);
                            $failLabel = $exam->result_thresholds['subject_fail_label'] ?? 'Fail';
                            $failedCount = $items->filter(fn ($item) => stripos($item->status, $failLabel) !== false || stripos($item->status, 'Fail') !== false)->count();
                            
                            $passThreshold = (float) ($exam->result_thresholds['overall_pass_threshold'] ?? 0);
                            $maxFailedSubjects = (int) ($exam->result_thresholds['max_failed_subjects'] ?? 0);
                            
                            $totalMaxMarks = 0;
                            $totalMarks = 0;
                            foreach($items as $i) {
                                $totalMaxMarks += (float) ($i->paper->maximum_marks ?? 0);
                                $totalMarks += (float) ($i->obtained_marks ?? 0);
                            }
                            $percentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;
                            
                            $overallStatus = ($percentage >= $passThreshold && $failedCount <= $maxFailedSubjects) ? 'Pass' : 'Fail';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors row-item">
                            <td class="px-4 py-2 text-center">
                                <input type="checkbox" value="<?php echo e($result->id); ?>" onchange="updateBulkToolbar()"
                                       class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-3 font-semibold text-navy"><?php echo e($result->student->full_name); ?></td>
                            <td class="px-4 py-3 text-body text-xs"><?php echo e($result->student->schoolClass->name ?? '---'); ?></td>
                            <td class="px-4 py-3 text-body text-xs"><?php echo e($result->student->section?->name ?? '---'); ?></td>
                            <td class="px-4 py-3 text-body text-xs font-mono"><?php echo e($result->student->roll_number ?? '---'); ?></td>
                            <td class="px-4 py-3 text-body text-xs font-mono"><?php echo e($result->student->admission_no); ?></td>
                            <td class="px-4 py-3 text-xs font-semibold">
                                <?php if($overallStatus === 'Pass'): ?>
                                    <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-200">Pass</span>
                                <?php else: ?>
                                    <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200">Fail</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" data-print-result="<?php echo e($result->id); ?>" title="Print Result"
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <a href="<?php echo e(route('examination.results.generate-form', ['exam' => $exam->id, 'student_id' => $result->student_id])); ?>#result-entry"
                                       title="Edit Result"
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    
                                    <form method="POST" action="<?php echo e(route('examination.results.destroy', ['exam' => $exam->id, 'result' => $result->id])); ?>"
                                          onsubmit="return confirm('Are you sure you want to permanently delete this result?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" title="Delete Result"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-body text-sm">
                                <div class="flex flex-col items-center gap-3">
                                    <p class="text-gray-500">No results found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="print-container" style="display: none;">
<?php
    // Calculate Ranks
    $examResults = $results->map(function($r) {
        $totalMarks = 0;
        $totalMaxMarks = 0;
        foreach($r->items as $item) {
            if($item->paper) {
                $totalMarks += (float) $item->obtained_marks;
                $totalMaxMarks += (float) $item->paper->maximum_marks;
            }
        }
        $r->total_marks = $totalMarks;
        $r->total_max_marks = $totalMaxMarks;
        $r->percentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;
        return $r;
    });

    $rankedResults = $examResults->sortByDesc('total_marks')->values();
    $currentRank = 1;
    $previousMarks = null;
    $rankOffset = 0;

    foreach($rankedResults as $idx => $r) {
        if ($previousMarks === null) {
            $r->rank = $currentRank;
        } else {
            if ($r->total_marks == $previousMarks) {
                $r->rank = $currentRank;
                $rankOffset++;
            } else {
                $currentRank += $rankOffset + 1;
                $r->rank = $currentRank;
                $rankOffset = 0;
            }
        }
        $previousMarks = $r->total_marks;
    }
?>

<?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php 
        $items = $result->items->filter(fn ($item) => $item->paper !== null);
        
        // Calculate failed subjects count
        $failLabel = $exam->result_thresholds['subject_fail_label'] ?? 'Fail';
        $failedCount = $items->filter(fn ($item) => stripos($item->status, $failLabel) !== false || stripos($item->status, 'Fail') !== false)->count();
        
        // Check overall thresholds
        $passThreshold = (float) ($exam->result_thresholds['overall_pass_threshold'] ?? 0);
        $maxFailedSubjects = (int) ($exam->result_thresholds['max_failed_subjects'] ?? 0);
        
        $overallStatus = ($result->percentage >= $passThreshold && $failedCount <= $maxFailedSubjects) ? 'Pass' : 'Fail';
        
        // Find Grade
        $grade = null;
        if($exam->grade_criteria) {
            $gradeRow = collect($exam->grade_criteria)->first(function($row) use($result) {
                return $result->percentage >= (float) $row['percentage_from'] && $result->percentage <= (float) $row['percentage_to'];
            });
            $grade = $gradeRow['grade'] ?? '---';
        }
    ?>
    
    <?php
        $school = $exam->school;
        $session = $exam->academicSession ?? \App\Models\AcademicSession::find(session('selected_academic_session_id'));
        $bg = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'examination_background');
        $bgClass = $bg ? 'has-bg' : '';
        $showPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exam_result_photo', '0') == '1';
    ?>
    
    <div id="result-<?php echo e($result->id); ?>" class="result-card p-8 mb-6 <?php echo e($bgClass); ?>" style="font-family: sans-serif; position: relative; min-height: 296mm; background-color: transparent !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; display: flex; flex-direction: column; overflow: hidden; z-index: 0; border: none;">
        
        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;" alt="">
        <?php if($bg): ?>
            <img src="<?php echo e(asset($bg)); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; opacity: 0.85;" alt="Background">
        <?php endif; ?>
        <div style="position: relative; z-index: 1; flex: 1; display: flex; flex-direction: column;">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-black"><?php echo e($exam->school->name ?? 'School Name'); ?></h1>
            <p class="text-sm font-bold mt-2 underline">Exam: <?php echo e($exam->title); ?> (<?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?> - <?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?>)</p>
        </div>

        
        <div class="flex justify-between items-start mb-6 text-xs text-black">
            <div class="flex-1">
                <table class="w-full text-left">
                    <tr><td class="font-bold py-1 w-32">Student Name:</td><td class="font-bold"><?php echo e($result->student->full_name); ?></td></tr>
                    <tr><td class="font-bold py-1">Admission Number:</td><td class="font-bold"><?php echo e($result->student->admission_no); ?></td></tr>
                    <tr><td class="font-bold py-1">Class: <span class="font-normal"><?php echo e($result->student->schoolClass->name ?? '---'); ?></span></td><td class="font-bold">Section: <span class="font-normal"><?php echo e($result->student->section?->name ?? '---'); ?></span></td></tr>
                    <tr><td class="font-bold py-1">Father's Name:</td><td class="font-bold"><?php echo e($result->student->father_name ?? '---'); ?></td></tr>
                </table>
            </div>
            <div class="flex-1 text-right pr-10">
                <table class="w-full text-right">
                    <tr><td class="font-bold py-1">Session:</td><td class="font-bold"><?php echo e($exam->session->name ?? '---'); ?></td></tr>
                    <tr><td class="font-bold py-1">Exam Roll Number:</td><td class="font-bold"><?php echo e($result->student->roll_number ?? '---'); ?></td></tr>
                </table>
            </div>
            <?php if($showPhoto): ?>
                <?php if($result->student->photo): ?>
                    <div class="border border-black flex-shrink-0" style="width: 70px; height: 90px;">
                        <img src="<?php echo e($result->student->photo_url); ?>" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="border border-black flex-shrink-0 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400" style="width: 70px; height: 90px;">Photo</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        
        <div class="mb-2 border border-black">
            <table class="w-full text-xs text-left border-collapse text-black">
                <thead>
                    <tr class="border-b border-black">
                        <?php if($exam->settings['subject_code'] ?? false): ?>
                            <th class="p-1.5 font-bold border-r border-black">Paper Code</th>
                        <?php endif; ?>
                        <th class="p-1.5 font-bold border-r border-black">Subject Name</th>
                        <?php if($exam->settings['subject_type'] ?? false): ?>
                            <th class="p-1.5 font-bold border-r border-black">Subject Type</th>
                        <?php endif; ?>
                        <?php if($exam->exam_type === 'marks'): ?>
                            <?php if($exam->settings['total_marks'] ?? false): ?>
                                <th class="p-1.5 font-bold border-r border-black text-center">Maximum Marks</th>
                            <?php endif; ?>
                            <?php if($exam->settings['total_obtained_marks'] ?? false): ?>
                                <th class="p-1.5 font-bold border-r border-black text-center">Obtained Marks</th>
                            <?php endif; ?>
                        <?php endif; ?>
                        <th class="p-1.5 font-bold text-center <?php echo e(($exam->result_thresholds['show_subject_status'] ?? false) ? 'border-r border-black' : ''); ?>">Grade</th>
                        <?php if($exam->result_thresholds['show_subject_status'] ?? false): ?>
                            <th class="p-1.5 font-bold text-center"><?php echo e($exam->result_thresholds['subject_status_header'] ?? 'Status'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-black">
                            <?php if($exam->settings['subject_code'] ?? false): ?>
                                <td class="p-1.5 border-r border-black"><?php echo e($item->paper->paper_code ?? '---'); ?></td>
                            <?php endif; ?>
                            <td class="p-1.5 border-r border-black"><?php echo e($item->paper->subject_name); ?></td>
                            <?php if($exam->settings['subject_type'] ?? false): ?>
                                <td class="p-1.5 border-r border-black"><?php echo e($item->paper->subject_type ?? '---'); ?></td>
                            <?php endif; ?>
                            <?php if($exam->exam_type === 'marks'): ?>
                                <?php if($exam->settings['total_marks'] ?? false): ?>
                                    <td class="p-1.5 border-r border-black text-center"><?php echo e($item->paper->maximum_marks); ?></td>
                                <?php endif; ?>
                                <?php if($exam->settings['total_obtained_marks'] ?? false): ?>
                                    <td class="p-1.5 border-r border-black text-center font-bold"><?php echo e($item->obtained_marks ?? '---'); ?></td>
                                <?php endif; ?>
                            <?php endif; ?>
                            <td class="p-1.5 text-center font-bold <?php echo e(($exam->result_thresholds['show_subject_status'] ?? false) ? 'border-r border-black' : ''); ?>"><?php echo e($item->grade ?? '---'); ?></td>
                            <?php if($exam->result_thresholds['show_subject_status'] ?? false): ?>
                                <td class="p-1.5 text-center font-bold"><?php echo e($item->status ?? '---'); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    
                    <?php 
                        $colspan = 1;
                        if($exam->settings['subject_code'] ?? false) $colspan++;
                        if($exam->settings['subject_type'] ?? false) $colspan++;
                        
                        $rightColspan = 1;
                        if($exam->settings['total_marks'] ?? false) $rightColspan++;
                        if($exam->settings['total_obtained_marks'] ?? false) $rightColspan++;
                        if($exam->result_thresholds['show_subject_status'] ?? false) $rightColspan++;
                    ?>

                    <?php if($exam->exam_type === 'marks'): ?>
                        <tr class="border-b border-black font-bold">
                            <td colspan="<?php echo e($colspan); ?>" class="p-1.5 border-r border-black">Total</td>
                            <?php if($exam->settings['total_marks'] ?? false): ?>
                                <td class="p-1.5 border-r border-black text-center"><?php echo e($result->total_max_marks); ?></td>
                            <?php endif; ?>
                            <?php if($exam->settings['total_obtained_marks'] ?? false): ?>
                                <td class="p-1.5 border-r border-black text-center"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format($result->total_marks, 2)); ?></td>
                            <?php endif; ?>
                            <td colspan="<?php echo e(1 + ($exam->result_thresholds['show_subject_status'] ?? false ? 1 : 0)); ?>" class="p-1.5"></td>
                        </tr>
                        <tr class="border-b border-black font-bold">
                            <td colspan="<?php echo e($colspan); ?>" class="p-1.5 border-r border-black">Percentage</td>
                            <td colspan="<?php echo e($rightColspan); ?>" class="p-1.5"><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format($result->percentage, 2)); ?> %</td>
                        </tr>
                        <?php if($exam->settings['show_ranks'] ?? false): ?>
                            <tr class="border-b border-black font-bold">
                                <td colspan="<?php echo e($colspan); ?>" class="p-1.5 border-r border-black">Rank</td>
                                <td colspan="<?php echo e($rightColspan); ?>" class="p-1.5"><?php echo e($result->rank); ?> / <?php echo e($results->count()); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($exam->result_thresholds['show_overall_status'] ?? false): ?>
                        <tr class="border-b border-black font-bold">
                            <td colspan="<?php echo e($colspan); ?>" class="p-1.5 border-r border-black"><?php echo e($exam->result_thresholds['overall_row_header'] ?? 'Overall Result'); ?></td>
                            <td colspan="<?php echo e($rightColspan); ?>" class="p-1.5 uppercase <?php echo e($overallStatus === 'Pass' ? 'text-green-700' : 'text-red-700'); ?>"><?php echo e($overallStatus === 'Pass' ? ($exam->result_thresholds['overall_pass_label'] ?? 'Pass') : ($exam->result_thresholds['overall_fail_label'] ?? 'Fail')); ?></td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr class="font-bold text-xs">
                        <td colspan="100%" class="p-1.5">
                            <div class="flex gap-4">
                                <span class="w-1/2">Teacher Remark : </span>
                                <span class="w-1/2 border-l border-black pl-2">School Remark : </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        
        <div class="mt-auto pt-20 text-xs text-black flex justify-end">
            <div class="text-center w-48">
                <?php if(($exam->settings['teacher_signature_enabled'] ?? false) && isset($exam->settings['teacher_signature_path'])): ?>
                    <img src="<?php echo e(asset('storage/'.$exam->settings['teacher_signature_path'])); ?>" class="h-10 mx-auto mb-1 object-contain">
                <?php else: ?>
                    <div class="h-10"></div>
                <?php endif; ?>
                <div class="border-t border-black pt-1 font-bold">Principal Signature</div>
            </div>
        </div>
        </div> 
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<script>
    document.querySelectorAll('[data-print-result]').forEach((button) => {
        button.addEventListener('click', () => printResult(button.dataset.printResult));
    });

    document.querySelector('[data-print-all-results]')?.addEventListener('click', printAllResults);

    function printResult(resultId) {
        document.body.classList.remove('print-all');
        document.body.classList.add('print-single');
        document.querySelectorAll('.result-card').forEach((card) => {
            card.classList.toggle('print-selected', card.id === `result-${resultId}`);
        });
        window.print();
    }

    function printSelectedResults() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;
        
        document.body.classList.remove('print-all');
        document.body.classList.add('print-single');
        document.querySelectorAll('.result-card').forEach((card) => {
            const resultId = card.id.replace('result-', '');
            card.classList.toggle('print-selected', selectedIds.includes(resultId));
        });
        window.print();
    }

    function printAllResults() {
        document.body.classList.remove('print-single');
        document.body.classList.add('print-all');
        document.querySelectorAll('.result-card').forEach((card) => card.classList.remove('print-selected'));
        window.print();
    }

    window.addEventListener('afterprint', () => {
        document.body.classList.remove('print-single', 'print-all');
        document.querySelectorAll('.result-card').forEach((card) => card.classList.remove('print-selected'));
    });

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
            toolbar.classList.remove('hidden');
            countEl.textContent = checked.length + ' result(s) selected';
        } else {
            toolbar.classList.add('hidden');
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
        document.getElementById('bulkToolbar').classList.add('hidden');
    }
    function confirmBulkDelete() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm(checked.length + ' result(s) permanently delete honge. Sure hain?')) return false;
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/results/view.blade.php ENDPATH**/ ?>