@extends('layouts.app')

@section('header', 'Book Issue')

@section('content')

{{-- Header Banner --}}
<div class="rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6" style="background:#B47A42;">
    <div class="flex items-center gap-2">
        <i data-lucide="book-open-check" class="w-5 h-5 text-white"></i>
        <span class="text-white font-bold text-base tracking-wide">Books Issued</span>
    </div>
    <button type="button" onclick="openIssueModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
            style="color:#B47A42;">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Issue Book
    </button>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium" style="background:#eaf7ee; color:#2e7d52; border-left:4px solid #2e7d52;">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium" style="background:#fef0f0; color:#b44242; border-left:4px solid #b44242;">
    {{ session('error') }}
</div>
@endif

{{-- Stats Bar --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    @php
        $totalAll      = $issues instanceof \Illuminate\Pagination\LengthAwarePaginator ? $issues->total() : $issues->count();
        $totalPending  = $issues->where('status', 'issued')->count();
        $totalReturned = $issues->where('status', 'returned')->count();
    @endphp

    {{-- Stat Card: Total Issued --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="book-open" class="w-4 h-4 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Total Issued</p>
                <p class="text-xs text-gray-400">{{ $school?->name ?? 'Library' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalAll }}</div>
    </div>

    {{-- Stat Card: Pending Return --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="clock" class="w-4 h-4 text-red-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Pending Return</p>
                <p class="text-xs text-gray-400">Not yet returned</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalPending }}</div>
    </div>

    {{-- Stat Card: Returned --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Returned</p>
                <p class="text-xs text-gray-400">Successfully returned</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalReturned }}</div>
    </div>

</div>

{{-- Search + Filter Bar --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-3 mb-4 flex flex-wrap items-center gap-3">
    <div class="flex items-center gap-2 flex-1 min-w-[180px]">
        <i data-lucide="search" class="w-4 h-4 text-body flex-shrink-0"></i>
        <input type="text" id="tableSearch" placeholder="Search book, student, enrollment..."
               class="text-sm outline-none bg-transparent text-heading placeholder-body w-full" />
    </div>
    <div class="flex items-center gap-2">
        <label class="text-xs text-body font-medium">Status:</label>
        <select id="statusFilter"
                class="text-xs border rounded-lg px-2 py-1.5 text-heading outline-none"
                style="border-color:#DED5CB;">
            <option value="">All</option>
            <option value="issued">Pending</option>
            <option value="returned">Returned</option>
        </select>
    </div>
</div>

{{-- Issues Table --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="issueTable">
            <thead>
                <tr style="background:#B47A42;">
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">#</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Book Title</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Issued To</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Enrollment No.</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Class</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Section</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Date Issued</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Return Date</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Status</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Author</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Subject</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Rack No.</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="issueTableBody">
                @forelse($issues as $i => $issue)
                @php
                    $isPending = $issue->status === 'issued';
                    $isOverdue = $isPending && $issue->return_due_date < now()->toDateString();
                @endphp
                <tr class="hover:bg-orange-50/30 transition issue-row"
                    data-status="{{ $issue->status }}">
                    <td class="px-4 py-3 text-body text-xs">{{ $loop->iteration }}</td>

                    {{-- Book --}}
                    <td class="px-4 py-3">
                        <p class="font-semibold text-heading text-xs leading-tight">{{ $issue->book->title }}</p>
                    </td>

                    {{-- Student --}}
                    <td class="px-4 py-3">
                        <p class="font-medium text-heading text-xs">
                            {{ $issue->student->first_name }} {{ $issue->student->last_name }}
                        </p>
                    </td>

                    {{-- Enrollment --}}
                    <td class="px-4 py-3 text-body text-xs">{{ $issue->student->admission_no ?? '—' }}</td>

                    {{-- Class --}}
                    <td class="px-4 py-3 text-body text-xs">{{ $issue->student->schoolClass->name ?? '—' }}</td>

                    {{-- Section --}}
                    <td class="px-4 py-3 text-body text-xs">{{ $issue->student->section->name ?? '—' }}</td>

                    {{-- Date Issued --}}
                    <td class="px-4 py-3 text-body text-xs whitespace-nowrap">
                        {{ $issue->issue_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                    </td>

                    {{-- Return Date --}}
                    <td class="px-4 py-3 text-xs whitespace-nowrap">
                        @if($issue->status === 'returned' && $issue->actual_return_date)
                            <span class="font-medium" style="color:#2e7d52;">
                                {{ $issue->actual_return_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                            </span>
                            <p class="text-body text-xs">Due: {{ $issue->return_due_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</p>
                        @else
                            <span class="{{ $isOverdue ? 'font-semibold' : '' }}"
                                  style="color: {{ $isOverdue ? '#b44242' : '#766C63' }};">
                                {{ $issue->return_due_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                                @if($isOverdue) <br><span class="text-xs">(Overdue)</span> @endif
                            </span>
                        @endif
                    </td>

                    {{-- Status Badge --}}
                    <td class="px-4 py-3">
                        @if($issue->status === 'returned')
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                  style="background:#eaf7ee; color:#2e7d52;">Returned</span>
                        @elseif($isOverdue)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                  style="background:#fef0f0; color:#b44242;">Overdue</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                  style="background:#fff4e5; color:#c26c10;">Pending</span>
                        @endif
                    </td>

                    {{-- Author --}}
                    <td class="px-4 py-3 text-body text-xs">{{ $issue->book->author ?? '—' }}</td>

                    {{-- Subject --}}
                    <td class="px-4 py-3 text-body text-xs">{{ $issue->book->subject ?? '—' }}</td>

                    {{-- Rack No --}}
                    <td class="px-4 py-3 text-body text-xs">{{ $issue->book->rack_number ?? '—' }}</td>

                    {{-- Action --}}
                    <td class="px-4 py-3">
                        @if($isPending)
                        <button type="button"
                                onclick="openReturnModal({{ $issue->id }}, '{{ addslashes($issue->book->title) }}', '{{ addslashes($issue->student->first_name.' '.$issue->student->last_name) }}')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-semibold text-white hover:opacity-90 transition"
                                style="background:#2e7d52;">
                            <i data-lucide="corner-up-left" class="w-3 h-3"></i> Return
                        </button>
                        @else
                        <span class="text-xs text-body">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="13" class="px-4 py-14 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="book-open" class="w-10 h-10 text-body opacity-30"></i>
                            <p class="text-sm text-body">No books issued yet.</p>
                            <button type="button" onclick="openIssueModal()"
                                    class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition"
                                    style="background:#B47A42;">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Issue First Book
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(method_exists($issues, 'links') && $issues->lastPage() > 1)
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $issues->links() }}
    </div>
    @endif
</div>

{{-- ═══════════════════ ISSUE BOOK MODAL ═══════════════════ --}}
<div id="issueModal" class="fixed inset-0 z-50 hidden items-center justify-center"
     style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4" style="background:#B47A42;">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="book-plus" class="w-4 h-4"></i> Issue Book to Student
            </h3>
            <button type="button" onclick="closeIssueModal()"
                    class="text-white opacity-80 hover:opacity-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('library.book-issue.store') }}" id="issueForm">
            @csrf
            
            {{-- Modal Body --}}
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                @if($errors->any())
                <div class="mb-5 p-3 rounded-lg text-sm" style="background:#fef0f0; color:#7a181c; border-left:4px solid #b44242;">
                    @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
                @endif

                <div class="space-y-5">
                    {{-- Book --}}
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <span class="text-red-500">*</span> Select Book
                        </label>
                        <select name="book_id" id="modalBookSelect" required
                                class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                style="border-color: #DED5CB;">
                            <option value="">— Select Book —</option>
                            @foreach($books as $book)
                            <option value="{{ $book->id }}"
                                    data-qty="{{ $book->quantity }}"
                                    data-issued="{{ $book->activeIssues()->count() }}"
                                    {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                {{ $book->title }}{{ $book->author ? ' — '.$book->author : '' }}
                                (Avail: {{ max(0, $book->quantity - $book->activeIssues()->count()) }}/{{ $book->quantity }})
                            </option>
                            @endforeach
                        </select>
                        <p id="modalBookAvail" class="text-xs mt-1 hidden font-medium"></p>
                    </div>

                    {{-- Class + Section row --}}
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <span class="text-red-500">*</span> Class
                            </label>
                            <select id="modalClassSelect" name="_class_id"
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                    style="border-color: #DED5CB;">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">Section</label>
                            <select id="modalSectionSelect" name="_section_id"
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                    style="border-color: #DED5CB;">
                                <option value="">All Sections</option>
                            </select>
                        </div>
                    </div>

                    {{-- Student --}}
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <span class="text-red-500">*</span> Student
                        </label>
                        <select name="student_id" id="modalStudentSelect" required
                                class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                style="border-color: #DED5CB;">
                            <option value="">Select Student</option>
                        </select>
                    </div>

                    {{-- Dates row --}}
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <span class="text-red-500">*</span> Date Issued
                            </label>
                            <input type="date" name="issue_date"
                                   value="{{ old('issue_date', date('Y-m-d')) }}" required
                                   class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                   style="border-color: #DED5CB;" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <span class="text-red-500">*</span> Return Due Date
                            </label>
                            <input type="date" name="return_due_date"
                                   value="{{ old('return_due_date') }}" required
                                   class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                   style="border-color: #DED5CB;" />
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-4">
                <button type="button" onclick="closeIssueModal()"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium border hover:bg-white transition bg-white shadow-sm"
                        style="border-color:#DED5CB; color:#766C63;">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-bold text-white hover:opacity-90 transition shadow-sm"
                        style="background:#B47A42;">
                    <i data-lucide="send" class="w-4 h-4 inline mr-1"></i>
                    Issue Book
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════ RETURN MODAL ═══════════════════ --}}
<div id="returnModal" class="fixed inset-0 z-50 hidden items-center justify-center"
     style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4" style="background:#2e7d52;">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="corner-up-left" class="w-4 h-4"></i> Return Book
            </h3>
            <button type="button" onclick="closeReturnModal()"
                    class="text-white opacity-80 hover:opacity-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" id="returnForm" action="">
            @csrf
            
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                <div id="returnBookInfo" class="text-sm font-medium text-body mb-5 p-3 rounded-lg border border-gray-100"
                     style="background:#f9fafb;"></div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <span class="text-red-500">*</span> Actual Return Date
                        </label>
                        <input type="date" name="actual_return_date" value="{{ date('Y-m-d') }}" required
                               class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-green-600 transition-colors"
                               style="border-color: #DED5CB;" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">Note (optional)</label>
                        <textarea name="return_note" rows="3"
                                  placeholder="Book condition, remarks..."
                                  class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none resize-none focus:border-green-600 transition-colors"
                                  style="border-color: #DED5CB;"></textarea>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-4">
                <button type="button" onclick="closeReturnModal()"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium border hover:bg-white transition bg-white shadow-sm"
                        style="border-color:#DED5CB; color:#766C63;">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-bold text-white hover:opacity-90 transition shadow-sm"
                        style="background:#2e7d52;">
                    Save & Return
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Issue Modal ──────────────────────────────────────────────────────────
function openIssueModal() {
    const m = document.getElementById('issueModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeIssueModal() {
    const m = document.getElementById('issueModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// Auto-open if validation errors (form was submitted)
@if($errors->any())
openIssueModal();
@endif

// ── Book availability hint ───────────────────────────────────────────────
document.getElementById('modalBookSelect').addEventListener('change', function () {
    const opt    = this.options[this.selectedIndex];
    const qty    = parseInt(opt.getAttribute('data-qty') || 0);
    const issued = parseInt(opt.getAttribute('data-issued') || 0);
    const avail  = qty - issued;
    const msg    = document.getElementById('modalBookAvail');
    if (this.value) {
        msg.textContent = `Available: ${avail} / ${qty} copies`;
        msg.style.color = avail > 0 ? '#2e7d52' : '#b44242';
        msg.classList.remove('hidden');
    } else {
        msg.classList.add('hidden');
    }
});

// ── Class → Section AJAX ─────────────────────────────────────────────────
const modalClass   = document.getElementById('modalClassSelect');
const modalSection = document.getElementById('modalSectionSelect');
const modalStudent = document.getElementById('modalStudentSelect');

modalClass.addEventListener('change', function () {
    modalSection.innerHTML = '<option value="">All Sections</option>';
    modalStudent.innerHTML = '<option value="">Select Student</option>';
    if (!this.value) return;
    fetch('{{ url("library/classes") }}/' + this.value + '/sections')
        .then(r => r.json())
        .then(data => {
            data.forEach(s => {
                modalSection.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
            loadStudents();
        });
});

modalSection.addEventListener('change', loadStudents);

function loadStudents() {
    const cid = modalClass.value;
    const sid = modalSection.value;
    if (!cid) { modalStudent.innerHTML = '<option value="">Select Student</option>'; return; }
    modalStudent.innerHTML = '<option value="">Loading...</option>';
    fetch(`{{ url('library/students') }}?class_id=${cid}&section_id=${sid}`)
        .then(r => r.json())
        .then(data => {
            modalStudent.innerHTML = '<option value="">Select Student</option>';
            data.forEach(s => {
                modalStudent.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });
}

// ── Return Modal ─────────────────────────────────────────────────────────
function openReturnModal(issueId, bookTitle, studentName) {
    document.getElementById('returnForm').action = '/library/book-issue/' + issueId + '/return';
    document.getElementById('returnBookInfo').innerHTML =
        `<strong>Book:</strong> ${bookTitle}<br><strong>Student:</strong> ${studentName}`;
    const m = document.getElementById('returnModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeReturnModal() {
    const m = document.getElementById('returnModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// ── Table Search + Status Filter ─────────────────────────────────────────
const searchInput  = document.getElementById('tableSearch');
const statusFilter = document.getElementById('statusFilter');

function filterTable() {
    const q      = searchInput.value.toLowerCase();
    const status = statusFilter.value;
    let visibleCount = 0;

    document.querySelectorAll('.issue-row').forEach(row => {
        const text     = row.textContent.toLowerCase();
        const rowStatus = row.getAttribute('data-status');
        const matchQ   = !q || text.includes(q);
        const matchS   = !status || rowStatus === status;
        row.style.display = (matchQ && matchS) ? '' : 'none';
        if (matchQ && matchS) visibleCount++;
    });

    // Show empty state if nothing visible
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.style.display = visibleCount === 0 ? '' : 'none';
}

searchInput.addEventListener('input', filterTable);
statusFilter.addEventListener('change', filterTable);

// Close modals on backdrop click
['issueModal', 'returnModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function (e) {
        if (e.target === this) {
            this.classList.add('hidden');
            this.classList.remove('flex');
        }
    });
});
</script>

@endsection
