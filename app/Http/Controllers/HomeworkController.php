<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Homework;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolUser;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HomeworkController extends Controller
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

    private function resolveAddedBy(): string
    {
        $guard = session('auth_guard');
        $userId = session('auth_user_id');

        if ($guard === 'super_admin') {
            $data = session('super_admin_data');
            return $data['name'] ?? 'Super Admin';
        }

        if ($guard === 'school_user' && $userId) {
            $schoolUser = SchoolUser::find($userId);
            if ($schoolUser) {
                return $schoolUser->name . ($schoolUser->role ? ' (' . $schoolUser->role . ')' : '');
            }
        }

        if ($guard === 'staff' && $userId) {
            $staff = Staff::find($userId);
            if ($staff) {
                return $staff->name;
            }
        }

        if (auth()->check() && auth()->user()?->name) {
            return auth()->user()->name;
        }

        return 'Teacher';
    }

    private function detectFileType(string $extension, string $mime = ''): string
    {
        $ext = strtolower($extension);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'])) {
            return 'image';
        }
        if (in_array($ext, ['mp4', 'webm', 'mov', 'mkv', 'avi', '3gp', 'flv'])) {
            return 'video';
        }
        if ($ext === 'pdf') {
            return 'pdf';
        }
        if (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp'])) {
            return 'doc';
        }
        if (in_array($ext, ['txt', 'csv', 'rtf', 'log', 'json'])) {
            return 'text';
        }
        if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return 'archive';
        }

        return 'other';
    }

    public function index(Request $request): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = Homework::with(['schoolClass', 'section', 'subject'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('homework_date', $request->date);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'overdue') {
                $query->where('due_date', '<', now()->toDateString());
            } elseif ($status === 'active') {
                $query->where('due_date', '>=', now()->toDateString());
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('added_by', 'like', "%{$search}%")
                  ->orWhereHas('schoolClass', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('section', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('subject', function ($subq) use ($search) {
                      $subq->where('name', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $homeworks = $query->latest('id')->paginate($perPage)->withQueryString();

        // Classes with sections & subjects for current school & session
        $classes = SchoolClass::with([
                'sections' => fn ($q) => $q->orderBy('name'),
                'subjects' => fn ($q) => $q->where('school_id', $school->id)->where('academic_session_id', $session->id)->orderBy('name'),
            ])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // ── Analytics for Top Header Cards (matching /academic/class-teachers) ──
        $allSessionHomeworks = Homework::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        $totalHomeworkCount = $allSessionHomeworks->count();
        $totalClassesCount = $classes->count();
        $classesCoveredCount = $classes->filter(fn ($c) => $allSessionHomeworks->contains('school_class_id', $c->id))->count();
        $classCoveragePercent = $totalClassesCount > 0 ? (int) round(($classesCoveredCount / $totalClassesCount) * 100) : 0;

        $totalSubjectsCount = Subject::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->count();
        $subjectsCoveredCount = $allSessionHomeworks->pluck('subject_id')->filter()->unique()->count();
        $subjectCoveragePercent = $totalSubjectsCount > 0 ? (int) round(($subjectsCoveredCount / $totalSubjectsCount) * 100) : 0;

        $activeHomeworkCount = $allSessionHomeworks->filter(fn ($h) => $h->due_date && $h->due_date->endOfDay()->isFuture())->count();
        $activeRatePercent = $totalHomeworkCount > 0 ? (int) round(($activeHomeworkCount / $totalHomeworkCount) * 100) : 0;

        $overdueCount = $allSessionHomeworks->filter(fn ($h) => $h->due_date && $h->due_date->endOfDay()->isPast())->count();
        $withAttachmentsCount = $allSessionHomeworks->filter(fn ($h) => !empty($h->attachments) || !empty($h->attachment_url))->count();

        // Teacher/creator breakdown
        $creatorBreakdown = $allSessionHomeworks->groupBy('added_by')->map(function ($items, $name) {
            return [
                'name' => $name ?: 'Teacher',
                'count' => $items->count(),
            ];
        })->sortByDesc('count')->values();

        return view('sm-academic.homework.index', compact(
            'school',
            'session',
            'homeworks',
            'classes',
            'totalHomeworkCount',
            'classesCoveredCount',
            'totalClassesCount',
            'classCoveragePercent',
            'totalSubjectsCount',
            'subjectsCoveredCount',
            'subjectCoveragePercent',
            'activeHomeworkCount',
            'activeRatePercent',
            'overdueCount',
            'withAttachmentsCount',
            'creatorBreakdown',
            'perPage'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'homework_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:homework_date',
            'description' => 'nullable|string',
            'attachment_url' => 'nullable|url|max:2048',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,bmp,mp4,webm,mov,mkv,avi,txt,csv,rtf,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:102400',
        ], [
            'subject_id.required' => 'Please select a subject for the homework.',
            'due_date.after_or_equal' => 'The submission due date must be on or after the assigned date.',
        ]);

        $storedAttachments = [];

        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (! is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $ext = $file->getClientOriginalExtension();
                    $mime = $file->getClientMimeType() ?: '';
                    $size = $file->getSize();
                    $type = $this->detectFileType($ext, $mime);

                    $folder = "homeworks/{$school->id}/{$session->id}";
                    $storedPath = $file->store($folder, 'public');

                    $storedAttachments[] = [
                        'id' => (string) Str::uuid(),
                        'original_name' => $originalName,
                        'path' => $storedPath,
                        'url' => asset('storage/' . $storedPath),
                        'size' => round($size / 1024, 1) . ' KB',
                        'size_bytes' => $size,
                        'mime' => $mime,
                        'extension' => strtolower($ext),
                        'type' => $type,
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }

        Homework::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?: null,
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'homework_date' => $validated['homework_date'],
            'due_date' => $validated['due_date'],
            'attachment_url' => $validated['attachment_url'] ?? null,
            'attachments' => $storedAttachments,
            'added_by' => $this->resolveAddedBy(),
            'created_by_user_id' => session('auth_user_id'),
            'created_by_guard' => session('auth_guard'),
            'status' => 'active',
        ]);

        if ($request->input('action') === 'save_and_next' || $request->has('save_and_next')) {
            return redirect()->route('academic.homework.index', [
                'prefill_class_id' => $validated['school_class_id'],
                'prefill_section_id' => $validated['section_id'] ?? '',
                'prefill_subject_id' => $validated['subject_id'],
                'prefill_homework_date' => $validated['homework_date'],
                'prefill_due_date' => $validated['due_date'],
            ])->with('success', "Homework \"{$validated['title']}\" created successfully! You can now create the next one.");
        }

        return redirect()->route('academic.homework.index')->with('success', 'Homework created successfully.');
    }

    public function show(Homework $homework): JsonResponse
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($homework->school_id !== $school->id, 403);

        $homework->load(['schoolClass', 'section', 'subject']);

        return response()->json([
            'id' => $homework->id,
            'title' => $homework->title,
            'class_id' => $homework->school_class_id,
            'class_name' => $homework->schoolClass?->name ?? 'N/A',
            'section_id' => $homework->section_id,
            'section_name' => $homework->section?->name ?? 'All Sections',
            'subject_id' => $homework->subject_id,
            'subject_name' => $homework->subject?->name ?? 'N/A',
            'subject_code' => $homework->subject?->code ?? '',
            'homework_date' => $homework->homework_date?->format('Y-m-d'),
            'homework_date_formatted' => $homework->homework_date?->format('M d, Y'),
            'due_date' => $homework->due_date?->format('Y-m-d'),
            'due_date_formatted' => $homework->due_date?->format('M d, Y'),
            'description' => $homework->description,
            'attachment_url' => $homework->attachment_url,
            'attachments' => $homework->attachments_list,
            'attachments_count' => $homework->attachments_count,
            'added_by' => $homework->added_by,
            'is_overdue' => $homework->is_overdue,
            'status' => $homework->status,
            'created_at' => $homework->created_at?->format('M d, Y h:i A'),
        ]);
    }

    public function update(Request $request, Homework $homework): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($homework->school_id !== $school->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'homework_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:homework_date',
            'description' => 'nullable|string',
            'attachment_url' => 'nullable|url|max:2048',
            'status' => 'nullable|string|in:active,completed,closed',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,bmp,mp4,webm,mov,mkv,avi,txt,csv,rtf,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:102400',
            'deleted_attachment_ids' => 'nullable|array',
            'deleted_attachment_ids.*' => 'string',
        ]);

        $currentAttachments = $homework->attachments_list;

        // Handle deletion of specific attachments if requested
        if (! empty($validated['deleted_attachment_ids'])) {
            $filteredAttachments = [];
            foreach ($currentAttachments as $att) {
                if (in_array($att['id'] ?? '', $validated['deleted_attachment_ids'])) {
                    if (! empty($att['path'])) {
                        Storage::disk('public')->delete($att['path']);
                    }
                } else {
                    $filteredAttachments[] = $att;
                }
            }
            $currentAttachments = $filteredAttachments;
        }

        // Add newly uploaded attachments
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (! is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $ext = $file->getClientOriginalExtension();
                    $mime = $file->getClientMimeType() ?: '';
                    $size = $file->getSize();
                    $type = $this->detectFileType($ext, $mime);

                    $folder = "homeworks/{$school->id}/{$session->id}";
                    $storedPath = $file->store($folder, 'public');

                    $currentAttachments[] = [
                        'id' => (string) Str::uuid(),
                        'original_name' => $originalName,
                        'path' => $storedPath,
                        'url' => asset('storage/' . $storedPath),
                        'size' => round($size / 1024, 1) . ' KB',
                        'size_bytes' => $size,
                        'mime' => $mime,
                        'extension' => strtolower($ext),
                        'type' => $type,
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }

        $homework->update([
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?: null,
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'homework_date' => $validated['homework_date'],
            'due_date' => $validated['due_date'],
            'attachment_url' => $validated['attachment_url'] ?? null,
            'attachments' => $currentAttachments,
            'status' => $validated['status'] ?? $homework->status,
        ]);

        return redirect()->route('academic.homework.index')->with('success', 'Homework updated successfully.');
    }

    public function destroy(Homework $homework): RedirectResponse
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($homework->school_id !== $school->id, 403);

        // Delete all attached files from disk
        foreach ($homework->attachments_list as $att) {
            if (! empty($att['path'])) {
                Storage::disk('public')->delete($att['path']);
            }
        }

        $homework->delete();

        return redirect()->route('academic.homework.index')->with('success', 'Homework deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        [$school] = $this->getSchoolAndSession();

        $ids = $request->input('ids', []);
        if (empty($ids) || ! is_array($ids)) {
            return back()->with('error', 'No homework selected for deletion.');
        }

        $homeworks = Homework::where('school_id', $school->id)->whereIn('id', $ids)->get();

        foreach ($homeworks as $hw) {
            foreach ($hw->attachments_list as $att) {
                if (! empty($att['path'])) {
                    Storage::disk('public')->delete($att['path']);
                }
            }
            $hw->delete();
        }

        return redirect()->route('academic.homework.index')->with('success', count($homeworks) . ' homework records deleted successfully.');
    }

    public function getSectionsAndSubjects(SchoolClass $schoolClass): JsonResponse
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($schoolClass->school_id !== $school->id, 403);

        $sections = $schoolClass->sections()->orderBy('name')->get(['id', 'name']);
        $subjects = Subject::where('school_class_id', $schoolClass->id)
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'sections' => $sections,
            'subjects' => $subjects,
        ]);
    }

    public function downloadAttachment(Homework $homework, $index = 0): BinaryFileResponse|RedirectResponse
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($homework->school_id !== $school->id, 403);

        $attachments = $homework->attachments_list;
        $attachment = null;

        if (is_numeric($index)) {
            $attachment = $attachments[(int) $index] ?? null;
        } else {
            // Find by attachment id
            foreach ($attachments as $att) {
                if (($att['id'] ?? '') === $index) {
                    $attachment = $att;
                    break;
                }
            }
        }

        if (! $attachment || empty($attachment['path'])) {
            abort(404, 'Attachment not found.');
        }

        $path = Storage::disk('public')->path($attachment['path']);
        abort_if(! file_exists($path), 404, 'Attachment file not found on disk.');

        return response()->download($path, $attachment['original_name'] ?: basename($path));
    }

    public function export(Request $request): StreamedResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = Homework::with(['schoolClass', 'section', 'subject'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('ids')) {
            $ids = is_array($request->input('ids')) ? $request->input('ids') : explode(',', $request->input('ids'));
            $query->whereIn('id', array_filter($ids));
        }
        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('added_by', 'like', "%{$search}%");
            });
        }

        $homeworks = $query->latest('homework_date')->get();
        $filename = 'homeworks_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($homeworks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Class', 'Section', 'Subject', 'Homework Date', 'Due Date', 'Description', 'Attachment URL', 'Attachments Count', 'Added By', 'Status']);

            foreach ($homeworks as $hw) {
                fputcsv($handle, [
                    $hw->id,
                    $hw->title,
                    $hw->schoolClass?->name ?? 'N/A',
                    $hw->section?->name ?? 'All Sections',
                    $hw->subject?->name ?? 'N/A',
                    $hw->homework_date?->format('Y-m-d') ?? '',
                    $hw->due_date?->format('Y-m-d') ?? '',
                    $hw->description,
                    $hw->attachment_url,
                    $hw->attachments_count,
                    $hw->added_by,
                    $hw->status,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function print(Request $request): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = Homework::with(['schoolClass', 'section', 'subject'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('ids')) {
            $ids = is_array($request->input('ids')) ? $request->input('ids') : explode(',', $request->input('ids'));
            $query->whereIn('id', array_filter($ids));
        }
        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('added_by', 'like', "%{$search}%");
            });
        }

        $homeworks = $query->latest('homework_date')->get();

        return view('sm-academic.homework.print', compact('school', 'session', 'homeworks'));
    }

    public function sampleCsv(): StreamedResponse
    {
        $filename = 'homework_sample_' . date('Y_m_d') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['title', 'class', 'section', 'subject', 'homework_date', 'due_date', 'description', 'attachment_url']);
            fputcsv($handle, [
                'Chapter 4 Quadratic Equations',
                '10th',
                'A',
                'Mathematics',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+3 days')),
                'Solve Exercises 4.1 to 4.3 in homework notebook',
                'https://drive.google.com/sample',
            ]);
            fputcsv($handle, [
                'Essay on Environmental Conservation',
                '10th',
                '',
                'English',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+4 days')),
                'Write a 250-300 word essay covering key topics',
                '',
            ]);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        // Normalize header columns
        $headerMap = [];
        foreach ($header as $idx => $colName) {
            $cleanName = strtolower(trim(str_replace([' ', '_', '-'], '', $colName)));
            $headerMap[$cleanName] = $idx;
        }

        if (! isset($headerMap['title']) || ! isset($headerMap['class']) || ! isset($headerMap['subject'])) {
            fclose($handle);
            return back()->with('error', 'Invalid CSV headers. Required headers: title, class, subject, homework_date, due_date.');
        }

        $classes = SchoolClass::where('school_id', $school->id)->with('sections')->get();
        $subjects = Subject::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        $addedBy = $this->resolveAddedBy();
        $importedCount = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) {
                continue;
            }

            $title = trim($row[$headerMap['title']] ?? '');
            $className = trim($row[$headerMap['class']] ?? '');
            $secName = isset($headerMap['section']) ? trim($row[$headerMap['section']] ?? '') : '';
            $subjectName = trim($row[$headerMap['subject']] ?? '');
            $hwDate = isset($headerMap['homeworkdate']) ? trim($row[$headerMap['homeworkdate']] ?? '') : (isset($headerMap['date']) ? trim($row[$headerMap['date']] ?? '') : date('Y-m-d'));
            $dueDate = isset($headerMap['duedate']) ? trim($row[$headerMap['duedate']] ?? '') : date('Y-m-d', strtotime('+3 days'));
            $desc = isset($headerMap['description']) ? trim($row[$headerMap['description']] ?? '') : null;
            $url = isset($headerMap['attachmenturl']) ? trim($row[$headerMap['attachmenturl']] ?? '') : null;

            if (! $title) {
                $errors[] = "Row {$rowNum}: Homework title is required.";
                continue;
            }

            // Find matching class
            $matchedClass = $classes->first(function ($c) use ($className) {
                return strcasecmp($c->name, $className) === 0 || (string)$c->id === $className;
            });

            if (! $matchedClass) {
                $errors[] = "Row {$rowNum}: Class '{$className}' not found.";
                continue;
            }

            // Find matching section if provided
            $sectionId = null;
            if ($secName && strtolower($secName) !== 'all' && strtolower($secName) !== 'all sections') {
                $matchedSection = $matchedClass->sections->first(function ($s) use ($secName) {
                    return strcasecmp($s->name, $secName) === 0 || (string)$s->id === $secName;
                });
                if ($matchedSection) {
                    $sectionId = $matchedSection->id;
                }
            }

            // Find matching subject
            $matchedSubject = $subjects->first(function ($s) use ($subjectName, $matchedClass) {
                $nameMatches = strcasecmp($s->name, $subjectName) === 0 || ($s->code && strcasecmp($s->code, $subjectName) === 0);
                return $nameMatches && ($s->school_class_id == $matchedClass->id || ! $s->school_class_id);
            });

            if (! $matchedSubject) {
                $matchedSubject = $subjects->first(function ($s) use ($subjectName) {
                    return strcasecmp($s->name, $subjectName) === 0 || ($s->code && strcasecmp($s->code, $subjectName) === 0);
                });
            }

            if (! $matchedSubject) {
                $errors[] = "Row {$rowNum}: Subject '{$subjectName}' not found for class '{$className}'.";
                continue;
            }

            $parsedHwDate = null;
            $parsedDueDate = null;
            try {
                $parsedHwDate = date('Y-m-d', strtotime($hwDate));
                $parsedDueDate = date('Y-m-d', strtotime($dueDate));
            } catch (\Exception $e) {
                $parsedHwDate = date('Y-m-d');
                $parsedDueDate = date('Y-m-d', strtotime('+3 days'));
            }

            Homework::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $matchedClass->id,
                'section_id' => $sectionId,
                'subject_id' => $matchedSubject->id,
                'title' => $title,
                'description' => $desc ?: null,
                'homework_date' => $parsedHwDate,
                'due_date' => $parsedDueDate,
                'attachment_url' => $url ?: null,
                'attachments' => [],
                'added_by' => $addedBy,
                'created_by_user_id' => session('auth_user_id'),
                'created_by_guard' => session('auth_guard'),
                'status' => 'active',
            ]);

            $importedCount++;
        }

        fclose($handle);

        $msg = "Imported {$importedCount} homework assignments successfully.";
        if (count($errors) > 0) {
            $msg .= ' (Encountered ' . count($errors) . ' issues: ' . implode('; ', array_slice($errors, 0, 3)) . ')';
        }

        return redirect()->route('academic.homework.index')->with('success', $msg);
    }
}
