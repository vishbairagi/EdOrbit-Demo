@extends('layouts.app')
@section('header', 'Administrator – Edit Admin')
@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Edit: {{ $admin->name }}
        </div>
        <a href="{{ route('sm-administrator.admins.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">← View All</a>
    </div>

    <form method="POST" action="{{ route('sm-administrator.admins.update', $admin) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Personal Detail</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="field-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="field-input" required>
                </div>
                <div>
                    <label class="field-label">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" class="field-input" required>
                        @foreach(['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" {{ old('gender', $admin->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $admin->date_of_birth?->format('Y-m-d')) }}" class="field-input">
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">Address</label>
                    <textarea name="address" rows="2" class="field-input">{{ old('address', $admin->address) }}</textarea>
                </div>
                <div>
                    <label class="field-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" class="field-input" required>
                        @foreach(['Admin','Principal','Headmaster'] as $r)
                        <option value="{{ $r }}" {{ old('role', $admin->role) === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="Active" {{ old('status', $admin->status) === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $admin->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $admin->joining_date?->format('Y-m-d')) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Update Photo</label>
                    @if($admin->photo)
                    <div class="mb-2"><img src="{{ asset('storage/'.$admin->photo) }}" class="w-12 h-12 rounded-full object-cover border-2" style="border-color:#B47A42;"></div>
                    @endif
                    <input type="file" name="photo" accept="image/*" class="field-input">
                </div>
                <div>
                    <label class="field-label">Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $admin->employee_id) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Salary Type</label>
                    <select name="salary_type" class="field-input">
                        <option value="">Select</option>
                        @foreach(['Monthly','Weekly','Daily'] as $st)
                        <option value="{{ $st }}" {{ old('salary_type', $admin->salary_type) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Salary (₹)</label>
                    <input type="number" name="salary" value="{{ old('salary', $admin->salary) }}" min="0" step="0.01" class="field-input">
                </div>
                <div class="md:col-span-3">
                    <label class="field-label">Note</label>
                    <textarea name="note" rows="2" class="field-input">{{ old('note', $admin->note) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Login Details --}}
        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Login Details</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="field-label">Login Email</label>
                    <input type="email" name="login_email" value="{{ old('login_email', $admin->login_email) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Login Phone</label>
                    <input type="text" name="login_phone" value="{{ old('login_phone', $admin->login_phone) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="{{ old('username', $admin->username) }}" class="field-input" required>
                </div>
                <div>
                    <label class="field-label">New Password <span class="text-xs font-normal" style="color:#9e8f84;">(leave empty to keep existing)</span></label>
                    <input type="password" name="password" class="field-input" placeholder="Min 8 characters">
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="px-6 py-3 font-semibold text-white text-sm flex items-center justify-between"
                 style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                Permissions
                <div class="flex gap-2">
                    <button type="button" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(c=>c.checked=true)" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,.2);">Select All</button>
                    <button type="button" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(c=>c.checked=false)" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,.2);">Clear All</button>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($permissions as $group => $perms)
                <div class="border rounded-lg overflow-hidden" style="border-color:#E8E0D8;">
                    <div class="px-3 py-2" style="background:#f5ece0;">
                        <span class="font-semibold text-sm" style="color:#211B17;">{{ $group }}</span>
                    </div>
                    <div class="p-3 space-y-2">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                   style="accent-color:#B47A42;"
                                   {{ in_array($perm, old('permissions', $adminPerms)) ? 'checked' : '' }}>
                            <span class="text-sm" style="color:#766C63;">{{ ucwords(str_replace(['-', '.'], ' ', $perm)) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sm-administrator.admins.index') }}" class="px-6 py-2.5 rounded-lg font-medium text-sm"
               style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">Cancel</a>
            <button type="submit" class="px-8 py-2.5 rounded-lg font-semibold text-white"
                    style="background:linear-gradient(135deg,#B47A42,#D4A055);">Update Admin</button>
        </div>
    </form>
</div>

<style>
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { width:100%; padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; transition:border-color 0.2s; outline:none; font-family:inherit; }
.field-input:focus { border-color:#B47A42; box-shadow:0 0 0 3px rgba(180,122,66,.1); }
textarea.field-input { resize:vertical; }
</style>
@endsection
