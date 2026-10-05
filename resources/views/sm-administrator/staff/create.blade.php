@extends('layouts.app')
@section('header', 'Administrator – Add New Staff')
@section('content')
<div style="max-width:900px; margin:0 auto;">

    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Add New Staff Member
        </div>
        <a href="{{ route('sm-administrator.staff.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">← View All</a>
    </div>

    {{-- Steps --}}
    <div class="flex items-center mb-6" id="stepsBar">
        @php $steps = ['Personal Details', 'Govt Documents', 'Salary & Employee ID', 'Login Details', 'Review & Save']; @endphp
        @foreach($steps as $si => $stepName)
        <div class="flex items-center {{ $si < count($steps)-1 ? 'flex-1' : '' }}">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                     id="step-circle-{{ $si+1 }}"
                     style="{{ $si === 0 ? 'background:linear-gradient(135deg,#B47A42,#D4A055);color:#fff;' : 'background:#E8E0D8;color:#9e8f84;' }}">
                    {{ $si + 1 }}
                </div>
                <div class="text-xs mt-1 font-medium whitespace-nowrap hidden md:block" id="step-label-{{ $si+1 }}"
                     style="{{ $si === 0 ? 'color:#B47A42;' : 'color:#9e8f84;' }}">{{ $stepName }}</div>
            </div>
            @if($si < count($steps)-1)
            <div class="flex-1 h-0.5 mx-2" id="step-line-{{ $si+1 }}" style="{{ $si === 0 ? 'background:#B47A42;' : 'background:#E8E0D8;' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('sm-administrator.staff.store') }}" enctype="multipart/form-data" id="staffForm">
        @csrf

        {{-- STEP 1: Personal Details --}}
        <div class="step-panel" id="panel-1">
            <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
                <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Personal Detail</div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="field-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter name" class="field-input" required>
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
                        <label class="field-label">Designation</label>
                        <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Teacher, Librarian" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Joining Date</label>
                        <input type="date" name="joining_date" value="{{ old('joining_date') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Role</label>
                        <select name="staff_role_id" class="field-input" id="roleSelect" onchange="fetchRolePermissions(this.value)">
                            <option value="">No Role Assigned</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('staff_role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
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

            {{-- Role Permissions Display --}}
            <div id="rolePermissionsPanel" class="rounded-xl overflow-hidden hidden" style="background:#fff; border:1px solid #E8E0D8;">
                <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#11998e,#38ef7d);">
                    Permissions for Selected Role
                </div>
                <div id="rolePermissionsList" class="p-6 flex flex-wrap gap-2"></div>
            </div>
        </div>

        {{-- STEP 2: Government Documents --}}
        <div class="step-panel hidden" id="panel-2">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8;">
                <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Government Issue Documents</div>
                <div class="p-6">
                    <p class="text-sm mb-4" style="color:#766C63;">⚠️ Name in government documents must match the name entered in personal details.</p>
                    <div id="docsList" class="space-y-4"></div>
                    <button type="button" onclick="addDocument()"
                            class="mt-4 flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium"
                            style="background:#f5ece0; color:#B47A42; border:1px dashed #B47A42;">
                        + Add Document
                    </button>
                </div>
            </div>
        </div>

        {{-- STEP 3: Salary & Employee ID --}}
        <div class="step-panel hidden" id="panel-3">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8;">
                <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Salary & Employee ID</div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="field-label">Employee ID</label>
                        <div class="flex gap-2">
                            <input type="text" name="employee_id" id="employeeIdInput" value="{{ old('employee_id') }}" placeholder="Auto-generated or manual" class="field-input flex-1">
                            <button type="button" onclick="generateEmpId()" class="px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap" style="background:linear-gradient(135deg,#B47A42,#D4A055); color:#fff;">Auto Generate</button>
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
                        <input type="number" name="salary" value="{{ old('salary') }}" min="0" step="0.01" placeholder="Enter basic salary" class="field-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 4: Login Details --}}
        <div class="step-panel hidden" id="panel-4">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8;">
                <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Login Details</div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2 p-3 rounded-lg text-sm" style="background:#f5ece0; color:#766C63;">
                        Either Email or Phone is required. Username is mandatory and must be unique.
                    </div>
                    <div>
                        <label class="field-label">Login Email</label>
                        <input type="email" name="login_email" value="{{ old('login_email') }}" placeholder="Enter login email" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Login Phone</label>
                        <input type="text" name="login_phone" value="{{ old('login_phone') }}" placeholder="Enter login phone" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Username <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="username" value="{{ old('username') }}" placeholder="Unique username" class="field-input" id="usernameInput" oninput="checkUsername(this.value)" required>
                            <span id="usernameStatus" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium"></span>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" placeholder="Min 8 characters" class="field-input pr-10" id="passInput" oninput="checkPasswordStrength(this.value)" required>
                            <button type="button" onclick="togglePass('passInput')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">👁</button>
                        </div>
                        <div id="passStrength" class="mt-1 text-xs font-medium"></div>
                        <div id="passBar" class="h-1.5 rounded-full mt-1 transition-all" style="width:0%; background:#B4232A;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 5: Review --}}
        <div class="step-panel hidden" id="panel-5">
            <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8;">
                <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Review All Details</div>
                <div class="p-6">
                    <div id="reviewContent" class="grid grid-cols-2 gap-2 text-sm"></div>
                    <div class="mt-4 p-3 rounded-lg text-sm" style="background:#f5ece0; color:#766C63;">
                        Review all details before saving.
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center justify-between mt-6">
            <button type="button" id="prevBtn" onclick="prevStep()" class="hidden px-6 py-2.5 rounded-lg font-medium text-sm"
                    style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">← Previous</button>
            <div class="ml-auto flex gap-3">
                <button type="button" id="nextBtn" onclick="nextStep()" class="px-6 py-2.5 rounded-lg font-semibold text-white text-sm"
                        style="background:linear-gradient(135deg,#B47A42,#D4A055);">Next →</button>
                <button type="submit" id="saveBtn" class="hidden px-6 py-2.5 rounded-lg font-semibold text-white text-sm"
                        style="background:linear-gradient(135deg,#4F7D5A,#6aae78);">✓ Add Staff Member</button>
            </div>
        </div>
    </form>
</div>

<style>
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { width:100%; padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; transition:border-color 0.2s; outline:none; font-family:inherit; }
.field-input:focus { border-color:#B47A42; box-shadow:0 0 0 3px rgba(180,122,66,.1); }
textarea.field-input { resize:vertical; }
</style>

<script>
let currentStep = 1;
const totalSteps = 5;
let usernameTimer = null;
let docIndex = 0;

function showStep(step) {
    document.querySelectorAll('.step-panel').forEach((p, i) => p.classList.toggle('hidden', i + 1 !== step));
    for (let i = 1; i <= totalSteps; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const label  = document.getElementById('step-label-' + i);
        const line   = document.getElementById('step-line-' + i);
        if (i < step) {
            circle.style.cssText = 'background:linear-gradient(135deg,#4F7D5A,#6aae78);color:#fff;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:700;';
            circle.innerHTML = '✓';
            if (label) label.style.color = '#4F7D5A';
            if (line) line.style.background = '#B47A42';
        } else if (i === step) {
            circle.style.cssText = 'background:linear-gradient(135deg,#B47A42,#D4A055);color:#fff;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:700;';
            circle.textContent = i;
            if (label) label.style.color = '#B47A42';
        } else {
            circle.style.cssText = 'background:#E8E0D8;color:#9e8f84;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:700;';
            circle.textContent = i;
            if (label) label.style.color = '#9e8f84';
        }
    }
    document.getElementById('prevBtn').classList.toggle('hidden', step === 1);
    document.getElementById('nextBtn').classList.toggle('hidden', step === totalSteps);
    document.getElementById('saveBtn').classList.toggle('hidden', step !== totalSteps);
    if (step === totalSteps) populateReview();
}
function nextStep() { if (currentStep < totalSteps) { currentStep++; showStep(currentStep); } }
function prevStep() { if (currentStep > 1) { currentStep--; showStep(currentStep); } }

// Fetch role permissions to display
function fetchRolePermissions(roleId) {
    const panel = document.getElementById('rolePermissionsPanel');
    const list  = document.getElementById('rolePermissionsList');
    if (!roleId) { panel.classList.add('hidden'); return; }
    fetch(`{{ url('sm-administrator/staff/role-permissions') }}/${roleId}`)
        .then(r => r.json())
        .then(perms => {
            panel.classList.remove('hidden');
            list.innerHTML = perms.map(p =>
                `<span class="px-2 py-1 rounded-full text-xs font-medium" style="background:#eef7f0;color:#2d5434;">${p.replace(/[\.\-]/g,' ')}</span>`
            ).join('');
        });
}

document.getElementById('photoInput')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('photoImg').src = e.target.result; document.getElementById('photoPreview').classList.remove('hidden'); };
    reader.readAsDataURL(file);
});

let empCount = {{ \App\Models\Staff::where('school_id', session('selected_school_id') ?? 0)->count() + 1 }};
function generateEmpId() {
    document.getElementById('employeeIdInput').value = 'EMP-' + String(empCount).padStart(4, '0');
}

function addDocument() {
    const idx = docIndex++;
    document.getElementById('docsList').insertAdjacentHTML('beforeend', `
    <div class="border rounded-xl p-4 relative" style="border-color:#E8E0D8;" id="doc-${idx}">
        <button type="button" onclick="document.getElementById('doc-${idx}').remove()" class="absolute top-3 right-3 p-1 rounded text-xs" style="background:#fef0f0;color:#B4232A;border:none;">✕</button>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="field-label">Document Type</label>
            <select name="govt_documents[${idx}][type]" class="field-input">
                <option value="">Select</option>
                <option value="Aadhaar">Aadhaar Card</option>
                <option value="PAN">PAN Card</option>
                <option value="DrivingLicense">Driving License</option>
                <option value="Passport">Passport</option>
                <option value="VoterID">Voter ID</option>
                <option value="Other">Other</option>
            </select></div>
            <div><label class="field-label">Upload Method</label>
            <select name="govt_documents[${idx}][method]" class="field-input" onchange="switchMethod(${idx}, this.value)">
                <option value="manual">Enter Manually</option>
                <option value="image">Upload Image/PDF</option>
                <option value="digilocker">DigiLocker</option>
            </select></div>
            <div id="doc-input-${idx}"><label class="field-label">Document Number</label>
            <input type="text" name="govt_documents[${idx}][value]" class="field-input" placeholder="Enter number"></div>
        </div>
    </div>`);
}

function switchMethod(idx, method) {
    const c = document.getElementById('doc-input-' + idx);
    if (!c) return;
    if (method === 'manual') c.innerHTML = `<label class="field-label">Document Number</label><input type="text" name="govt_documents[${idx}][value]" class="field-input" placeholder="Enter number">`;
    else if (method === 'image') c.innerHTML = `<label class="field-label">Upload File</label><input type="file" name="govt_documents[${idx}][file]" class="field-input" accept=".pdf,image/*">`;
    else if (method === 'digilocker') c.innerHTML = `<label class="field-label">DigiLocker ID</label><input type="text" name="govt_documents[${idx}][value]" class="field-input" placeholder="Enter DigiLocker ID">`;
}

function checkUsername(val) {
    if (usernameTimer) clearTimeout(usernameTimer);
    const status = document.getElementById('usernameStatus');
    if (!val || val.length < 3) { status.textContent = ''; return; }
    status.style.color = '#9e8f84'; status.textContent = 'Checking…';
    usernameTimer = setTimeout(() => {
        fetch(`{{ route('sm-administrator.staff.check-username') }}?username=${encodeURIComponent(val)}&type=staff`)
            .then(r => r.json())
            .then(data => { status.textContent = data.available ? '✓ Available' : '✗ Taken'; status.style.color = data.available ? '#2d5434' : '#B4232A'; });
    }, 500);
}

function checkPasswordStrength(val) {
    const bar = document.getElementById('passBar'); const txt = document.getElementById('passStrength');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [['#B4232A','Very Weak','20%'],['#e07b39','Weak','40%'],['#f0b429','Fair','60%'],['#4F7D5A','Strong','80%'],['#2d5434','Very Strong','100%']];
    const l = levels[Math.min(score,4)];
    bar.style.width = l[2]; bar.style.background = l[0];
    txt.textContent = val.length ? 'Strength: ' + l[1] : ''; txt.style.color = l[0];
}

function togglePass(id) { const el = document.getElementById(id); el.type = el.type === 'password' ? 'text' : 'password'; }

function getVal(name) { const el = document.querySelector(`[name="${name}"]`); if (!el) return '—'; if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—'; return el.value || '—'; }
function populateReview() {
    const fields = [['Name','name'],['Gender','gender'],['DOB','date_of_birth'],['Phone','phone'],['Email','email'],['Designation','designation'],['Role','staff_role_id'],['Joining Date','joining_date'],['Employee ID','employee_id'],['Salary Type','salary_type'],['Salary (₹)','salary'],['Login Email','login_email'],['Login Phone','login_phone'],['Username','username']];
    document.getElementById('reviewContent').innerHTML = fields.map(([l,n]) => `<div class="flex justify-between py-2" style="border-bottom:1px solid #F0EAE3;"><span class="font-semibold text-sm" style="color:#211B17;">${l}</span><span class="text-sm" style="color:#766C63;">${getVal(n)}</span></div>`).join('');
}
showStep(1);
</script>
@endsection
