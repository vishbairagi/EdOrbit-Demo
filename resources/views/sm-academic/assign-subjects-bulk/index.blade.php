@extends('layouts.app')

@section('header', 'Assign Subject in Bulk')

@section('content')

{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Assign Subject in Bulk</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Assign multiple class subjects to students in bulk</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('academic.assign-subjects.index') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="user-check" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Assign to Teachers
        </a>
        <a href="{{ route('academic.assign-subjects-bulk.index') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Refresh
        </a>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg text-xs font-medium flex items-center justify-between gap-2 shadow-2xs"
         style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-800 hover:text-emerald-950">&times;</button>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs bg-red-50 text-red-700 border border-red-200">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-950">&times;</button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="mb-4 p-4 rounded-xl text-xs bg-red-50 text-red-700 border border-red-200 shadow-2xs">
        <div class="font-bold mb-1 flex items-center gap-1.5 text-red-800">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> Please check form errors:
        </div>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Filter Card: Class & Section Selector --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('academic.assign-subjects-bulk.index') }}" id="filterForm" class="flex flex-col md:flex-row items-end gap-3 flex-wrap">
        {{-- Class --}}
        <div class="w-full md:w-64">
            <label class="block text-xs font-semibold text-heading mb-1.5">
                <span class="text-red-500">*</span> Class:
            </label>
            <select name="class_id" id="filter_class_id" required onchange="updateFilterSections()"
                    class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                <option value="">Select Class</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Section --}}
        <div class="w-full md:w-56">
            <label class="block text-xs font-semibold text-heading mb-1.5">Section:</label>
            <select name="section_id" id="filter_section_id"
                    class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                <option value="">All Sections</option>
                @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                        {{ $sec->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Fetch Button --}}
        <div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-2xs">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Fetch Students & Subjects</span>
            </button>
        </div>

        @if($selectedClassId)
            <div>
                <a href="{{ route('academic.assign-subjects-bulk.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-3 h-3 text-gray-500"></i>
                    <span>Reset</span>
                </a>
            </div>
        @endif
    </form>
</div>

@if(!$selectedClassId)
    {{-- Initial Empty Prompt State --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-3 text-navy">
            <i data-lucide="book-open-check" class="w-7 h-7"></i>
        </div>
        <h4 class="text-base font-bold text-heading">Select Class & Section</h4>
        <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">
            Choose a class and optionally a section from the filters above, then click <strong>"Fetch Students & Subjects"</strong> to load students and subjects for bulk assignment.
        </p>
    </div>
@else

    {{-- Main Bulk Assignment Form --}}
    <form method="POST" action="{{ route('academic.assign-subjects-bulk.assign') }}" id="bulkAssignForm" onsubmit="return validateBulkAssignForm()">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">

        {{-- Side-by-Side Dual Table Container --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5 items-start">

            {{-- ══ TABLE 1: SELECT SUBJECTS (LEFT) ══ --}}
            <div class="lg:col-span-5 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                {{-- Table Top Banner --}}
                <div class="bg-navy px-4 py-2.5 flex items-center justify-between text-white">
                    <div class="flex items-center gap-2 font-bold text-xs">
                        <i data-lucide="book" class="w-4 h-4 text-amber-300"></i>
                        <span>Select Subjects ({{ $subjects->count() }})</span>
                    </div>
                    <span class="text-[11px] text-white/80 font-medium">
                        <span id="subjectsSelectedCount">0</span> of {{ $subjects->count() }} selected
                    </span>
                </div>

                {{-- Subjects Table --}}
                <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0 z-10 shadow-2xs">
                                <th class="px-3.5 py-2.5 w-10 text-center bg-gray-50">
                                    <input type="checkbox" id="selectAllSubjects" onchange="toggleSelectAllSubjects(this)"
                                           class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-navy" title="Select All Subjects">
                                </th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Subject Name</th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Code</th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs bg-white">
                            @forelse($subjects as $subject)
                                <tr class="hover:bg-gray-50/80 transition-colors subject-row cursor-pointer" onclick="toggleRowCheckbox(this, event, 'subject-checkbox')">
                                    <td class="px-3.5 py-2.5 text-center">
                                        <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                               class="subject-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-navy"
                                               onchange="updateSubjectsCount(); event.stopPropagation();">
                                    </td>
                                    <td class="px-3.5 py-2.5 font-semibold text-heading">
                                        {{ $subject->name }}
                                    </td>
                                    <td class="px-3.5 py-2.5 font-mono text-gray-500">
                                        {{ $subject->code ?: '—' }}
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        @php
                                            $typeStr = strtolower($subject->type ?: ($subject->subjectType?->name ?? 'theory'));
                                            $isPractical = str_contains($typeStr, 'prac');
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $isPractical ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                            {{ ucfirst($subject->type ?: ($subject->subjectType?->name ?? 'Theory')) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-xs text-gray-400 italic">
                                        <i data-lucide="info" class="w-5 h-5 mx-auto mb-1 text-gray-300"></i>
                                        No subjects found for {{ $selectedClass->name }}.<br>
                                        <a href="{{ route('academic.subjects.index') }}" class="text-blue-600 hover:underline font-semibold mt-1 inline-block">Create subjects first</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ══ TABLE 2: SELECT STUDENTS (RIGHT) ══ --}}
            <div class="lg:col-span-7 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                {{-- Table Top Banner --}}
                <div class="bg-navy px-4 py-2.5 flex items-center justify-between text-white">
                    <div class="flex items-center gap-2 font-bold text-xs">
                        <i data-lucide="users" class="w-4 h-4 text-amber-300"></i>
                        <span>Select Students ({{ $students->count() }})</span>
                    </div>
                    <span class="text-[11px] text-white/80 font-medium">
                        <span id="studentsSelectedCount">0</span> of {{ $students->count() }} selected
                    </span>
                </div>

                {{-- Students Table --}}
                <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider sticky top-0 z-10 shadow-2xs">
                                <th class="px-3.5 py-2.5 w-10 text-center bg-gray-50">
                                    <input type="checkbox" id="selectAllStudents" onchange="toggleSelectAllStudents(this)"
                                           class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-navy" title="Select All Students">
                                </th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Roll No.</th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Student Name</th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Enrollment No.</th>
                                <th class="px-3.5 py-2.5 bg-gray-50">Section</th>
                                <th class="px-3.5 py-2.5 text-center bg-gray-50">Current Subs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs bg-white">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50/80 transition-colors student-row cursor-pointer" onclick="toggleRowCheckbox(this, event, 'student-checkbox')">
                                    <td class="px-3.5 py-2.5 text-center">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                               class="student-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-navy"
                                               onchange="updateStudentsCount(); event.stopPropagation();">
                                    </td>
                                    <td class="px-3.5 py-2.5 font-semibold text-heading font-mono">
                                        {{ $student->roll_number ?: '—' }}
                                    </td>
                                    <td class="px-3.5 py-2.5 font-bold text-heading">
                                        {{ $student->full_name }}
                                    </td>
                                    <td class="px-3.5 py-2.5 font-mono text-gray-500">
                                        {{ $student->admission_no ?: '—' }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-gray-700 font-medium">
                                        {{ $student->section?->name ?: '—' }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-center">
                                        @php
                                            $currCount = $student->activeSubjects->count();
                                            $currNames = $student->activeSubjects->pluck('name')->join(', ');
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $currCount > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-500' }}"
                                              title="{{ $currNames ?: 'No subjects assigned yet' }}">
                                            {{ $currCount }} {{ Str::plural('sub', $currCount) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400 italic">
                                        <i data-lucide="users" class="w-5 h-5 mx-auto mb-1 text-gray-300"></i>
                                        No active students found in this class/section.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ══ ASSIGNMENT OPTION & ACTION BAR ══ --}}
        @if($subjects->count() > 0 && $students->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                {{-- Options Left --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-heading uppercase tracking-wider">
                        Assignment Option:
                    </label>
                    <div class="space-y-2 pl-1">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-medium text-heading hover:text-navy transition-colors">
                            <input type="radio" name="assignment_option" value="append" checked
                                   class="w-4 h-4 text-navy accent-navy cursor-pointer">
                            <span>Append selected subjects (keep existing student subjects)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-medium text-heading hover:text-navy transition-colors">
                            <input type="radio" name="assignment_option" value="replace"
                                   class="w-4 h-4 text-navy accent-navy cursor-pointer">
                            <span>Replace existing subjects with selected subjects</span>
                        </label>
                    </div>
                </div>

                {{-- Action Button Right --}}
                <div class="flex items-center gap-3 self-stretch md:self-auto justify-end">
                    <button type="submit" id="btnAssignSubjects"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-navy hover:bg-primary-dark text-white text-xs font-bold rounded-lg shadow-sm transition cursor-pointer">
                        <i data-lucide="check-check" class="w-4 h-4 text-amber-300"></i>
                        <span id="assignBtnText">Assign Subjects</span>
                    </button>
                </div>
            </div>
        @endif

    </form>

@endif

{{-- Client-Side Logic --}}
<script>
    // Class-Section Cascading Map
    const classSectionsMap = @json($classes->mapWithKeys(fn($c) => [$c->id => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])]));

    function updateFilterSections() {
        const classSelect = document.getElementById('filter_class_id');
        const secSelect = document.getElementById('filter_section_id');
        if (!classSelect || !secSelect) return;

        const classId = classSelect.value;
        const currentSecId = "{{ $selectedSectionId }}";

        secSelect.innerHTML = '<option value="">All Sections</option>';

        if (classId && classSectionsMap[classId]) {
            classSectionsMap[classId].forEach(sec => {
                const opt = document.createElement('option');
                opt.value = sec.id;
                opt.textContent = sec.name;
                if (String(sec.id) === String(currentSecId)) {
                    opt.selected = true;
                }
                secSelect.appendChild(opt);
            });
        }
    }

    // Toggle Select All Subjects
    function toggleSelectAllSubjects(masterCb) {
        document.querySelectorAll('.subject-checkbox').forEach(cb => {
            cb.checked = masterCb.checked;
            cb.closest('tr').classList.toggle('bg-amber-50/50', cb.checked);
        });
        updateSubjectsCount();
    }

    // Toggle Select All Students
    function toggleSelectAllStudents(masterCb) {
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = masterCb.checked;
            cb.closest('tr').classList.toggle('bg-blue-50/50', cb.checked);
        });
        updateStudentsCount();
    }

    // Row Click to Toggle
    function toggleRowCheckbox(row, event, cbClass) {
        if (event.target.tagName.toLowerCase() === 'input') {
            return;
        }
        const cb = row.querySelector('.' + cbClass);
        if (cb) {
            cb.checked = !cb.checked;
            if (cbClass === 'subject-checkbox') {
                row.classList.toggle('bg-amber-50/50', cb.checked);
                updateSubjectsCount();
            } else {
                row.classList.toggle('bg-blue-50/50', cb.checked);
                updateStudentsCount();
            }
        }
    }

    // Update Subjects Count & Master Checkbox
    function updateSubjectsCount() {
        const all = document.querySelectorAll('.subject-checkbox');
        const checked = document.querySelectorAll('.subject-checkbox:checked');
        const countEl = document.getElementById('subjectsSelectedCount');
        const master = document.getElementById('selectAllSubjects');

        all.forEach(cb => {
            cb.closest('tr').classList.toggle('bg-amber-50/50', cb.checked);
        });

        if (countEl) countEl.textContent = checked.length;
        if (master) {
            master.checked = (all.length > 0 && checked.length === all.length);
            master.indeterminate = (checked.length > 0 && checked.length < all.length);
        }
        updateButtonText();
    }

    // Update Students Count & Master Checkbox
    function updateStudentsCount() {
        const all = document.querySelectorAll('.student-checkbox');
        const checked = document.querySelectorAll('.student-checkbox:checked');
        const countEl = document.getElementById('studentsSelectedCount');
        const master = document.getElementById('selectAllStudents');

        all.forEach(cb => {
            cb.closest('tr').classList.toggle('bg-blue-50/50', cb.checked);
        });

        if (countEl) countEl.textContent = checked.length;
        if (master) {
            master.checked = (all.length > 0 && checked.length === all.length);
            master.indeterminate = (checked.length > 0 && checked.length < all.length);
        }
        updateButtonText();
    }

    function updateButtonText() {
        const subCount = document.querySelectorAll('.subject-checkbox:checked').length;
        const stuCount = document.querySelectorAll('.student-checkbox:checked').length;
        const btnText = document.getElementById('assignBtnText');

        if (btnText) {
            if (subCount > 0 && stuCount > 0) {
                btnText.textContent = `Assign ${subCount} Subject(s) to ${stuCount} Student(s)`;
            } else if (subCount > 0) {
                btnText.textContent = `Assign ${subCount} Subject(s) (Select Students)`;
            } else if (stuCount > 0) {
                btnText.textContent = `Assign to ${stuCount} Student(s) (Select Subjects)`;
            } else {
                btnText.textContent = `Assign Subjects`;
            }
        }
    }

    function validateBulkAssignForm() {
        const subCount = document.querySelectorAll('.subject-checkbox:checked').length;
        const stuCount = document.querySelectorAll('.student-checkbox:checked').length;

        if (subCount === 0) {
            alert('Please select at least one subject to assign.');
            return false;
        }

        if (stuCount === 0) {
            alert('Please select at least one student.');
            return false;
        }

        const option = document.querySelector('input[name="assignment_option"]:checked')?.value;
        const optText = option === 'replace' 
            ? 'REPLACE existing subjects with selected subjects for' 
            : 'APPEND selected subjects to';

        return confirm(`Are you sure you want to ${optText} ${stuCount} student(s)?`);
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateSubjectsCount();
        updateStudentsCount();
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

@endsection
