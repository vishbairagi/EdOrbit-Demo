@extends('layouts.app')
@section('header', 'Administrator – Edit Role')
@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Edit Role: {{ $role->name }}
        </div>
        <a href="{{ route('sm-administrator.roles.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">← View Roles</a>
    </div>

    <form method="POST" action="{{ route('sm-administrator.roles.update', $role) }}">
        @csrf @method('PUT')

        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Role Information</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="field-label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" class="field-input" required>
                </div>
                <div>
                    <label class="field-label">Description</label>
                    <input type="text" name="description" value="{{ old('description', $role->description) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="Active" {{ old('status', $role->status) === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $role->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm flex items-center justify-between"
                 style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                Permissions
                <div class="flex gap-2">
                    <button type="button" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(c=>c.checked=true)" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,.2);">Select All</button>
                    <button type="button" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(c=>c.checked=false)" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,.2);">Clear All</button>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($permissions as $group => $perms)
                <div class="border rounded-lg overflow-hidden" style="border-color:#E8E0D8;">
                    <div class="flex items-center justify-between px-3 py-2" style="background:#f5ece0;">
                        <span class="font-semibold text-sm" style="color:#211B17;">{{ $group }}</span>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" onchange="toggleGroupPerms(this, '{{ $loop->index }}')"
                                   {{ collect(array_keys($perms))->every(fn($k) => in_array($k, $rolePerms)) ? 'checked' : '' }}>
                            <span class="text-xs" style="color:#766C63;">All</span>
                        </label>
                    </div>
                    <div class="p-3 space-y-2" id="pgrp-{{ $loop->index }}">
                        @foreach($perms as $key => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" data-group="{{ $loop->parent->index }}"
                                   style="accent-color:#B47A42;"
                                   {{ in_array($key, old('permissions', $rolePerms)) ? 'checked' : '' }}>
                            <span class="text-sm" style="color:#766C63;">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sm-administrator.roles.index') }}" class="px-6 py-2.5 rounded-lg font-medium text-sm"
               style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">Cancel</a>
            <button type="submit" class="px-8 py-2.5 rounded-lg font-semibold text-white"
                    style="background:linear-gradient(135deg,#B47A42,#D4A055);">Update Role</button>
        </div>
    </form>
</div>

<style>
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { width:100%; padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; transition:border-color 0.2s; outline:none; font-family:inherit; }
.field-input:focus { border-color:#B47A42; box-shadow:0 0 0 3px rgba(180,122,66,.1); }
</style>
<script>
function toggleGroupPerms(checkbox, groupIdx) {
    document.querySelectorAll(`[data-group="${groupIdx}"]`).forEach(c => c.checked = checkbox.checked);
}
</script>
@endsection
