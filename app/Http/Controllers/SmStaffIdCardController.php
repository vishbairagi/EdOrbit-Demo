<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmStaffIdCardController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $staff = Staff::with('role')
            ->where('school_id', $schoolId)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        return view('sm-administrator.id-cards.index', compact('staff'));
    }

    public function print(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $staffIds = array_filter((array) $request->input('staff_ids', []));
        $printAll = $request->boolean('all');

        $query = Staff::where('school_id', $schoolId)->with(['school', 'role']);

        if (! $printAll && ! empty($staffIds)) {
            $query->whereIn('id', $staffIds);
        }

        $cardBuilder = app(PrintIdCardController::class);
        $cards = $query->where('status', 'Active')
            ->orderBy('name')
            ->get()
            ->map(fn (Staff $member) => $cardBuilder->staffCard($member))
            ->all();

        return view('student.print-id-cards.print', [
            'cards' => $cards,
            'title' => 'Staff ID Cards',
        ]);
    }
}
