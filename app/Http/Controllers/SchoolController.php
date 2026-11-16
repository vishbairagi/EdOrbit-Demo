<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\StudentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }

        $school = School::create($data);

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
        $data = $request->validated();

        if ($request->boolean('remove_logo')) {
            if ($school->logo && !filter_var($school->logo, FILTER_VALIDATE_URL)) {
                if (str_starts_with($school->logo, 'uploads/')) {
                    @unlink(public_path($school->logo));
                } else {
                    Storage::disk('public')->delete($school->logo);
                }
            }
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($school->logo && !filter_var($school->logo, FILTER_VALIDATE_URL)) {
                if (str_starts_with($school->logo, 'uploads/')) {
                    @unlink(public_path($school->logo));
                } else {
                    Storage::disk('public')->delete($school->logo);
                }
            }
            $data['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }

        $school->update($data);

        return redirect()->route('school-management.schools.index')->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {
        if ($school->logo && !filter_var($school->logo, FILTER_VALIDATE_URL)) {
            if (str_starts_with($school->logo, 'uploads/')) {
                @unlink(public_path($school->logo));
            } else {
                Storage::disk('public')->delete($school->logo);
            }
        }

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