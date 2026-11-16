@extends('layouts.app')

@section('header', $exam ? 'Edit Exam' : 'Add New Exam')

@section('content')
@php
    $selectedClassId = old('school_class_id', $exam?->school_class_id);
    $selectedSections = old('section_ids', $exam?->sections->pluck('id')->all() ?? []);
    $initialSubjectIds = old('subject_ids', $exam?->papers->pluck('subject_id')->all() ?? []);
    $initialPapers = $exam?->papers->keyBy('subject_id')->map(fn ($paper) => [
        'subject_type' => $paper->subject_type,
        'maximum_marks' => $paper->maximum_marks,
        'paper_code' => $paper->paper_code,
        'paper_date' => $paper->paper_date?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')),
        'start_time' => $paper->start_time,
        'end_time' => $paper->end_time,
        'room_number' => $paper->room_number,
    ]) ?? collect();
    $settings = array_merge([
        'overall_grade' => false,
        'admit_card' => 'unpublished',
        'time_table' => 'unpublished',
        'exam_result' => 'unpublished',
        'total_marks' => true,
        'total_obtained_marks' => true,
        'subject_type' => true,
        'subject_code' => false,
        'show_ranks' => false,
        'rank_criteria' => 'class',
        'teacher_signature_enabled' => false,
    ], $exam?->settings ?? [], old('settings', []));
    $thresholds = array_merge([
        'show_subject_status' => false,
        'subject_pass_threshold' => 0,
        'subject_status_header' => 'Status',
        'subject_pass_label' => 'Pass',
        'subject_fail_label' => 'Fail',
        'show_overall_status' => false,
        'overall_pass_threshold' => 0,
        'max_failed_subjects' => 0,
        'overall_row_header' => 'Overall Result',
        'overall_pass_label' => 'Pass',
        'overall_fail_label' => 'Fail',
    ], old('result_thresholds', $exam?->result_thresholds ?? []));
    $defaultGrades = [
        ['percentage_from' => 0, 'percentage_to' => 40, 'grade' => 'F', 'status' => 'Fail', 'remark' => 'Needs Improvement'],
        ['percentage_from' => 41, 'percentage_to' => 50, 'grade' => 'D', 'status' => 'Pass', 'remark' => 'Satisfactory'],
        ['percentage_from' => 51, 'percentage_to' => 60, 'grade' => 'C', 'status' => 'Pass', 'remark' => 'Satisfactory'],
        ['percentage_from' => 61, 'percentage_to' => 70, 'grade' => 'B', 'status' => 'Pass', 'remark' => 'Good'],
        ['percentage_from' => 71, 'percentage_to' => 80, 'grade' => 'B+', 'status' => 'Pass', 'remark' => 'Good'],
        ['percentage_from' => 81, 'percentage_to' => 90, 'grade' => 'A', 'status' => 'Pass', 'remark' => 'Very Good'],
        ['percentage_from' => 91, 'percentage_to' => 100, 'grade' => 'A+', 'status' => 'Pass', 'remark' => 'Excellent'],
    ];
    $oldGradeCriteria = old('grade_criteria');
    if (is_array($oldGradeCriteria) && isset($oldGradeCriteria['percentage_from'])) {
        $gradeRows = [];
        foreach ($oldGradeCriteria['percentage_from'] as $index => $from) {
            $gradeRows[] = [
                'percentage_from' => $from,
                'percentage_to' => $oldGradeCriteria['percentage_to'][$index] ?? '',
                'grade' => $oldGradeCriteria['grade'][$index] ?? '',
                'status' => $oldGradeCriteria['status'][$index] ?? 'Pass',
                'remark' => $oldGradeCriteria['remark'][$index] ?? '',
            ];
        }
    } else {
        $gradeRows = $exam?->grade_criteria ?: $defaultGrades;
    }
    $gradeRows = collect($gradeRows)->map(function (array $row): array {
        if (in_array(strtolower((string) ($row['status'] ?? '')), ['active', 'inactive'], true)) {
            $row['status'] = ($row['grade'] ?? '') === 'F' ? 'Fail' : 'Pass';
        }

        return $row;
    })->all();
    $classSectionData = $classes->mapWithKeys(function ($class) {
        return [(string) $class->id => $class->sections->map(fn ($section) => ['id' => $section->id, 'name' => $section->name])->values()];
    })->all();
    $subjectCatalogData = $subjects->map(fn ($subject) => [
        'id' => $subject->id,
        'class_id' => $subject->school_class_id,
        'name' => $subject->name,
        'code' => $subject->code,
        'type' => $subject->subjectType?->name ?? ucfirst($subject->type),
    ])->values()->all();

    $initialSectionIds = array_map('intval', $selectedSections);
    $initialSubjectIds = array_map('intval', $initialSubjectIds);
@endphp

<div class="mb-5 flex items-center justify-between rounded-lg px-4 py-2 text-white shadow-md" style="background:var(--color-primary);">
    <h1 class="text-base font-bold">{{ $exam ? 'Edit Exam: '.$exam->title : 'Add New Exam' }}</h1>
    <a href="{{ route('examination.exams.index') }}" class="rounded border border-white/70 px-3 py-1.5 text-xs font-semibold">Back to Exams</a>
</div>

@if($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
        <p class="font-bold">Please review the highlighted exam details.</p>
        <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form id="exam-form" method="POST" enctype="multipart/form-data" action="{{ $exam ? route('examination.exams.update', $exam) : route('examination.exams.store') }}" class="space-y-4">
    @csrf
    @if($exam) @method('PUT') @endif
    <nav class="grid grid-cols-2 gap-2 md:grid-cols-4" aria-label="Exam setup steps">
        @foreach(['Exam details', 'Exam papers', 'Grade criteria', 'Result settings'] as $step => $label)
            <button type="button" data-step-button="{{ $step }}" class="step-button rounded border px-3 py-2 text-left text-xs font-semibold" style="border-color:#e5e7eb;">
                <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100">{{ $step + 1 }}</span>{{ $label }}
            </button>
        @endforeach
    </nav>

    <section data-step="0" class="step-panel rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Exam Details</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <label class="text-xs font-semibold text-heading">Exam Title<input name="title" required maxlength="255" value="{{ old('title', $exam?->title) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
            <label class="text-xs font-semibold text-heading">Exam Center<input name="exam_center" required maxlength="255" value="{{ old('exam_center', $exam?->exam_center) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
            <label class="text-xs font-semibold text-heading">Session<input value="{{ $session->name }}" readonly class="mt-1 w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 font-normal"></label>
            <label class="text-xs font-semibold text-heading">Start Date<input name="start_date" type="date" required value="{{ old('start_date', $exam?->start_date?->format('Y-m-d')) }}" class="exam-start-date mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
            <label class="text-xs font-semibold text-heading">End Date<input name="end_date" type="date" required value="{{ old('end_date', $exam?->end_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>

            <label class="text-xs font-semibold text-heading">Class
                <select name="school_class_id" id="school-class" required class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)<option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->name }}</option>@endforeach
                </select>
            </label>
            <fieldset>
                <legend class="mb-2 text-xs font-semibold text-heading">Exam Type</legend>
                <div class="flex flex-wrap gap-4 text-xs">
                    <label class="flex items-center gap-2"><input type="radio" name="exam_type" value="marks" @checked(old('exam_type', $exam?->exam_type ?? 'marks') === 'marks')> Marks and Grade</label>
                    <label class="flex items-center gap-2"><input type="radio" name="exam_type" value="grade" @checked(old('exam_type', $exam?->exam_type) === 'grade')> Grade Only</label>
                </div>
            </fieldset>
            <fieldset>
                <legend class="mb-2 text-xs font-semibold text-heading">Final Status</legend>
                <div class="flex gap-4 text-xs">
                    <label class="flex items-center gap-2"><input type="radio" name="status" value="active" @checked(old('status', $exam?->status ?? 'active') === 'active')> Active</label>
                    <label class="flex items-center gap-2"><input type="radio" name="status" value="inactive" @checked(old('status', $exam?->status) === 'inactive')> Inactive</label>
                </div>
            </fieldset>
        </div>
        <div class="mt-5">
            <h3 class="mb-2 text-xs font-bold text-heading">Sections for this exam</h3>
            <div id="section-options" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4"></div>
            <p id="section-error" class="mt-1 hidden text-xs text-red-700">Choose at least one section.</p>
        </div>
        <div class="mt-5 flex justify-end"><button type="button" data-next class="rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);">Next: Exam Papers</button></div>
    </section>

    <section data-step="1" class="step-panel hidden rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Subjects and Exam Papers</div>
        <p class="mb-3 text-xs text-body">Assigned subjects appear when you select a class. Remove subjects that do not belong in this exam, or add them back from this list.</p>
        <div id="subject-options" class="mb-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-3"></div>
        <p id="subject-error" class="mb-2 hidden text-xs text-red-700">Choose at least one subject and add its paper details.</p>
        <div id="paper-options" class="space-y-3"></div>
        <div class="mt-5 flex justify-between"><button type="button" data-back class="rounded border border-gray-300 px-4 py-2 text-xs font-semibold">Back</button><button type="button" data-next class="rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);">Next: Grade Criteria</button></div>
    </section>

    <section data-step="2" class="step-panel hidden rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">
            <span>Grade Criteria</span><button type="button" id="add-grade-row" class="rounded border border-white/70 px-3 py-1 text-xs">Add row</button>
        </div>
        <p class="mb-3 text-xs text-body">Percentage ranges and grades are saved with this exam. Values copied from an exam group can be edited here.</p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-xs">
                <thead class="bg-gray-50 text-left"><tr><th class="p-2">Percentage &gt;=</th><th class="p-2">Percentage &lt;=</th><th class="p-2">Assign Grade</th><th class="p-2">Status</th><th class="p-2">Remark</th><th class="p-2">Action</th></tr></thead>
                <tbody id="exam-grade-rows" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
        <div class="mt-5 flex justify-between"><button type="button" data-back class="rounded border border-gray-300 px-4 py-2 text-xs font-semibold">Back</button><button type="button" data-next class="rounded px-4 py-2 text-xs font-bold text-white" style="background:var(--color-primary);">Next: Result Settings</button></div>
    </section>

    <section data-step="3" class="step-panel hidden space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div>
            <h2 class="mb-3 rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Exam Settings</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="settings[overall_grade]" value="0"><input type="checkbox" name="settings[overall_grade]" value="1" @checked($settings['overall_grade'])> Overall grade in result</label>
                @foreach(['admit_card' => 'Admit Card', 'time_table' => 'Time Table', 'exam_result' => 'Exam Result'] as $key => $label)
                    <label class="text-xs font-semibold">{{ $label }}
                        <select name="settings[{{ $key }}]" class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 text-xs font-normal"><option value="published" @selected($settings[$key] === 'published')>Published</option><option value="unpublished" @selected($settings[$key] === 'unpublished')>Unpublished</option></select>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="mb-3 rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Result Thresholds &amp; Status</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="result_thresholds[show_subject_status]" value="0"><input type="checkbox" name="result_thresholds[show_subject_status]" value="1" @checked($thresholds['show_subject_status'])> Show Subject-wise Result Status</label>
                <label class="text-xs font-semibold">Subject Pass Threshold (%)<input name="result_thresholds[subject_pass_threshold]" type="number" min="0" max="100" step="0.01" value="{{ $thresholds['subject_pass_threshold'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Subject Status Column Header<input name="result_thresholds[subject_status_header]" value="{{ $thresholds['subject_status_header'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Subject Pass Label<input name="result_thresholds[subject_pass_label]" value="{{ $thresholds['subject_pass_label'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Subject Fail Label<input name="result_thresholds[subject_fail_label]" value="{{ $thresholds['subject_fail_label'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="result_thresholds[show_overall_status]" value="0"><input type="checkbox" name="result_thresholds[show_overall_status]" value="1" @checked($thresholds['show_overall_status'])> Show Overall Result Status</label>
                <label class="text-xs font-semibold">Overall Pass Threshold (%)<input name="result_thresholds[overall_pass_threshold]" type="number" min="0" max="100" step="0.01" value="{{ $thresholds['overall_pass_threshold'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Max Failed Subjects Allowed<input name="result_thresholds[max_failed_subjects]" type="number" min="0" value="{{ $thresholds['max_failed_subjects'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Overall Result Row Header<input name="result_thresholds[overall_row_header]" value="{{ $thresholds['overall_row_header'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Overall Pass Label<input name="result_thresholds[overall_pass_label]" value="{{ $thresholds['overall_pass_label'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Overall Fail Label<input name="result_thresholds[overall_fail_label]" value="{{ $thresholds['overall_fail_label'] }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
            </div>
        </div>

        <div>
            <h2 class="mb-3 rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Result Display</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(['total_marks' => 'Total Marks', 'total_obtained_marks' => 'Total Obtained Marks', 'subject_type' => 'Show Subject Type', 'subject_code' => 'Show Subject Code', 'show_ranks' => 'Show Ranks in Result'] as $key => $label)
                    <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="settings[{{ $key }}]" value="0"><input type="checkbox" name="settings[{{ $key }}]" value="1" @checked($settings[$key])> {{ $label }}</label>
                @endforeach
                <label class="text-xs font-semibold">Rank Criteria<select name="settings[rank_criteria]" class="mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 font-normal"><option value="class" @selected($settings['rank_criteria'] === 'class')>Class Wise Rank</option><option value="section" @selected($settings['rank_criteria'] === 'section')>Section Wise Rank</option></select></label>
            </div>
        </div>

        <div>
            <h2 class="mb-3 rounded px-4 py-2 text-sm font-bold text-white" style="background:var(--color-primary);">Class Teacher Signature</h2>
            <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="settings[teacher_signature_enabled]" value="0"><input id="teacher-signature-enabled" type="checkbox" name="settings[teacher_signature_enabled]" value="1" @checked($settings['teacher_signature_enabled'])> Show class teacher signature</label>
            <div id="teacher-signature-upload" class="{{ $settings['teacher_signature_enabled'] ? '' : 'hidden' }} mt-3">
                <label class="block text-xs font-semibold">Upload Signature (JPG, PNG or WebP, max 2 MB)<input name="teacher_signature" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-xs"></label>
                @if(!empty($settings['teacher_signature_path']))<img src="{{ asset('storage/'.$settings['teacher_signature_path']) }}" alt="Current class teacher signature" class="mt-2 h-12 max-w-40 object-contain">@endif
            </div>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-4"><button type="button" data-back class="rounded border border-gray-300 px-4 py-2 text-xs font-semibold">Back</button><button class="rounded px-5 py-2 text-xs font-bold text-white" style="background:var(--color-primary);">{{ $exam ? 'Save Exam' : 'Save Exam' }}</button></div>
    </section>
</form>

<script>
(() => {
    const classSelect = document.getElementById('school-class');
    const sectionContainer = document.getElementById('section-options');
    const subjectContainer = document.getElementById('subject-options');
    const paperContainer = document.getElementById('paper-options');
    const gradeContainer = document.getElementById('exam-grade-rows');
    const classSections = @json($classSectionData);
    const subjectCatalog = @json($subjectCatalogData);
    const existingPapers = @json($initialPapers);
    const initialSections = @json($initialSectionIds);
    const initialSubjectIds = @json($initialSubjectIds);
    const existingGradeRows = @json($gradeRows);

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    let activeStep = 0;

    function setStep(step) {
        activeStep = Math.max(0, Math.min(3, step));
        document.querySelectorAll('.step-panel').forEach(panel => panel.classList.toggle('hidden', Number(panel.dataset.step) !== activeStep));
        document.querySelectorAll('.step-button').forEach(button => {
            const active = Number(button.dataset.stepButton) === activeStep;
            button.style.borderColor = active ? 'var(--color-primary)' : '#e5e7eb';
            button.style.color = active ? 'var(--color-primary-dark)' : '';
            button.querySelector('span').style.background = active ? 'var(--color-primary-light)' : '';
        });
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function renderSections(selectedIds = []) {
        const sections = classSections[classSelect.value] ?? [];
        sectionContainer.innerHTML = sections.length ? sections.map(section => `<label class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-xs"><input type="checkbox" name="section_ids[]" value="${section.id}" ${selectedIds.includes(Number(section.id)) ? 'checked' : ''}> ${escapeHtml(section.name)}</label>`).join('') : '<p class="text-xs text-body">This class has no sections assigned.</p>';
    }

    function selectedSubjects() {
        return [...subjectContainer.querySelectorAll('input[name="subject_ids[]"]:checked')].map(input => Number(input.value));
    }

    function renderPapers() {
        const checked = selectedSubjects();
        const date = document.querySelector('[name="start_date"]').value;
        const marksExam = document.querySelector('[name="exam_type"]:checked')?.value === 'marks';
        paperContainer.innerHTML = checked.map(id => {
            const subject = subjectCatalog.find(item => Number(item.id) === id);
            const saved = existingPapers[id] ?? {};
            const value = (key, fallback = '') => escapeHtml(saved[key] ?? fallback);
            return `<article class="rounded border border-gray-200 p-4"><div class="mb-3 flex items-center justify-between gap-3"><h3 class="text-sm font-bold text-heading">${escapeHtml(subject?.name)}</h3><span class="text-[11px] text-body">${escapeHtml(subject?.code || 'No subject code')}</span></div><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><label class="text-[11px] font-semibold">Subject Type<input name="papers[${id}][subject_type]" value="${value('subject_type', subject?.type)}" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label>${marksExam ? `<label class="text-[11px] font-semibold">Maximum Marks<input name="papers[${id}][maximum_marks]" type="number" min="0.01" step="0.01" required value="${value('maximum_marks', '100')}" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label>` : ''}<label class="text-[11px] font-semibold">Paper Code<input name="papers[${id}][paper_code]" value="${value('paper_code', subject?.code)}" maxlength="100" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label><label class="text-[11px] font-semibold">Paper Date<input name="papers[${id}][paper_date]" type="date" required value="${value('paper_date', date)}" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label><label class="text-[11px] font-semibold">Start Time<input name="papers[${id}][start_time]" type="time" required value="${value('start_time', '09:00')}" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label><label class="text-[11px] font-semibold">End Time<input name="papers[${id}][end_time]" type="time" required value="${value('end_time', '12:00')}" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label><label class="text-[11px] font-semibold">Room Number<input name="papers[${id}][room_number]" value="${value('room_number')}" maxlength="100" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-xs font-normal"></label></div></article>`;
        }).join('') || '<p class="rounded border border-dashed border-gray-300 p-6 text-center text-xs text-body">Select a class with assigned subjects to add exam papers.</p>';
    }

    function renderSubjects(selectedIds = []) {
        const subjects = subjectCatalog.filter(subject => Number(subject.class_id) === Number(classSelect.value));
        const selected = selectedIds.length ? selectedIds : subjects.map(subject => Number(subject.id));
        subjectContainer.innerHTML = subjects.length ? subjects.map(subject => `<label class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-xs"><input type="checkbox" name="subject_ids[]" value="${subject.id}" ${selected.includes(Number(subject.id)) ? 'checked' : ''}><span class="font-semibold">${escapeHtml(subject.name)}</span><span class="ml-auto text-[10px] text-body">${escapeHtml(subject.type)}</span></label>`).join('') : '<p class="text-xs text-body">No active subjects are assigned to this class.</p>';
        subjectContainer.querySelectorAll('input').forEach(input => input.addEventListener('change', renderPapers));
        renderPapers();
    }

    function renderGradeRows(rows) {
        gradeContainer.innerHTML = rows.map(row => `<tr><td class="p-1"><input name="grade_criteria[percentage_from][]" type="number" min="0" max="100" step="0.01" required value="${escapeHtml(row.percentage_from)}" class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[percentage_to][]" type="number" min="0" max="100" step="0.01" required value="${escapeHtml(row.percentage_to)}" class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[grade][]" required maxlength="20" value="${escapeHtml(row.grade)}" class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[status][]" type="text" required maxlength="100" placeholder="Pass, Fail, etc." value="${escapeHtml(row.status || 'Pass')}" class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1"><input name="grade_criteria[remark][]" type="text" maxlength="150" placeholder="Good, Excellent, etc." value="${escapeHtml(row.remark || '')}" class="w-full rounded border border-gray-300 px-2 py-2"></td><td class="p-1 text-center"><button type="button" class="remove-exam-grade px-2 py-1 font-bold text-red-600" aria-label="Remove grade row">×</button></td></tr>`).join('');
    }

    function applyThresholds(values) {
        Object.entries(values ?? {}).forEach(([key, value]) => {
            const inputs = [...document.querySelectorAll(`[name="result_thresholds[${key}]"]`)];
            const input = inputs.find(candidate => candidate.type !== 'hidden') ?? inputs[0];
            if (!input) return;
            if (input.type === 'checkbox') input.checked = value === true || value === 1 || value === '1';
            else input.value = value ?? '';
        });
    }

    document.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', () => {
        const panel = document.querySelector(`.step-panel[data-step="${activeStep}"]`);
        const invalidInput = [...panel.querySelectorAll('input:required, select:required')].find(input => !input.checkValidity());
        if (invalidInput) {
            invalidInput.reportValidity();
            return;
        }
        if (activeStep === 0 && !sectionContainer.querySelector('input:checked')) {
            document.getElementById('section-error').classList.remove('hidden');
            return;
        }
        if (activeStep === 1 && selectedSubjects().length === 0) {
            document.getElementById('subject-error').classList.remove('hidden');
            return;
        }
        document.getElementById('section-error').classList.add('hidden');
        document.getElementById('subject-error').classList.add('hidden');
        setStep(activeStep + 1);
    }));
    document.querySelectorAll('[data-back]').forEach(button => button.addEventListener('click', () => setStep(activeStep - 1)));
    document.querySelectorAll('[data-step-button]').forEach(button => button.addEventListener('click', () => setStep(Number(button.dataset.stepButton))));
    document.getElementById('add-grade-row').addEventListener('click', () => {
        const rows = [...gradeContainer.querySelectorAll('tr')].map(row => ({
            percentage_from: row.querySelector('[name="grade_criteria[percentage_from][]"]').value,
            percentage_to: row.querySelector('[name="grade_criteria[percentage_to][]"]').value,
            grade: row.querySelector('[name="grade_criteria[grade][]"]').value,
            status: row.querySelector('[name="grade_criteria[status][]"]').value,
            remark: row.querySelector('[name="grade_criteria[remark][]"]').value,
        }));
        rows.push({percentage_from: '', percentage_to: '', grade: '', status: 'Pass', remark: ''});
        renderGradeRows(rows);
    });
    document.addEventListener('click', event => {
        if (event.target.closest('.remove-exam-grade')) event.target.closest('tr').remove();
    });
    document.querySelectorAll('[name="exam_type"]').forEach(input => input.addEventListener('change', renderPapers));
    classSelect.addEventListener('change', () => {
        renderSections((classSections[classSelect.value] ?? []).map(section => Number(section.id)));
        renderSubjects([]);
    });
    document.querySelector('[name="start_date"]').addEventListener('change', renderPapers);

    document.getElementById('teacher-signature-enabled').addEventListener('change', event => {
        document.getElementById('teacher-signature-upload').classList.toggle('hidden', !event.target.checked);
    });

    renderSections(initialSections);
    renderSubjects(initialSubjectIds);
    renderGradeRows(existingGradeRows);
    setStep(0);
})();
</script>
@endsection