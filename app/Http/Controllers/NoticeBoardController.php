<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\NoticeBoard;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoticeBoardController extends Controller
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

        return 'Admin';
    }

    public function index(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = NoticeBoard::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('link_type')) {
            $query->where('link_type', $request->link_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%")
                    ->orWhere('added_by', 'like', "%{$kw}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $notices = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Classes for current school and session
        $classes = SchoolClass::with('sections')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Statistics
        $totalNotices = NoticeBoard::where('school_id', $school->id)->where('academic_session_id', $session->id)->count();
        $activeNotices = NoticeBoard::where('school_id', $school->id)->where('academic_session_id', $session->id)->where('is_active', true)->count();
        $attachmentNotices = NoticeBoard::where('school_id', $school->id)->where('academic_session_id', $session->id)->where('link_type', 'attachment')->count();
        $urlNotices = NoticeBoard::where('school_id', $school->id)->where('academic_session_id', $session->id)->where('link_type', 'url')->count();

        return view('sm-academic.noticeboards.index', compact(
            'school',
            'session',
            'notices',
            'classes',
            'totalNotices',
            'activeNotices',
            'attachmentNotices',
            'urlNotices',
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
            'description' => 'nullable|string',
            'link_type' => 'required|in:none,attachment,url',
            'link_url' => 'nullable|required_if:link_type,url|url|max:2048',
            'attachment' => 'nullable|required_if:link_type,attachment|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp,gif,txt,xls,xlsx,ppt,pptx,zip|max:51200',
            'status' => 'required|in:active,inactive',
        ]);

        $filePath = null;
        $originalName = null;
        $fileSize = null;

        if ($validated['link_type'] === 'attachment' && $request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            $filePath = $file->store('notice-boards', 'public');
        }

        $isActive = ($validated['status'] === 'active');
        $addedBy = $this->resolveAddedBy();

        NoticeBoard::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'link_type' => $validated['link_type'],
            'link_url' => $validated['link_type'] === 'url' ? $validated['link_url'] : null,
            'attachment_path' => $filePath,
            'attachment_original_name' => $originalName,
            'attachment_size' => $fileSize,
            'is_active' => $isActive,
            'status' => $validated['status'],
            'added_by' => $addedBy,
        ]);

        return redirect()->route('academic.noticeboards.index')->with('success', 'Notice published successfully.');
    }

    public function update(Request $request, NoticeBoard $noticeboard): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($noticeboard->school_id !== $school->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'description' => 'nullable|string',
            'link_type' => 'required|in:none,attachment,url',
            'link_url' => 'nullable|required_if:link_type,url|url|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp,gif,txt,xls,xlsx,ppt,pptx,zip|max:51200',
            'status' => 'required|in:active,inactive',
        ]);

        $filePath = $noticeboard->attachment_path;
        $originalName = $noticeboard->attachment_original_name;
        $fileSize = $noticeboard->attachment_size;

        if ($validated['link_type'] === 'attachment') {
            if ($request->hasFile('attachment')) {
                if ($filePath && ! str_starts_with($filePath, 'http')) {
                    Storage::disk('public')->delete($filePath);
                }
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $fileSize = round($file->getSize() / 1024, 1) . ' KB';
                $filePath = $file->store('notice-boards', 'public');
            }
        } else {
            // If switched away from attachment, remove uploaded file
            if ($filePath && ! str_starts_with($filePath, 'http')) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = null;
            $originalName = null;
            $fileSize = null;
        }

        $isActive = ($validated['status'] === 'active');

        $noticeboard->update([
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'link_type' => $validated['link_type'],
            'link_url' => $validated['link_type'] === 'url' ? $validated['link_url'] : null,
            'attachment_path' => $filePath,
            'attachment_original_name' => $originalName,
            'attachment_size' => $fileSize,
            'is_active' => $isActive,
            'status' => $validated['status'],
        ]);

        return redirect()->route('academic.noticeboards.index')->with('success', 'Notice updated successfully.');
    }

    public function destroy(NoticeBoard $noticeboard): RedirectResponse
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($noticeboard->school_id !== $school->id, 403);

        if ($noticeboard->attachment_path && ! str_starts_with($noticeboard->attachment_path, 'http')) {
            Storage::disk('public')->delete($noticeboard->attachment_path);
        }

        $noticeboard->delete();

        return redirect()->route('academic.noticeboards.index')->with('success', 'Notice deleted successfully.');
    }

    public function updateStatus(NoticeBoard $noticeboard): RedirectResponse|JsonResponse
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($noticeboard->school_id !== $school->id, 403);

        $newActive = ! $noticeboard->is_active;
        $noticeboard->update([
            'is_active' => $newActive,
            'status' => $newActive ? 'active' : 'inactive',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $noticeboard->is_active,
                'status' => $noticeboard->status,
                'message' => 'Status updated successfully.',
            ]);
        }

        return back()->with('success', 'Notice status updated.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        [$school] = $this->getSchoolAndSession();

        $ids = $request->input('selected_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No notices selected for deletion.');
        }

        $notices = NoticeBoard::where('school_id', $school->id)->whereIn('id', $ids)->get();
        foreach ($notices as $n) {
            if ($n->attachment_path && ! str_starts_with($n->attachment_path, 'http')) {
                Storage::disk('public')->delete($n->attachment_path);
            }
            $n->delete();
        }

        return redirect()->route('academic.noticeboards.index')->with('success', count($notices) . ' notices deleted successfully.');
    }

    public function getSections(SchoolClass $schoolClass): JsonResponse
    {
        return response()->json($schoolClass->sections()->orderBy('name')->get(['id', 'name']));
    }

    public function download(NoticeBoard $noticeboard): BinaryFileResponse|RedirectResponse
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($noticeboard->school_id !== $school->id, 403);
        abort_if(! $noticeboard->attachment_path, 404, 'File not found');

        if (str_starts_with($noticeboard->attachment_path, 'http')) {
            return redirect()->away($noticeboard->attachment_path);
        }

        $path = Storage::disk('public')->path($noticeboard->attachment_path);
        abort_if(! file_exists($path), 404, 'File does not exist on disk.');

        return response()->download($path, $noticeboard->attachment_original_name ?: basename($path));
    }

    public function export(Request $request): StreamedResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = NoticeBoard::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('link_type')) {
            $query->where('link_type', $request->link_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%")
                    ->orWhere('added_by', 'like', "%{$kw}%");
            });
        }

        $notices = $query->orderByDesc('created_at')->get();

        $safeSession = preg_replace('/[^A-Za-z0-9_-]/', '_', $session->name);
        $fileName = 'notices_' . $safeSession . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($notices) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID',
                'Notice Title',
                'Class Name',
                'Section Name',
                'Description',
                'Link Type',
                'Link URL or Attachment',
                'Status',
                'Is Active',
                'Added By',
                'Created Date',
            ]);

            foreach ($notices as $n) {
                $linkInfo = '';
                if ($n->link_type === 'url') {
                    $linkInfo = $n->link_url;
                } elseif ($n->link_type === 'attachment') {
                    $linkInfo = $n->attachment_path ? (str_starts_with($n->attachment_path, 'http') ? $n->attachment_path : url(Storage::url($n->attachment_path))) : '';
                }

                fputcsv($out, [
                    $n->id,
                    $n->title,
                    $n->schoolClass?->name ?? 'All Classes',
                    $n->section?->name ?? 'All Sections',
                    $n->description,
                    $n->link_type,
                    $linkInfo,
                    $n->status,
                    $n->is_active ? 'Yes' : 'No',
                    $n->added_by,
                    $n->created_at ? $n->created_at->format('Y-m-d H:i') : '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sampleCsv(): StreamedResponse
    {
        $fileName = 'sample_noticeboards_import.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Notice Title',
                'Class Name',
                'Section Name',
                'Description',
                'Link Type (none/attachment/url)',
                'Link URL or Attachment URL',
                'Status (active/inactive)',
            ]);

            fputcsv($out, [
                'Annual Sports Day 2026',
                'Class 1',
                'A',
                'Annual sports day will take place on upcoming Friday. All students must wear sports uniform.',
                'none',
                '',
                'active',
            ]);

            fputcsv($out, [
                'Parent-Teacher Meeting Schedule & Link',
                'Class 2',
                '',
                'Online PTM link for meeting discussion. Click to join the room.',
                'url',
                'https://example.com/ptm-meet',
                'active',
            ]);

            fputcsv($out, [
                'Mid-Term Examination Syllabus & Guidelines',
                '',
                '',
                'Please download and review the detailed mid-term examination syllabus.',
                'attachment',
                'https://example.com/syllabus.pdf',
                'active',
            ]);

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:5120',
        ]);

        [$school, $session] = $this->getSchoolAndSession();

        $path = $request->file('csv_file')->getRealPath();
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($data = fgetcsv($handle, 5000, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        $header = array_shift($rows);
        $map = [];
        foreach ($header as $i => $col) {
            $colClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $col));
            if (str_contains($colClean, 'noticetitle') || str_contains($colClean, 'title')) {
                $map['title'] = $i;
            } elseif (str_contains($colClean, 'classname') || str_contains($colClean, 'class')) {
                $map['class'] = $i;
            } elseif (str_contains($colClean, 'section')) {
                $map['section'] = $i;
            } elseif (str_contains($colClean, 'desc')) {
                $map['description'] = $i;
            } elseif (str_contains($colClean, 'linktype') || str_contains($colClean, 'type')) {
                $map['link_type'] = $i;
            } elseif (str_contains($colClean, 'linkurl') || str_contains($colClean, 'attachmenturl') || str_contains($colClean, 'url') || str_contains($colClean, 'link')) {
                $map['link_url'] = $i;
            } elseif (str_contains($colClean, 'status')) {
                $map['status'] = $i;
            }
        }

        if (! isset($map['title'])) {
            return back()->with('import_errors', ['CSV must contain a "Notice Title" column header.']);
        }

        $classes = SchoolClass::with('sections')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        $addedBy = $this->resolveAddedBy();
        $importedCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $title = isset($map['title'], $row[$map['title']]) ? trim($row[$map['title']]) : '';
            $className = isset($map['class'], $row[$map['class']]) ? trim($row[$map['class']]) : '';
            $sectionName = isset($map['section'], $row[$map['section']]) ? trim($row[$map['section']]) : '';
            $description = isset($map['description'], $row[$map['description']]) ? trim($row[$map['description']]) : '';
            $linkTypeRaw = isset($map['link_type'], $row[$map['link_type']]) ? strtolower(trim($row[$map['link_type']])) : 'none';
            $linkUrlOrAttachment = isset($map['link_url'], $row[$map['link_url']]) ? trim($row[$map['link_url']]) : '';
            $statusRaw = isset($map['status'], $row[$map['status']]) ? strtolower(trim($row[$map['status']])) : 'active';

            if (empty($title)) {
                $errors[] = "Row {$rowNum}: Notice Title is required.";
                continue;
            }

            $classId = null;
            $sectionId = null;

            if (! empty($className)) {
                $matchedClass = $classes->first(fn($c) => strcasecmp(trim($c->name), $className) === 0);
                if (! $matchedClass) {
                    $errors[] = "Row {$rowNum}: Class '{$className}' not found in the current session.";
                    continue;
                }
                $classId = $matchedClass->id;

                if (! empty($sectionName)) {
                    $matchedSection = $matchedClass->sections->first(fn($s) => strcasecmp(trim($s->name), $sectionName) === 0);
                    if (! $matchedSection) {
                        $errors[] = "Row {$rowNum}: Section '{$sectionName}' not found in Class '{$matchedClass->name}'.";
                        continue;
                    }
                    $sectionId = $matchedSection->id;
                }
            }

            $linkType = 'none';
            if (in_array($linkTypeRaw, ['attachment', 'file', 'doc', 'pdf', 'image'])) {
                $linkType = 'attachment';
            } elseif (in_array($linkTypeRaw, ['url', 'link', 'web'])) {
                $linkType = 'url';
            }

            $linkUrl = null;
            $attachmentPath = null;
            $attachmentName = null;
            $attachmentSize = null;

            if ($linkType === 'url') {
                $linkUrl = ! empty($linkUrlOrAttachment) ? $linkUrlOrAttachment : null;
            } elseif ($linkType === 'attachment') {
                if (! empty($linkUrlOrAttachment)) {
                    $attachmentPath = $linkUrlOrAttachment;
                    $attachmentName = basename(parse_url($linkUrlOrAttachment, PHP_URL_PATH) ?: $linkUrlOrAttachment);
                    $attachmentSize = 'Web Link';
                }
            }

            $status = in_array($statusRaw, ['inactive', 'draft', 'false', '0']) ? 'inactive' : 'active';
            $isActive = ($status === 'active');

            NoticeBoard::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $classId,
                'section_id' => $sectionId,
                'title' => $title,
                'description' => $description ?: null,
                'link_type' => $linkType,
                'link_url' => $linkUrl,
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $attachmentName,
                'attachment_size' => $attachmentSize,
                'is_active' => $isActive,
                'status' => $status,
                'added_by' => $addedBy,
            ]);

            $importedCount++;
        }

        if (! empty($errors)) {
            return back()
                ->with('success', "Import finished: {$importedCount} notice(s) created.")
                ->with('import_errors', $errors);
        }

        return back()->with('success', "Successfully imported {$importedCount} notice(s).");
    }
}
