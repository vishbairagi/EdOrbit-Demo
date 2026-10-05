@extends('layouts.app')

@section('header', 'Edit Student')

@section('content')

<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-heading">Edit: {{ $student->full_name }}</h2>
    <a href="{{ route('sm-student.students.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        Back
    </a>
</div>

<form method="POST" action="{{ route('sm-student.students.update', $student) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- Personal Details --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Personal Detail</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">* First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_middle_name', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-body mb-1">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Gender</label>
                <div class="flex gap-4 mt-1">
                    @foreach(['Male','Female','Other'] as $g)
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="radio" name="gender" value="{{ $g }}" class="accent-navy"
                                   {{ old('gender', $student->gender) === $g ? 'checked' : '' }}> {{ $g }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_blood_group', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Blood Group</label>
                <select name="blood_group" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Blood Group</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_religion', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Religion</label>
                <input type="text" name="religion" value="{{ old('religion', $student->religion) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @endif
            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_caste', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Caste</label>
                <input type="text" name="caste" value="{{ old('caste', $student->caste) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            @endif
            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_student_category', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $student->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_student_type', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Student Type</label>
                <select name="student_type_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Student Type</option>
                    @foreach($studentTypes as $studentType)
                        <option value="{{ $studentType->id }}" {{ old('student_type_id', $student->student_type_id) == $studentType->id ? 'selected' : '' }}>{{ $studentType->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_medium', '1') == '1')
            <div>
                <label class="block text-xs font-medium text-body mb-1">Medium</label>
                <select name="medium_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Medium</option>
                    @foreach($mediums as $medium)
                        <option value="{{ $medium->id }}" {{ old('medium_id', $student->medium_id) == $medium->id ? 'selected' : '' }}>{{ $medium->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Address</label>
                <textarea name="address" rows="2"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">{{ old('address', $student->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $student->email) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">City</label>
                <input type="text" name="city" value="{{ old('city', $student->city) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">State</label>
                <input type="text" name="state" value="{{ old('state', $student->state) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Country</label>
                <input type="text" name="country" value="{{ old('country', $student->country) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Photo</label>
                @if($student->photo_url)
                    <img src="{{ $student->photo_url }}" class="w-16 h-16 rounded-lg object-cover mb-2 border" alt="Photo">
                @endif
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-sm text-body file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-navy file:text-white hover:file:opacity-90">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    @foreach(['Active','Inactive','Transferred','Promoted'] as $s)
                        <option value="{{ $s }}" {{ old('status', $student->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- Class & Enrollment --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Class & Enrollment</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Class</label>
                <select name="school_class_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $student->school_class_id == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Section</label>
                <select name="section_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ $student->section_id == $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Roll Number</label>
                <input type="number" name="roll_number" value="{{ old('roll_number', $student->roll_number) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

        </div>
    </div>

    {{-- Subjects --}}
    @if($subjects->count())
        <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
                <h3 class="font-bold text-heading text-sm">Assigned Subjects</h3>
                <p class="text-xs text-body mt-0.5">Uncheck to remove a subject from this student.</p>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($subjects as $subject)
                        <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer hover:border-navy hover:bg-blue-50 transition
                            {{ in_array($subject->id, $assignedSubjectIds) ? 'border-navy bg-blue-50' : 'border-gray-200' }}">
                            <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                   class="accent-navy w-4 h-4"
                                   {{ in_array($subject->id, $assignedSubjectIds) ? 'checked' : '' }}>
                            <div>
                                <p class="text-sm font-medium text-heading">{{ $subject->name }}</p>
                                @if($subject->code)
                                    <p class="text-xs text-body">{{ $subject->code }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Parent Details --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Parent / Guardian Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="block text-xs font-medium text-body mb-1">Father's Name</label>
                <input type="text" name="father_name" value="{{ old('father_name', $student->father_name) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Father's Phone</label>
                <input type="text" name="father_phone" value="{{ old('father_phone', $student->father_phone) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Father's Occupation</label>
                <input type="text" name="father_occupation" value="{{ old('father_occupation', $student->father_occupation) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Mother's Name</label>
                <input type="text" name="mother_name" value="{{ old('mother_name', $student->mother_name) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Mother's Phone</label>
                <input type="text" name="mother_phone" value="{{ old('mother_phone', $student->mother_phone) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Mother's Occupation</label>
                <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $student->mother_occupation) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Guardian Name</label>
                <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Guardian Phone</label>
                <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Guardian Relation</label>
                <input type="text" name="guardian_relation" value="{{ old('guardian_relation', $student->guardian_relation) }}"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex gap-3 justify-end">
        <a href="{{ route('sm-student.students.index') }}"
           class="px-5 py-2 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">Cancel</a>
        <button type="submit"
                class="px-6 py-2 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                style="background:#B47A42;">
            <i data-lucide="save" class="w-4 h-4 inline-block mr-1"></i>
            Update Student
        </button>
    </div>

</form>
@endsection
