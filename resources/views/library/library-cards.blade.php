@extends('layouts.app')

@section('header', 'Library Cards')

@section('content')

{{-- Header Banner --}}
<div class="rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6" style="background:#B47A42;">
    <div class="flex items-center gap-2">
        <i data-lucide="id-card" class="w-5 h-5 text-white"></i>
        <span class="text-white font-bold text-base tracking-wide">Library Cards</span>
    </div>
    <button type="button" onclick="openGenerateModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
            style="color:#B47A42;">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Generate Library Card
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

{{-- Cards Table --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#fdf8f4; color:#B47A42; border-bottom: 2px solid #f0e6d8;">
                    <th class="text-left px-4 py-3 font-semibold text-xs">#</th>
                    <th class="text-left px-4 py-3 font-semibold text-xs">Card Number</th>
                    <th class="text-left px-4 py-3 font-semibold text-xs">Student</th>
                    <th class="text-left px-4 py-3 font-semibold text-xs">Class</th>
                    <th class="text-left px-4 py-3 font-semibold text-xs">Section</th>
                    <th class="text-left px-4 py-3 font-semibold text-xs">Date Issued</th>
                    <th class="text-left px-4 py-3 font-semibold text-xs">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cards as $i => $card)
                <tr class="hover:bg-orange-50/30 transition">
                    <td class="px-4 py-3 text-body text-xs">{{ $loop->iteration }}</td>

                    <td class="px-4 py-3">
                        <span class="font-bold text-xs" style="color:#B47A42;">{{ $card->card_number }}</span>
                    </td>

                    <td class="px-4 py-3">
                        <p class="font-semibold text-heading text-xs leading-tight">
                            {{ $card->student->first_name }} {{ $card->student->last_name }}
                        </p>
                        <p class="text-xs text-body">{{ $card->student->admission_no ?? '—' }}</p>
                    </td>

                    <td class="px-4 py-3 text-body text-xs">{{ $card->student->schoolClass->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-body text-xs">{{ $card->student->section->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-body text-xs">{{ $card->date_issued->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>

                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition" style="background:#2e7d52;">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print
                            </button>
                            <form action="{{ route('library.library-cards.destroy', $card) }}" method="POST" onsubmit="return confirm('Delete this library card?');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition" style="background:#b44242;">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="id-card" class="w-10 h-10 text-body opacity-30"></i>
                            <p class="text-sm text-body">No library cards generated yet.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cards->lastPage() > 1)
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $cards->links() }}
    </div>
    @endif
</div>

{{-- ═══════════════════ GENERATE CARD MODAL ═══════════════════ --}}
<div id="generateModal" class="fixed inset-0 z-50 hidden items-center justify-center"
     style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4" style="background:#B47A42;">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="id-card" class="w-4 h-4"></i> Generate Library Card(s)
            </h3>
            <button type="button" onclick="closeGenerateModal()"
                    class="text-white opacity-80 hover:opacity-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('library.library-cards.generate') }}">
            @csrf
            
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                @if($errors->any())
                <div class="mb-5 p-3 rounded-lg text-sm" style="background:#fef0f0; color:#7a181c; border-left:4px solid #b44242;">
                    @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
                @endif

                {{-- Mode Toggle --}}
                <div class="mb-6 flex gap-6 p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="generation_type" value="single" class="accent-orange-700 w-4 h-4"
                               {{ old('generation_type', 'single') === 'single' ? 'checked' : '' }}
                               onchange="toggleGenMode()">
                        <span class="text-sm font-semibold text-heading">Single Card</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="generation_type" value="bulk" class="accent-orange-700 w-4 h-4"
                               {{ old('generation_type') === 'bulk' ? 'checked' : '' }}
                               onchange="toggleGenMode()">
                        <span class="text-sm font-semibold text-heading">Bulk Cards (Class/Section)</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-5 mb-5">
                    {{-- Prefix --}}
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <span class="text-red-500">*</span> Prefix
                        </label>
                        <input type="text" name="prefix" id="prefix" value="{{ old('prefix', 'LIB') }}" required
                               class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                               style="border-color: #DED5CB;" />
                    </div>

                    {{-- Start Number --}}
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <span class="text-red-500">*</span> Starting Number
                        </label>
                        <input type="number" name="starting_number" id="startNum" value="{{ old('starting_number', 1) }}" required min="1"
                               class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                               style="border-color: #DED5CB;" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5 mb-5">
                    {{-- Class --}}
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <span class="text-red-500">*</span> Class
                        </label>
                        <select name="class_id" id="modalClassSelect" required
                                class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                style="border-color: #DED5CB;">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">Section</label>
                        <select name="section_id" id="modalSectionSelect"
                                class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                                style="border-color: #DED5CB;">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                </div>

                {{-- Student (Single Only) --}}
                <div id="studentWrap" class="mb-5">
                    <label class="block text-sm font-semibold text-heading mb-2">
                        <span class="text-red-500">*</span> Student
                    </label>
                    <select name="student_id" id="modalStudentSelect"
                            class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                            style="border-color: #DED5CB;">
                        <option value="">Select Student</option>
                    </select>
                </div>

                {{-- Date Issued --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-heading mb-2">
                        <span class="text-red-500">*</span> Date Issued
                    </label>
                    <input type="date" name="date_issued" value="{{ old('date_issued', date('Y-m-d')) }}" required
                           class="w-full border rounded-lg px-3 py-2.5 text-sm text-heading outline-none focus:border-orange-500 transition-colors"
                           style="border-color: #DED5CB;" />
                </div>

                {{-- Preview --}}
                <div class="p-4 rounded-xl mb-2 bg-orange-50/50 border border-orange-200 border-dashed">
                    <p class="text-sm font-medium text-body mb-1">First Card Preview:</p>
                    <p id="cardPreview" class="text-lg font-bold tracking-wider" style="color:#B47A42;">LIB0001</p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-4">
                <button type="button" onclick="closeGenerateModal()"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium border hover:bg-white transition bg-white shadow-sm"
                        style="border-color:#DED5CB; color:#766C63;">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-bold text-white hover:opacity-90 transition shadow-sm"
                        style="background:#B47A42;">
                    <i data-lucide="zap" class="w-4 h-4 inline mr-1"></i>
                    Generate
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .bg-card, .bg-card * { visibility: visible; }
    .bg-card { position: absolute; left: 0; top: 0; width: 100%; border:none; box-shadow:none; }
    .overflow-x-auto { overflow: visible !important; }
    button, a, form { display: none !important; }
}
</style>

<script>
// ── Modal ──────────────────────────────────────────────────────────────
function openGenerateModal() {
    const m = document.getElementById('generateModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeGenerateModal() {
    const m = document.getElementById('generateModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

@if($errors->any())
openGenerateModal();
@endif

// ── Preview Logic ──────────────────────────────────────────────────────
const prefixInp = document.getElementById('prefix');
const startNumInp = document.getElementById('startNum');
const previewEl = document.getElementById('cardPreview');

function updatePreview() {
    const p = prefixInp.value;
    const n = parseInt(startNumInp.value) || 1;
    previewEl.textContent = p + n.toString().padStart(4, '0');
}
prefixInp.addEventListener('input', updatePreview);
startNumInp.addEventListener('input', updatePreview);
updatePreview();

// ── Generation Mode (Single vs Bulk) ───────────────────────────────────
function toggleGenMode() {
    const mode = document.querySelector('input[name="generation_type"]:checked').value;
    const stuWrap = document.getElementById('studentWrap');
    const stuSelect = document.getElementById('modalStudentSelect');

    if (mode === 'single') {
        stuWrap.style.display = 'block';
        stuSelect.setAttribute('required', 'required');
    } else {
        stuWrap.style.display = 'none';
        stuSelect.removeAttribute('required');
    }
}
toggleGenMode(); // init

// ── AJAX Classes → Sections → Students ─────────────────────────────────
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
    fetch(`{{ url('library/students') }}?class_id=${cid}&section_id=${sid}&without_library_card=1`)
        .then(r => r.json())
        .then(data => {
            modalStudent.innerHTML = '<option value="">Select Student</option>';
            data.forEach(s => {
                modalStudent.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });
}
</script>

@endsection
