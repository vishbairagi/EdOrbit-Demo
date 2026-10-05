<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SmAcademicClassSectionController extends Controller
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

        $query = SchoolClass::with(['sections'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where('name', 'like', "%{$kw}%");
        }

        $classes = $query->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function ($class) {
                $class->male_students = Student::where('school_class_id', $class->id)
                    ->where('status', 'Active')
                    ->where('gender', 'Male')
                    ->count();

                $class->female_students = Student::where('school_class_id', $class->id)
                    ->where('status', 'Active')
                    ->where('gender', 'Female')
                    ->count();

                $class->total_students = Student::where('school_class_id', $class->id)
                    ->where('status', 'Active')
                    ->count();

                return $class;
            });

        return view('sm-academic.class-sections.index', compact('school', 'session', 'classes'));
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

        return redirect()->route('academic.class-sections.index')->with('success', 'Section added successfully.');
    }

    public function destroySection(Section $section)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($section->schoolClass->school_id !== $school->id, 403);
        $section->delete();

        return redirect()->route('academic.class-sections.index')->with('success', 'Section deleted.');
    }

    /** Export class sections as CSV */
    public function export(): StreamedResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = SchoolClass::with('sections')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $filename = 'class_sections_' . str_replace([' ', '/'], '_', $session->name) . '_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($classes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Class Name', 'Section Name', 'Capacity']);
            foreach ($classes as $class) {
                if ($class->sections->isEmpty()) {
                    fputcsv($file, [$class->name, '', '']);
                } else {
                    foreach ($class->sections as $section) {
                        fputcsv($file, [
                            $class->name,
                            $section->name,
                            $section->capacity ?? '',
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download sample CSV template */
    public function importDemo(): StreamedResponse
    {
        $filename = 'class_sections_sample.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Class Name', 'Section Name', 'Capacity']);
            fputcsv($file, ['Class 1', 'A', '40']);
            fputcsv($file, ['Class 1', 'B', '40']);
            fputcsv($file, ['Class 2', 'A', '45']);
            fputcsv($file, ['Class 2', 'B', '45']);
            fputcsv($file, ['Class 3', 'Rose', '30']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import class sections from uploaded CSV */
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
            $className = trim($row[0] ?? '');
            $sectionName = trim($row[1] ?? '');
            $capacity = trim($row[2] ?? '');

            if (empty($className) || empty($sectionName)) {
                $skipped++;
                continue;
            }

            // Find the class in this school and session
            $schoolClass = SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('name', $className)
                ->first();

            if (!$schoolClass) {
                // Cannot add section if class does not exist
                $skipped++;
                continue;
            }

            Section::updateOrCreate(
                [
                    'school_class_id' => $schoolClass->id,
                    'name' => $sectionName,
                ],
                [
                    'capacity' => is_numeric($capacity) ? (int) $capacity : null,
                ]
            );
            $imported++;
        }
        fclose($handle);

        return redirect()->route('academic.class-sections.index')
            ->with('success', "{$imported} section(s) imported. {$skipped} row(s) skipped (e.g. if class was not found).");
    }
}
