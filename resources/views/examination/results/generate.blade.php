@extends('layouts.app')

@section('header', 'Generate Exam Results')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background:var(--color-primary);">
    <div><h1 class="text-base font-bold">{{ $exam->title }}</h1><p class="mt-1 text-xs">{{ $exam->schoolClass->name }} · {{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} to {{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</p></div>
    <div class="flex gap-2"><a href="{{ route('examination.results.index') }}" class="rounded border border-white/70 px-3 py-2 text-xs font-semibold">Exam List</a>@if($exam->results_count > 0)<a href="{{ route('examination.results.view', $exam) }}" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold"><i data-lucide="eye" class="h-3.5 w-3.5"></i> View Results</a>@endif</div>
</div>

@if($errors->any())<div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="mb-5 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
        <div><h2 class="text-sm font-bold text-heading">Students Pending Results</h2><p class="mt-1 text-xs text-body">Students with saved results are hidden from this list.</p></div>
        <form method="GET" action="{{ route('examination.results.generate-form', $exam) }}" class="flex items-center gap-2 text-xs">
            <label for="sort-by" class="font-semibold">Ascending Order</label>
            <select id="sort-by" name="sort_by" onchange="this.form.submit()" class="rounded border border-gray-300 bg-white px-3 py-2"><option value="roll_number" @selected(request('sort_by', 'roll_number') === 'roll_number')>Main Roll Number</option><option value="name" @selected(request('sort_by') === 'name')>Name Alphabetically</option></select>
        </form>
    </div>
    @if($students->isNotEmpty())
        <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-xs"><thead class="bg-gray-50 text-left"><tr><th class="p-3">Main Roll No.</th><th class="p-3">Student Name</th><th class="p-3">Section</th><th class="p-3">Subjects in Exam</th><th class="p-3">Action</th></tr></thead><tbody class="divide-y divide-gray-200">
            @foreach($students as $student)
                <tr><td class="p-3">{{ $student->roll_number ?? '—' }}</td><td class="p-3 font-semibold text-heading">{{ $student->full_name }}</td><td class="p-3">{{ $student->section?->name ?? '—' }}</td><td class="p-3">{{ $student->activeSubjects->whereIn('id', $exam->papers->pluck('subject_id'))->pluck('name')->join(', ') }}</td><td class="p-3"><a href="{{ route('examination.results.generate-form', ['exam' => $exam->id, 'student_id' => $student->id, 'sort_by' => request('sort_by', 'roll_number')]) }}#result-entry" class="inline-flex items-center gap-1 rounded px-3 py-1.5 font-semibold text-white" style="background:var(--color-primary);"><i data-lucide="square-pen" class="h-3.5 w-3.5"></i> Enter Result</a></td></tr>
            @endforeach
        </tbody></table></div>
    @else
        <p class="p-8 text-center text-xs text-body">All eligible students already have results, or no students match this exam’s sections and subjects.</p>
    @endif
</section>

@if($selectedStudent && $papers->isNotEmpty())
    <section id="result-entry" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3"><div><h2 class="text-sm font-bold text-heading">{{ $selectedStudent->full_name }}</h2><p class="mt-1 text-xs text-body">{{ $selectedStudent->schoolClass->name }} · {{ $selectedStudent->section?->name ?? '—' }} · Roll {{ $selectedStudent->roll_number ?? '—' }}</p></div><span class="text-xs font-semibold text-body">{{ $exam->title }}</span></div>
        <form method="POST" action="{{ route('examination.results.store') }}">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}"><input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
            <div class="overflow-x-auto"><table class="w-full min-w-[520px] text-xs"><thead class="border-b border-gray-200 text-left"><tr><th class="p-3">Paper Code</th><th class="p-3">Subject</th><th class="p-3">Subject Type</th>@if($exam->exam_type === 'marks')<th class="p-3">Maximum Marks</th><th class="p-3">Obtained Marks</th>@else<th class="p-3">Grade</th>@endif</tr></thead><tbody class="divide-y divide-gray-200">
                @foreach($papers as $paper)
                    <tr><td class="p-3">{{ $paper->paper_code ?? '—' }}</td><td class="p-3 font-semibold text-heading">{{ $paper->subject_name }}</td><td class="p-3">{{ $paper->subject_type ?? '—' }}</td>
                        @if($exam->exam_type === 'marks')
                            <td class="p-3">{{ $paper->maximum_marks }}</td><td class="p-2"><label class="sr-only" for="mark-{{ $paper->id }}">Obtained marks for {{ $paper->subject_name }}</label><input id="mark-{{ $paper->id }}" name="marks[{{ $paper->id }}]" type="number" min="0" max="{{ $paper->maximum_marks }}" step="0.01" required value="{{ old('marks.'.$paper->id, $existingResult?->items->firstWhere('exam_paper_id', $paper->id)?->obtained_marks) }}" placeholder="Enter marks" class="w-full rounded border border-gray-300 px-3 py-2"></td>
                        @else
                            <td class="p-2"><label class="sr-only" for="grade-{{ $paper->id }}">Grade for {{ $paper->subject_name }}</label><select id="grade-{{ $paper->id }}" name="grades[{{ $paper->id }}]" required class="w-full rounded border border-gray-300 bg-white px-3 py-2"><option value="">Select Grade</option>@foreach($exam->grade_criteria ?? [] as $gradeRow)<option value="{{ $gradeRow['grade'] }}" @selected(old('grades.'.$paper->id, $existingResult?->items->firstWhere('exam_paper_id', $paper->id)?->grade) === $gradeRow['grade'])>{{ $gradeRow['grade'] }}</option>@endforeach</select></td>
                        @endif
                    </tr>
                @endforeach
            </tbody></table></div>
            <div class="mt-4 flex justify-end"><button class="rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);">{{ isset($existingResult) ? 'Update Result' : 'Generate Result' }}</button></div>
        </form>
    </section>
@elseif($selectedStudent)
    <div id="result-entry" class="rounded border border-sky-200 bg-sky-50 p-4 text-center text-xs text-sky-900">This student has no active subjects included in this exam.</div>
@endif
@endsection