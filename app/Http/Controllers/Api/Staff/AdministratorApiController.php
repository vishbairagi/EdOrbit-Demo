<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\SmRoleController;
use App\Models\RolePermission;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffLeave;
use App\Models\StaffRole;
use App\Models\StaffSalaryRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdministratorApiController extends BaseApiController
{
    // ==========================================
    // 1. ADMINS CRUD (sm-administrator.admins)
    // ==========================================

    public function adminsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $query = SchoolUser::where('school_id', $school?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Admin users retrieved');
    }

    public function adminStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:school_users,username',
            'email' => 'required|email|max:100|unique:school_users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:20',
            'status' => 'nullable|in:Active,Inactive,active,inactive',
        ]);

        $admin = SchoolUser::create([
            'school_id' => $school->id,
            'employee_id' => SchoolUser::generateEmployeeId($school->id),
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'login_email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'Admin',
            'gender' => $validated['gender'] ?? 'Male',
            'status' => ucfirst(strtolower($validated['status'] ?? 'Active')),
        ]);

        return $this->success($request, $admin, 'Admin user created successfully', 201);
    }

    public function adminShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $admin = SchoolUser::where('school_id', $school?->id)->find($id);

        if (! $admin) {
            return $this->error('Admin user not found.', 404);
        }

        return $this->success($request, $admin, 'Admin user details retrieved');
    }

    public function adminUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $admin = SchoolUser::where('school_id', $school?->id)->find($id);

        if (! $admin) {
            return $this->error('Admin user not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'username' => ['nullable', 'string', 'max:100', Rule::unique('school_users')->ignore($admin->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('school_users')->ignore($admin->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Inactive,active,inactive',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['status'])) {
            $validated['status'] = ucfirst(strtolower($validated['status']));
        }

        $admin->update($validated);

        return $this->success($request, $admin, 'Admin user updated successfully');
    }

    public function adminDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $admin = SchoolUser::where('school_id', $school?->id)->find($id);

        if (! $admin) {
            return $this->error('Admin user not found.', 404);
        }

        $admin->delete();

        return $this->success($request, null, 'Admin user deleted successfully');
    }

    // ==========================================
    // 2. ROLES CRUD (sm-administrator.roles)
    // ==========================================

    public function rolesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $roles = StaffRole::with(['permissions'])
            ->withCount('staff')
            ->where('school_id', $school?->id)
            ->latest()
            ->get();

        return $this->success($request, $roles, 'Staff roles retrieved successfully');
    }

    public function roleStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('staff_roles')->where('school_id', $school?->id)],
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role = StaffRole::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $key) {
                RolePermission::create([
                    'staff_role_id' => $role->id,
                    'permission_key' => $key,
                ]);
            }
        }

        $role->load('permissions');

        return $this->success($request, $role, 'Role created successfully', 201);
    }

    public function roleShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $role = StaffRole::with('permissions')
            ->where('school_id', $school?->id)
            ->find($id);

        if (! $role) {
            return $this->error('Role not found.', 404);
        }

        return $this->success($request, $role, 'Role details retrieved');
    }

    public function roleUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $role = StaffRole::where('school_id', $school?->id)->find($id);

        if (! $role) {
            return $this->error('Role not found.', 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('staff_roles')->where('school_id', $school?->id)->ignore($role->id)],
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name' => $validated['name'] ?? $role->name,
            'description' => $validated['description'] ?? $role->description,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->delete();
            foreach ($validated['permissions'] as $key) {
                RolePermission::create([
                    'staff_role_id' => $role->id,
                    'permission_key' => $key,
                ]);
            }
        }

        $role->load('permissions');

        return $this->success($request, $role, 'Role updated successfully');
    }

    public function roleDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $role = StaffRole::where('school_id', $school?->id)->find($id);

        if (! $role) {
            return $this->error('Role not found.', 404);
        }

        $role->permissions()->delete();
        $role->delete();

        return $this->success($request, null, 'Role deleted successfully');
    }

    // ==========================================
    // 3. STAFF CRUD (sm-administrator.staff)
    // ==========================================

    public function staffIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $query = Staff::with('role')
            ->where('school_id', $school?->id);

        if ($roleId = $request->input('role_id')) {
            $query->where('staff_role_id', $roleId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', ucfirst(strtolower($status)));
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->orderBy('name')->get(), 'Staff members retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->orderBy('name')->paginate($perPage), 'Staff members retrieved');
    }

    public function staffStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'staff_role_id' => 'required|exists:staff_roles,id',
            'username' => 'required|string|max:100|unique:staff,username',
            'email' => 'required|email|max:100|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'gender' => 'nullable|in:Male,Female,Other,male,female,other',
            'designation' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:Active,Inactive,active,inactive',
        ]);

        $staff = Staff::create([
            'school_id' => $school->id,
            'employee_id' => Staff::generateEmployeeId($school->id),
            'staff_role_id' => $validated['staff_role_id'],
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'login_email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'gender' => ucfirst(strtolower($validated['gender'] ?? 'Male')),
            'designation' => $validated['designation'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'salary' => $validated['salary'] ?? null,
            'salary_type' => $validated['salary_type'] ?? 'Monthly',
            'address' => $validated['address'] ?? null,
            'status' => ucfirst(strtolower($validated['status'] ?? 'Active')),
        ]);

        $staff->load('role');

        return $this->success($request, $staff, 'Staff member created successfully', 201);
    }

    public function staffShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $staff = Staff::with(['role', 'attendances', 'leaves'])
            ->where('school_id', $school?->id)
            ->find($id);

        if (! $staff) {
            return $this->error('Staff member not found.', 404);
        }

        return $this->success($request, $staff, 'Staff details retrieved');
    }

    public function staffUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $staff = Staff::where('school_id', $school?->id)->find($id);

        if (! $staff) {
            return $this->error('Staff member not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'staff_role_id' => 'sometimes|required|exists:staff_roles,id',
            'email' => ['nullable', 'email', 'max:100', Rule::unique('staff')->ignore($staff->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'designation' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:Active,Inactive,active,inactive',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['status'])) {
            $validated['status'] = ucfirst(strtolower($validated['status']));
        }

        $staff->update($validated);
        $staff->load('role');

        return $this->success($request, $staff, 'Staff member updated successfully');
    }

    public function staffDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $staff = Staff::where('school_id', $school?->id)->find($id);

        if (! $staff) {
            return $this->error('Staff member not found.', 404);
        }

        $staff->delete();

        return $this->success($request, null, 'Staff member deleted successfully');
    }

    // ==========================================
    // 4. ATTENDANCE & LEAVES (sm-administrator.attendance, leaves)
    // ==========================================

    public function staffAttendanceIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $date = $request->input('date', date('Y-m-d'));

        $query = StaffAttendance::with('staff.role')
            ->where('school_id', $school?->id)
            ->whereDate('attendance_date', $date);

        $attendances = $query->get();

        return $this->success($request, $attendances, "Staff attendance for {$date}");
    }

    public function staffAttendanceStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.staff_id' => 'required|exists:staff,id',
            'attendance.*.status' => 'required|in:Present,Absent,Late,HalfDay,Holiday',
        ]);

        $saved = 0;
        foreach ($validated['attendance'] as $entry) {
            StaffAttendance::updateOrCreate([
                'school_id' => $school->id,
                'staff_id' => $entry['staff_id'],
                'attendance_date' => $validated['date'],
            ], [
                'status' => $entry['status'],
            ]);
            $saved++;
        }

        return $this->success($request, ['saved_count' => $saved], 'Staff attendance recorded successfully');
    }

    public function staffLeavesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $leaves = StaffLeave::with('applicant')
            ->where('school_id', $school?->id)
            ->latest('id')
            ->get();

        return $this->success($request, $leaves, 'Staff leaves retrieved');
    }

    public function staffLeaveStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $user = $this->getUser($request);
        $userType = $this->getUserType($request);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        $applicantId = $validated['staff_id'] ?? $user->id;
        $applicantType = ($userType === 'admin') ? 'school_user' : 'staff';

        $leave = StaffLeave::create([
            'school_id' => $school->id,
            'applicant_id' => $applicantId,
            'applicant_type' => $applicantType,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return $this->success($request, $leave, 'Leave applied successfully', 201);
    }

    public function staffLeaveUpdateStatus(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);

        $leave = StaffLeave::where('school_id', $school?->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected,Pending',
            'note' => 'nullable|string|max:255',
        ]);

        $leave->update($validated);

        return $this->success($request, $leave, "Leave status updated to {$validated['status']}");
    }
}
