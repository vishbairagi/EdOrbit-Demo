<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\SchoolUserPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SmAdminController extends Controller
{
    private array $allPermissions = [
        'School Management' => ['sm-school.dashboard', 'sm-school.class-groups', 'sm-school.classes'],
        'Academic' => ['academic.dashboard', 'academic.class-sections', 'academic.mediums', 'academic.subjects', 'academic.subject-types', 'academic.study-materials'],
        'Student' => ['sm-student.dashboard', 'sm-student.students', 'sm-student.print-id-cards', 'sm-student.promote', 'sm-student.transfer'],
        'Administrator' => ['sm-administrator.dashboard', 'sm-administrator.admins', 'sm-administrator.roles', 'sm-administrator.staff', 'sm-administrator.attendance', 'sm-administrator.leaves', 'sm-administrator.id-cards', 'sm-administrator.payroll', 'sm-administrator.users'],
        'Accounting' => ['accounting.dashboard', 'accounting.fee-types', 'accounting.fee-invoices', 'accounting.collect-payment', 'accounting.bulk-invoice-print'],
        'Transport' => ['transport.dashboard', 'transport.vehicles', 'transport.routes', 'transport.assign-students', 'transport.report'],
        'Library' => ['library.dashboard', 'library.books', 'library.book-issue', 'library.library-cards'],
    ];

    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $admins = SchoolUser::where('school_id', $schoolId)->latest()->get();

        return view('sm-administrator.admins.index', compact('admins'));
    }

    public function create(): View
    {
        $permissions = $this->allPermissions;

        return view('sm-administrator.admins.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('selected_school_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|max:2048',
            'joining_date' => 'nullable|date',
            'role' => 'required|in:Principal,Headmaster,Admin',
            'note' => 'nullable|string',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string',
            'username' => ['required', 'string', 'max:50', Rule::unique('school_users'), Rule::unique('staff')],
            'login_email' => ['nullable', 'email', Rule::unique('school_users'), Rule::unique('staff'), 'required_without:login_phone'],
            'login_phone' => ['nullable', 'string', Rule::unique('school_users'), Rule::unique('staff'), 'required_without:login_email'],
            'password' => 'required|string|min:8',
            'permissions' => 'nullable|array',
            'govt_documents' => 'nullable|array',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('school-users/photos', 'public');
        }

        // Handle document file uploads
        $govtDocs = [];
        if ($request->has('govt_documents')) {
            foreach ($request->input('govt_documents', []) as $idx => $doc) {
                $entry = [
                    'type' => $doc['type'] ?? '',
                    'method' => $doc['method'] ?? 'manual',
                    'value' => $doc['value'] ?? '',
                ];
                if ($request->hasFile("govt_documents.{$idx}.file")) {
                    $entry['file'] = $request->file("govt_documents.{$idx}.file")
                        ->store('school-users/documents', 'public');
                }
                $govtDocs[] = $entry;
            }
        }

        $admin = SchoolUser::create([
            'school_id' => $schoolId,
            'employee_id' => $validated['employee_id'] ?? SchoolUser::generateEmployeeId($schoolId),
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'photo' => $photoPath,
            'joining_date' => $validated['joining_date'] ?? null,
            'role' => $validated['role'],
            'note' => $validated['note'] ?? null,
            'govt_documents' => $govtDocs ?: null,
            'salary' => $validated['salary'] ?? null,
            'salary_type' => $validated['salary_type'] ?? null,
            'username' => $validated['username'],
            'login_email' => $validated['login_email'] ?? null,
            'login_phone' => $validated['login_phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // Save permissions
        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permKey) {
                SchoolUserPermission::create([
                    'school_user_id' => $admin->id,
                    'permission_key' => $permKey,
                ]);
            }
        }

        return redirect()->route('sm-administrator.admins.index')
            ->with('success', 'Admin/Principal created successfully.');
    }

    public function edit(SchoolUser $admin): View
    {
        $permissions = $this->allPermissions;
        $adminPerms = $admin->permissions()->pluck('permission_key')->toArray();

        return view('sm-administrator.admins.edit', compact('admin', 'permissions', 'adminPerms'));
    }

    public function update(Request $request, SchoolUser $admin): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|max:2048',
            'joining_date' => 'nullable|date',
            'role' => 'required|in:Principal,Headmaster,Admin',
            'note' => 'nullable|string',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string',
            'username' => ['required', 'string', 'max:50', Rule::unique('school_users')->ignore($admin->id), Rule::unique('staff')],
            'login_email' => ['nullable', 'email', Rule::unique('school_users')->ignore($admin->id), 'required_without:login_phone'],
            'login_phone' => ['nullable', 'string', Rule::unique('school_users')->ignore($admin->id), 'required_without:login_email'],
            'password' => 'nullable|string|min:8',
            'permissions' => 'nullable|array',
            'status' => 'required|in:Active,Inactive',
        ]);

        $data = $validated;
        unset($data['permissions'], $data['password']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('school-users/photos', 'public');
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $admin->update($data);

        // Sync permissions
        $admin->permissions()->delete();
        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permKey) {
                SchoolUserPermission::create([
                    'school_user_id' => $admin->id,
                    'permission_key' => $permKey,
                ]);
            }
        }

        return redirect()->route('sm-administrator.admins.index')
            ->with('success', 'Admin/Principal updated successfully.');
    }

    public function destroy(SchoolUser $admin): RedirectResponse
    {
        $admin->delete();

        return redirect()->route('sm-administrator.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    public function updateStatus(Request $request, SchoolUser $admin): RedirectResponse
    {
        $admin->update(['status' => $request->input('status')]);

        return back()->with('success', 'Status updated.');
    }

    public function getAllPermissions(): array
    {
        return $this->allPermissions;
    }

    /** Export all admins as CSV */
    public function export(): StreamedResponse
    {
        $schoolId = session('selected_school_id');
        $admins = SchoolUser::where('school_id', $schoolId)
            ->orderBy('employee_id')
            ->get();

        $filename = 'admins_export_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Employee ID', 'Name', 'Gender', 'Date of Birth', 'Phone', 'Email',
            'Role', 'Designation', 'Joining Date', 'Salary', 'Salary Type',
            'Username', 'Login Email', 'Login Phone', 'Status', 'Address', 'Note',
            'Doc Type (optional)', 'Doc Number (optional)',
        ];

        $callback = function () use ($admins, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($admins as $admin) {
                fputcsv($file, [
                    $admin->employee_id,
                    $admin->name,
                    $admin->gender,
                    $admin->date_of_birth?->format('Y-m-d'),
                    $admin->phone,
                    $admin->email,
                    $admin->role,
                    $admin->designation ?? '',
                    $admin->joining_date?->format('Y-m-d'),
                    $admin->salary,
                    $admin->salary_type,
                    $admin->username,
                    $admin->login_email,
                    $admin->login_phone,
                    $admin->status,
                    $admin->address,
                    $admin->note,
                    $admin->govt_documents[0]['type'] ?? '',
                    $admin->govt_documents[0]['value'] ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download sample CSV for admins import */
    public function importDemo(): StreamedResponse
    {
        $filename = 'admins_import_sample.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Employee ID', 'Name', 'Gender', 'Date of Birth', 'Phone', 'Email',
            'Role', 'Designation', 'Joining Date', 'Salary', 'Salary Type',
            'Username', 'Login Email', 'Login Phone', 'Password', 'Status', 'Address', 'Note',
            'Doc Type (optional)', 'Doc Number (optional)',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'ADM-0001', 'Ahmad Raza', 'Male', '1985-03-10', '03001112233', 'ahmad@school.com',
                'Principal', 'Principal', '2023-08-01', '80000', 'Monthly',
                'ahmad.raza', 'ahmad@school.com', '', 'Password@123', 'Active', 'Lahore', '',
                'Aadhaar Card', '1234-5678-9012',
            ]);
            fputcsv($file, [
                'ADM-0002', 'Nadia Ali', 'Female', '1988-06-25', '03111223344', 'nadia@school.com',
                'Admin', 'Admin', '2024-01-15', '60000', 'Monthly',
                'nadia.ali', 'nadia@school.com', '', 'Password@123', 'Active', 'Karachi', '',
                'PAN Card', 'ABCDE1234F',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import admins from uploaded CSV */
    public function import(Request $request): RedirectResponse
    {
        $schoolId = session('selected_school_id');

        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:5120',
        ]);

        $file   = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');

        // Skip header
        fgetcsv($handle);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            // 0-EmpID,1-Name,2-Gender,3-DOB,4-Phone,5-Email,
            // 6-Role,7-Designation,8-JoiningDate,9-Salary,10-SalaryType,
            // 11-Username,12-LoginEmail,13-LoginPhone,14-Password,
            // 15-Status,16-Address,17-Note,18-DocType(opt),19-DocNumber(opt)
            if (empty(trim($row[1] ?? ''))) {
                continue;
            }

            $username   = trim($row[11] ?? '');
            $loginEmail = trim($row[12] ?? '') ?: null;
            $loginPhone = trim($row[13] ?? '') ?: null;
            $password   = trim($row[14] ?? '');

            if (empty($username)) {
                $skipped++;
                continue;
            }

            $role = in_array(trim($row[6] ?? ''), ['Principal','Headmaster','Admin'])
                ? trim($row[6])
                : 'Admin';

            // Document
            $docType   = trim($row[18] ?? '');
            $docNumber = trim($row[19] ?? '');
            $govtDocs  = ! empty($docType)
                ? [['type' => $docType, 'method' => 'manual', 'value' => $docNumber]]
                : null;

            $existing = SchoolUser::where('username', $username)->first();

            if ($existing) {
                $data = [
                    'name'          => trim($row[1]),
                    'gender'        => in_array(trim($row[2]), ['Male','Female','Other']) ? trim($row[2]) : 'Male',
                    'date_of_birth' => ! empty(trim($row[3] ?? '')) ? trim($row[3]) : null,
                    'phone'         => $row[4] ?: null,
                    'email'         => $row[5] ?: null,
                    'role'          => $role,
                    'joining_date'  => ! empty(trim($row[8] ?? '')) ? trim($row[8]) : null,
                    'salary'        => is_numeric($row[9] ?? '') ? (float)$row[9] : null,
                    'salary_type'   => $row[10] ?: null,
                    'login_email'   => $loginEmail,
                    'login_phone'   => $loginPhone,
                    'status'        => in_array(trim($row[15] ?? ''), ['Active','Inactive']) ? trim($row[15]) : 'Active',
                    'address'       => $row[16] ?: null,
                    'note'          => $row[17] ?: null,
                ];
                if ($govtDocs) {
                    $data['govt_documents'] = $govtDocs;
                }
                if (! empty($password)) {
                    $data['password'] = Hash::make($password);
                }
                $existing->update($data);
                $imported++;
            } else {
                if (empty($password)) {
                    $errors[] = "Row skipped (username: {$username}): password required for new admin.";
                    $skipped++;
                    continue;
                }

                $empId = ! empty(trim($row[0] ?? ''))
                    ? trim($row[0])
                    : SchoolUser::generateEmployeeId($schoolId);

                SchoolUser::create([
                    'school_id'      => $schoolId,
                    'employee_id'    => $empId,
                    'name'           => trim($row[1]),
                    'gender'         => in_array(trim($row[2]), ['Male','Female','Other']) ? trim($row[2]) : 'Male',
                    'date_of_birth'  => ! empty(trim($row[3] ?? '')) ? trim($row[3]) : null,
                    'phone'          => $row[4] ?: null,
                    'email'          => $row[5] ?: null,
                    'role'           => $role,
                    'joining_date'   => ! empty(trim($row[8] ?? '')) ? trim($row[8]) : null,
                    'salary'         => is_numeric($row[9] ?? '') ? (float)$row[9] : null,
                    'salary_type'    => $row[10] ?: null,
                    'username'       => $username,
                    'login_email'    => $loginEmail,
                    'login_phone'    => $loginPhone,
                    'password'       => Hash::make($password),
                    'status'         => in_array(trim($row[15] ?? ''), ['Active','Inactive']) ? trim($row[15]) : 'Active',
                    'address'        => $row[16] ?: null,
                    'note'           => $row[17] ?: null,
                    'govt_documents' => $govtDocs,
                ]);
                $imported++;
            }
        }
        fclose($handle);

        $message = "{$imported} admin record(s) imported successfully.";
        if ($skipped) {
            $message .= " {$skipped} row(s) skipped.";
        }

        return redirect()->route('sm-administrator.admins.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\SchoolUser::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}