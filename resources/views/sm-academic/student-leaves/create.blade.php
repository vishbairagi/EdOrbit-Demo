@extends('layouts.app')

@section('header', 'Add Student Leave')

@section('content')

{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Add Student Leave</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name ?? 'Current Session' }}</p>
    </div>
    <a href="{{ route('academic.student-leaves.index') }}"
       class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
        <i data-lucide="list" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
        View All
    </a>
</div>

<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-6" style="background:#fff;">
    <form method="POST" action="{{ route('academic.student-leaves.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Class --}}
            <div>
                <label class="block text-sm font-medium mb-1" style="color:#211B17;">Class <span class="text-red-500">*</span></label>
                <select name="class_id" id="class_id" onchange="updateSections()" required class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
                @error('class_id')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            {{-- Section --}}
            <div>
                <label class="block text-sm font-medium mb-1" style="color:#211B17;">Section <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                <select name="section_id" id="section_id" onchange="updateStudents()" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">
                    <option value="">All Sections</option>
                </select>
                @error('section_id')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            {{-- Student --}}
            <div>
                <label class="block text-sm font-medium mb-1" style="color:#211B17;">Student <span class="text-red-500">*</span></label>
                <select name="student_id" id="student_id" required class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">
                    <option value="">Select Student</option>
                </select>
                @error('student_id')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Number of Leave Days --}}
            <div>
                <label class="block text-sm font-medium mb-2" style="color:#211B17;">Number of Leave Days: <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-4 mt-1.5">
                    <label class="flex items-center gap-1 text-sm cursor-pointer"><input type="radio" name="leave_days" value="single" checked onchange="toggleEndDate(this.value)"> Single Day</label>
                    <label class="flex items-center gap-1 text-sm cursor-pointer"><input type="radio" name="leave_days" value="multiple" onchange="toggleEndDate(this.value)"> Multiple Days</label>
                </div>
            </div>

            {{-- Start Date --}}
            <div>
                <label class="block text-sm font-medium mb-1" style="color:#211B17;" id="start_date_label">Leave Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" id="start_date" required value="{{ old('start_date') }}" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">
                @error('start_date')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>

            {{-- End Date --}}
            <div id="end_date_container" class="hidden">
                <label class="block text-sm font-medium mb-1" style="color:#211B17;">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">
                @error('end_date')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Leave Type --}}
            <div>
                <label class="block text-sm font-medium mb-1" style="color:#211B17;">Leave Type</label>
                <select name="leave_type" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">
                    <option value="">Select Type</option>
                    <option value="Sick">Sick</option>
                    <option value="Personal">Personal</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            {{-- Attachment --}}
            <div>
                <label class="block text-sm font-medium mb-1" style="color:#211B17;">Attachment (Optional)</label>
                <input type="file" name="attachment" class="w-full text-sm mt-1">
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium mb-2" style="color:#211B17;">Status <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-4 mt-1.5">
                    <label class="flex items-center gap-1 text-sm text-blue-600 font-medium cursor-pointer"><input type="radio" name="status" value="Approved"> Approved</label>
                    <label class="flex items-center gap-1 text-sm text-yellow-600 font-medium cursor-pointer"><input type="radio" name="status" value="Pending" checked> Pending</label>
                    <label class="flex items-center gap-1 text-sm text-red-600 font-medium cursor-pointer"><input type="radio" name="status" value="Rejected"> Rejected</label>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1" style="color:#211B17;">Reason</label>
            <textarea name="reason" rows="3" placeholder="Enter reason" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none">{{ old('reason') }}</textarea>
        </div>

        <div class="text-center mt-8">
            <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-navy hover:opacity-90 transition-colors inline-flex items-center justify-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Student Leave
            </button>
        </div>
    </form>
</div>

<script>
    function toggleEndDate(val) {
        if(val === 'multiple') {
            document.getElementById('end_date_container').classList.remove('hidden');
            document.getElementById('start_date_label').innerHTML = 'Start Date <span class="text-red-500">*</span>';
            document.getElementById('end_date').required = true;
        } else {
            document.getElementById('end_date_container').classList.add('hidden');
            document.getElementById('start_date_label').innerHTML = 'Leave Date <span class="text-red-500">*</span>';
            document.getElementById('end_date').required = false;
            document.getElementById('end_date').value = document.getElementById('start_date').value;
        }
    }

    // Copy start date to end date if single
    document.getElementById('start_date').addEventListener('change', function() {
        let val = document.querySelector('input[name="leave_days"]:checked').value;
        if(val === 'single') {
            document.getElementById('end_date').value = this.value;
        }
    });

    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section_id');
    const studentSelect = document.getElementById('student_id');

    function updateSections() {
        sectionSelect.innerHTML = '<option value="">All Sections</option>';
        studentSelect.innerHTML = '<option value="">Select Student</option>';
        
        if (!classSelect.value) return;

        fetch(`/academic/study-materials/sections/${classSelect.value}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(sec => {
                    let opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = sec.name;
                    sectionSelect.appendChild(opt);
                });
            });
            
        // Fetch students for the class right after class is selected
        updateStudents();
    }

    function updateStudents() {
        studentSelect.innerHTML = '<option value="">Select Student</option>';
        
        if (!classSelect.value) return;

        let url = `/academic/student-leaves/students?class_id=${classSelect.value}`;
        if (sectionSelect.value) {
            url += `&section_id=${sectionSelect.value}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                data.forEach(student => {
                    let opt = document.createElement('option');
                    opt.value = student.id;
                    opt.textContent = student.first_name + ' ' + (student.last_name || '');
                    studentSelect.appendChild(opt);
                });
            });
    }
    
    // Ensure form submittal works smoothly if multiple days is clicked early on
    document.querySelector('form').addEventListener('submit', function() {
        let val = document.querySelector('input[name="leave_days"]:checked').value;
        if(val === 'single') {
            document.getElementById('end_date').value = document.getElementById('start_date').value;
        }
    });
</script>
@endsection
