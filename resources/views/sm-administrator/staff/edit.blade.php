@extends('layouts.app')
@section('header', 'Administrator – Edit Staff')
@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Edit Staff: {{ $staff->name }}
        </div>
        <a href="{{ route('sm-administrator.staff.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">← View All</a>
    </div>

    <form method="POST" action="{{ route('sm-administrator.staff.update', $staff) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Personal Detail</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="field-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" class="field-input" required>
                    <p class="text-xs mt-1" style="color:#9e8f84;">Name as per government document</p>
                </div>
                <div>
                    <label class="field-label">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" class="field-input" required>
                        @foreach(['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" {{ old('gender', $staff->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $staff->date_of_birth?->format('Y-m-d')) }}" class="field-input">
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">Address</label>
                    <textarea name="address" rows="2" class="field-input">{{ old('address', $staff->address) }}</textarea>
                </div>
                <div>
                    <label class="field-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation', $staff->designation) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Role</label>
                    <select name="staff_role_id" class="field-input">
                        <option value="">No Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('staff_role_id', $staff->staff_role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $staff->joining_date?->format('Y-m-d')) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="Active" {{ old('status', $staff->status) === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $staff->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Update Photo</label>
                    @if($staff->photo)
                    <div class="mb-2"><img src="{{ asset('storage/'.$staff->photo) }}" class="w-12 h-12 rounded-full object-cover border-2" style="border-color:#B47A42;"></div>
                    @endif
                    <input type="file" name="photo" accept="image/*" class="field-input">
                </div>
                <div>
                    <label class="field-label">Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $staff->employee_id) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Salary Type</label>
                    <select name="salary_type" class="field-input">
                        <option value="">Select</option>
                        @foreach(['Monthly','Weekly','Daily'] as $st)
                        <option value="{{ $st }}" {{ old('salary_type', $staff->salary_type) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Salary (₹)</label>
                    <input type="number" name="salary" value="{{ old('salary', $staff->salary) }}" min="0" step="0.01" class="field-input">
                </div>
                <div class="md:col-span-3">
                    <label class="field-label">Note</label>
                    <textarea name="note" rows="2" class="field-input">{{ old('note', $staff->note) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Login Details --}}
        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Login Details</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="field-label">Login Email</label>
                    <input type="email" name="login_email" value="{{ old('login_email', $staff->login_email) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Login Phone</label>
                    <input type="text" name="login_phone" value="{{ old('login_phone', $staff->login_phone) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="{{ old('username', $staff->username) }}" class="field-input" required>
                </div>
                <div>
                    <label class="field-label">New Password <span class="text-xs font-normal" style="color:#9e8f84;">(empty = keep existing)</span></label>
                    <input type="password" name="password" class="field-input" placeholder="Min 8 characters">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sm-administrator.staff.index') }}" class="px-6 py-2.5 rounded-lg font-medium text-sm"
               style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">Cancel</a>
            <button type="submit" class="px-8 py-2.5 rounded-lg font-semibold text-white"
                    style="background:linear-gradient(135deg,#B47A42,#D4A055);">Update Staff</button>
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
