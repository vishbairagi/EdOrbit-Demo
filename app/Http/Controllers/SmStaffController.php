<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmStaffController extends Controller
{
    public function index(): View
    {
        $schoolId = session('selected_school_id');
        $staff = Staff::with('role')
            ->where('school_id', $schoolId)
            ->latest()
            ->get();

        return view('sm-administrator.staff.index', compact('staff'));
    }

    public function create(): View
    {
        $schoolId = session('selected_school_id');
        $roles = StaffRole::where('school_id', $schoolId)->where('status', 'Active')->get();

        return view('sm-administrator.staff.create', compact('roles'));
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
            'designation' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'staff_role_id' => 'nullable|exists:staff_roles,id',
            'note' => 'nullable|string',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string',
            'username' => ['required', 'string', 'max:50', Rule::unique('staff'), Rule::unique('school_users')],
            'login_email' => ['nullable', 'email', Rule::unique('staff'), Rule::unique('school_users'), 'required_without:login_phone'],
            'login_phone' => ['nullable', 'string', Rule::unique('staff'), Rule::unique('school_users'), 'required_without:login_email'],
            'password' => 'required|string|min:8',
            'govt_documents' => 'nullable|array',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff/photos', 'public');
        }

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
                        ->store('staff/documents', 'public');
                }
                $govtDocs[] = $entry;
            }
        }

        Staff::create([
            'school_id' => $schoolId,
            'staff_role_id' => $validated['staff_role_id'] ?? null,
            'employee_id' => $validated['employee_id'] ?? Staff::generateEmployeeId($schoolId),
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'photo' => $photoPath,
            'designation' => $validated['designation'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'note' => $validated['note'] ?? null,
            'govt_documents' => $govtDocs ?: null,
            'salary' => $validated['salary'] ?? null,
            'salary_type' => $validated['salary_type'] ?? null,
            'username' => $validated['username'],
            'login_email' => $validated['login_email'] ?? null,
            'login_phone' => $validated['login_phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('sm-administrator.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    public function edit(Staff $staff): View
    {
        $schoolId = session('selected_school_id');
        $roles = StaffRole::where('school_id', $schoolId)->where('status', 'Active')->get();

        return view('sm-administrator.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|max:2048',
            'designation' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'staff_role_id' => 'nullable|exists:staff_roles,id',
            'note' => 'nullable|string',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string',
            'username' => ['required', 'string', 'max:50', Rule::unique('staff')->ignore($staff->id), Rule::unique('school_users')],
            'login_email' => ['nullable', 'email', Rule::unique('staff')->ignore($staff->id), 'required_without:login_phone'],
            'login_phone' => ['nullable', 'string', Rule::unique('staff')->ignore($staff->id), 'required_without:login_email'],
            'password' => 'nullable|string|min:8',
            'status' => 'required|in:Active,Inactive',
        ]);

        $data = $validated;
        unset($data['password']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff/photos', 'public');
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $staff->update($data);

        return redirect()->route('sm-administrator.staff.index')
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->delete();

        return redirect()->route('sm-administrator.staff.index')
            ->with('success', 'Staff deleted.');
    }

    public function checkUsername(Request $request): JsonResponse
    {
        $username = $request->query('username');
        $excludeId = $request->query('exclude_id');
        $type = $request->query('type', 'staff'); // 'staff' or 'school_user'

        $staffExists = Staff::where('username', $username)
            ->when($type === 'staff' && $excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        $adminExists = SchoolUser::where('username', $username)
            ->when($type === 'school_user' && $excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        return response()->json([
            'available' => ! $staffExists && ! $adminExists,
        ]);
    }

    public function getRolePermissions(StaffRole $role): JsonResponse
    {
        return response()->json($role->permissions()->pluck('permission_key'));
    }

    /** Export all staff as CSV */
    public function export(): StreamedResponse
    {
        $schoolId = session('selected_school_id');
        $staff = Staff::with('role')
            ->where('school_id', $schoolId)
            ->orderBy('employee_id')
            ->get();

        $filename = 'staff_export_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Employee ID', 'Name', 'Gender', 'Date of Birth', 'Phone', 'Email',
            'Designation', 'Joining Date', 'Salary', 'Salary Type',
            'Role', 'Username', 'Login Email', 'Login Phone', 'Status', 'Address', 'Note',
            'Doc Type (optional)', 'Doc Number (optional)',
        ];

        $callback = function () use ($staff, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($staff as $member) {
                fputcsv($file, [
                    $member->employee_id,
                    $member->name,
                    $member->gender,
                    $member->date_of_birth?->format('Y-m-d'),
                    $member->phone,
                    $member->email,
                    $member->designation,
                    $member->joining_date?->format('Y-m-d'),
                    $member->salary,
                    $member->salary_type,
                    $member->role?->name,
                    $member->username,
                    $member->login_email,
                    $member->login_phone,
                    $member->status,
                    $member->address,
                    $member->note,
                    // First govt document (optional)
                    $member->govt_documents[0]['type'] ?? '',
                    $member->govt_documents[0]['value'] ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download a sample / demo CSV for import */
    public function importDemo(): StreamedResponse
    {
        $filename = 'staff_import_sample.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Employee ID', 'Name', 'Gender', 'Date of Birth', 'Phone', 'Email',
            'Designation', 'Joining Date', 'Salary', 'Salary Type',
            'Role', 'Username', 'Login Email', 'Login Phone', 'Password', 'Status', 'Address', 'Note',
            'Doc Type (optional)', 'Doc Number (optional)',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'EMP-0001', 'Ali Hassan', 'Male', '1990-05-15', '03001234567', 'ali@school.com',
                'Teacher', '2024-01-10', '50000', 'Monthly',
                '', 'ali.hassan', 'ali@school.com', '', 'Password@123', 'Active', 'Lahore', '',
                'Aadhaar Card', '1234-5678-9012',
            ]);
            fputcsv($file, [
                'EMP-0002', 'Sara Khan', 'Female', '1992-08-20', '03111234567', 'sara@school.com',
                'Librarian', '2024-03-01', '40000', 'Monthly',
                '', 'sara.khan', 'sara@school.com', '', 'Password@123', 'Active', 'Karachi', '',
                'PAN Card', 'ABCDE1234F',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import staff from uploaded CSV */
    public function import(Request $request): RedirectResponse
    {
        $schoolId = session('selected_school_id');

        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:5120',
        ]);

        $file   = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');

        // Skip header row
        fgetcsv($handle);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            // Columns: 0-EmpID,1-Name,2-Gender,3-DOB,4-Phone,5-Email,
            //          6-Designation,7-JoiningDate,8-Salary,9-SalaryType,
            //          10-Role,11-Username,12-LoginEmail,13-LoginPhone,14-Password,
            //          15-Status,16-Address,17-Note,18-DocType(opt),19-DocNumber(opt)
            if (empty(trim($row[1] ?? ''))) {
                continue; // skip blank rows
            }

            $username   = trim($row[11] ?? '');
            $loginEmail = trim($row[12] ?? '') ?: null;
            $loginPhone = trim($row[13] ?? '') ?: null;
            $password   = trim($row[14] ?? '');

            if (empty($username)) {
                $skipped++;
                continue;
            }

            // Resolve role
            $roleId = null;
            if (! empty(trim($row[10] ?? ''))) {
                $role = StaffRole::where('school_id', $schoolId)
                    ->where('name', trim($row[10]))
                    ->first();
                $roleId = $role?->id;
            }

            $existing = Staff::where('username', $username)->first();

            if ($existing) {
                // Update but do NOT change password unless provided
                // Build govt_documents if doc type provided
                $docType   = trim($row[18] ?? '');
                $docNumber = trim($row[19] ?? '');

                $data = [
                    'name'          => trim($row[1]),
                    'gender'        => in_array(trim($row[2]), ['Male','Female','Other']) ? trim($row[2]) : 'Male',
                    'date_of_birth' => ! empty(trim($row[3] ?? '')) ? trim($row[3]) : null,
                    'phone'         => $row[4] ?: null,
                    'email'         => $row[5] ?: null,
                    'designation'   => $row[6] ?: null,
                    'joining_date'  => ! empty(trim($row[7] ?? '')) ? trim($row[7]) : null,
                    'salary'        => is_numeric($row[8]) ? (float)$row[8] : null,
                    'salary_type'   => $row[9] ?: null,
                    'staff_role_id' => $roleId,
                    'login_email'   => $loginEmail,
                    'login_phone'   => $loginPhone,
                    'status'        => in_array(trim($row[15] ?? ''), ['Active','Inactive']) ? trim($row[15]) : 'Active',
                    'address'       => $row[16] ?: null,
                    'note'          => $row[17] ?: null,
                ];
                if (! empty($docType)) {
                    $data['govt_documents'] = [[
                        'type'   => $docType,
                        'method' => 'manual',
                        'value'  => $docNumber,
                    ]];
                }
                if (! empty($password)) {
                    $data['password'] = Hash::make($password);
                }
                $existing->update($data);
                $imported++;
            } else {
                // Create new — password required
                if (empty($password)) {
                    $errors[] = "Row skipped (username: {$username}): password is required for new staff.";
                    $skipped++;
                    continue;
                }

                $empId = ! empty(trim($row[0] ?? ''))
                    ? trim($row[0])
                    : Staff::generateEmployeeId($schoolId);

                // Build govt_documents if doc type provided
                $docType   = trim($row[18] ?? '');
                $docNumber = trim($row[19] ?? '');

                $govtDocs = [];
                if (! empty($docType)) {
                    $govtDocs = [[
                        'type'   => $docType,
                        'method' => 'manual',
                        'value'  => $docNumber,
                    ]];
                }

                Staff::create([
                    'school_id'      => $schoolId,
                    'employee_id'    => $empId,
                    'name'           => trim($row[1]),
                    'gender'         => in_array(trim($row[2]), ['Male','Female','Other']) ? trim($row[2]) : 'Male',
                    'date_of_birth'  => ! empty(trim($row[3] ?? '')) ? trim($row[3]) : null,
                    'phone'          => $row[4] ?: null,
                    'email'          => $row[5] ?: null,
                    'designation'    => $row[6] ?: null,
                    'joining_date'   => ! empty(trim($row[7] ?? '')) ? trim($row[7]) : null,
                    'salary'         => is_numeric($row[8]) ? (float)$row[8] : null,
                    'salary_type'    => $row[9] ?: null,
                    'staff_role_id'  => $roleId,
                    'username'       => $username,
                    'login_email'    => $loginEmail,
                    'login_phone'    => $loginPhone,
                    'password'       => Hash::make($password),
                    'status'         => in_array(trim($row[15] ?? ''), ['Active','Inactive']) ? trim($row[15]) : 'Active',
                    'address'        => $row[16] ?: null,
                    'note'           => $row[17] ?: null,
                    'govt_documents' => $govtDocs ?: null,
                ]);
                $imported++;
            }
        }
        fclose($handle);

        $message = "{$imported} staff record(s) imported successfully.";
        if ($skipped) {
            $message .= " {$skipped} row(s) skipped.";
        }

        return redirect()->route('sm-administrator.staff.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\Staff::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}