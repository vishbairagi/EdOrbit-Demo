<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\LibraryBook;
use App\Models\LibraryBookIssue;
use App\Models\LibraryCard;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    private function currentSchool(): ?School
    {
        $id = session('selected_school_id');

        return $id ? School::find($id) : null;
    }

    private function currentSession(): ?AcademicSession
    {
        $id = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');

        return $id ? AcademicSession::find($id) : null;
    }

    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $schoolId = $school?->id;

        $totalBooks = $schoolId ? LibraryBook::where('school_id', $schoolId)->when($session, fn($q) => $q->where('academic_session_id', $session->id))->count() : 0;
        $totalIssued = $schoolId ? LibraryBookIssue::where('school_id', $schoolId)->when($session, fn($q) => $q->where('academic_session_id', $session->id))->where('status', 'issued')->count() : 0;
        $totalCards = $schoolId ? LibraryCard::where('school_id', $schoolId)->when($session, fn($q) => $q->where('academic_session_id', $session->id))->count() : 0;
        $totalReturned = $schoolId ? LibraryBookIssue::where('school_id', $schoolId)->when($session, fn($q) => $q->where('academic_session_id', $session->id))->where('status', 'returned')->count() : 0;

        return view('library.dashboard', compact(
            'school', 'session', 'totalBooks', 'totalIssued', 'totalCards', 'totalReturned'
        ));
    }

    // ── All Books ─────────────────────────────────────────────────────────

    public function books(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $books = $school
            ? LibraryBook::where('school_id', $school->id)->when($session, fn($q) => $q->where('academic_session_id', $session->id))->withCount(['activeIssues'])->latest()->paginate(15)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        return view('library.books', compact('school', 'books'));
    }

    public function createBook(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $classes = $school && $session
            ? SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->orderBy('name')
                ->get()
            : collect();

        return view('library.books-create', compact('school', 'session', 'classes'));
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();

        $validated = $request->validate([
            'book_type' => 'required|in:individual,class_wise',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_number' => 'nullable|string|max:100',
            'book_number' => 'nullable|string|max:100',
            'isbn_number' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $validated['school_id'] = $school?->id;
        $validated['academic_session_id'] = $this->currentSession()?->id;
        $validated['class_ids'] = $validated['class_ids'] ?? null;
        $validated['subject_ids'] = $validated['subject_ids'] ?? null;

        // For individual books, clear class/subject assignment
        if ($validated['book_type'] === 'individual') {
            $validated['class_ids'] = null;
            $validated['subject_ids'] = null;
        }

        LibraryBook::create($validated);

        return redirect()->route('library.books')->with('success', 'Book added successfully.');
    }

    public function editBook(LibraryBook $book): View
    {
        return view('library.books-edit', compact('book'));
    }

    public function updateBook(Request $request, LibraryBook $book): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_number' => 'nullable|string|max:100',
            'book_number' => 'nullable|string|max:100',
            'isbn_number' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $book->update($validated);

        return redirect()->route('library.books')->with('success', 'Book updated successfully.');
    }

    public function destroyBook(LibraryBook $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('library.books')->with('success', 'Book deleted successfully.');
    }

    public function bulkDestroyBooks(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            LibraryBook::whereIn('id', $ids)->delete();
            return redirect()->route('library.books')->with('success', count($ids) . ' books deleted successfully.');
        }
        return redirect()->route('library.books')->with('error', 'No books selected for deletion.');
    }

    // ── Book Issue ────────────────────────────────────────────────────────

    public function bookIssue(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        // All issues (both pending & returned), newest first
        $issues = $school
            ? LibraryBookIssue::with(['book', 'student.schoolClass', 'student.section'])
                ->where('school_id', $school->id)
                ->when($session, fn($q) => $q->where('academic_session_id', $session->id))
                ->latest()
                ->paginate(25)
            : collect();

        $classes = $school && $session
            ? SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->orderBy('name')
                ->get()
            : collect();

        $books = $school
            ? LibraryBook::where('school_id', $school->id)->when($session, fn($q) => $q->where('academic_session_id', $session->id))->orderBy('title')->get()
            : collect();

        return view('library.book-issue', compact('school', 'session', 'issues', 'classes', 'books'));
    }

    public function storeIssue(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();

        $validated = $request->validate([
            'book_id' => 'required|exists:library_books,id',
            'student_id' => 'required|exists:students,id',
            'issue_date' => 'required|date',
            'return_due_date' => 'required|date|after_or_equal:issue_date',
        ]);

        $book = LibraryBook::findOrFail($validated['book_id']);
        if ($book->available <= 0) {
            return back()->with('error', 'No copies available for this book.')->withInput();
        }

        $validated['school_id'] = $school?->id;
        $validated['academic_session_id'] = $this->currentSession()?->id;
        $validated['status'] = 'issued';

        LibraryBookIssue::create($validated);

        return redirect()->route('library.book-issue')->with('success', 'Book issued successfully.');
    }

    public function returnBook(Request $request, LibraryBookIssue $issue): RedirectResponse
    {
        $validated = $request->validate([
            'actual_return_date' => 'required|date',
            'return_note' => 'nullable|string|max:1000',
        ]);

        $issue->update([
            'actual_return_date' => $validated['actual_return_date'],
            'return_note' => $validated['return_note'],
            'status' => 'returned',
        ]);

        return redirect()->route('library.book-issue')->with('success', 'Book returned successfully.');
    }

    /** AJAX – get sections for a class */
    public function getSections(SchoolClass $schoolClass): JsonResponse
    {
        return response()->json($schoolClass->sections()->select('id', 'name')->get());
    }

    /** AJAX – get subjects for one or more class IDs (used in Add Book form) */
    public function getSubjectsForClasses(Request $request): JsonResponse
    {
        $classIds = $request->input('class_ids', []);
        if (empty($classIds)) {
            return response()->json([]);
        }

        $subjects = Subject::whereIn('school_class_id', $classIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'school_class_id']);

        // Group by class for cleaner display
        $grouped = $subjects->groupBy('school_class_id')->map(function ($items, $classId) {
            return $items->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'class_id' => $classId,
            ]);
        });

        return response()->json([
            'subjects' => $subjects->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'class_id' => $s->school_class_id,
            ]),
        ]);
    }

    /** AJAX – get students for class + section */
    public function getStudents(Request $request): JsonResponse
    {
        $session = $this->currentSession();
        $school = $this->currentSchool();
        
        $query = Student::query()
            ->when($request->class_id, fn ($q) => $q->where('school_class_id', $request->class_id))
            ->when($request->section_id, fn ($q) => $q->where('section_id', $request->section_id))
            ->when($session, fn ($q) => $q->where('academic_session_id', $session->id))
            ->when($request->without_library_card, function ($q) use ($school) {
                $q->whereDoesntHave('libraryCards', function ($q2) use ($school) {
                    $q2->where('school_id', $school?->id);
                });
            });

        return response()->json($query->select('id', 'first_name', 'last_name', 'admission_no')->get()->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->first_name.' '.$s->last_name.' ('.$s->admission_no.')',
        ]));
    }

    // ── Library Cards ─────────────────────────────────────────────────────

    public function libraryCards(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $cards = $school
            ? LibraryCard::with(['student.schoolClass', 'student.section'])
                ->where('school_id', $school->id)
                ->when($session, fn($q) => $q->where('academic_session_id', $session->id))
                ->latest()
                ->paginate(25)
            : collect();

        $classes = $school && $session
            ? SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->orderBy('name')
                ->get()
            : collect();

        return view('library.library-cards', compact('school', 'session', 'cards', 'classes'));
    }

    public function generateCards(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $validated = $request->validate([
            'generation_type' => 'required|in:single,bulk',
            'prefix' => 'required|string|max:20',
            'starting_number' => 'required|integer|min:1',
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'student_id' => 'nullable|exists:students,id',
            'date_issued' => 'required|date',
        ]);

        if ($validated['generation_type'] === 'single') {
            if (empty($validated['student_id'])) {
                return back()->with('error', 'Student is required for single generation.')->withInput();
            }
            $students = Student::where('id', $validated['student_id'])->get();
        } else {
            $query = Student::where('school_class_id', $validated['class_id'])
                ->when($validated['section_id'], fn ($q) => $q->where('section_id', $validated['section_id']))
                ->when($session, fn ($q) => $q->where('academic_session_id', $session->id))
                ->when($school, fn ($q) => $q->where('school_id', $school->id));
            $students = $query->get();
        }

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for the selected criteria.')->withInput();
        }

        $counter = (int) $validated['starting_number'];
        $created = 0;

        foreach ($students as $student) {
            $cardNumber = $validated['prefix'].str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Skip if card already exists for student in this school
            $exists = LibraryCard::where('school_id', $school?->id)
                ->where('student_id', $student->id)
                ->exists();

            if (! $exists) {
                try {
                    LibraryCard::create([
                        'school_id' => $school?->id,
                        'academic_session_id' => $session?->id,
                        'student_id' => $student->id,
                        'card_number' => $cardNumber,
                        'prefix' => $validated['prefix'],
                        'date_issued' => $validated['date_issued'],
                    ]);
                    $created++;
                } catch (\Exception) {
                    // skip duplicate card numbers
                }
            }

            $counter++;
        }

        if ($created > 0) {
            return redirect()->route('library.library-cards')->with('success', "{$created} library card(s) generated successfully.");
        } else {
            return redirect()->route('library.library-cards')->with('error', 'No cards generated. Selected student(s) may already have a library card.');
        }
    }

    public function destroyCard(LibraryCard $card): RedirectResponse
    {
        $card->delete();

        return redirect()->route('library.library-cards')->with('success', 'Library card deleted.');
    }

    public function bulkDestroyCards(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            LibraryCard::whereIn('id', $ids)->delete();
            return redirect()->route('library.library-cards')->with('success', count($ids) . ' library card(s) deleted successfully.');
        }
        return redirect()->route('library.library-cards')->with('error', 'No cards selected for deletion.');
    }
}
