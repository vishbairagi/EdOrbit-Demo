@extends('layouts.app')

@section('header', 'Student Leaves')

@section('content')

{{-- Page Header --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-4 flex items-center justify-between mb-6">
    <div class="flex items-center text-white font-bold text-lg">
        <i data-lucide="calendar" class="w-5 h-5 mr-2"></i>
        Student Leaves
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('academic.student-leaves.export', request()->all()) }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-white/20 hover:bg-white/30 flex items-center gap-2 transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </a>
        <button onclick="document.getElementById('importLeaveModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-white/20 hover:bg-white/30 flex items-center gap-2 transition-colors">
            <i data-lucide="upload" class="w-4 h-4"></i> Import CSV
        </button>
        <button onclick="openAddLeaveModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-navy bg-white hover:bg-gray-50 flex items-center gap-2 transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i> Add New Leave
        </button>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
</div>
@endif
@if(session('import_errors') && count(session('import_errors')))
<div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <p class="font-semibold mb-1">Some rows were skipped during import:</p>
    <ul class="list-disc ml-4 space-y-0.5">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Search & Filter Bar --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5" style="background:#fff;">
    <form method="GET" action="{{ route('academic.student-leaves.index') }}" class="flex flex-col md:flex-row items-end gap-3">
        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Class</label>
            <select name="class_id" id="filter_class_id" onchange="updateFilterSections();" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
                <option value="">All</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Section</label>
            <select name="section_id" id="filter_section_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
                <option value="">All</option>
            </select>
        </div>

        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Status</label>
            <select name="status" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
                <option value="">All</option>
                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="w-full md:w-40">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
        </div>

        <div class="w-full md:w-48">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Student Name</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..." class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg text-white text-xs font-semibold bg-navy hover:opacity-90 transition">
                Search
            </button>
            @if(request()->anyFilled(['class_id', 'section_id', 'status', 'date', 'search']))
                <a href="{{ route('academic.student-leaves.index') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-xs font-medium bg-gray-50 hover:bg-gray-100 transition" style="color:#211B17;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
    {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('academic.student-leaves.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Student Name</th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Class</th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Section</th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Reason</th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Leave Date</th>
                    <th class="text-center text-white font-semibold text-sm px-4 py-3">Attachment</th>
                    <th class="text-left text-white font-semibold text-sm px-4 py-3">Status</th>
                    <th class="text-center text-white font-semibold text-sm px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                    <td class="px-4 py-3 text-sm font-bold" style="color:#211B17;">
                        {{ $leave->student?->first_name }} {{ $leave->student?->last_name }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-bold">
                        {{ $leave->schoolClass?->name }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-bold">
                        {{ $leave->section?->name }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate font-bold" title="{{ $leave->reason }}">
                        {{ \Illuminate\Support\Str::limit($leave->reason, 40) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-bold">
                        @if($leave->start_date->equalTo($leave->end_date))
                            {{ $leave->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                        @else
                            {{ $leave->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} to <br> {{ $leave->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($leave->attachment)
                            <a href="{{ asset($leave->attachment) }}" target="_blank" class="inline-flex items-center justify-center p-1.5 rounded bg-gray-100 text-navy hover:bg-gray-200 transition-colors" title="View Attachment">
                                <i data-lucide="paperclip" class="w-4 h-4"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-xs font-bold">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-bold">
                        @if($leave->status === 'Approved')
                            <span class="text-blue-500 font-bold">{{ $leave->status }}</span>
                        @elseif($leave->status === 'Rejected')
                            <span class="text-red-500 font-bold">{{ $leave->status }}</span>
                        @else
                            <span class="text-yellow-500 font-bold">{{ $leave->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button 
                            data-leave="{{ json_encode([
                                'id' => $leave->id,
                                'student_name' => $leave->student?->first_name . ' ' . $leave->student?->last_name,
                                'start_date' => $leave->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')),
                                'end_date' => $leave->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')),
                                'leave_type' => $leave->leave_type,
                                'status' => $leave->status,
                                'reason' => $leave->reason
                            ]) }}"
                            onclick="openEditLeaveModal(JSON.parse(this.dataset.leave))" 
                            class="p-1.5 rounded text-navy hover:bg-gray-100 transition-colors mr-1" title="Edit">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <form method="POST" action="{{ route('academic.student-leaves.destroy', $leave) }}" class="inline" onsubmit="return confirm('Delete this leave record?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="100%" class="text-center py-12 text-sm font-bold" style="color:#9e8f84;">No student leaves found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $leaves->links() }}
        </div>
    @endif
</div>

{{-- Add Leave Modal --}}
<div id="addLeaveModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-navy"></i>
                Add Student Leave
            </h3>
            <button type="button" onclick="closeAddLeaveModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('academic.student-leaves.store') }}" enctype="multipart/form-data">
            @csrf
            
            {{-- STEP 1 --}}
            <div id="leave-step-1">
                <div class="space-y-4">
                    {{-- Class --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Class <span class="text-red-500">*</span></label>
                        <select name="class_id" id="modal_class_id" onchange="updateModalSections()" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Section <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                        <select name="section_id" id="modal_section_id" onchange="updateModalStudents()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">All Sections</option>
                        </select>
                    </div>

                    {{-- Student --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Student <span class="text-red-500">*</span></label>
                        <select name="student_id" id="modal_student_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">Select Student</option>
                        </select>
                    </div>

                    {{-- Number of Leave Days --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-2">Number of Leave Days: <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-4 mt-1.5">
                            <label class="flex items-center gap-1 text-sm cursor-pointer"><input type="radio" name="leave_days" value="single" checked onchange="toggleModalEndDate(this.value)"> Single Day</label>
                            <label class="flex items-center gap-1 text-sm cursor-pointer"><input type="radio" name="leave_days" value="multiple" onchange="toggleModalEndDate(this.value)"> Multiple Days</label>
                        </div>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1" id="modal_start_date_label">Leave Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="modal_start_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>

                    {{-- End Date --}}
                    <div id="modal_end_date_container" class="hidden">
                        <label class="block text-sm font-medium text-heading mb-1">End Date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" id="modal_end_date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="goToStep(2)" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                        Next
                    </button>
                    <button type="button" onclick="closeAddLeaveModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                </div>
            </div>

            {{-- STEP 2 --}}
            <div id="leave-step-2" class="hidden">
                <div class="space-y-4">
                    {{-- Leave Type --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Leave Type</label>
                        <select name="leave_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">Select Type</option>
                            <option value="Sick">Sick</option>
                            <option value="Personal">Personal</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    {{-- Attachment --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="w-full text-sm mt-1">
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-2">Status <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-4 mt-1.5">
                            <label class="flex items-center gap-1 text-sm text-blue-600 font-medium cursor-pointer"><input type="radio" name="status" value="Approved"> Approved</label>
                            <label class="flex items-center gap-1 text-sm text-yellow-600 font-medium cursor-pointer"><input type="radio" name="status" value="Pending" checked> Pending</label>
                            <label class="flex items-center gap-1 text-sm text-red-600 font-medium cursor-pointer"><input type="radio" name="status" value="Rejected"> Rejected</label>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Reason</label>
                        <textarea name="reason" rows="2" placeholder="Enter reason" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                        Save Leave
                    </button>
                    <button type="button" onclick="goToStep(1)" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                        Back
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Leave Modal --}}
<div id="editLeaveModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="pencil" class="w-5 h-5 text-navy"></i>
                Edit Student Leave
            </h3>
            <button type="button" onclick="closeEditLeaveModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="" id="editLeaveForm" enctype="multipart/form-data">
            @csrf @method('PUT')
            
            <div class="space-y-4">
                {{-- Student Info (Readonly) --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Student</label>
                    <input type="text" id="edit_student_name" readonly class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Start Date --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="edit_start_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">End Date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" id="edit_end_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>

                {{-- Leave Type --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Leave Type</label>
                    <select name="leave_type" id="edit_leave_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        <option value="">Select Type</option>
                        <option value="Sick">Sick</option>
                        <option value="Personal">Personal</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                {{-- Attachment --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Update Attachment (Optional)</label>
                    <input type="file" name="attachment" class="w-full text-sm mt-1">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-2">Status <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-4 mt-1.5">
                        <label class="flex items-center gap-1 text-sm text-blue-600 font-medium cursor-pointer"><input type="radio" name="status" id="edit_status_approved" value="Approved"> Approved</label>
                        <label class="flex items-center gap-1 text-sm text-yellow-600 font-medium cursor-pointer"><input type="radio" name="status" id="edit_status_pending" value="Pending"> Pending</label>
                        <label class="flex items-center gap-1 text-sm text-red-600 font-medium cursor-pointer"><input type="radio" name="status" id="edit_status_rejected" value="Rejected"> Rejected</label>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Reason</label>
                    <textarea name="reason" id="edit_reason" rows="2" placeholder="Enter reason" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                    Update Leave
                </button>
                <button type="button" onclick="closeEditLeaveModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import Leave Modal --}}
<div id="importLeaveModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5 text-navy"></i>
                Import Student Leaves
            </h3>
            <button type="button" onclick="document.getElementById('importLeaveModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="mb-5 bg-blue-50 text-blue-800 p-4 rounded-lg text-sm">
            <p class="font-semibold mb-1">Instructions:</p>
            <ul class="list-disc ml-4 space-y-1 mb-3">
                <li>Download the sample CSV file and fill it with your data.</li>
                <li>Make sure to use correct <strong>Admission No</strong> of the students.</li>
                <li>Dates must be in <strong>YYYY-MM-DD</strong> format.</li>
                <li>Status must be one of: <strong>Approved, Pending, Rejected</strong>.</li>
            </ul>
            <a href="{{ route('academic.student-leaves.import-demo') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md font-medium transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Download Sample CSV
            </a>
        </div>

        <form method="POST" action="{{ route('academic.student-leaves.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-heading mb-1">Upload CSV File <span class="text-red-500">*</span></label>
                <input type="file" name="csv_file" required accept=".csv,.txt"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                    Import Leaves
                </button>
                <button type="button" onclick="document.getElementById('importLeaveModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const filterClassSelect = document.getElementById('filter_class_id');
    const filterSectionSelect = document.getElementById('filter_section_id');
    const selectedFilterSectionId = '{{ request('section_id') }}';

    function updateFilterSections() {
        filterSectionSelect.replaceChildren(new Option('All', ''));
        
        if (filterClassSelect.value === '') {
            return;
        }

        fetch(`/academic/study-materials/sections/${filterClassSelect.value}`)
            .then(res => res.json())
            .then(data => {
                filterSectionSelect.innerHTML = '<option value="">All</option>';
                data.forEach(sec => {
                    let opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = sec.name;
                    if (selectedFilterSectionId == sec.id) opt.selected = true;
                    filterSectionSelect.appendChild(opt);
                });
            });
    }
    
    if (filterClassSelect.value !== '') {
        updateFilterSections();
    }

    // Modal Logic
    function openAddLeaveModal() {
        document.getElementById('addLeaveModal').classList.remove('hidden');
        goToStep(1); // Reset to step 1 when opening
    }
    
    function closeAddLeaveModal() {
        document.getElementById('addLeaveModal').classList.add('hidden');
    }

    function goToStep(step) {
        if (step === 1) {
            document.getElementById('leave-step-1').classList.remove('hidden');
            document.getElementById('leave-step-2').classList.add('hidden');
        } else if (step === 2) {
            // Optional: You can validate step 1 inputs here before moving to step 2
            const classSelect = document.getElementById('modal_class_id');
            const studentSelect = document.getElementById('modal_student_id');
            const startDate = document.getElementById('modal_start_date');
            
            if (!classSelect.value) {
                alert('Please select a class.');
                classSelect.focus();
                return;
            }
            if (!studentSelect.value) {
                alert('Please select a student.');
                studentSelect.focus();
                return;
            }
            if (!startDate.value) {
                alert('Please select a leave date.');
                startDate.focus();
                return;
            }

            document.getElementById('leave-step-1').classList.add('hidden');
            document.getElementById('leave-step-2').classList.remove('hidden');
        }
    }

    function openEditLeaveModal(leave) {
        document.getElementById('editLeaveForm').action = `/academic/student-leaves/${leave.id}`;
        document.getElementById('edit_student_name').value = leave.student_name;
        document.getElementById('edit_start_date').value = leave.start_date;
        document.getElementById('edit_end_date').value = leave.end_date;
        document.getElementById('edit_leave_type').value = leave.leave_type || '';
        document.getElementById('edit_reason').value = leave.reason || '';

        if(leave.status === 'Approved') document.getElementById('edit_status_approved').checked = true;
        else if(leave.status === 'Rejected') document.getElementById('edit_status_rejected').checked = true;
        else document.getElementById('edit_status_pending').checked = true;

        document.getElementById('editLeaveModal').classList.remove('hidden');
    }

    function closeEditLeaveModal() {
        document.getElementById('editLeaveModal').classList.add('hidden');
    }

    const modalClassSelect = document.getElementById('modal_class_id');
    const modalSectionSelect = document.getElementById('modal_section_id');
    const modalStudentSelect = document.getElementById('modal_student_id');

    function updateModalSections() {
        modalSectionSelect.innerHTML = '<option value="">All Sections</option>';
        modalStudentSelect.innerHTML = '<option value="">Select Student</option>';
        
        if (!modalClassSelect.value) return;

        fetch(`/academic/study-materials/sections/${modalClassSelect.value}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(sec => {
                    let opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = sec.name;
                    modalSectionSelect.appendChild(opt);
                });
            });
            
        updateModalStudents();
    }

    function updateModalStudents() {
        modalStudentSelect.innerHTML = '<option value="">Select Student</option>';
        
        if (!modalClassSelect.value) return;

        let url = `/academic/student-leaves/students?class_id=${modalClassSelect.value}`;
        if (modalSectionSelect.value) {
            url += `&section_id=${modalSectionSelect.value}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                data.forEach(student => {
                    let opt = document.createElement('option');
                    opt.value = student.id;
                    opt.textContent = student.first_name + ' ' + (student.last_name || '');
                    modalStudentSelect.appendChild(opt);
                });
            });
    }

    function toggleModalEndDate(val) {
        if(val === 'multiple') {
            document.getElementById('modal_end_date_container').classList.remove('hidden');
            document.getElementById('modal_start_date_label').innerHTML = 'Start Date <span class="text-red-500">*</span>';
            document.getElementById('modal_end_date').required = true;
        } else {
            document.getElementById('modal_end_date_container').classList.add('hidden');
            document.getElementById('modal_start_date_label').innerHTML = 'Leave Date <span class="text-red-500">*</span>';
            document.getElementById('modal_end_date').required = false;
            document.getElementById('modal_end_date').value = document.getElementById('modal_start_date').value;
        }
    }

    document.getElementById('modal_start_date').addEventListener('change', function() {
        let val = document.querySelector('input[name="leave_days"]:checked').value;
        if(val === 'single') {
            document.getElementById('modal_end_date').value = this.value;
        }
    });

    document.querySelector('#addLeaveModal form').addEventListener('submit', function() {
        let val = document.querySelector('input[name="leave_days"]:checked').value;
        if(val === 'single') {
            document.getElementById('modal_end_date').value = document.getElementById('modal_start_date').value;
        }
    });
</script>

<style>
.hover-row:hover { background:#FEFAF6; }
</style>

    {{-- ══ Bulk Select JS (auto-injected) ══ --}}
    <script>
    (function() {
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
                if(toolbar) toolbar.classList.remove('hidden');
                if(countEl) countEl.textContent = checked.length + ' selected';
            } else {
                if(toolbar) toolbar.classList.add('hidden');
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
            var toolbar = document.getElementById('bulkToolbar');
            if(toolbar) toolbar.classList.add('hidden');
        }
        function confirmBulkDelete() {
            var checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return false;
            if (!confirm(checked.length + ' item(s) permanently delete honge. Sure hain?')) return false;
            var c = document.getElementById('bulkIdsContainer');
            c.innerHTML = '';
            checked.forEach(function(cb) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
                c.appendChild(inp);
            });
            return true;
        }
        window.toggleSelectAll   = toggleSelectAll;
        window.updateBulkToolbar = updateBulkToolbar;
        window.clearSelection    = clearSelection;
        window.confirmBulkDelete = confirmBulkDelete;
    })();
    </script>
@endsection
