<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LibraryBook;
use App\Models\LibraryBookIssue;
use App\Models\LibraryCard;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryApiController extends BaseApiController
{
    // ==========================================
    // 1. BOOKS CRUD (library.books)
    // ==========================================

    public function booksIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = LibraryBook::withCount('activeIssues')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('book_number', 'like', "%{$search}%")
                  ->orWhere('isbn_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->orderBy('title')->get(), 'Books retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->orderBy('title')->paginate($perPage), 'Books retrieved');
    }

    public function bookStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_number' => 'nullable|string|max:50',
            'book_number' => 'nullable|string|max:50',
            'isbn_number' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'class_ids' => 'nullable|array',
            'subject_ids' => 'nullable|array',
        ]);

        $book = LibraryBook::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'title' => $validated['title'],
            'author' => $validated['author'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'rack_number' => $validated['rack_number'] ?? null,
            'book_number' => $validated['book_number'] ?? null,
            'isbn_number' => $validated['isbn_number'] ?? null,
            'price' => $validated['price'] ?? 0,
            'quantity' => $validated['quantity'],
            'description' => $validated['description'] ?? null,
            'class_ids' => $validated['class_ids'] ?? null,
            'subject_ids' => $validated['subject_ids'] ?? null,
        ]);

        return $this->success($request, $book, 'Book added to library successfully', 201);
    }

    public function bookShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $book = LibraryBook::with(['activeIssues.student'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $book) {
            return $this->error('Book not found.', 404);
        }

        return $this->success($request, $book, 'Book details retrieved');
    }

    public function bookUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $book = LibraryBook::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $book) {
            return $this->error('Book not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_number' => 'nullable|string|max:50',
            'book_number' => 'nullable|string|max:50',
            'isbn_number' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'class_ids' => 'nullable|array',
            'subject_ids' => 'nullable|array',
        ]);

        $book->update($validated);

        return $this->success($request, $book, 'Book updated successfully');
    }

    public function bookDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $book = LibraryBook::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $book) {
            return $this->error('Book not found.', 404);
        }

        $book->issues()->delete();
        $book->delete();

        return $this->success($request, null, 'Book removed from library successfully');
    }

    // ==========================================
    // 2. BOOK ISSUE (library.book-issue)
    // ==========================================

    public function bookIssuesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = LibraryBookIssue::with(['book', 'student.schoolClass', 'student.section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($studentId = $request->input('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($bookId = $request->input('book_id')) {
            $query->where('book_id', $bookId);
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Book issues retrieved');
    }

    public function bookIssueStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'book_id' => 'required|exists:library_books,id',
            'student_id' => 'required|exists:students,id',
            'issue_date' => 'required|date',
            'return_due_date' => 'required|date|after_or_equal:issue_date',
        ]);

        $book = LibraryBook::where('school_id', $school->id)->findOrFail($validated['book_id']);

        if ($book->available <= 0) {
            return $this->error('No copies of this book are currently available for issue.', 400);
        }

        $issue = LibraryBookIssue::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'book_id' => $book->id,
            'student_id' => $validated['student_id'],
            'issue_date' => $validated['issue_date'],
            'return_due_date' => $validated['return_due_date'],
            'status' => 'issued',
        ]);

        $issue->load(['book', 'student']);

        return $this->success($request, $issue, 'Book issued to student successfully', 201);
    }

    public function bookIssueReturn(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);

        $issue = LibraryBookIssue::where('school_id', $school?->id)->findOrFail($id);

        $validated = $request->validate([
            'actual_return_date' => 'nullable|date',
            'return_note' => 'nullable|string|max:500',
        ]);

        $issue->update([
            'actual_return_date' => $validated['actual_return_date'] ?? date('Y-m-d'),
            'return_note' => $validated['return_note'] ?? null,
            'status' => 'returned',
        ]);

        $issue->load(['book', 'student']);

        return $this->success($request, $issue, 'Book marked as returned successfully');
    }

    public function bookIssueDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);

        $issue = LibraryBookIssue::where('school_id', $school?->id)->findOrFail($id);
        $issue->delete();

        return $this->success($request, null, 'Book issue record deleted successfully');
    }

    // ==========================================
    // 3. LIBRARY CARDS (library.library-cards)
    // ==========================================

    public function libraryCardsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = LibraryCard::with(['student.schoolClass', 'student.section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('card_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Library cards retrieved');
    }

    public function libraryCardStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'card_number' => 'nullable|string|max:50',
            'prefix' => 'nullable|string|max:20',
            'date_issued' => 'nullable|date',
        ]);

        $prefix = $validated['prefix'] ?? 'LIB-';
        $cardNumber = $validated['card_number'] ?? null;

        if (! $cardNumber) {
            $count = LibraryCard::where('school_id', $school->id)->count() + 1;
            $cardNumber = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $card = LibraryCard::updateOrCreate([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'student_id' => $validated['student_id'],
        ], [
            'card_number' => $cardNumber,
            'prefix' => $prefix,
            'date_issued' => $validated['date_issued'] ?? date('Y-m-d'),
        ]);

        $card->load(['student.schoolClass', 'student.section']);

        return $this->success($request, $card, 'Library card issued successfully', 201);
    }

    public function libraryCardDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);

        $card = LibraryCard::where('school_id', $school?->id)->findOrFail($id);
        $card->delete();

        return $this->success($request, null, 'Library card deleted successfully');
    }
}
