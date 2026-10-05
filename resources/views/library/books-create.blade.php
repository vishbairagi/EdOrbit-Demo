@extends('layouts.app')

@section('header', 'Add New Book')

@section('content')

{{-- Header Banner --}}
<div class="rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6" style="background:#B47A42;">
    <div class="flex items-center gap-2">
        <i data-lucide="book-plus" class="w-5 h-5 text-white"></i>
        <span class="text-white font-bold text-base">Add New Book</span>
    </div>
    <a href="{{ route('library.books') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
       style="color:#B47A42;">
        <i data-lucide="list" class="w-3.5 h-3.5"></i> View All
    </a>
</div>

@if($errors->any())
<div class="mb-5 p-4 rounded-lg" style="background:#fef0f0; border-left: 4px solid #B4232A;">
    <ul class="text-sm list-disc pl-5" style="color:#7a181c;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-6">
    <form method="POST" action="{{ route('library.books.store') }}" id="bookForm">
        @csrf

        {{-- ── Book Type Toggle ─────────────────────────────────────────── --}}
        <div class="mb-6">
            <label class="block text-xs font-semibold text-heading mb-2">Book Type</label>
            <div class="flex flex-wrap gap-3">

                {{-- Individual --}}
                <label id="modeIndLabel"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all select-none"
                       style="border-color:#B47A42; background:#fff8f2;">
                    <input type="radio" name="book_type" value="individual"
                           class="hidden" {{ old('book_type','individual') === 'individual' ? 'checked' : '' }} />
                    <div id="indDot" class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                         style="border-color:#B47A42;">
                        <div id="indDotFill" class="w-2 h-2 rounded-full" style="background:#B47A42;"></div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-heading">Individual Book</p>
                        <p class="text-xs text-body leading-tight">No class assignment — general library book</p>
                    </div>
                </label>

                {{-- Class-wise --}}
                <label id="modeCwLabel"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all select-none"
                       style="border-color:#DED5CB; background:transparent;">
                    <input type="radio" name="book_type" value="class_wise"
                           class="hidden" {{ old('book_type') === 'class_wise' ? 'checked' : '' }} />
                    <div id="cwDot" class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                         style="border-color:#DED5CB;">
                        <div id="cwDotFill" class="w-2 h-2 rounded-full hidden" style="background:#B47A42;"></div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-heading">Class-wise Book</p>
                        <p class="text-xs text-body leading-tight">Assign to specific class(es) & subject(s)</p>
                    </div>
                </label>

            </div>
        </div>

        {{-- ── Class & Subject Section (class_wise only) ───────────────── --}}
        <div id="classWiseSection" class="{{ old('book_type') === 'class_wise' ? '' : 'hidden' }}">
            <div class="rounded-xl border-2 p-5 mb-5" style="border-color:#f0e6d8; background:#fdf8f4;">

                {{-- Classes --}}
                <div class="mb-5">
                    <p class="text-xs font-bold text-heading mb-2">
                        <span class="text-red-500">*</span>
                        Select Class(es)
                        <span class="ml-1 text-xs font-normal text-body">(ek ya ek se jyada)</span>
                    </p>

                    @if($classes->isEmpty())
                        <p class="text-xs p-3 rounded-lg" style="background:#fef0f0; color:#b44242;">
                            No classes found for current session.
                        </p>
                    @else
                        <div class="flex flex-wrap gap-2" id="classChipContainer">
                            @foreach($classes as $class)
                            <label class="class-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 cursor-pointer transition-all text-xs select-none"
                                   style="border-color:#DED5CB; background:#fff;">
                                <input type="checkbox" name="class_ids[]"
                                       value="{{ $class->id }}"
                                       class="class-cb hidden"
                                       {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }} />
                                <div class="w-3.5 h-3.5 rounded border-2 flex-shrink-0 chip-box flex items-center justify-center transition-all"
                                     style="border-color:#DED5CB;">
                                    <i data-lucide="check" class="w-2.5 h-2.5 text-white chip-tick" style="display:none;"></i>
                                </div>
                                <span class="font-semibold chip-name text-heading">{{ $class->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        <p id="classSelSummary" class="text-xs mt-2 hidden">
                            Selected: <span id="classSelText" class="font-semibold" style="color:#B47A42;"></span>
                        </p>
                    @endif
                </div>

                {{-- Subjects (AJAX) --}}
                <div id="subjectPanel" class="hidden border-t pt-4" style="border-color:#e8ddd3;">
                    <p class="text-xs font-bold text-heading mb-2">
                        Select Subject(s)
                        <span class="ml-1 text-xs font-normal text-body">(ek ya ek se jyada)</span>
                    </p>
                    <div id="subjectLoadingSpinner" class="hidden items-center gap-2 text-xs text-body py-1">
                        <div class="w-4 h-4 rounded-full border-2 border-t-transparent subject-spinner"
                             style="border-color:#B47A42; border-top-color:transparent;"></div>
                        Fetching subjects...
                    </div>
                    <div id="subjectChipContainer" class="flex flex-wrap gap-2"></div>
                    <p id="noSubjectsMsg" class="text-xs text-body mt-1 hidden">
                        No active subjects found for selected class(es).
                    </p>
                </div>

            </div>
        </div>

        {{-- ── Book Details ─────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-heading mb-1.5">
                    <span class="text-red-500">*</span> Title
                </label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="Enter book title"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" required />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Author</label>
                <input type="text" name="author" value="{{ old('author') }}"
                       placeholder="Enter book author"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">
                    Subject
                    <span id="subjectFieldHint" class="ml-1 font-normal text-body text-xs hidden">(Individual book ke liye)</span>
                </label>
                <input type="text" name="subject" value="{{ old('subject') }}"
                       placeholder="Enter subject (e.g. Science, History)"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Price (₹)</label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0"
                       placeholder="Enter price"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">
                    <span class="text-red-500">*</span> Quantity
                </label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1"
                       placeholder="Enter quantity"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" required />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Rack Number</label>
                <input type="text" name="rack_number" value="{{ old('rack_number') }}"
                       placeholder="e.g. A1, B2, D3"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Book Number</label>
                <input type="text" name="book_number" value="{{ old('book_number') }}"
                       placeholder="e.g. BK-001"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">ISBN Number</label>
                <input type="text" name="isbn_number" value="{{ old('isbn_number') }}"
                       placeholder="Enter 13-digit ISBN"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-heading mb-1.5">Description</label>
                <textarea name="description" rows="3"
                          placeholder="Enter book description (optional)"
                          class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none resize-none"
                          style="border-color: #DED5CB;">{{ old('description') }}</textarea>
            </div>

        </div>

        <div class="flex justify-center mt-6">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-8 py-2.5 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition"
                    style="background:#B47A42;">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add New Book
            </button>
        </div>
    </form>
</div>

<style>
/* Class chips */
.class-chip.active { border-color:#B47A42 !important; background:#fff8f2 !important; }
.class-chip.active .chip-box  { background:#B47A42; border-color:#B47A42 !important; }
.class-chip.active .chip-tick { display:flex !important; }
.class-chip.active .chip-name { color:#B47A42; }

/* Subject chips */
.subj-chip        { display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
                    border-radius:8px; border:2px solid #DED5CB; background:#fff;
                    font-size:12px; cursor:pointer; transition:all .15s; user-select:none; }
.subj-chip.active { border-color:#2e7d52 !important; background:#eaf7ee !important; }
.subj-chip.active .sbox  { background:#2e7d52; border-color:#2e7d52 !important; }
.subj-chip.active .stick { display:flex !important; }
.subj-chip.active .sname { color:#2e7d52; font-weight:700; }
.sbox  { width:14px; height:14px; border-radius:4px; border:2px solid #DED5CB;
          flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.stick { display:none; }

/* Spinner */
.subject-spinner { animation: spin360 .7s linear infinite; }
@keyframes spin360 { to { transform:rotate(360deg); } }
</style>

<script>
const SUBJECTS_URL = '{{ route("library.subjects") }}';
const oldSubjectIds = @json(old('subject_ids', []));

// ── Mode Toggle ──────────────────────────────────────────────────────────
const radioButtons = document.querySelectorAll('input[name="book_type"]');
const cwSection    = document.getElementById('classWiseSection');
const modeIndLabel = document.getElementById('modeIndLabel');
const modeCwLabel  = document.getElementById('modeCwLabel');
const indDotFill   = document.getElementById('indDotFill');
const cwDotFill    = document.getElementById('cwDotFill');
const indDot       = document.getElementById('indDot');
const cwDot        = document.getElementById('cwDot');
const subjectHint  = document.getElementById('subjectFieldHint');

function setMode(val) {
    const cw = (val === 'class_wise');
    cwSection.classList.toggle('hidden', !cw);
    subjectHint.classList.toggle('hidden', !cw);

    if (cw) {
        modeCwLabel.style.borderColor  = '#B47A42';
        modeCwLabel.style.background   = '#fff8f2';
        modeIndLabel.style.borderColor = '#DED5CB';
        modeIndLabel.style.background  = 'transparent';
        cwDot.style.borderColor = '#B47A42';
        cwDotFill.classList.remove('hidden');
        indDotFill.style.background = '';
        indDot.style.borderColor = '#DED5CB';
    } else {
        modeIndLabel.style.borderColor = '#B47A42';
        modeIndLabel.style.background  = '#fff8f2';
        modeCwLabel.style.borderColor  = '#DED5CB';
        modeCwLabel.style.background   = 'transparent';
        indDot.style.borderColor = '#B47A42';
        indDotFill.style.background = '#B47A42';
        cwDotFill.classList.add('hidden');
        cwDot.style.borderColor = '#DED5CB';
    }
}

radioButtons.forEach(r => {
    r.closest('label').addEventListener('click', function () {
        const val = this.querySelector('input[type="radio"]').value;
        this.querySelector('input[type="radio"]').checked = true;
        setMode(val);
    });
});

setMode(document.querySelector('input[name="book_type"]:checked')?.value || 'individual');

// ── Class Chips ──────────────────────────────────────────────────────────
const classCbs       = document.querySelectorAll('.class-cb');
const classSelSummary = document.getElementById('classSelSummary');
const classSelText    = document.getElementById('classSelText');

function getSelectedClassIds() {
    return [...classCbs].filter(c => c.checked).map(c => c.value);
}

function updateClassChips() {
    const selected = [...classCbs].filter(c => c.checked);
    if (selected.length) {
        classSelSummary.classList.remove('hidden');
        classSelText.textContent = selected
            .map(c => c.closest('label').querySelector('.chip-name').textContent.trim())
            .join(', ');
        fetchSubjects(selected.map(c => c.value));
    } else {
        classSelSummary.classList.add('hidden');
        hideSubjects();
    }
}

classCbs.forEach(cb => {
    const chip = cb.closest('label');
    if (cb.checked) chip.classList.add('active');  // restore old() state

    chip.addEventListener('click', function (e) {
        e.preventDefault();
        cb.checked = !cb.checked;
        chip.classList.toggle('active', cb.checked);
        updateClassChips();
    });
});

updateClassChips(); // run on page load (handles old() restoration)

// ── Subjects AJAX ────────────────────────────────────────────────────────
const subjectPanel     = document.getElementById('subjectPanel');
const subjectSpinner   = document.getElementById('subjectLoadingSpinner');
const subjectContainer = document.getElementById('subjectChipContainer');
const noSubjMsg        = document.getElementById('noSubjectsMsg');
let subjectDebounce    = null;

function hideSubjects() {
    subjectPanel.classList.add('hidden');
    subjectContainer.innerHTML = '';
}

function fetchSubjects(classIds) {
    clearTimeout(subjectDebounce);
    subjectDebounce = setTimeout(() => {
        if (!classIds.length) { hideSubjects(); return; }

        subjectPanel.classList.remove('hidden');
        subjectSpinner.classList.remove('hidden');
        subjectContainer.innerHTML = '';
        noSubjMsg.classList.add('hidden');

        const qs = new URLSearchParams();
        classIds.forEach(id => qs.append('class_ids[]', id));

        fetch(`${SUBJECTS_URL}?${qs}`)
            .then(r => r.json())
            .then(data => {
                subjectSpinner.classList.add('hidden');
                const subjects = data.subjects || [];

                if (!subjects.length) {
                    noSubjMsg.classList.remove('hidden');
                    return;
                }

                // Build class name map
                const nameMap = {};
                classCbs.forEach(cb => {
                    if (cb.checked) {
                        nameMap[cb.value] = cb.closest('label').querySelector('.chip-name').textContent.trim();
                    }
                });

                // Group by class_id
                const grouped = {};
                subjects.forEach(s => {
                    if (!grouped[s.class_id]) grouped[s.class_id] = [];
                    grouped[s.class_id].push(s);
                });

                let html = '';
                Object.entries(grouped).forEach(([cid, subjs]) => {
                    if (Object.keys(grouped).length > 1) {
                        html += `<div class="w-full mb-1 mt-2">
                            <span class="text-xs font-bold" style="color:#B47A42;">
                                ${nameMap[cid] || 'Class ' + cid}
                            </span>
                        </div>`;
                    }
                    subjs.forEach(s => {
                        const active = oldSubjectIds.includes(String(s.id)) ? 'active' : '';
                        const checked = active ? 'checked' : '';
                        html += `
                        <label class="subj-chip ${active}" data-id="${s.id}">
                            <input type="checkbox" name="subject_ids[]" value="${s.id}" class="hidden subj-cb" ${checked} />
                            <div class="sbox"><i data-lucide="check" class="w-2.5 h-2.5 text-white stick"></i></div>
                            <span class="sname font-medium text-heading">${s.name}</span>
                            ${s.code ? `<span class="text-body">(${s.code})</span>` : ''}
                        </label>`;
                    });
                });

                subjectContainer.innerHTML = html;
                lucide.createIcons();

                subjectContainer.querySelectorAll('.subj-chip').forEach(chip => {
                    chip.addEventListener('click', function (e) {
                        e.preventDefault();
                        const cb = this.querySelector('.subj-cb');
                        cb.checked = !cb.checked;
                        this.classList.toggle('active', cb.checked);
                    });
                });
            })
            .catch(() => {
                subjectSpinner.classList.add('hidden');
                noSubjMsg.classList.remove('hidden');
            });
    }, 300);
}
</script>

@endsection
