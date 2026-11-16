<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\StudyMaterial;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudyMaterialController extends Controller
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

        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // ── Session-Wide Analytics for Study Materials ──
        $allSessionMaterials = StudyMaterial::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        $totalMaterialsCount = $allSessionMaterials->count();
        $totalClassesCount = $classes->count();
        $classesWithMaterialsCount = $classes->filter(fn ($c) => $allSessionMaterials->contains('school_class_id', $c->id))->count();
        $classCoverageRate = $totalClassesCount > 0 ? round(($classesWithMaterialsCount / $totalClassesCount) * 100) : 0;
        $avgMaterialsPerClass = $totalClassesCount > 0 ? round($totalMaterialsCount / $totalClassesCount, 1) : 0;

        $withFilesCount = $allSessionMaterials->filter(fn ($m) => !empty($m->file_path))->count();
        $textOnlyCount = max(0, $totalMaterialsCount - $withFilesCount);
        $digitalResourceRate = $totalMaterialsCount > 0 ? round(($withFilesCount / $totalMaterialsCount) * 100) : 0;

        $pdfCount = $allSessionMaterials->where('type', 'pdf')->count();
        $videoCount = $allSessionMaterials->where('type', 'video')->count();
        $imageCount = $allSessionMaterials->where('type', 'image')->count();
        $notesCount = $allSessionMaterials->where('type', 'description')->count();

        // Material Type Breakdown for modal
        $typeKeys = ['pdf' => 'PDF Documents', 'video' => 'Video Lessons', 'image' => 'Diagrams & Images', 'description' => 'Notes & Descriptions'];
        $typeBreakdown = collect($typeKeys)->map(function ($typeName, $typeKey) use ($allSessionMaterials, $totalMaterialsCount) {
            $typeItems = $allSessionMaterials->where('type', $typeKey)->values();
            $classesUsing = $typeItems->pluck('schoolClass.name')->filter()->unique()->values()->all();

            return [
                'type' => $typeKey,
                'name' => $typeName,
                'count' => $typeItems->count(),
                'percentage' => $totalMaterialsCount > 0 ? round(($typeItems->count() / $totalMaterialsCount) * 100, 1) : 0,
                'classes' => $classesUsing,
                'materials' => $typeItems,
            ];
        })->values();

        // Class Breakdown for modal
        $classBreakdown = $classes->map(function ($cls) use ($allSessionMaterials) {
            $clsMaterials = $allSessionMaterials->where('school_class_id', $cls->id)->values();
            $pdf = $clsMaterials->where('type', 'pdf')->count();
            $video = $clsMaterials->where('type', 'video')->count();
            $image = $clsMaterials->where('type', 'image')->count();
            $notes = $clsMaterials->where('type', 'description')->count();

            return [
                'id' => $cls->id,
                'name' => $cls->name,
                'count' => $clsMaterials->count(),
                'pdf_count' => $pdf,
                'video_count' => $video,
                'image_count' => $image,
                'notes_count' => $notes,
                'materials' => $clsMaterials,
            ];
        });

        $analytics = [
            'totalMaterials' => $totalMaterialsCount,
            'totalClasses' => $totalClassesCount,
            'classesWithMaterials' => $classesWithMaterialsCount,
            'classCoverageRate' => $classCoverageRate,
            'avgMaterialsPerClass' => $avgMaterialsPerClass,
            'withFilesCount' => $withFilesCount,
            'textOnlyCount' => $textOnlyCount,
            'digitalResourceRate' => $digitalResourceRate,
            'pdfCount' => $pdfCount,
            'videoCount' => $videoCount,
            'imageCount' => $imageCount,
            'notesCount' => $notesCount,
            'typeBreakdown' => $typeBreakdown,
            'classBreakdown' => $classBreakdown,
        ];

        // ── Main Query with Filters ──
        $query = StudyMaterial::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('book_name', 'like', "%{$kw}%")
                    ->orWhere('title', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        // For grouped view: all matching materials grouped by class
        $filteredAllMaterials = (clone $query)->orderByDesc('created_at')->get();
        $materialsByClass = $filteredAllMaterials->groupBy(fn ($item) => $item->schoolClass?->name ?? 'Unassigned');

        // Pagination for single table view
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $materials = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('sm-academic.study-materials.index', compact('school', 'session', 'materials', 'classes', 'materialsByClass', 'analytics', 'perPage'));
    }

    public function create()
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = SchoolClass::with('sections')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('sm-academic.study-materials.create', compact('school', 'session', 'classes'));
    }

    public function store(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'book_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'type' => 'required|in:pdf,image,video,description',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200', // 50MB max
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('study-materials', 'public');
        }

        StudyMaterial::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'book_name' => $validated['book_name'],
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('academic.study-materials.index')->with('success', 'Study material uploaded successfully.');
    }

    public function update(Request $request, StudyMaterial $studyMaterial)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($studyMaterial->school_id !== $school->id, 403);

        $validated = $request->validate([
            'book_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'type' => 'required|in:pdf,image,video,description',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200', // 50MB max
        ]);

        if ($request->hasFile('file')) {
            if ($studyMaterial->file_path && !str_starts_with($studyMaterial->file_path, 'http')) {
                Storage::disk('public')->delete($studyMaterial->file_path);
            }
            $studyMaterial->file_path = $request->file('file')->store('study-materials', 'public');
        } elseif ($request->type === 'description' && $studyMaterial->file_path && !str_starts_with($studyMaterial->file_path, 'http')) {
            // Delete file if changed to description only
            Storage::disk('public')->delete($studyMaterial->file_path);
            $studyMaterial->file_path = null;
        }

        $studyMaterial->update([
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'book_name' => $validated['book_name'],
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('academic.study-materials.index')->with('success', 'Study material updated successfully.');
    }

    public function destroy(StudyMaterial $studyMaterial)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($studyMaterial->school_id !== $school->id, 403);

        if ($studyMaterial->file_path) {
            Storage::disk('public')->delete($studyMaterial->file_path);
        }

        $studyMaterial->delete();

        return redirect()->route('academic.study-materials.index')->with('success', 'Study material deleted.');
    }

    /** Return sections for a class (AJAX) */
    public function getSections(SchoolClass $schoolClass)
    {
        return response()->json($schoolClass->sections()->orderBy('name')->get(['id', 'name']));
    }

    public function export(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = StudyMaterial::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('book_name', 'like', "%{$kw}%")
                    ->orWhere('title', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        $materials = $query->latest()->get();
        $filename = "study_materials_{$session->name}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Book Name', 'Title / Topic', 'Class Name', 'Section Name', 'Material Type (pdf/image/video/description)', 'Description', 'File URL'];

        $callback = function() use ($materials, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($materials as $m) {
                fputcsv($file, [
                    $m->book_name,
                    $m->title,
                    $m->schoolClass?->name,
                    $m->section?->name,
                    $m->type,
                    $m->description,
                    $m->file_path ? (str_starts_with($m->file_path, 'http') ? $m->file_path : url(Storage::url($m->file_path))) : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function importDemo()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=study_materials_demo.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = ['Book Name', 'Title / Topic', 'Class Name', 'Section Name', 'Material Type (pdf/image/video/description)', 'Description', 'File URL'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Mathematics Part-1', 'Chapter 1', 'Class 1', 'A', 'pdf', 'Read carefully', 'https://example.com/math1.pdf']);
            fputcsv($file, ['Science Notes', 'Biology', 'Class 2', '', 'description', 'Study these notes.', '']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|extensions:csv,txt|max:2048'
        ]);

        [$school, $session] = $this->getSchoolAndSession();

        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data);

        // Map header to indices
        $map = [];
        foreach ($header as $i => $col) {
            $colName = strtolower(preg_replace('/[^a-zA-Z]/', '', $col));
            if (str_contains($colName, 'bookname')) $map['book'] = $i;
            elseif (str_contains($colName, 'title')) $map['title'] = $i;
            elseif (str_contains($colName, 'classname')) $map['class'] = $i;
            elseif (str_contains($colName, 'section')) $map['section'] = $i;
            elseif (str_contains($colName, 'materialtype') || str_contains($colName, 'type')) $map['type'] = $i;
            elseif (str_contains($colName, 'description') || str_contains($colName, 'notes')) $map['description'] = $i;
            elseif (str_contains($colName, 'fileurl') || str_contains($colName, 'url')) $map['url'] = $i;
        }

        $errors = [];
        $importedCount = 0;

        if (!isset($map['book']) || !isset($map['class']) || !isset($map['type'])) {
            return back()->with('import_errors', ["CSV is missing required headers (Book Name, Class Name, Material Type)."]);
        }

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            
            if (count(array_filter($row)) === 0) continue;

            $bookName = isset($map['book'], $row[$map['book']]) ? trim($row[$map['book']]) : '';
            $title = isset($map['title'], $row[$map['title']]) ? trim($row[$map['title']]) : '';
            $className = isset($map['class'], $row[$map['class']]) ? trim($row[$map['class']]) : '';
            $sectionName = isset($map['section'], $row[$map['section']]) ? trim($row[$map['section']]) : '';
            $type = isset($map['type'], $row[$map['type']]) ? trim($row[$map['type']]) : '';
            $description = isset($map['description'], $row[$map['description']]) ? trim($row[$map['description']]) : '';
            $url = isset($map['url'], $row[$map['url']]) ? trim($row[$map['url']]) : '';

            if (empty($bookName) || empty($className) || empty($type)) {
                $errors[] = "Row $rowNum is missing required fields (Book Name, Class Name, Material Type).";
                continue;
            }

            $type = strtolower($type);
            if (!in_array($type, ['pdf', 'image', 'video', 'description'])) {
                $errors[] = "Row $rowNum has invalid type ($type). Must be pdf, image, video, or description.";
                continue;
            }

            $schoolClass = SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('name', $className)
                ->first();

            if (!$schoolClass) {
                $errors[] = "Row $rowNum: Class '$className' not found in current session.";
                continue;
            }

            $sectionId = null;
            if (!empty($sectionName)) {
                $section = \App\Models\Section::where('school_class_id', $schoolClass->id)
                    ->where('name', $sectionName)
                    ->first();
                if (!$section) {
                    $errors[] = "Row $rowNum: Section '$sectionName' not found in class '$className'.";
                    continue;
                }
                $sectionId = $section->id;
            }

            StudyMaterial::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $sectionId,
                'book_name' => $bookName,
                'title' => $title,
                'type' => $type,
                'description' => $description,
                'file_path' => $url ?: null
            ]);

            $importedCount++;
        }

        if (count($errors) > 0) {
            return back()->with('success', "$importedCount materials imported successfully.")->with('import_errors', $errors);
        }

        return back()->with('success', "$importedCount materials imported successfully.");
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\StudyMaterial::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}