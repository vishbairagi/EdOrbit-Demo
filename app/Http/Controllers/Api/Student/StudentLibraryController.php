<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LibraryBookIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentLibraryController extends BaseApiController
{
    /**
     * Books Issued to Student List API.
     */
    public function booksIssued(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $issues = LibraryBookIssue::with(['book'])
            ->where('student_id', $student->id)
            ->get()
            ->map(function ($issue) {
                $issueDate = $issue->issue_date instanceof \DateTimeInterface ? $issue->issue_date->format('Y-m-d') : (string) $issue->issue_date;
                $dueDate = $issue->due_date instanceof \DateTimeInterface ? $issue->due_date->format('Y-m-d') : (string) $issue->due_date;
                $returnDate = $issue->return_date instanceof \DateTimeInterface ? $issue->return_date->format('Y-m-d') : ($issue->return_date ? (string) $issue->return_date : null);

                return [
                    'id' => $issue->id,
                    'book_id' => $issue->book_id,
                    'book_title' => $issue->book?->title ?? 'Library Reference Book',
                    'author' => $issue->book?->author ?? 'Editorial Board',
                    'isbn' => $issue->book?->isbn ?? '',
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'return_date' => $returnDate,
                    'status' => $issue->status ?? 'issued',
                    'fine_amount' => (float) ($issue->fine_amount ?? 0),
                ];
            });

        return $this->success($request, ['books_issued' => $issues], 'Books issued retrieved successfully');
    }

    /**
     * Single Issued Book Details API.
     */
    public function bookIssuedDetails(Request $request, $id): JsonResponse
    {
        $student = $this->getStudent($request);

        $issue = LibraryBookIssue::with(['book'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $issueDate = $issue->issue_date instanceof \DateTimeInterface ? $issue->issue_date->format('Y-m-d') : (string) $issue->issue_date;
        $dueDate = $issue->due_date instanceof \DateTimeInterface ? $issue->due_date->format('Y-m-d') : (string) $issue->due_date;
        $returnDate = $issue->return_date instanceof \DateTimeInterface ? $issue->return_date->format('Y-m-d') : ($issue->return_date ? (string) $issue->return_date : null);

        $data = [
            'id' => $issue->id,
            'book_id' => $issue->book_id,
            'book_title' => $issue->book?->title ?? 'Library Reference Book',
            'author' => $issue->book?->author ?? 'Editorial Board',
            'publisher' => $issue->book?->publisher ?? '',
            'isbn' => $issue->book?->isbn ?? '',
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'return_date' => $returnDate,
            'status' => $issue->status ?? 'issued',
            'fine_amount' => (float) ($issue->fine_amount ?? 0),
            'remarks' => $issue->remarks ?? '',
        ];

        return $this->success($request, ['book_issue' => $data], 'Book issue details retrieved successfully');
    }
}
