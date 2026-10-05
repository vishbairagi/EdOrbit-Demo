@extends('layouts.app')

@section('header', 'New Admission')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center">
            <i data-lucide="user-plus" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight">New Admission</h2>
            <p class="text-xs text-body">Session: <span class="font-semibold text-navy">{{ $session?->name ?? '—' }}</span></p>
        </div>
    </div>
    <a href="{{ route('sm-student.students.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        View Students
    </a>
</div>

{{-- Banner --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center">
        <i data-lucide="user-plus" class="w-5 h-5 text-white mr-2.5"></i>
        <span class="text-white font-bold text-base tracking-wide">New Admission For Session: {{ $session?->name ?? '—' }}</span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('sm-student.students.index') }}"
           class="text-xs px-3 py-1.5 rounded bg-white font-medium hover:bg-gray-100 transition" style="color:#211B17;">
            View Students
        </a>
    </div>
</div>

<form method="POST" action="{{ route('sm-student.students.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- ── PERSONAL DETAILS ── --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Personal Detail</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">* First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}"
                       placeholder="Enter first name" required
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy @error('first_name') border-red-400 @enderror">
                @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_middle_name', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                       placeholder="Enter middle name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-body mb-1">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}"
                       placeholder="Enter last name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Gender</label>
                <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                        <input type="radio" name="gender" value="Male" class="accent-navy"
                               {{ old('gender', 'Male') === 'Male' ? 'checked' : '' }}> Male
                    </label>
                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                        <input type="radio" name="gender" value="Female" class="accent-navy"
                               {{ old('gender') === 'Female' ? 'checked' : '' }}> Female
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_blood_group', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Blood Group</label>
                <select name="blood_group" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Blood Group</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_religion', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Religion</label>
                <input type="text" name="religion" value="{{ old('religion') }}"
                       placeholder="Enter religion"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_caste', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Caste / Sub Caste</label>
                <input type="text" name="caste" value="{{ old('caste') }}"
                       placeholder="Enter caste/sub caste"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_student_category', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_student_type', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Student Type</label>
                <select id="studentTypeSelect" name="student_type_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Student Type</option>
                    @foreach($studentTypes as $studentType)
                        <option value="{{ $studentType->id }}" {{ old('student_type_id') == $studentType->id ? 'selected' : '' }}>{{ $studentType->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_medium', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Medium</label>
                <select name="medium_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Medium</option>
                    @foreach($mediums as $medium)
                        <option value="{{ $medium->id }}" {{ old('medium_id') == $medium->id ? 'selected' : '' }}>{{ $medium->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Address</label>
                <textarea name="address" rows="2" placeholder="Enter student address"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">{{ old('address') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       placeholder="Enter student phone number"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="Enter student email address"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       placeholder="Enter city"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">State</label>
                <input type="text" name="state" value="{{ old('state') }}"
                       placeholder="Enter state"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Country</label>
                <input type="text" name="country" value="{{ old('country') }}"
                       placeholder="Enter country"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Photo</label>
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-sm text-body file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-navy file:text-white hover:file:opacity-90">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Extra Note / Detail</label>
                <textarea name="extra_note" rows="2" placeholder="Enter any extra notes"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">{{ old('extra_note') }}</textarea>
            </div>

        </div>
    </div>

    {{-- ── CLASS & ADMISSION ── --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Class & Admission Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">Admission No</label>
                <input type="text" name="admission_no" value="{{ old('admission_no', $admissionNo) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Class</label>
                <select name="school_class_id" id="classSelect" required
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Section</label>
                <select name="section_id" id="sectionSelect"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Section</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Roll Number</label>
                <input type="number" name="roll_number" value="{{ old('roll_number') }}"
                       placeholder="Roll number"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

        </div>
    </div>

    {{-- ── ADMISSION FEES ── --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5" id="admissionFeesCard">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Admission Fee Types</h3>
            <p class="text-xs text-body mt-0.5">Choose the class fees to invoice now. You can add or remove fees before admission.</p>
        </div>
        <div class="p-5" id="admissionFeeTypesList">
            <p class="text-sm text-body italic">Select a class to load its admission fee types.</p>
        </div>
    </div>

    {{-- ── SUBJECTS ── --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5" id="subjectsCard">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Assign Subjects</h3>
            <p class="text-xs text-body mt-0.5">Subjects will load automatically based on selected class. You can uncheck to remove.</p>
        </div>
        <div class="p-5" id="subjectsList">
            <p class="text-sm text-body italic">Please select a class to load subjects.</p>
        </div>
    </div>

    {{-- ── PARENT / GUARDIAN DETAILS ── --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Parent / Guardian Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">Father's Name</label>
                <input type="text" name="father_name" value="{{ old('father_name') }}"
                       placeholder="Enter father's name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Father's Phone</label>
                <input type="text" name="father_phone" value="{{ old('father_phone') }}"
                       placeholder="Enter father's phone"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Father's Occupation</label>
                <input type="text" name="father_occupation" value="{{ old('father_occupation') }}"
                       placeholder="Enter occupation"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Mother's Name</label>
                <input type="text" name="mother_name" value="{{ old('mother_name') }}"
                       placeholder="Enter mother's name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Mother's Phone</label>
                <input type="text" name="mother_phone" value="{{ old('mother_phone') }}"
                       placeholder="Enter mother's phone"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Mother's Occupation</label>
                <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}"
                       placeholder="Enter occupation"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Guardian Name</label>
                <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                       placeholder="Enter guardian name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Guardian Phone</label>
                <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}"
                       placeholder="Enter guardian phone"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Guardian Relation</label>
                <input type="text" name="guardian_relation" value="{{ old('guardian_relation') }}"
                       placeholder="e.g. Uncle, Aunt"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Guardian Address</label>
                <textarea name="guardian_address" rows="2" placeholder="Enter guardian address"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">{{ old('guardian_address') }}</textarea>
            </div>

        </div>
    </div>

    {{-- ── PREVIOUS SCHOOL DETAILS ── --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Previous School Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">Previous School Name</label>
                <input type="text" name="previous_school_name" value="{{ old('previous_school_name') }}"
                       placeholder="Enter previous school name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Previous Class</label>
                <input type="text" name="previous_class" value="{{ old('previous_class') }}"
                       placeholder="e.g. Grade 5"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Board</label>
                <input type="text" name="previous_school_board" value="{{ old('previous_school_board') }}"
                       placeholder="e.g. CBSE, ICSE"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">TC Number (Leaving Certificate)</label>
                <input type="text" name="leaving_certificate_no" value="{{ old('leaving_certificate_no') }}"
                       placeholder="Enter TC number"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-body mb-1">Previous School Address</label>
                <input type="text" name="previous_school_address" value="{{ old('previous_school_address') }}"
                       placeholder="Enter previous school address"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

        </div>
    </div>

    {{-- Submit --}}
    <div class="flex gap-3 justify-end">
        <a href="{{ route('sm-student.students.index') }}"
           class="px-5 py-2 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit"
                class="px-6 py-2 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                style="background:#B47A42;">
            <i data-lucide="save" class="w-4 h-4 inline-block mr-1"></i>
            Save Admission
        </button>
    </div>

</form>

<script>
const classSelect   = document.getElementById('classSelect');
const sectionSelect = document.getElementById('sectionSelect');
const subjectsList  = document.getElementById('subjectsList');
const admissionFeeTypesList = document.getElementById('admissionFeeTypesList');
const studentTypeSelect = document.getElementById('studentTypeSelect');
const previouslySelectedFeeTypes = @json(old('fee_type_ids', []));
let feeTypeRequestNumber = 0;

async function loadAdmissionFeeTypes() {
    const classId = classSelect.value;
    const requestNumber = ++feeTypeRequestNumber;
    if (!classId) {
        admissionFeeTypesList.innerHTML = '<p class="text-sm text-body italic">Select a class to load its admission fee types.</p>';
        return;
    }

    admissionFeeTypesList.innerHTML = '<p class="text-sm text-body italic">Loading fee types...</p>';
    const studentTypeQuery = studentTypeSelect && studentTypeSelect.value
        ? `?student_type_id=${encodeURIComponent(studentTypeSelect.value)}`
        : '';

    try {
        const response = await fetch(`{{ url('sm-student/classes') }}/${classId}/fee-types${studentTypeQuery}`, {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) throw new Error('Fee types could not be loaded.');
        const feeTypes = await response.json();
        if (requestNumber !== feeTypeRequestNumber) return;
        if (!feeTypes.length) {
            admissionFeeTypesList.innerHTML = '<p class="text-sm text-body italic">No admission fee types match this class and student type.</p>';
            return;
        }

        const selectedIds = previouslySelectedFeeTypes.map(Number);
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3';
        feeTypes.forEach(fee => {
            const label = document.createElement('label');
            label.className = 'flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:border-navy hover:bg-gray-50 transition';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'fee_type_ids[]';
            checkbox.value = fee.id;
            checkbox.checked = selectedIds.length ? selectedIds.includes(Number(fee.id)) : true;
            checkbox.className = 'mt-0.5 rounded accent-navy';
            const text = document.createElement('span');
            text.className = 'min-w-0 flex-1';
            const name = document.createElement('span');
            name.className = 'block text-sm font-semibold text-heading';
            name.textContent = fee.name;
            const details = document.createElement('span');
            details.className = 'block text-xs text-body';
            details.textContent = `${fee.period} · ${Number(fee.amount).toFixed(2)}`;
            text.append(name, details);
            label.append(checkbox, text);
            grid.append(label);
        });
        admissionFeeTypesList.replaceChildren(grid);
    } catch (error) {
        if (requestNumber !== feeTypeRequestNumber) return;
        admissionFeeTypesList.innerHTML = '<p class="text-sm text-red-700">Unable to load fee types. Change the class or student type and try again.</p>';
    }
}

classSelect.addEventListener('change', function () {
    const classId = this.value;
    if (!classId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        subjectsList.innerHTML  = '<p class="text-sm text-body italic">Please select a class to load subjects.</p>';
        loadAdmissionFeeTypes();
        return;
    }

    // Load sections
    fetch(`{{ url('sm-student/classes') }}/${classId}/sections`)
        .then(r => r.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            data.forEach(s => {
                sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });

    // Load subjects
    fetch(`{{ url('sm-student/classes') }}/${classId}/subjects`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                subjectsList.innerHTML = '<p class="text-sm text-body italic">No subjects found for this class.</p>';
                return;
            }
            let html = '<div class="grid grid-cols-2 md:grid-cols-4 gap-3">';
            data.forEach(s => {
                html += `
                    <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-navy hover:bg-blue-50 transition">
                        <input type="checkbox" name="subject_ids[]" value="${s.id}" checked
                               class="accent-navy w-4 h-4 rounded">
                        <div>
                            <p class="text-sm font-medium text-heading">${s.name}</p>
                            ${s.code ? `<p class="text-xs text-body">${s.code}</p>` : ''}
                        </div>
                    </label>`;
            });
            html += '</div>';
            subjectsList.innerHTML = html;
        });

    loadAdmissionFeeTypes();
});

if (studentTypeSelect) studentTypeSelect.addEventListener('change', loadAdmissionFeeTypes);
if (classSelect.value) classSelect.dispatchEvent(new Event('change'));
</script>

@endsection
