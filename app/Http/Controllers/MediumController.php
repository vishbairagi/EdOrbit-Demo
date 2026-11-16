<?php

namespace App\Http\Controllers;

use App\Models\Medium;
use App\Models\School;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediumController extends Controller
{
    private function getSchool(): School
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');

        return School::findOrFail($schoolId);
    }

    public function index(Request $request)
    {
        $school = $this->getSchool();
        $query = Medium::where('school_id', $school->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $mediums = $query->orderBy('name')->get();

        return view('sm-academic.mediums.index', compact('school', 'mediums'));
    }

    public function store(Request $request)
    {
        $school = $this->getSchool();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        $validated['school_id'] = $school->id;
        $validated['is_active'] = true;
        Medium::create($validated);

        return redirect()->route('academic.mediums.index')->with('success', 'Medium created successfully.');
    }

    public function update(Request $request, Medium $medium)
    {
        $school = $this->getSchool();
        abort_if($medium->school_id !== $school->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $medium->update($validated);

        return redirect()->route('academic.mediums.index')->with('success', 'Medium updated successfully.');
    }

    public function updateStatus(Request $request, Medium $medium)
    {
        $school = $this->getSchool();
        abort_if($medium->school_id !== $school->id, 403);

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);
        $medium->update(['is_active' => $validated['is_active']]);

        return back()->with('success', 'Medium status updated.');
    }

    public function destroy(Medium $medium)
    {
        $school = $this->getSchool();
        abort_if($medium->school_id !== $school->id, 403);
        $medium->delete();

        return redirect()->route('academic.mediums.index')->with('success', 'Medium deleted.');
    }

    /** Export all mediums as CSV */
    public function export(): StreamedResponse
    {
        $school = $this->getSchool();
        $mediums = Medium::where('school_id', $school->id)->orderBy('name')->get();

        $filename = 'mediums_export_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($mediums) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Medium Name', 'Description', 'Status']);
            foreach ($mediums as $medium) {
                fputcsv($file, [
                    $medium->name,
                    $medium->description ?? '',
                    $medium->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download sample CSV for mediums import */
    public function importDemo(): StreamedResponse
    {
        $filename = 'mediums_sample.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Medium Name', 'Description', 'Status']);
            fputcsv($file, ['English',     'English Medium',    'Active']);
            fputcsv($file, ['Hindi',       'Hindi Medium',      'Active']);
            fputcsv($file, ['Urdu',        'Urdu Medium',       'Active']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import mediums from uploaded CSV */
    public function import(Request $request)
    {
        $school = $this->getSchool();

        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('csv_file')->path(), 'r');
        fgetcsv($handle); // skip header

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            if (empty($name)) { $skipped++; continue; }

            $desc     = trim($row[1] ?? '') ?: null;
            $isActive = strtolower(trim($row[2] ?? 'active')) !== 'inactive';

            Medium::updateOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                ['description' => $desc, 'is_active' => $isActive]
            );
            $imported++;
        }
        fclose($handle);

        return redirect()->route('academic.mediums.index')
            ->with('success', "{$imported} medium(s) imported. {$skipped} skipped.");
    }
    public function bulkDestroy(Request $request)
    {
        $school = $this->getSchool();
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = Medium::where('school_id', $school->id)->whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted medium(s) deleted successfully.");
    }
}