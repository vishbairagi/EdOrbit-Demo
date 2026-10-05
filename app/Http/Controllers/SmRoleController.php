<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\StaffRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmRoleController extends Controller
{
    /** All available permission groups and keys. */
    public static array $availablePermissions = [
        'School Management' => [
            'sm-school.dashboard' => 'Dashboard',
            'sm-school.class-groups' => 'Class Groups',
            'sm-school.classes' => 'Classes & Sections',
            'sm-school.settings' => 'School Settings',
        ],
        'Academic' => [
            'academic.dashboard' => 'Dashboard',
            'academic.class-teachers' => 'Class Teachers',
            'academic.class-sections' => 'Class Sections',
            'academic.mediums' => 'Mediums',
            'academic.subjects' => 'Subjects',
            'academic.subject-types' => 'Subject Types',
            'academic.study-materials' => 'Study Materials',
        ],
        'Examination' => [
            'examination.dashboard' => 'Dashboard',
            'examination.exams' => 'Manage Exams',
            'examination.groups' => 'Manage Exam Groups',
            'examination.admit-cards' => 'Admit Cards',
            'examination.results' => 'Exam Results',
            'examination.reports' => 'Academic Report',
        ],
        'Student' => [
            'sm-student.dashboard' => 'Dashboard',
            'sm-student.students' => 'Students',
            'sm-student.print-id-cards' => 'Print ID Cards',
            'sm-student.promote' => 'Promote Students',
            'sm-student.transfer' => 'Transfer Students',
        ],
        'Administrator' => [
            'sm-administrator.dashboard' => 'Dashboard',
            'sm-administrator.admins' => 'Admins',
            'sm-administrator.roles' => 'Roles',
            'sm-administrator.staff' => 'Staff List',
            'sm-administrator.attendance' => 'Staff Attendance',
            'sm-administrator.leaves' => 'Staff Leaves',
            'sm-administrator.id-cards' => 'Staff ID Cards',
            'sm-administrator.payroll' => 'Staff Payroll',
            'sm-administrator.users' => 'Users',
        ],
        'Accounting' => [
            'accounting.dashboard' => 'Accounting Dashboard',
            'accounting.fee-types' => 'Fee Types',
            'accounting.fee-invoices' => 'Fee Invoices & Collection',
            'accounting.collect-payment' => 'Collect Payment & History',
            'accounting.bulk-invoice-print' => 'Bulk Invoice Print',
        ],
        'Transport' => [
            'transport.dashboard' => 'Dashboard',
            'transport.vehicles' => 'Vehicles',
            'transport.routes' => 'Routes',
            'transport.assign-students' => 'Assign Students',
            'transport.report' => 'Report',
        ],
        'Library' => [
            'library.dashboard' => 'Dashboard',
            'library.books' => 'Books',
            'library.book-issue' => 'Book Issue',
            'library.library-cards' => 'Library Cards',
        ],
    ];

    public function index(): View
    {
        $schoolId = session('selected_school_id');
        $roles = StaffRole::where('school_id', $schoolId)->withCount('staff')->latest()->get();

        return view('sm-administrator.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = self::$availablePermissions;

        return view('sm-administrator.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('selected_school_id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('staff_roles')->where('school_id', $schoolId)],
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role = StaffRole::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $key) {
                RolePermission::create(['staff_role_id' => $role->id, 'permission_key' => $key]);
            }
        }

        return redirect()->route('sm-administrator.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(StaffRole $role): View
    {
        $permissions = self::$availablePermissions;
        $rolePerms = $role->permissions()->pluck('permission_key')->toArray();

        return view('sm-administrator.roles.edit', compact('role', 'permissions', 'rolePerms'));
    }

    public function update(Request $request, StaffRole $role): RedirectResponse
    {
        $schoolId = session('selected_school_id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('staff_roles')->where('school_id', $schoolId)->ignore($role->id)],
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'status' => 'required|in:Active,Inactive',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        $role->permissions()->delete();
        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $key) {
                RolePermission::create(['staff_role_id' => $role->id, 'permission_key' => $key]);
            }
        }

        return redirect()->route('sm-administrator.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(StaffRole $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('sm-administrator.roles.index')
            ->with('success', 'Role deleted.');
    }

    public function getPermissions(StaffRole $role): JsonResponse
    {
        return response()->json($role->permissions()->pluck('permission_key'));
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\StaffRole::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}