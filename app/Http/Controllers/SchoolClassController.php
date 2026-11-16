<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassGroup;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
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

        $query = SchoolClass::with(['classGroup', 'sections'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('classGroup', function ($gq) use ($search) {
                      $gq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sections', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($classGroupId = $request->input('class_group_id')) {
            $query->where('class_group_id', $classGroupId);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [2, 5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $classes = $query->orderBy('order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $classGroups = ClassGroup::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('sm-school.classes.index', compact('school', 'session', 'classes', 'classGroups'));
    }

    public function store(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'order' => 'integer|min:0',
        ]);
        $validated['school_id'] = $school->id;
        $validated['academic_session_id'] = $session->id;
        $validated['order'] = $validated['order'] ?? 0;
        SchoolClass::create($validated);

        return redirect()->route('sm-school.classes.index')->with('success', 'Class created successfully.');
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($schoolClass->school_id !== $school->id, 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class_group_id' => 'nullable|exists:class_groups,id',
        ]);
        $schoolClass->update($validated);

        return redirect()->route('sm-school.classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(SchoolClass $schoolClass)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($schoolClass->school_id !== $school->id, 403);
        $schoolClass->delete();

        return redirect()->route('sm-school.classes.index')->with('success', 'Class deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        [$school] = $this->getSchoolAndSession();
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('sm-school.classes.index')->with('error', 'No classes selected.');
        }

        $deleted = SchoolClass::whereIn('id', $ids)
            ->where('school_id', $school->id)
            ->delete();

        return redirect()->route('sm-school.classes.index')
            ->with('success', "{$deleted} class(es) deleted successfully.");
    }

    public function addSection(Request $request, SchoolClass $schoolClass)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($schoolClass->school_id !== $school->id, 403);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);
        $validated['school_class_id'] = $schoolClass->id;
        Section::create($validated);

        return redirect()->route('sm-school.classes.index')->with('success', 'Section added.');
    }

    public function destroySection(Section $section)
    {
        $section->delete();

        return redirect()->route('sm-school.classes.index')->with('success', 'Section deleted.');
    }

    public function export()
    {
        [$school, $session] = $this->getSchoolAndSession();
        $classes = SchoolClass::with('classGroup')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $filename = "classes_export_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Class Name', 'Class Group', 'Order'];

        $callback = function() use($classes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($classes as $class) {
                fputcsv($file, [
                    $class->name,
                    $class->classGroup ? $class->classGroup->name : '',
                    $class->order
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importDemo()
    {
        $filename = "classes_import_sample.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Class Name', 'Class Group', 'Order'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Class 1', 'Primary', '1']);
            fputcsv($file, ['Class 2', '', '2']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        
        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');

        $header = fgetcsv($handle);
        
        $importedCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;
            
            $className = $row[0];
            $groupName = $row[1] ?? null;
            $order = $row[2] ?? 0;

            $classGroupId = null;
            if (!empty($groupName)) {
                $group = ClassGroup::where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->where('name', trim($groupName))
                    ->first();
                if ($group) {
                    $classGroupId = $group->id;
                }
            }

            SchoolClass::updateOrCreate([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'name' => trim($className),
            ], [
                'class_group_id' => $classGroupId,
                'order' => (int)$order,
            ]);
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('sm-school.classes.index')->with('success', "$importedCount classes imported successfully.");
    }
}
