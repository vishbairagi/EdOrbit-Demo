@extends('layouts.app')

@section('header', 'Generate Admit Cards')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-2 text-white shadow-md" style="background:var(--color-primary);">
    <div><h1 class="text-base font-bold">{{ $exam->title }}</h1><p class="mt-1 text-xs">{{ $exam->schoolClass->name }} · {{ $exam->exam_center }} · {{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} to {{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</p></div>
    <div class="flex gap-2"><a href="{{ route('examination.admit-cards.index') }}" class="rounded border border-white/70 px-3 py-1.5 text-xs font-semibold">Exam List</a>@if($exam->admit_cards_count > 0)<a href="{{ route('examination.admit-cards.view', $exam) }}" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-1.5 text-xs font-semibold"><i data-lucide="eye" class="h-3.5 w-3.5"></i> View Generated Cards</a>@endif</div>
</div>

@if($errors->any())<div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div><h2 class="text-sm font-bold text-heading">Generate Admit Cards</h2><p class="mt-1 text-xs text-body">Only active students with at least one matching exam subject are listed.</p></div>
        <form method="GET" action="{{ route('examination.admit-cards.generate-form', $exam) }}" class="flex items-center gap-2 text-xs">
            <label for="sort-by" class="font-semibold">Ascending Order</label>
            <select id="sort-by" name="sort_by" onchange="this.form.submit()" class="rounded border border-gray-300 bg-white px-3 py-2"><option value="roll_number" @selected(request('sort_by', 'roll_number') === 'roll_number')>Main Roll Number</option><option value="name" @selected(request('sort_by') === 'name')>Name Alphabetically</option></select>
        </form>
    </div>

    @if($students->isNotEmpty())
        <form method="POST" action="{{ route('examination.admit-cards.generate', $exam) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'roll_number') }}">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="text-xs font-semibold">Exam Roll Number Prefix<input name="prefix" maxlength="30" value="{{ old('prefix', $exam->admit_roll_prefix) }}" placeholder="e.g. CS" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
                <label class="text-xs font-semibold">Starting Count<input name="starting_number" type="number" min="1" required value="{{ old('starting_number', $exam->admit_roll_start) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-normal"></label>
            </div>
            @error('starting_number')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
            <div class="overflow-x-auto rounded border border-gray-200">
                <table class="w-full min-w-[620px] text-xs">
                    <thead class="bg-gray-50 text-left"><tr><th class="p-3"><input type="checkbox" id="select-all-students" checked aria-label="Select all students"></th><th class="p-3">Main Roll No.</th><th class="p-3">Student Name</th><th class="p-3">Section</th><th class="p-3">Exam Subjects</th><th class="p-3">Exam Roll No.</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($students as $student)
                            <tr>
                                <td class="p-3"><input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-choice" checked aria-label="Select {{ $student->full_name }}"></td>
                                <td class="p-3">{{ $student->roll_number ?? '—' }}</td>
                                <td class="p-3 font-semibold text-heading">{{ $student->full_name }}</td>
                                <td class="p-3">{{ $student->section?->name ?? '—' }}</td>
                                <td class="p-3">{{ $student->activeSubjects->whereIn('id', $exam->papers->pluck('subject_id'))->pluck('name')->join(', ') }}</td>
                                <td class="p-3">New card</td>
                            </tr>
                        @endforeach
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
    @else
        <div class="rounded border border-sky-200 bg-sky-50 px-4 py-4 text-center text-xs text-sky-900">No eligible students found. Students must be Active and have at least one assigned subject included in this exam.</div>
    @endif
</section>

@endsection