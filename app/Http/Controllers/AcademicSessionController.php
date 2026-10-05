<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicSessionRequest;
use App\Http\Requests\UpdateAcademicSessionRequest;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicSession::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $sessions = $query->latest()->paginate(10);

        return view('school-management.sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('school-management.sessions.create');
    }

    public function store(StoreAcademicSessionRequest $request)
    {
        AcademicSession::create($request->validated());

        return redirect()->route('school-management.sessions.index')->with('success', 'Session created successfully.');
    }

    public function show(AcademicSession $academic_session)
    {
        return view('school-management.sessions.show', compact('academic_session'));
    }

    public function edit(AcademicSession $academic_session)
    {
        return view('school-management.sessions.edit', compact('academic_session'));
    }

    public function update(UpdateAcademicSessionRequest $request, AcademicSession $academic_session)
    {
        $academic_session->update($request->validated());

        return redirect()->route('school-management.sessions.index')->with('success', 'Session updated successfully.');
    }

    public function destroy(AcademicSession $academic_session)
    {
        $academic_session->delete();

        return redirect()->route('school-management.sessions.index')->with('success', 'Session deleted successfully.');
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\AcademicSession::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}