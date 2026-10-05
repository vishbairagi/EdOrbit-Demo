@extends('layouts.app')

@section('header', 'Academic Report')

@section('content')
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
    <form method="GET" action="{{ route('examination.reports.academic') }}" class="grid items-end gap-3 md:grid-cols-4">
        <label class="text-xs font-semibold">Class
            <select name="class_id" onchange="this.form.section_id.value='';this.form.student_id.value='';this.form.submit()" class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal focus:ring-1 focus:ring-navy">
                <option value="">Select Class</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected($schoolClass?->id === $class->id)>{{ $class->name }}</option>@endforeach
            </select>
        </label>
        <label class="text-xs font-semibold">Section
            <select name="section_id" onchange="this.form.student_id.value='';this.form.submit()" @disabled(!$schoolClass) class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal focus:ring-1 focus:ring-navy">
                <option value="">Select Section</option>@foreach($sections as $item)<option value="{{ $item->id }}" @selected($section?->id === $item->id)>{{ $item->name }}</option>@endforeach
            </select>
        </label>
        <label class="text-xs font-semibold">Student
            <select name="student_id" @disabled(!$section) class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal focus:ring-1 focus:ring-navy">
                <option value="">Select Student</option>@foreach($students as $item)<option value="{{ $item->id }}" @selected($student?->id === $item->id)>{{ $item->full_name }} · {{ $item->admission_no }}</option>@endforeach
            </select>
        </label>
        <button class="rounded px-4 py-2 text-xs font-bold text-white transition-opacity hover:opacity-90" style="background:var(--color-primary);">Fetch Report</button>
    </form>
</section>

@if($student)
    <div class="print-hide mb-4 flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <div>
            <h1 class="text-sm font-bold text-heading">{{ $student->full_name }}</h1>
            <p class="mt-1 text-xs text-body">{{ $student->schoolClass->name }} · {{ $student->section?->name ?? '—' }} · Admission No. {{ $student->admission_no }}</p>
        </div>
        @if($results->isNotEmpty())
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded px-4 py-2 text-xs font-bold text-white transition-opacity hover:opacity-90 shadow-sm" style="background:var(--color-primary);">
                <i data-lucide="printer" class="h-4 w-4"></i> Print Full Report
            </button>
        @endif
    </div>

    @if($results->isEmpty())
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-8 text-center text-sm text-sky-900 font-medium">
            No exam results found for this student in the {{ $session->name }} session yet. <br>
            <span class="text-xs font-normal mt-2 block">Generate results for this student in the Exam Results module first.</span>
        </div>
    @else
        <div id="print-container">
            {{-- PAGE 1: Overall Profile & Combined Summary --}}
            <div class="report-page bg-white p-6 border border-gray-200 rounded-xl shadow-sm">
                {{-- School Header --}}
                <div class="school-header">
                    <h1 class="text-3xl font-black uppercase text-gray-900 tracking-wide">{{ $student->school->name ?? 'School Name' }}</h1>
                    <p class="text-gray-700 mt-2 text-sm">{{ $student->school->address ?? '' }}</p>
                    <p class="text-gray-700 text-sm font-medium mt-1">{{ $student->school->phone ?? '' }} &nbsp;|&nbsp; {{ $student->school->email ?? '' }}</p>
                    <div class="mt-6 inline-block bg-gray-900 text-white px-6 py-2 rounded-full">
                        <h2 class="text-lg font-bold uppercase tracking-widest">Annual Academic Report</h2>
                    </div>
                    <p class="text-sm font-bold text-gray-600 mt-3 uppercase tracking-wide">Session: {{ $session->name }}</p>
                </div>

                {{-- Student Profile --}}
                <div class="student-details text-sm">
                    <div>
                        <table class="w-full">
                            <tr><td class="py-1.5 font-bold text-gray-600 w-32">Student Name:</td><td class="text-gray-900 font-black uppercase text-base">{{ $student->full_name }}</td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Class & Section:</td><td class="text-gray-900 font-bold">{{ $student->schoolClass->name }} - {{ $student->section?->name ?? '' }}</td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Father's Name:</td><td class="text-gray-900">{{ $student->father_name ?? '—' }}</td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Mother's Name:</td><td class="text-gray-900">{{ $student->mother_name ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div>
                        <table class="w-full">
                            <tr><td class="py-1.5 font-bold text-gray-600 w-32">Admission No:</td><td class="text-gray-900 font-mono font-bold">{{ $student->admission_no }}</td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Date of Birth:</td><td class="text-gray-900">{{ $student->dob ? $student->dob->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) : '—' }}</td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Blood Group:</td><td class="text-gray-900">{{ $student->blood_group ?? '—' }}</td></tr>
                            <tr><td class="py-1.5 font-bold text-gray-600">Contact:</td><td class="text-gray-900">{{ $student->phone ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Academic Performance Overview --}}
                <div class="mt-8 mb-4">
                    <h3 class="text-lg font-black uppercase text-gray-800 border-b-2 border-gray-800 pb-2 mb-4">Performance Overview</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($results as $result)
                            @php
                                $exam = $result->exam;
                                $items = $result->items->filter(fn ($item) => $item->paper !== null)->values();
                                $totalMaximum = $items->sum(fn ($item) => (float) ($item->paper->maximum_marks ?? 0));
                                $totalObtained = $items->sum(fn ($item) => (float) ($item->obtained_marks ?? 0));
                                $percentage = $totalMaximum > 0 ? ($totalObtained / $totalMaximum) * 100 : 0;
                            @endphp
                            <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $exam->title }}</p>
                                @if($exam->exam_type === 'marks')
                                    <div class="text-2xl font-black text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($percentage, 1) }}%</div>
                                    <div class="text-xs font-medium text-gray-500 mt-1">{{ $totalObtained }} / {{ $totalMaximum }}</div>
                                @else
                                    <div class="text-2xl font-black text-gray-900">Grades</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- PAGE 2+: Detailed Exam Breakdowns --}}
            <div class="report-page bg-white p-6 border border-gray-200 rounded-xl shadow-sm mt-6">
                <h3 class="text-xl font-black uppercase text-gray-800 border-b-2 border-gray-800 pb-2 mb-6 text-center">Detailed Examination Results</h3>
                
                @foreach($results as $result)
                    @php
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
                    @endphp
                    
                    <div class="page-break-inside-avoid mb-10 border border-gray-300 rounded-lg overflow-hidden">
                        {{-- Exam Header --}}
                        <div class="bg-gray-100 border-b border-gray-300 px-5 py-3 flex justify-between items-center">
                            <div>
                                <h4 class="text-base font-black text-gray-900 uppercase">{{ $exam->title }}</h4>
                                <p class="text-xs text-gray-600 font-medium mt-0.5">{{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} - {{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</p>
                            </div>
                            @if($resultSettings['show_ranks'] ?? false)
                                <div class="bg-white border border-gray-300 px-3 py-1.5 rounded text-xs font-bold text-gray-800">
                                    {{ ($resultSettings['rank_criteria'] ?? 'class') === 'section' ? 'Section' : 'Class' }} Rank: {{ $result->rank ?? '—' }}
                                </div>
                            @endif
                        </div>

                        {{-- Subjects Table --}}
                        <table class="w-full text-sm text-left">
                            <thead class="bg-white border-b-2 border-gray-200">
                                <tr>
                                    <th class="p-3 font-bold text-gray-700">Subject</th>
                                    @if($resultSettings['subject_code'] ?? false)<th class="p-3 font-bold text-gray-700">Code</th>@endif
                                    @if($resultSettings['subject_type'] ?? true)<th class="p-3 font-bold text-gray-700 text-center">Type</th>@endif 
                                    @if($exam->exam_type === 'marks' && ($resultSettings['total_marks'] ?? true))<th class="p-3 font-bold text-gray-700 text-center">Max Marks</th>@endif 
                                    @if($exam->exam_type === 'marks' && ($resultSettings['total_obtained_marks'] ?? true))<th class="p-3 font-bold text-gray-700 text-center">Obtained</th>@endif 
                                    @if($resultSettings['overall_grade'] ?? false)<th class="p-3 font-bold text-gray-700 text-center">Grade</th>@endif 
                                    @if($resultThresholds['show_subject_status'] ?? false)<th class="p-3 font-bold text-gray-700 text-center">{{ $resultThresholds['subject_status_header'] ?? 'Status' }}</th>@endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($items as $item)
                                    <tr>
                                        <td class="p-3 font-bold text-gray-800">{{ $item->paper->subject_name }}</td>
                                        @if($resultSettings['subject_code'] ?? false)<td class="p-3 text-gray-600">{{ $item->paper->paper_code ?? '—' }}</td>@endif 
                                        @if($resultSettings['subject_type'] ?? true)<td class="p-3 text-gray-600 text-center">{{ $item->paper->subject_type ?? '—' }}</td>@endif 
                                        @if($exam->exam_type === 'marks' && ($resultSettings['total_marks'] ?? true))<td class="p-3 text-gray-800 text-center font-mono">{{ $item->paper->maximum_marks }}</td>@endif 
                                        @if($exam->exam_type === 'marks' && ($resultSettings['total_obtained_marks'] ?? true))<td class="p-3 text-gray-900 text-center font-mono font-bold">{{ $item->obtained_marks ?? '—' }}</td>@endif 
                                        @if($resultSettings['overall_grade'] ?? false)<td class="p-3 text-gray-900 text-center font-bold">{{ $item->grade ?? '—' }}</td>@endif 
                                        @if($resultThresholds['show_subject_status'] ?? false)
                                            <td class="p-3 text-center font-bold">
                                                @if(stripos($item->status, 'Fail') !== false)
                                                    <span class="text-red-600">Fail</span>
                                                @elseif(stripos($item->status, 'Pass') !== false)
                                                    <span class="text-green-600">Pass</span>
                                                @else
                                                    <span class="text-gray-800">{{ $item->status ?? '—' }}</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            @if(($exam->exam_type === 'marks' && (($resultSettings['total_marks'] ?? true) || ($resultSettings['total_obtained_marks'] ?? true))) || ($resultSettings['overall_grade'] ?? false))
                                <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                                    <tr>
                                        <td colspan="{{ 1 + (int) ($resultSettings['subject_code'] ?? false) + (int) ($resultSettings['subject_type'] ?? true) }}" class="p-3 text-right font-black uppercase text-gray-800">{{ $exam->exam_type === 'marks' ? 'Grand Total' : 'Overall Grade' }}</td>
                                        @if($exam->exam_type === 'marks' && ($resultSettings['total_marks'] ?? true))<td class="p-3 text-center font-black font-mono text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($totalMaximum, 2) }}</td>@endif 
                                        @if($exam->exam_type === 'marks' && ($resultSettings['total_obtained_marks'] ?? true))<td class="p-3 text-center font-black font-mono text-gray-900">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($totalObtained, 2) }}</td>@endif 
                                        @if($resultSettings['overall_grade'] ?? false)<td class="p-3 text-center font-black text-gray-900">{{ $overallGrade ?? '—' }}</td>@endif 
                                        @if($resultThresholds['show_subject_status'] ?? false)<td></td>@endif
                                    </tr>
                                </tfoot>
                            @endif
                        </table>

                        {{-- Exam Summary Footer --}}
                        @if($resultThresholds['show_overall_status'] ?? false)
                            <div class="bg-white border-t border-gray-300 px-5 py-3 flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-700 uppercase">{{ $resultThresholds['overall_row_header'] ?? 'Overall Result' }}:</span>
                                <span class="font-black uppercase {{ $overallPass ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $overallPass ? ($resultThresholds['overall_pass_label'] ?? 'Pass') : ($resultThresholds['overall_fail_label'] ?? 'Fail') }}
                                </span>
                            </div>
                        @endif
                        
                        @if(($resultSettings['teacher_signature_enabled'] ?? false) && !empty($resultSettings['teacher_signature_path']))
                            <div class="bg-white flex justify-end border-t border-gray-200 px-5 py-3">
                                <div class="text-center">
                                    <img src="{{ asset('storage/'.$resultSettings['teacher_signature_path']) }}" alt="Signature" class="h-12 object-contain mb-1">
                                    <div class="text-xs font-semibold text-gray-500 border-t border-gray-300 pt-1 px-4">Class Teacher</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
                
                {{-- Final Signatures Area --}}
                <div class="mt-20 flex justify-between px-10">
                    <div class="text-center w-40 border-t-2 border-gray-800 pt-2 text-sm font-bold text-gray-800">Class Teacher</div>
                    <div class="text-center w-40 border-t-2 border-gray-800 pt-2 text-sm font-bold text-gray-800">Principal</div>
                </div>
            </div>
        </div>
    @endif
@endif
@endsection