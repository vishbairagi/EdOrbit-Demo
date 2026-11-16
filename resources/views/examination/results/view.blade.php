@extends('layouts.app')

@section('header', 'Generated Exam Results')

@section('content')
@php
    // Subject status is always evaluated live against the exam's current pass threshold/labels,
    // so changing the threshold in Result Settings applies to existing results too.
    $subjectStatusOf = function ($item) use ($exam) {
        $t = $exam->result_thresholds ?? [];
        $max = (float) ($item->paper->maximum_marks ?? 0);
        if (($t['show_subject_status'] ?? false) && $exam->exam_type === 'marks' && $item->obtained_marks !== null && $max > 0) {
            $pct = ((float) $item->obtained_marks / $max) * 100;
            return $pct >= (float) ($t['subject_pass_threshold'] ?? 0)
                ? ($t['subject_pass_label'] ?? 'Pass')
                : ($t['subject_fail_label'] ?? 'Fail');
        }
        return $item->status;
    };
@endphp
<style>
    .result-card {
        background: #fff; color: #000; box-sizing: border-box;
        width: 100%; max-width: 210mm; margin: 0 auto 24px auto; padding: 10mm 12mm;
        font-family: "Segoe UI", Roboto, Arial, sans-serif; font-size: 11px; line-height: 1.4;
    }
    .rc-header { text-align: center; }
    .rc-school { font-size: 12px; font-weight: 700; margin: 0 0 2px 0; }
    .rc-contact { font-size: 7px; margin-bottom: 1px; }
    .rc-divider { border-bottom: 1px solid #d1d5db; margin: 8px 0 12px 0; }
    .rc-title { text-align: center; font-size: 13px; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; margin-bottom: 12px; }

    .rc-details { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 12px; }
    .rc-details-col { flex: 1; }
    .rc-row { border-bottom: 1px solid #e5e7eb; padding: 3px 0 4px 0; font-size: 11px; }
    .rc-split { display: flex; justify-content: space-between; }
    .rc-photo { width: 70px; height: 80px; flex-shrink: 0; border: 1px solid #d1d5db; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #6b7280; }
    .rc-photo img { width: 100%; height: 100%; object-fit: cover; }

    table.rc-table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #000 !important; margin-bottom: 24px; }
    table.rc-table thead tr, table.rc-table thead th, table.rc-table tbody tr, table.rc-table tbody td {
        background: #fff !important; color: #000 !important; border: 1px solid #000 !important;
        padding: 3px 6px !important; text-align: left !important; font-size: 10px !important;
        text-transform: none !important; letter-spacing: normal !important; white-space: normal !important;
    }
    table.rc-table thead th { font-weight: 700 !important; }
    table.rc-table tr.rc-bold td { font-weight: 700 !important; }

    .rc-sign-wrap { display: flex; justify-content: flex-end; margin-top: 40px; }
    .rc-sign { width: 140px; text-align: center; }
    .rc-sign-img { min-height: 26px; display: flex; align-items: center; justify-content: center; }
    .rc-sign-img img { height: 28px; max-width: 130px; object-fit: contain; }
    .rc-script { font-family: 'Dancing Script', 'Brush Script MT', cursive; font-size: 12px; font-style: italic; }
    .rc-sign-line { border-bottom: 1px solid #000; margin: 2px 0 3px 0; }
    .rc-sign-title { font-size: 8px; font-weight: 700; }

    @media print {
        @page { size: A4 portrait; margin: 8mm; }
        html, body, main, .page-content-area {
            background: #fff !important; padding: 0 !important; margin: 0 !important;
            height: auto !important; min-height: 0 !important; overflow: visible !important; display: block !important;
        }
        body > aside, body > main > header, main > header, #ui-container { display: none !important; }
        #print-container { display: block !important; }
        .result-card { margin: 0 auto !important; padding: 0 !important; max-width: none !important; }
        body.print-single .result-card { display: none !important; }
        body.print-single .result-card.print-selected { display: block !important; page-break-after: always; break-after: page; }
        body.print-all .result-card { display: block !important; page-break-after: always; break-after: page; }
        body.print-all .result-card:last-of-type { page-break-after: auto; break-after: auto; }
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">

{{-- UI Container (Hidden on Print) --}}
<div id="ui-container">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background:var(--color-primary);">
        <div><h1 class="text-base font-bold">{{ $exam->title }}</h1><p class="mt-1 text-xs">{{ $exam->schoolClass->name }} Â· {{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} to {{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</p></div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-print-all-results class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition"><i data-lucide="printer" class="h-3.5 w-3.5"></i> Print All Results</button>
            <a href="{{ route('examination.results.index') }}" class="rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition">Exam List</a>
            <a href="{{ route('examination.results.generate-form', $exam) }}" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition"><i data-lucide="square-pen" class="h-3.5 w-3.5"></i> Generate Pending Results</a>
        </div>
    </div>

    <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-5">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <p class="text-sm font-bold text-heading">Student Results</p>
        </div>

        {{-- Bulk Actions Toolbar --}}
        <div id="bulkToolbar" class="hidden mx-5 mt-3 mb-1 flex items-center gap-3 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-2.5">
            <i data-lucide="check-square" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
            <span id="selectedCount" class="text-sm font-semibold text-indigo-700">0 selected</span>
            
            <button type="button" onclick="printSelectedResults()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors shadow-sm" style="background-color: #4f46e5; color: #ffffff;">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Selected
            </button>

            <form id="bulkDeleteForm" method="POST" action="{{ route('examination.results.bulk-destroy', $exam) }}" onsubmit="return confirmBulkDelete()" class="ml-2">
                @csrf
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
                    @forelse($results as $result)
                        @php 
                            $items = $result->items->filter(fn ($item) => $item->paper !== null);
                            $failLabel = $exam->result_thresholds['subject_fail_label'] ?? 'Fail';
                            $failedCount = $items->filter(fn ($item) => stripos((string) $subjectStatusOf($item), $failLabel) !== false || stripos((string) $subjectStatusOf($item), 'Fail') !== false)->count();
                            
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
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors row-item">
                            <td class="px-4 py-2 text-center">
                                <input type="checkbox" value="{{ $result->id }}" onchange="updateBulkToolbar()"
                                       class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-3 font-semibold text-navy">{{ $result->student->full_name }}</td>
                            <td class="px-4 py-3 text-body text-xs">{{ $result->student->schoolClass->name ?? '---' }}</td>
                            <td class="px-4 py-3 text-body text-xs">{{ $result->student->section?->name ?? '---' }}</td>
                            <td class="px-4 py-3 text-body text-xs font-mono">{{ $result->student->roll_number ?? '---' }}</td>
                            <td class="px-4 py-3 text-body text-xs font-mono">{{ $result->student->admission_no }}</td>
                            <td class="px-4 py-3 text-xs font-semibold">
                                @if($overallStatus === 'Pass')
                                    <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-200">Pass</span>
                                @else
                                    <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200">Fail</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" data-print-result="{{ $result->id }}" title="Print Result"
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <a href="{{ route('examination.results.generate-form', ['exam' => $exam->id, 'student_id' => $result->student_id]) }}#result-entry"
                                       title="Edit Result"
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('examination.results.destroy', ['exam' => $exam->id, 'result' => $result->id]) }}"
                                          onsubmit="return confirm('Are you sure you want to permanently delete this result?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Delete Result"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-body text-sm">
                                <div class="flex flex-col items-center gap-3">
                                    <p class="text-gray-500">No results found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Print Container (Visible only on Print) --}}
<div id="print-container" style="display: none;">
@php
    $admitRolls = $exam->admitCards()->pluck('exam_roll_number', 'student_id');
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
@endphp

@foreach($results as $result)
    @php 
        $items = $result->items->filter(fn ($item) => $item->paper !== null);
        
        // Calculate failed subjects count
        $failLabel = $exam->result_thresholds['subject_fail_label'] ?? 'Fail';
        $failedCount = $items->filter(fn ($item) => stripos((string) $subjectStatusOf($item), $failLabel) !== false || stripos((string) $subjectStatusOf($item), 'Fail') !== false)->count();
        
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
    @endphp
    
    @php
        $school = $exam->school;
        $session = $exam->session ?? \App\Models\AcademicSession::find($exam->academic_session_id);
        $showPhoto = \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'exam_result_photo', '0') == '1';
        $dateFmt = \App\Models\SystemSetting::getSetting('date_format', 'd-m-Y');
        $showSubjectStatus = (bool) ($exam->result_thresholds['show_subject_status'] ?? false);
        $showCode = (bool) ($exam->settings['subject_code'] ?? false);
        $showType = (bool) ($exam->settings['subject_type'] ?? false);
        $showMax = $exam->exam_type === 'marks' && ($exam->settings['total_marks'] ?? false);
        $showObt = $exam->exam_type === 'marks' && ($exam->settings['total_obtained_marks'] ?? false);

        $leftCols = 1 + ($showCode ? 1 : 0) + ($showType ? 1 : 0);
        $markCols = ($showMax ? 1 : 0) + ($showObt ? 1 : 0);
        $tailCols = 1 + ($showSubjectStatus ? 1 : 0) + 1; // grade + status + remarks
        $totalCols = $leftCols + $markCols + $tailCols;

        $schoolSig = \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'school_signature');
        $sigPath = (($exam->settings['teacher_signature_enabled'] ?? false) && !empty($exam->settings['teacher_signature_path']))
            ? 'storage/'.$exam->settings['teacher_signature_path']
            : ($schoolSig ? 'storage/'.$schoolSig : null);
        $gradeRemarks = collect($exam->grade_criteria ?? [])->filter(fn ($r) => !empty($r['remark']))->pluck('remark', 'grade')->all();
        $examRoll = $admitRolls[$result->student_id] ?? ($result->student->roll_number ?? '---');
    @endphp

    <div id="result-{{ $result->id }}" class="result-card">
        {{-- School Header --}}
        <div class="rc-header">
            <h1 class="rc-school">{{ $school->name ?? 'School Name' }}</h1>
            <div class="rc-contact">Phone: {{ $school->phone ?? 'â€”' }} &nbsp;|&nbsp; Email: {{ $school->email ?? 'â€”' }}</div>
            <div class="rc-contact">Address: {{ $school->address ?? 'â€”' }}</div>
            <div class="rc-divider"></div>
        </div>

        {{-- Exam Title --}}
        <div class="rc-title">Exam: {{ $exam->title }} ({{ $exam->start_date->format($dateFmt) }} - {{ $exam->end_date->format($dateFmt) }})</div>

        {{-- Student Details --}}
        <div class="rc-details">
            <div class="rc-details-col">
                <div class="rc-row"><b>Student Name:</b>&nbsp;{{ $result->student->full_name }}</div>
                <div class="rc-row rc-split">
                    <span><b>Admission Number:</b> {{ $result->student->admission_no }}</span>
                    <span><b>Session:</b> {{ $session->name ?? '---' }}</span>
                </div>
                <div class="rc-row rc-split">
                    <span><b>Class:</b> {{ $result->student->schoolClass->name ?? '---' }} &nbsp;&nbsp; <b>Section:</b> {{ $result->student->section?->name ?? '---' }}</span>
                    <span><b>Exam Roll Number:</b> {{ $examRoll }}</span>
                </div>
                <div class="rc-row"><b>Father's Name:</b>&nbsp;{{ $result->student->father_name ?? '---' }}</div>
            </div>
            @if($showPhoto)
                <div class="rc-photo">
                    @if($result->student->photo_url)
                        <img src="{{ $result->student->photo_url }}" alt="">
                    @else
                        <span>Photo</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Subjects Table --}}
        <table class="rc-table no-theme-table">
            <thead>
                <tr>
                    @if($showCode)<th>Paper Code</th>@endif
                    <th>Subject Name</th>
                    @if($showType)<th>Subject Type</th>@endif
                    @if($showMax)<th>Maximum Marks</th>@endif
                    @if($showObt)<th>Obtained Marks</th>@endif
                    <th>Grade</th>
                    @if($showSubjectStatus)<th>{{ $exam->result_thresholds['subject_status_header'] ?? 'Status' }}</th>@endif
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        @if($showCode)<td>{{ $item->paper->paper_code ?? '---' }}</td>@endif
                        <td>{{ $item->paper->subject_name }}</td>
                        @if($showType)<td>{{ $item->paper->subject_type ?? '---' }}</td>@endif
                        @if($showMax)<td>{{ rtrim(rtrim(number_format((float) $item->paper->maximum_marks, 2, '.', ''), '0'), '.') }}</td>@endif
                        @if($showObt)<td>{{ $item->obtained_marks !== null ? rtrim(rtrim(number_format((float) $item->obtained_marks, 2, '.', ''), '0'), '.') : '---' }}</td>@endif
                        <td>{{ $item->grade ?? '---' }}</td>
                        @if($showSubjectStatus)<td><b>{{ $subjectStatusOf($item) ?? '---' }}</b></td>@endif
                        <td>{{ $item->remark ?: ($gradeRemarks[$item->grade] ?? '') }}</td>
                    </tr>
                @endforeach

                @if($exam->exam_type === 'marks')
                    <tr class="rc-bold">
                        <td colspan="{{ $leftCols }}">Total</td>
                        @if($showMax)<td>{{ rtrim(rtrim(number_format($result->total_max_marks, 2, '.', ''), '0'), '.') }}</td>@endif
                        @if($showObt)<td>{{ rtrim(rtrim(number_format($result->total_marks, 2, '.', ''), '0'), '.') }}</td>@endif
                        <td colspan="{{ $tailCols }}"></td>
                    </tr>
                    <tr class="rc-bold">
                        <td colspan="{{ $leftCols }}">Percentage</td>
                        @if($markCols > 0)<td colspan="{{ $markCols }}">{{ number_format($result->percentage, 2) }} %</td>@endif
                        <td>{{ $grade ?? '---' }}</td>
                        <td colspan="{{ $tailCols - 1 }}"></td>
                    </tr>
                @endif

                @if($exam->result_thresholds['show_overall_status'] ?? false)
                    <tr class="rc-bold">
                        <td colspan="{{ $leftCols }}">{{ $exam->result_thresholds['overall_row_header'] ?? 'Overall Result' }}</td>
                        <td colspan="{{ $totalCols - $leftCols }}">{{ $overallStatus === 'Pass' ? ($exam->result_thresholds['overall_pass_label'] ?? 'Pass') : ($exam->result_thresholds['overall_fail_label'] ?? 'Fail') }}</td>
                    </tr>
                @endif

                @if($exam->exam_type === 'marks' && ($exam->settings['show_ranks'] ?? false))
                    <tr class="rc-bold">
                        <td colspan="{{ $leftCols }}">Rank</td>
                        <td colspan="{{ $totalCols - $leftCols }}">{{ $result->rank }} / {{ $results->count() }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- Signature --}}
        <div class="rc-sign-wrap">
            <div class="rc-sign">
                <div class="rc-sign-img">
                    @if($sigPath && file_exists(public_path($sigPath)))
                        <img src="{{ asset($sigPath) }}" alt="Signature">
                    @else
                        <span class="rc-script">{{ $school->name ?? '' }}</span>
                    @endif
                </div>
                <div class="rc-sign-line"></div>
                <div class="rc-sign-title">Principal Signature</div>
            </div>
        </div>
    </div>
@endforeach
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
@endsection
