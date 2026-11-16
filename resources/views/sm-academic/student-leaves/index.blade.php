@extends('layouts.app')

@section('header', 'Student Leaves')

@section('content')

{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Student Leaves</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Leaves: {{ $leaves->total() }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" onclick="openLeaveAnalysisModal('students')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="pie-chart" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> View Analysis
        </button>
        <a href="{{ route('academic.student-leaves.export', request()->all()) }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Export CSV
        </a>
        <button type="button" onclick="document.getElementById('importLeaveModal').classList.remove('hidden')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Import CSV
        </button>
        <button type="button" onclick="openAddLeaveModal()"
                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Add New Leave
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

@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <i data-lucide="alert-circle" class="w-4 h-4"></i>
    {{ session('error') }}
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



{{-- Search & Filter Bar with Pagination Per-Page --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5" style="background:#fff;">
    <form method="GET" action="{{ route('academic.student-leaves.index') }}" id="filterForm" class="flex flex-col md:flex-row items-end gap-3 flex-wrap">
        
        {{-- Class --}}
        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Class</label>
            <select name="class_id" id="filter_class_id" onchange="updateFilterSections(); this.form.submit();" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
                <option value="">All Classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Section --}}
        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Section</label>
            <select name="section_id" id="filter_section_id" onchange="this.form.submit();" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
                <option value="">All</option>
            </select>
        </div>

        {{-- Status --}}
        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Status</label>
            <select name="status" onchange="this.form.submit();" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
                <option value="">All Statuses</option>
                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        {{-- Date --}}
        <div class="w-full md:w-40">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
        </div>

        {{-- Student Name / Search --}}
        <div class="w-full md:w-48">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Student / Adm No</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or admission no..." class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white">
        </div>

        {{-- Per Page Dropdown --}}
        <div class="w-full md:w-32">
            <label class="block text-xs font-medium mb-1" style="color:#211B17;">Per Page</label>
            <select name="per_page" onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none bg-white cursor-pointer"
                    title="Records per page">
                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / page</option>
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 / page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / page</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg text-white text-xs font-semibold bg-navy hover:opacity-90 transition">
                Search
            </button>
            @if(request()->anyFilled(['class_id', 'section_id', 'status', 'date', 'search', 'per_page']))
                <a href="{{ route('academic.student-leaves.index') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-xs font-medium bg-gray-50 hover:bg-gray-100 transition" style="color:#211B17;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ══ Bulk Delete Toolbar ══ --}}
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

{{-- Table Container --}}
<div class="rounded-xl overflow-hidden mb-6" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
    <div class="px-5 py-3 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2" style="background:#F5F1EB;">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-body">
                Showing: <strong>{{ $leaves->firstItem() ?? 0 }} - {{ $leaves->lastItem() ?? 0 }}</strong> of <strong>{{ $leaves->total() }}</strong> student leaves
            </span>
            <span class="text-xs text-stone-400">|</span>
            <span class="text-xs text-stone-600 font-medium">Session: <strong>{{ $session->name }}</strong></span>
        </div>
        <div class="flex items-center gap-2">
            @if(request('class_id'))
                @php $activeFilteredClass = $classes->firstWhere('id', request('class_id')); @endphp
                <span class="text-xs font-semibold text-navy bg-white px-2.5 py-1 rounded-md border border-gray-200">
                    Class: {{ $activeFilteredClass?->name ?? 'ID '.request('class_id') }}
                </span>
            @endif
            @if(request('status'))
                <span class="text-xs font-semibold text-stone-700 bg-white px-2.5 py-1 rounded-md border border-gray-200">
                    Status: {{ request('status') }}
                </span>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-3 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="text-left text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Student Name</th>
                    <th class="text-left text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Class</th>
                    <th class="text-left text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Section</th>
                    <th class="text-left text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Reason</th>
                    <th class="text-left text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Leave Date</th>
                    <th class="text-center text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Attachment</th>
                    <th class="text-left text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Status</th>
                    <th class="text-center text-white font-semibold text-xs px-4 py-3 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row transition-colors">
                    {{-- Row Checkbox for Bulk Delete --}}
                    <td class="px-4 py-3 text-center w-10">
                        <input type="checkbox" value="{{ $leave->id }}" onchange="updateBulkToolbar()"
                            class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                    </td>
                    <td class="px-4 py-3 text-sm font-bold" style="color:#211B17;">
                        <div>
                            <span class="font-bold text-heading">{{ $leave->student?->first_name }} {{ $leave->student?->last_name }}</span>
                            @if($leave->student?->admission_no)
                                <span class="text-[10px] text-stone-400 font-mono block">Adm: {{ $leave->student->admission_no }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 font-semibold">
                        {{ $leave->schoolClass?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-medium">
                        {{ $leave->section?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate font-medium" title="{{ $leave->reason }}">
                        {{ \Illuminate\Support\Str::limit($leave->reason, 40) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 font-semibold whitespace-nowrap">
                        @if($leave->start_date && $leave->end_date && $leave->start_date->equalTo($leave->end_date))
                            {{ $leave->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                        @elseif($leave->start_date && $leave->end_date)
                            {{ $leave->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                            <span class="text-stone-400 text-xs">to</span>
                            {{ $leave->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                        @else
                            —
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
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="check" class="w-3 h-3 mr-1"></i> Approved
                            </span>
                        @elseif($leave->status === 'Rejected')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                <i data-lucide="x" class="w-3 h-3 mr-1"></i> Rejected
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i> Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <button 
                            data-leave="{{ json_encode([
                                'id' => $leave->id,
                                'student_name' => $leave->student?->first_name . ' ' . $leave->student?->last_name,
                                'start_date' => $leave->start_date ? $leave->start_date->format('Y-m-d') : '',
                                'end_date' => $leave->end_date ? $leave->end_date->format('Y-m-d') : '',
                                'leave_type' => $leave->leave_type,
                                'status' => $leave->status,
                                'reason' => $leave->reason
                            ]) }}"
                            onclick="openEditLeaveModal(JSON.parse(this.dataset.leave))" 
                            class="p-1.5 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-2xs transition-colors mr-1" title="Edit">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <form method="POST" action="{{ route('academic.student-leaves.destroy', $leave) }}" class="inline" onsubmit="return confirm('Delete this leave record?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded border border-gray-300 bg-white hover:bg-gray-50 text-red-600 shadow-2xs transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-12 text-sm font-bold" style="color:#9e8f84;">
                        <i data-lucide="calendar" class="w-10 h-10 text-stone-300 mx-auto mb-2"></i>
                        No student leaves found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Bar --}}
    @if($leaves->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-white">
            {{ $leaves->links() }}
        </div>
    @else
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
            <span>Showing <strong class="text-gray-700 font-semibold">{{ $leaves->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $leaves->total() }}</strong> leaves</span>
            <span class="text-gray-400">Page 1 of 1</span>
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     INTERACTIVE DRILLDOWN MODAL: SESSION STUDENT LEAVE ANALYSIS
     ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="leaveAnalysisModal" class="hidden fixed inset-0 z-[120] flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-5">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-stone-200">
        
        {{-- Modal Top Banner Header --}}
        <div class="bg-navy text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold leading-tight">Session Student Leave Analysis</h3>
                    <p class="text-xs text-white/70 mt-0.5 flex items-center gap-2">
                        <span>Session: <strong class="text-white">{{ $session->name }}</strong></span>
                        <span>&bull;</span>
                        <span>School: <strong class="text-white">{{ $school->name }}</strong></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <span class="text-amber-300">{{ $analytics['totalLeaves'] }} Leaves</span>
                    <span class="text-white/40">|</span>
                    <span>{{ $analytics['zeroLeaveCount'] }} Zero Leaves</span>
                    <span class="text-white/40">|</span>
                    <span class="text-emerald-300">{{ $analytics['approvalRate'] }}% Approved</span>
                </div>
                <button type="button" onclick="closeLeaveAnalysisModal()"
                        class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Modal Interactive Tabs & Quick Search Bar --}}
        <div class="bg-stone-50 border-b border-stone-200 px-6 py-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button type="button" id="analysisBtn_students" onclick="switchLeaveTab('students')"
                        class="leave-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-navy text-white shadow-sm">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Student Leaves & Zero Leaves</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($analytics['studentBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_classes" onclick="switchLeaveTab('classes')"
                        class="leave-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>Class-wise Leaves Average</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-stone-200 text-stone-700">{{ count($analytics['classBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_months" onclick="switchLeaveTab('months')"
                        class="leave-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Month-wise Leaves</span>
                </button>
                <button type="button" id="analysisBtn_status" onclick="switchLeaveTab('status')"
                        class="leave-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                    <span>Status & Types</span>
                </button>
            </div>

            {{-- Client-side quick search inside modal --}}
            <div class="relative w-full md:w-64">
                <input type="text" id="modalSearchInput" oninput="filterLeaveModalSearch(this.value)"
                       placeholder="Filter by student, class, or admission..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                <i data-lucide="search" class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
            </div>
        </div>

        {{-- Modal Tab Content Scroll Area --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4" style="background: #faf7f2;">

            {{-- ── TAB 1: STUDENTS HAVING LEAVES & ZERO LEAVE STUDENTS ── --}}
            <div id="leaveTab_students" class="leave-tab-pane space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold text-emerald-700 uppercase">Zero-Leave Students</span>
                        <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $analytics['zeroLeaveCount'] }}</h4>
                        <span class="text-[11px] text-stone-400">100% Attendance (No Leaves)</span>
                    </div>
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold text-blue-700 uppercase">Students with Leaves</span>
                        <h4 class="text-2xl font-black text-blue-700 mt-1">{{ $analytics['studentsWithLeavesCount'] }}</h4>
                        <span class="text-[11px] text-stone-400">Applied for 1 or more leaves</span>
                    </div>
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold uppercase text-stone-600">Total Enrolled Students</span>
                        <h4 class="text-2xl font-black text-heading mt-1">{{ $analytics['totalStudents'] }}</h4>
                        <span class="text-[11px] text-stone-400">Active students in session</span>
                    </div>
                </div>

                {{-- Leaderboard of Students Having Number of Leaves --}}
                <div class="bg-white rounded-xl border border-stone-200 overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-stone-100 font-bold text-sm text-heading flex items-center justify-between" style="background:#F5F1EB;">
                        <span>Students Having Highest Number of Leaves</span>
                        <span class="text-xs font-semibold text-stone-500">{{ count($analytics['studentBreakdown']) }} Students on Record</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-stone-50 text-stone-600 font-semibold uppercase tracking-wider border-b border-stone-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Student Name</th>
                                    <th class="px-4 py-2.5 text-left">Admission No</th>
                                    <th class="px-4 py-2.5 text-left">Class & Section</th>
                                    <th class="px-4 py-2.5 text-center">Total Leaves</th>
                                    <th class="px-4 py-2.5 text-center">Leave Days</th>
                                    <th class="px-4 py-2.5 text-center">Approved</th>
                                    <th class="px-4 py-2.5 text-center">Pending</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100" id="modalStudentLeavesBody">
                                @forelse($analytics['studentBreakdown'] as $sb)
                                    <tr class="modal-student-row hover:bg-stone-50 transition"
                                        data-student-name="{{ strtolower($sb['name']) }}"
                                        data-admission-no="{{ strtolower($sb['admission_no']) }}"
                                        data-class="{{ strtolower($sb['class_name']) }}">
                                        <td class="px-4 py-2.5 font-bold text-heading flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-navy text-white text-[10px] flex items-center justify-center font-bold">
                                                {{ strtoupper(substr($sb['name'], 0, 1)) }}
                                            </div>
                                            {{ $sb['name'] }}
                                        </td>
                                        <td class="px-4 py-2.5 font-mono text-stone-600">{{ $sb['admission_no'] }}</td>
                                        <td class="px-4 py-2.5 text-stone-700 font-semibold">{{ $sb['class_name'] }} ({{ $sb['section_name'] }})</td>
                                        <td class="px-4 py-2.5 text-center font-black text-sm text-navy">
                                            {{ $sb['total_leaves'] }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-bold text-stone-700">
                                            {{ $sb['total_days'] }} {{ Str::plural('day', $sb['total_days']) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                {{ $sb['approved_count'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if($sb['pending_count'] > 0)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                                    {{ $sb['pending_count'] }}
                                                </span>
                                            @else
                                                <span class="text-stone-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-stone-400">No student leave records for this session.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── TAB 2: CLASS-WISE LEAVES AVERAGE ── --}}
            <div id="leaveTab_classes" class="leave-tab-pane hidden space-y-4">
                <div class="bg-amber-50/70 border border-amber-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-amber-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-700 flex-shrink-0"></i>
                        <span>Class-wise leave totals, average leaves per student, and zero-leave student count for session <strong>{{ $session->name }}</strong>.</span>
                    </div>
                    <span class="font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">Avg: {{ $analytics['avgLeavesPerClass'] }} / Class</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($analytics['classBreakdown'] as $cb)
                        <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-sm hover:shadow transition-all">
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-navy text-white flex items-center justify-center text-xs font-bold">
                                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading">{{ $cb['name'] }}</h4>
                                        <span class="text-[11px] text-stone-400">{{ $cb['total_students'] }} Students Enrolled</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-navy">{{ $cb['total_leaves'] }}</span>
                                    <span class="text-[10px] text-stone-400 block font-semibold">{{ $cb['total_days'] }} Leave Days</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 my-3 text-center text-xs">
                                <div class="p-2 rounded bg-stone-50 border border-stone-100">
                                    <span class="text-[10px] text-stone-500 uppercase block">Zero Leaves</span>
                                    <strong class="text-emerald-700 text-sm font-black">{{ $cb['zero_leave_students'] }}</strong>
                                </div>
                                <div class="p-2 rounded bg-stone-50 border border-stone-100">
                                    <span class="text-[10px] text-stone-500 uppercase block">Avg / Student</span>
                                    <strong class="text-stone-800 text-sm font-black">{{ $cb['avg_per_student'] }}</strong>
                                </div>
                                <div class="p-2 rounded bg-stone-50 border border-stone-100">
                                    <span class="text-[10px] text-stone-500 uppercase block">Approved</span>
                                    <strong class="text-blue-700 text-sm font-black">{{ $cb['approved_count'] }}</strong>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-xs">
                                <span class="text-stone-500 text-[11px]">{{ $cb['pending_count'] }} pending approvals</span>
                                <button type="button" onclick="filterMainTableByClass({{ $cb['class_id'] }})"
                                        class="text-navy hover:underline font-semibold inline-flex items-center gap-1">
                                    Filter Table by this Class <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No classes found.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 3: MONTH-WISE LEAVES ── --}}
            <div id="leaveTab_months" class="leave-tab-pane hidden space-y-4">
                <div class="bg-purple-50/70 border border-purple-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-purple-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-purple-700 flex-shrink-0"></i>
                        <span>12-Month distribution showing seasonal leave patterns and student leave volume throughout the year.</span>
                    </div>
                    <span class="font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">{{ $analytics['totalLeaves'] }} Total</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($analytics['monthBreakdown'] as $mb)
                        <div class="bg-white rounded-xl border border-stone-200 p-3.5 text-center shadow-sm">
                            <span class="text-xs font-bold text-stone-500 uppercase">{{ $mb['month_name'] }}</span>
                            <h4 class="text-xl font-black text-heading mt-1">{{ $mb['count'] }}</h4>
                            <div class="w-full bg-stone-100 rounded-full h-1.5 my-2 overflow-hidden">
                                <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $mb['percentage'] }}%;"></div>
                            </div>
                            <span class="text-[10px] text-stone-400 font-semibold">{{ $mb['percentage'] }}% of leaves</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── TAB 4: STATUS & LEAVE TYPES ── --}}
            <div id="leaveTab_status" class="leave-tab-pane hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm text-center">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-stone-500 uppercase">Approved Leaves</span>
                        <h3 class="text-2xl font-black text-emerald-700 mt-1">{{ $analytics['approvedCount'] }}</h3>
                        <span class="text-xs text-stone-400">{{ $analytics['approvalRate'] }}% Approval Rate</span>
                    </div>
                    <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm text-center">
                        <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-stone-500 uppercase">Pending Review</span>
                        <h3 class="text-2xl font-black text-amber-700 mt-1">{{ $analytics['pendingCount'] }}</h3>
                        <span class="text-xs text-stone-400">Awaiting faculty/admin approval</span>
                    </div>
                    <div class="bg-white rounded-xl border border-red-200 p-4 shadow-sm text-center">
                        <div class="w-10 h-10 rounded-full bg-red-50 text-red-700 flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="x-circle" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-stone-500 uppercase">Rejected Leaves</span>
                        <h3 class="text-2xl font-black text-red-700 mt-1">{{ $analytics['rejectedCount'] }}</h3>
                        <span class="text-xs text-stone-400">Applications declined</span>
                    </div>
                </div>

                {{-- Leave Type Categories --}}
                <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
                    <h4 class="font-bold text-sm text-heading mb-3 flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-navy"></i>
                        Leave Types Distribution
                    </h4>
                    <div class="space-y-3">
                        @forelse($analytics['typeBreakdown'] as $tb)
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                    <span class="text-stone-800">{{ $tb['type'] }}</span>
                                    <span class="text-stone-500">{{ $tb['count'] }} leaves ({{ $tb['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-navy h-2 rounded-full" style="width: {{ $tb['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-stone-400 text-xs text-center py-4">No leave types recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
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

<style>
.hover-row:hover { background:#FEFAF6; }
</style>

<script>
    // ── Class & Section Cascading Map ──
    const classSectionsMap = @json($classes->mapWithKeys(fn($c) => [$c->id => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])]));

    function updateFilterSections() {
        const classId = document.getElementById('filter_class_id').value;
        const secSelect = document.getElementById('filter_section_id');
        const selectedSec = "{{ request('section_id') }}";
        secSelect.innerHTML = '<option value="">All</option>';
        if (classId && classSectionsMap[classId]) {
            classSectionsMap[classId].forEach(sec => {
                const opt = document.createElement('option');
                opt.value = sec.id;
                opt.textContent = sec.name;
                if (String(sec.id) === String(selectedSec)) {
                    opt.selected = true;
                }
                secSelect.appendChild(opt);
            });
        }
    }

    // ── Bulk Select & Delete JS ──
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
        if (!confirm('Are you sure you want to delete ' + checked.length + ' selected student leave record(s)?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    // ── Drilldown Analysis Modal Controller ──
    function openLeaveAnalysisModal(tab = 'students') {
        const modal = document.getElementById('leaveAnalysisModal');
        if (modal) {
            modal.classList.remove('hidden');
            switchLeaveTab(tab);
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeLeaveAnalysisModal() {
        const modal = document.getElementById('leaveAnalysisModal');
        if (modal) modal.classList.add('hidden');
    }

    function switchLeaveTab(tabName) {
        document.querySelectorAll('.leave-tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.leave-tab-btn').forEach(btn => {
            btn.classList.remove('bg-navy', 'text-white', 'shadow-sm');
            btn.classList.add('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
        });

        const targetPane = document.getElementById('leaveTab_' + tabName);
        const targetBtn = document.getElementById('analysisBtn_' + tabName);

        if (targetPane) targetPane.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.remove('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
            targetBtn.classList.add('bg-navy', 'text-white', 'shadow-sm');
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function filterLeaveModalSearch(query) {
        query = (query || '').toLowerCase().trim();
        document.querySelectorAll('.modal-student-row').forEach(row => {
            const name = row.getAttribute('data-student-name') || '';
            const adm = row.getAttribute('data-admission-no') || '';
            const cls = row.getAttribute('data-class') || '';
            const match = !query || name.includes(query) || adm.includes(query) || cls.includes(query);
            row.style.display = match ? '' : 'none';
        });
    }

    function filterMainTableByClass(classId) {
        closeLeaveAnalysisModal();
        const select = document.getElementById('filter_class_id');
        if (select) {
            select.value = classId;
            updateFilterSections();
            document.getElementById('filterForm').submit();
        }
    }

    // Modal background click or ESC key to close
    document.getElementById('leaveAnalysisModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeLeaveAnalysisModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLeaveAnalysisModal();
            closeAddLeaveModal();
            closeEditLeaveModal();
        }
    });

    // ── Add/Edit Modals Logic ──
    function openAddLeaveModal() {
        document.getElementById('addLeaveModal').classList.remove('hidden');
        goToStep(1);
        if (window.lucide) window.lucide.createIcons();
    }

    function closeAddLeaveModal() {
        document.getElementById('addLeaveModal').classList.add('hidden');
    }

    function goToStep(step) {
        if (step === 1) {
            document.getElementById('leave-step-1').classList.remove('hidden');
            document.getElementById('leave-step-2').classList.add('hidden');
        } else if (step === 2) {
            const classVal = document.getElementById('modal_class_id').value;
            const studentVal = document.getElementById('modal_student_id').value;
            const startDateVal = document.getElementById('modal_start_date').value;
            const isMultiple = document.querySelector('input[name="leave_days"]:checked').value === 'multiple';
            const endDateVal = document.getElementById('modal_end_date').value;

            if (!classVal || !studentVal || !startDateVal) {
                alert('Please fill all required fields in Step 1.');
                return;
            }
            if (isMultiple && !endDateVal) {
                alert('Please select an End Date for multiple days leave.');
                return;
            }

            document.getElementById('leave-step-1').classList.add('hidden');
            document.getElementById('leave-step-2').classList.remove('hidden');
        }
    }

    function openEditLeaveModal(leave) {
        document.getElementById('edit_student_name').value = leave.student_name;
        document.getElementById('edit_start_date').value = leave.start_date;
        document.getElementById('edit_end_date').value = leave.end_date;
        document.getElementById('edit_leave_type').value = leave.leave_type || '';
        document.getElementById('edit_reason').value = leave.reason || '';
        
        if (leave.status === 'Approved') document.getElementById('edit_status_approved').checked = true;
        else if (leave.status === 'Rejected') document.getElementById('edit_status_rejected').checked = true;
        else document.getElementById('edit_status_pending').checked = true;

        document.getElementById('editLeaveForm').action = `/academic/student-leaves/${leave.id}`;
        document.getElementById('editLeaveModal').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
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

        if (classSectionsMap[modalClassSelect.value]) {
            classSectionsMap[modalClassSelect.value].forEach(sec => {
                let opt = document.createElement('option');
                opt.value = sec.id;
                opt.textContent = sec.name;
                modalSectionSelect.appendChild(opt);
            });
        }
            
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

    document.getElementById('modal_start_date')?.addEventListener('change', function() {
        let val = document.querySelector('input[name="leave_days"]:checked')?.value;
        if(val === 'single') {
            document.getElementById('modal_end_date').value = this.value;
        }
    });

    document.querySelector('#addLeaveModal form')?.addEventListener('submit', function() {
        let val = document.querySelector('input[name="leave_days"]:checked')?.value;
        if(val === 'single') {
            document.getElementById('modal_end_date').value = document.getElementById('modal_start_date').value;
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        updateFilterSections();
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

@endsection
