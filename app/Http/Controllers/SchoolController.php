<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\StudentCategory;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('registration_number', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $schools = $query->latest()->paginate(10);

        return view('school-management.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('school-management.schools.create');
    }

    public function store(StoreSchoolRequest $request)
    {
        $school = School::create($request->validated());

        // Create a default academic session for the new school
        $year = now()->year;
        $session = AcademicSession::create([
            'name'       => $year . '-' . ($year + 1),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date'   => now()->endOfYear()->toDateString(),
            'status'     => 'active',
        ]);

        $school->studentCategories()->createMany(array_map(
            fn (string $name): array => ['name' => $name, 'academic_session_id' => $session->id],
            StudentCategory::DEFAULT_NAMES,
        ));

        return redirect()->route('school-management.schools.index')->with('success', 'School created successfully.');
    }

    public function show(School $school)
    {
        return view('school-management.schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        return view('school-management.schools.edit', compact('school'));
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $school->update($request->validated());

        return redirect()->route('school-management.schools.index')->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {
        $school->delete();

        return redirect()->route('school-management.schools.index')->with('success', 'School deleted successfully.');
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\School::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}