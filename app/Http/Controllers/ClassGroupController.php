<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassGroup;
use App\Models\School;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassGroupController extends Controller
{
    private function getSchoolAndSession(): array
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        abort_if(! $sessionId, 403, 'No academic session selected or configured.');
        $session = AcademicSession::findOrFail($sessionId);

        return [$school, $session];
    }

    public function index(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = ClassGroup::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [2, 5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $classGroups = $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('sm-school.class-groups.index', compact('school', 'session', 'classGroups'));
    }

    public function store(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status'      => 'required|in:active,inactive',
        ]);

        $validated['school_id']           = $school->id;
        $validated['academic_session_id'] = $session->id;

        ClassGroup::create($validated);

        return redirect()->route('sm-school.class-groups.index')->with('success', 'Class group created successfully.');
    }

    public function update(Request $request, ClassGroup $classGroup)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($classGroup->school_id !== $school->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status'      => 'required|in:active,inactive',
        ]);

        $classGroup->update($validated);

        return redirect()->route('sm-school.class-groups.index')->with('success', 'Class group updated successfully.');
    }

    public function updateStatus(Request $request, ClassGroup $classGroup)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($classGroup->school_id !== $school->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $classGroup->update(['status' => $validated['status']]);

        return back()->with('success', 'Class group status updated.');
    }

    public function destroy(ClassGroup $classGroup)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($classGroup->school_id !== $school->id, 403);

        $classGroup->delete();

        return redirect()->route('sm-school.class-groups.index')->with('success', 'Class group deleted.');
    }

    /** Export class groups for current session as CSV */
    public function export(): StreamedResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $groups = ClassGroup::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get();

        $filename = 'class_groups_' . str_replace([' ', '/'], '_', $session->name) . '_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($groups) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Group Name', 'Description', 'Status']);
            foreach ($groups as $group) {
                fputcsv($file, [
                    $group->name,
                    $group->description ?? '',
                    ucfirst($group->status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download sample CSV template */
    public function importDemo(): StreamedResponse
    {
        $filename = 'class_groups_sample.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Group Name', 'Description', 'Status']);
            fputcsv($file, ['Primary',    'Classes 1 to 5',    'Active']);
            fputcsv($file, ['Middle',     'Classes 6 to 8',    'Active']);
            fputcsv($file, ['Secondary',  'Classes 9 to 10',   'Active']);
            fputcsv($file, ['Senior',     'Classes 11 to 12',  'Active']);
            fputcsv($file, ['Pre-School', 'Nursery & KG',      'Inactive']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import class groups from uploaded CSV (session-scoped) */
    public function import(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $handle   = fopen($request->file('csv_file')->path(), 'r');
        fgetcsv($handle); // skip header row

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            if (empty($name)) { $skipped++; continue; }

            $desc   = trim($row[1] ?? '') ?: null;
            $status = strtolower(trim($row[2] ?? 'active')) === 'inactive' ? 'inactive' : 'active';

            ClassGroup::updateOrCreate(
                [
                    'school_id'           => $school->id,
                    'academic_session_id' => $session->id,
                    'name'                => $name,
                ],
                ['description' => $desc, 'status' => $status]
            );
            $imported++;
        }
        fclose($handle);

        return redirect()->route('sm-school.class-groups.index')
            ->with('success', "{$imported} class group(s) imported for session '{$session->name}'. {$skipped} row(s) skipped.");
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\ClassGroup::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}