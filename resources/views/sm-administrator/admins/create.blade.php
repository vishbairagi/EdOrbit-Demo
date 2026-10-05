@extends('layouts.app')
@section('header', 'Administrator – Add New Admin')

@section('content')
<div style="max-width:900px; margin:0 auto;">

    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white flex items-center gap-2"
             style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Add New Admin / Principal
        </div>
        <a href="{{ route('sm-administrator.admins.index') }}"
           class="text-sm font-medium px-4 py-2 rounded-lg"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">
            ← View All
        </a>
    </div>

    {{-- Steps Indicator --}}
    <div class="flex items-center mb-6" id="stepsBar">
        @php $steps = ['Personal Details', 'Govt Documents', 'Salary & Employee ID', 'Login Details', 'Review & Save']; @endphp
        @foreach($steps as $si => $stepName)
            <div class="flex items-center {{ $si < count($steps)-1 ? 'flex-1' : '' }}">
                <div class="step-indicator flex flex-col items-center" data-step="{{ $si + 1 }}">
                    <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                         id="step-circle-{{ $si+1 }}"
                         style="{{ $si === 0 ? 'background:linear-gradient(135deg,#B47A42,#D4A055);color:#fff;' : 'background:#E8E0D8;color:#9e8f84;' }}">
                        {{ $si + 1 }}
                    </div>
                    <div class="text-xs mt-1 font-medium whitespace-nowrap hidden md:block" id="step-label-{{ $si+1 }}"
                         style="{{ $si === 0 ? 'color:#B47A42;' : 'color:#9e8f84;' }}">{{ $stepName }}</div>
                </div>
                @if($si < count($steps)-1)
                <div class="flex-1 h-0.5 mx-2" id="step-line-{{ $si+1 }}"
                     style="{{ $si === 0 ? 'background:#B47A42;' : 'background:#E8E0D8;' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('sm-administrator.admins.store') }}" enctype="multipart/form-data" id="adminForm">
        @csrf

        {{-- STEP 1: Personal Details --}}
        <div class="step-panel" id="panel-1">
            <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
                <div class="px-6 py-3 font-semibold text-white text-sm"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055);">Personal Detail</div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="field-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter name"
                               class="field-input" required>
                        <p class="text-xs mt-1" style="color:#9e8f84;">Enter name as per government document</p>
                    </div>
                    <div>
                        <label class="field-label">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" class="field-input" required>
                            <option value="Male" {{ old('gender')=='Male'?'selected':'' }}>Male</option>
                            <option value="Female" {{ old('gender')=='Female'?'selected':'' }}>Female</option>
                            <option value="Other" {{ old('gender')=='Other'?'selected':'' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="field-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="field-label">Address</label>
                        <textarea name="address" rows="2" placeholder="Enter address" class="field-input">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Enter phone number" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" class="field-input" required>
                            <option value="Admin" {{ old('role')=='Admin'?'selected':'' }}>Admin</option>
                            <option value="Principal" {{ old('role')=='Principal'?'selected':'' }}>Principal</option>
                            <option value="Headmaster" {{ old('role')=='Headmaster'?'selected':'' }}>Headmaster</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Joining Date</label>
                        <input type="date" name="joining_date" value="{{ old('joining_date') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Upload Photo</label>
                        <input type="file" name="photo" accept="image/*" class="field-input" id="photoInput">
                        <div id="photoPreview" class="mt-2 hidden">
                            <img id="photoImg" src="" class="w-16 h-16 rounded-full object-cover border-2" style="border-color:#B47A42;">
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="field-label">Note / Description</label>
                        <textarea name="note" rows="2" placeholder="Enter note or description" class="field-input">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Permissions Section --}}
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8;">
                <div class="px-6 py-3 font-semibold text-white text-sm flex items-center justify-between"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                    Permissions
                    <div class="flex gap-2">
                        <button type="button" onclick="selectAllPermissions()" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,0.2);">Expand All</button>
                        <button type="button" onclick="deselectAllPermissions()" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,0.2);">Collapse All</button>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($permissions as $group => $perms)
                    <div class="border rounded-lg overflow-hidden" style="border-color:#E8E0D8;">
                        <div class="flex items-center justify-between px-3 py-2" style="background:#f5ece0;">
                            <span class="font-semibold text-sm" style="color:#211B17;">{{ $group }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" class="group-check" data-group="{{ $loop->index }}"
                                       onchange="toggleGroup(this)">
                                <span class="text-xs" style="color:#766C63;">Select All</span>
                            </label>
                        </div>
                        <div class="p-3 space-y-2" id="group-{{ $loop->index }}">
                            @foreach($perms as $perm)
                            <label class="flex items-center gap-2 cursor-pointer group-perm" data-group="{{ $loop->parent->index }}">
                                <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                       class="rounded" style="accent-color:#B47A42;"
                                       {{ in_array($perm, old('permissions', [])) ? 'checked' : '' }}>
                                <span class="text-sm" style="color:#766C63;">{{ ucwords(str_replace(['-', '.'], ' ', $perm)) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- STEP 2: Government Documents --}}
        <div class="step-panel hidden" id="panel-2">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
                <div class="px-6 py-3 font-semibold text-white text-sm"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055);">Government Issue Documents</div>
                <div class="p-6">
                    <p class="text-sm mb-4" style="color:#766C63;">
                        ⚠️ Name in government documents must match the name entered in personal details.
                    </p>
                    <div id="docsList" class="space-y-4"></div>
                    <button type="button" onclick="addDocument()"
                            class="mt-4 flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium"
                            style="background:#f5ece0; color:#B47A42; border:1px dashed #B47A42;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Document
                    </button>
                </div>
            </div>
        </div>

        {{-- STEP 3: Salary & Employee ID --}}
        <div class="step-panel hidden" id="panel-3">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
                <div class="px-6 py-3 font-semibold text-white text-sm"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055);">Salary & Employee ID</div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="field-label">Employee ID</label>
                        <div class="flex gap-2">
                            <input type="text" name="employee_id" id="employeeIdInput" value="{{ old('employee_id') }}"
                                   placeholder="Auto-generated or enter manually" class="field-input flex-1">
                            <button type="button" onclick="generateEmpId()"
                                    class="px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap"
                                    style="background:linear-gradient(135deg,#B47A42,#D4A055); color:#fff;">
                                Auto Generate
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Salary Type</label>
                        <select name="salary_type" class="field-input">
                            <option value="">Select type</option>
                            <option value="Monthly" {{ old('salary_type')=='Monthly'?'selected':'' }}>Monthly</option>
                            <option value="Weekly"  {{ old('salary_type')=='Weekly'?'selected':'' }}>Weekly</option>
                            <option value="Daily"   {{ old('salary_type')=='Daily'?'selected':'' }}>Daily</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Basic Salary (₹)</label>
                        <input type="number" name="salary" value="{{ old('salary') }}" min="0" step="0.01"
                               placeholder="Enter basic salary" class="field-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 4: Login Details --}}
        <div class="step-panel hidden" id="panel-4">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
                <div class="px-6 py-3 font-semibold text-white text-sm"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055);">Login Details</div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <p class="text-sm mb-4 p-3 rounded-lg" style="background:#f5ece0; color:#766C63;">
                            Either Email or Phone is required for login. Username is mandatory.
                        </p>
                    </div>
                    <div>
                        <label class="field-label">Login Email</label>
                        <input type="email" name="login_email" value="{{ old('login_email') }}"
                               placeholder="Enter login email" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Login Phone</label>
                        <input type="text" name="login_phone" value="{{ old('login_phone') }}"
                               placeholder="Enter login phone" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Username <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="username" value="{{ old('username') }}"
                                   placeholder="Enter unique username" class="field-input" id="usernameInput"
                                   oninput="checkUsername(this.value)" required>
                            <span id="usernameStatus" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium"></span>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" placeholder="Min 8 characters" class="field-input pr-10"
                                   id="passInput" oninput="checkPasswordStrength(this.value)" required>
                            <button type="button" onclick="togglePass('passInput')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div id="passStrength" class="mt-2 text-xs font-medium"></div>
                        <div id="passBar" class="h-1.5 rounded-full mt-1 transition-all" style="width:0%; background:#B4232A;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 5: Review --}}
        <div class="step-panel hidden" id="panel-5">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
                <div class="px-6 py-3 font-semibold text-white text-sm"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055);">Review All Details</div>
                <div class="p-6">
                    <div id="reviewContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        {{-- Filled by JS --}}
                    </div>
                    <div class="mt-4 p-3 rounded-lg" style="background:#f5ece0; color:#766C63; font-size:0.8rem;">
                        Please review all information before saving. Once saved, you can still edit from the Admins list.
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="flex items-center justify-between mt-6">
            <button type="button" id="prevBtn" onclick="prevStep()" class="hidden px-6 py-2.5 rounded-lg font-medium text-sm"
                    style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">
                ← Previous
            </button>
            <div class="ml-auto flex gap-3">
                <button type="button" id="nextBtn" onclick="nextStep()"
                        class="px-6 py-2.5 rounded-lg font-semibold text-white text-sm"
                        style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                    Next →
                </button>
                <button type="submit" id="saveBtn" class="hidden px-6 py-2.5 rounded-lg font-semibold text-white text-sm flex items-center gap-2"
                        style="background:linear-gradient(135deg,#4F7D5A,#6aae78);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Add Admin
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
    .field-input {
        width:100%; padding:0.6rem 0.875rem;
        border:1px solid #DED5CB; border-radius:8px;
        font-size:0.9rem; color:#211B17; background:#FFFCF7;
        transition: border-color 0.2s; outline:none; font-family:inherit;
    }
    .field-input:focus { border-color:#B47A42; box-shadow:0 0 0 3px rgba(180,122,66,0.1); }
    textarea.field-input { resize:vertical; }
</style>

<script>
let currentStep = 1;
const totalSteps = 5;
let usernameTimer = null;
let docIndex = 0;

function showStep(step) {
    document.querySelectorAll('.step-panel').forEach((p, i) => {
        p.classList.toggle('hidden', i + 1 !== step);
    });

    for (let i = 1; i <= totalSteps; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const label  = document.getElementById('step-label-' + i);
        const line   = document.getElementById('step-line-' + i);

        if (i < step) {
            circle.style.cssText = 'background:linear-gradient(135deg,#4F7D5A,#6aae78);color:#fff;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.875rem;font-weight:700;';
            circle.innerHTML = '✓';
            if (label) label.style.color = '#4F7D5A';
            if (line) line.style.background = '#B47A42';
        } else if (i === step) {
            circle.style.cssText = 'background:linear-gradient(135deg,#B47A42,#D4A055);color:#fff;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.875rem;font-weight:700;';
            circle.textContent = i;
            if (label) label.style.color = '#B47A42';
        } else {
            circle.style.cssText = 'background:#E8E0D8;color:#9e8f84;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.875rem;font-weight:700;';
            circle.textContent = i;
            if (label) label.style.color = '#9e8f84';
        }
    }

    document.getElementById('prevBtn').classList.toggle('hidden', step === 1);
    document.getElementById('nextBtn').classList.toggle('hidden', step === totalSteps);
    document.getElementById('saveBtn').classList.toggle('hidden', step !== totalSteps);

    if (step === totalSteps) populateReview();
}

function nextStep() {
    if (currentStep < totalSteps) { currentStep++; showStep(currentStep); }
}
function prevStep() {
    if (currentStep > 1) { currentStep--; showStep(currentStep); }
}

// Photo preview
document.getElementById('photoInput')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photoImg').src = e.target.result;
        document.getElementById('photoPreview').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

// Add Government Document
function addDocument() {
    const idx = docIndex++;
    const html = `
    <div class="border rounded-xl p-4 relative" style="border-color:#E8E0D8;" id="doc-${idx}">
        <button type="button" onclick="removeDoc(${idx})" class="absolute top-3 right-3 p-1 rounded" style="background:#fef0f0;color:#B4232A;border:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="field-label">Document Type</label>
                <select name="govt_documents[${idx}][type]" class="field-input" onchange="updateDocMethod(${idx}, this.value)">
                    <option value="">Select Document</option>
                    <option value="Aadhaar">Aadhaar Card</option>
                    <option value="PAN">PAN Card</option>
                    <option value="DrivingLicense">Driving License</option>
                    <option value="Passport">Passport</option>
                    <option value="VoterID">Voter ID</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="field-label">Upload Method</label>
                <select name="govt_documents[${idx}][method]" class="field-input" id="method-${idx}" onchange="switchMethod(${idx})">
                    <option value="manual">Enter Manually</option>
                    <option value="image">Upload Image/PDF</option>
                    <option value="digilocker">DigiLocker</option>
                </select>
            </div>
            <div id="doc-input-${idx}">
                <label class="field-label">Document Number</label>
                <input type="text" name="govt_documents[${idx}][value]" class="field-input" placeholder="Enter number">
            </div>
        </div>
    </div>`;
    document.getElementById('docsList').insertAdjacentHTML('beforeend', html);
}

function removeDoc(idx) {
    document.getElementById('doc-' + idx)?.remove();
}

function switchMethod(idx) {
    const method = document.getElementById('method-' + idx)?.value;
    const container = document.getElementById('doc-input-' + idx);
    if (!container) return;

    if (method === 'manual') {
        container.innerHTML = `<label class="field-label">Document Number</label><input type="text" name="govt_documents[${idx}][value]" class="field-input" placeholder="Enter number">`;
    } else if (method === 'image') {
        container.innerHTML = `<label class="field-label">Upload File (PDF/Image)</label><input type="file" name="govt_documents[${idx}][file]" class="field-input" accept=".pdf,image/*">`;
    } else if (method === 'digilocker') {
        container.innerHTML = `<label class="field-label">DigiLocker ID</label><input type="text" name="govt_documents[${idx}][value]" class="field-input" placeholder="Enter DigiLocker document ID">`;
    }
}

// Select / Deselect all permissions
function selectAllPermissions() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(c => c.checked = true);
    document.querySelectorAll('.group-check').forEach(c => c.checked = true);
}
function deselectAllPermissions() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(c => c.checked = false);
    document.querySelectorAll('.group-check').forEach(c => c.checked = false);
}
function toggleGroup(checkbox) {
    const grp = checkbox.dataset.group;
    document.querySelectorAll(`[data-group="${grp}"] input[name="permissions[]"]`).forEach(c => c.checked = checkbox.checked);
}

// Auto-generate Employee ID
let empCount = {{ \App\Models\SchoolUser::where('school_id', session('selected_school_id') ?? 0)->count() + 1 }};
function generateEmpId() {
    document.getElementById('employeeIdInput').value = 'ADM-' + String(empCount).padStart(4, '0');
}

// Username availability check
function checkUsername(val) {
    if (usernameTimer) clearTimeout(usernameTimer);
    const status = document.getElementById('usernameStatus');
    if (!val || val.length < 3) { status.textContent = ''; return; }
    status.style.color = '#9e8f84'; status.textContent = 'Checking…';
    usernameTimer = setTimeout(() => {
        fetch(`{{ route('sm-administrator.staff.check-username') }}?username=${encodeURIComponent(val)}&type=school_user`)
            .then(r => r.json())
            .then(data => {
                status.textContent = data.available ? '✓ Available' : '✗ Taken';
                status.style.color = data.available ? '#2d5434' : '#B4232A';
            });
    }, 500);
}

// Password strength
function checkPasswordStrength(val) {
    const bar = document.getElementById('passBar');
    const txt = document.getElementById('passStrength');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { color: '#B4232A', label: 'Very Weak', pct: '20%' },
        { color: '#e07b39', label: 'Weak',      pct: '40%' },
        { color: '#f0b429', label: 'Fair',      pct: '60%' },
        { color: '#4F7D5A', label: 'Strong',    pct: '80%' },
        { color: '#2d5434', label: 'Very Strong', pct: '100%' },
    ];
    const l = levels[Math.min(score, 4)];
    bar.style.width = l.pct; bar.style.background = l.color;
    txt.textContent = val.length ? 'Password strength: ' + l.label : '';
    txt.style.color = l.color;
}

function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Review
function getVal(name) {
    const el = document.querySelector(`[name="${name}"]`);
    if (!el) return '—';
    return el.value || '—';
}
function populateReview() {
    const fields = [
        ['Name', 'name'], ['Gender', 'gender'], ['DOB', 'date_of_birth'],
        ['Phone', 'phone'], ['Email', 'email'], ['Role', 'role'],
        ['Joining Date', 'joining_date'], ['Employee ID', 'employee_id'],
        ['Salary Type', 'salary_type'], ['Salary (₹)', 'salary'],
        ['Login Email', 'login_email'], ['Login Phone', 'login_phone'],
        ['Username', 'username'],
    ];
    const html = fields.map(([label, name]) => `
        <div class="flex justify-between py-2" style="border-bottom:1px solid #F0EAE3;">
            <span class="font-semibold text-sm" style="color:#211B17;">${label}</span>
            <span class="text-sm" style="color:#766C63;">${getVal(name)}</span>
        </div>
    `).join('');
    document.getElementById('reviewContent').innerHTML = html;
}

showStep(1);
</script>
@endsection
