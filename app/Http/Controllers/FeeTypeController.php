<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeePeriod;
use App\Models\FeeType;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\StudentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeeTypeController extends AccountingController
{
    public function index(): View
    {
        $school = $this->selectedSchool();
        $sessionId = request()->integer('session_id');
        $session = $sessionId ? AcademicSession::findOrFail($sessionId) : $this->selectedSession();
        $sessions = AcademicSession::orderByDesc('name')->get();
        $feeTypes = FeeType::with(['period', 'classes', 'sections', 'studentType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get();

        return view('accounting.fee-types.index', [
            ...$this->formData($school->id, $session),
            'school' => $school,
            'session' => $session,
            'sessions' => $sessions,
            'feeTypes' => $feeTypes,
        ]);
    }

    public function create(): View
    {
        $school = $this->selectedSchool();
        $sessionId = request()->integer('session_id');
        $session = $sessionId ? AcademicSession::findOrFail($sessionId) : $this->selectedSession();

        return view('accounting.fee-types.form', [
            ...$this->formData($school->id, $session),
            'feeType' => null,
            'editing' => false,
        ]);
    }

    public function edit(FeeType $feeType): View
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feeType->school_id === $school->id, 404);
        $session = AcademicSession::findOrFail($feeType->academic_session_id);
        $feeType->load('classes');

        return view('accounting.fee-types.form', [
            ...$this->formData($school->id, $session),
            'feeType' => $feeType,
            'editing' => true,
        ]);
    }

    public function classes(AcademicSession $academicSession): JsonResponse
    {
        $school = $this->selectedSchool();
        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $academicSession->id)
            ->with(['sections' => fn ($query) => $query->orderBy('name')])
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);
        $periods = FeePeriod::where('school_id', $school->id)
            ->where('academic_session_id', $academicSession->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        $studentTypes = StudentType::where('school_id', $school->id)
            ->where('academic_session_id', $academicSession->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['classes' => $classes, 'periods' => $periods, 'student_types' => $studentTypes]);
    }

    public function forClass(Request $request, SchoolClass $schoolClass): JsonResponse
    {
        $school = $this->selectedSchool();
        abort_unless((int) $schoolClass->school_id === $school->id, 404);
        $validated = $request->validate([
            'student_type_id' => ['nullable', 'integer', Rule::exists('student_types', 'id')->where('school_id', $school->id)],
        ]);

        $feeTypes = FeeType::with('period')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $schoolClass->academic_session_id)
            ->whereHas('classes', fn (Builder $query) => $query->whereKey($schoolClass->id))
            ->where(function (Builder $query) use ($validated): void {
                $query->whereIn('student_type', ['all', 'new_admission']);
                if (! empty($validated['student_type_id'])) {
                    $query->orWhere(fn (Builder $customTypeQuery) => $customTypeQuery
                        ->where('student_type', 'custom')
                        ->where('student_type_id', $validated['student_type_id']));
                }
            })
            ->where('invoice_on_admission', true)
            ->orderBy('name')
            ->get()
            ->map(fn (FeeType $feeType): array => [
                'id' => $feeType->id,
                'name' => $feeType->name,
                'amount' => $feeType->amount,
                'period' => $feeType->period->name,
                'invoice_on_admission' => $feeType->invoice_on_admission,
                'student_type_id' => $feeType->student_type_id,
            ]);

        return response()->json($feeTypes);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->selectedSchool();
        $validated = $this->validatedData($request, $school->id);

        DB::transaction(function () use ($school, $validated): void {
            $feeType = FeeType::create([...$validated['data'], 'school_id' => $school->id]);
            $feeType->classes()->sync($validated['class_ids']);
            if (isset($validated['section_ids'])) {
                $feeType->sections()->sync($validated['section_ids']);
            }
        });

        $redirect = redirect()->route('accounting.fee-types.index', ['session_id' => $validated['data']['academic_session_id']])
            ->with('success', 'Fee type created successfully.');

        if ($request->input('action_type') === 'save_and_next') {
            return $redirect->with('reopen_add_modal', true);
        }

        return $redirect;
    }

    public function update(Request $request, FeeType $feeType): RedirectResponse
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feeType->school_id === $school->id, 404);
        $validated = $this->validatedData($request, $school->id);

        DB::transaction(function () use ($feeType, $validated): void {
            $feeType->update($validated['data']);
            $feeType->classes()->sync($validated['class_ids']);
            if (isset($validated['section_ids'])) {
                $feeType->sections()->sync($validated['section_ids']);
            }
        });

        return redirect()->route('accounting.fee-types.index', ['session_id' => $feeType->academic_session_id])
            ->with('success', 'Fee type updated successfully.');
    }

    public function destroy(FeeType $feeType): RedirectResponse
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feeType->school_id === $school->id, 404);

        if ($feeType->invoices()->exists()) {
            return back()->with('error', 'This fee type has invoices and cannot be deleted.');
        }

        $feeType->delete();

        return redirect()->route('accounting.fee-types.index', ['session_id' => $feeType->academic_session_id])
            ->with('success', 'Fee type deleted successfully.');
    }

    /** @return array{data: array<string, mixed>, class_ids: array<int, int>, section_ids: array<int, int>} */
    private function validatedData(Request $request, int $schoolId): array
    {
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'fee_period_id' => ['required', 'integer', Rule::exists('fee_periods', 'id')
                ->where('school_id', $schoolId)
                ->where('academic_session_id', $request->integer('academic_session_id'))],
            'name' => ['required', 'string', 'max:150'],
            'student_type' => ['required', Rule::in(array_merge(
                ['all', 'new_admission', 'existing'],
                StudentType::where('school_id', $schoolId)->pluck('id')->map(fn (int $id): string => (string) $id)->all(),
            ))],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['required', 'integer', 'distinct', 'exists:sections,id'],
            'invoice_on_admission' => ['nullable', 'boolean'],
            'include_on_promotion' => ['nullable', 'boolean'],
            'dashboard_disabled' => ['nullable', 'boolean'],
        ]);

        $classIds = array_map('intval', $validated['class_ids']);
        $classesCount = SchoolClass::where('school_id', $schoolId)
            ->where('academic_session_id', $validated['academic_session_id'])
            ->whereIn('id', $classIds)
            ->count();
        abort_if($classesCount !== count($classIds), 422, 'Select classes that belong to this school and session.');
        
        $sectionIds = isset($validated['section_ids']) ? array_map('intval', $validated['section_ids']) : [];

        return [
            'data' => [
                'academic_session_id' => $validated['academic_session_id'],
                'fee_period_id' => $validated['fee_period_id'],
                'name' => trim($validated['name']),
                'student_type' => in_array($validated['student_type'], ['all', 'new_admission', 'existing'], true) ? $validated['student_type'] : 'custom',
                'student_type_id' => in_array($validated['student_type'], ['all', 'new_admission', 'existing'], true) ? null : (int) $validated['student_type'],
                'amount' => $validated['amount'],
                'invoice_on_admission' => $request->boolean('invoice_on_admission'),
                'include_on_promotion' => $request->boolean('include_on_promotion'),
                'dashboard_disabled' => $request->boolean('dashboard_disabled'),
            ],
            'class_ids' => $classIds,
            'section_ids' => $sectionIds,
        ];
    }

    /** @return array{school: School, session: AcademicSession, sessions: Collection, periods: Collection, classes: Collection, studentTypes: Collection} */
    private function formData(int $schoolId, AcademicSession $session): array
    {
        $school = $this->selectedSchool();
        abort_unless($school->id === $schoolId, 404);

        return [
            'school' => $school,
            'session' => $session,
            'sessions' => AcademicSession::orderByDesc('name')->get(),
            'periods' => FeePeriod::where('school_id', $schoolId)
                ->where('academic_session_id', $session->id)
                ->orderBy('name')
                ->get(),
            'classes' => SchoolClass::where('school_id', $schoolId)
                ->where('academic_session_id', $session->id)
                ->orderBy('order')
                ->orderBy('name')
                ->get(),
            'studentTypes' => StudentType::where('school_id', $schoolId)
                ->where('academic_session_id', $session->id)
                ->orderBy('name')
                ->get(),
        ];
    }

    public function export(Request $request): StreamedResponse
    {
        $school = $this->selectedSchool();
        $sessionId = $request->integer('session_id') ?: $this->selectedSession()->id;
        
        $feeTypes = FeeType::with(['period', 'classes', 'sections', 'studentType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $sessionId)
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_types.csv"',
        ];

        $callback = function () use ($feeTypes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fee Name', 'Period Name', 'Student Type', 'Amount', 'Invoice on Admission', 'Include on Promotion', 'Hide from Dashboard', 'Classes', 'Sections']);

            foreach ($feeTypes as $fee) {
                $studentTypeLabel = match($fee->student_type) {
                    'all' => 'All Students',
                    'new_admission' => 'New Admissions',
                    'existing' => 'Existing Students',
                    default => $fee->studentType?->name ?? 'Unknown',
                };
                
                fputcsv($file, [
                    $fee->name,
                    $fee->period?->name,
                    $studentTypeLabel,
                    $fee->amount,
                    $fee->invoice_on_admission ? 'Yes' : 'No',
                    $fee->include_on_promotion ? 'Yes' : 'No',
                    $fee->dashboard_disabled ? 'Yes' : 'No',
                    $fee->classes->pluck('name')->join(', '),
                    $fee->sections->pluck('name')->join(', '),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadSample(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_types_sample.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fee Name', 'Period Name', 'Student Type', 'Amount', 'Invoice on Admission', 'Include on Promotion', 'Hide from Dashboard', 'Classes', 'Sections']);
            fputcsv($file, ['Tuition Fee', 'Monthly', 'All Students', '1000.00', 'Yes', 'Yes', 'No', 'Class 1, Class 2', 'A, B']);
            fputcsv($file, ['Admission Fee', 'Annual', 'New Admissions', '5000.00', 'Yes', 'No', 'No', 'Class 1', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
            'session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
        ]);

        $school = $this->selectedSchool();
        $sessionId = $request->integer('session_id');

        $file = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($file);
        
        $expectedHeader = ['Fee Name', 'Period Name', 'Student Type', 'Amount', 'Invoice on Admission', 'Include on Promotion', 'Hide from Dashboard', 'Classes', 'Sections'];
        
        // Let's trim headers
        if ($header) {
            $header = array_map('trim', $header);
        }

        if ($header !== $expectedHeader) {
            return back()->with('error', 'Invalid CSV format. Please download the sample file and try again.');
        }

        $imported = 0;
        $errors = [];
        $row = 1;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($file)) !== false) {
                $row++;
                if (count($data) < 8) continue;
                
                $feeName = trim($data[0]);
                $periodName = trim($data[1]);
                $studentTypeInput = trim($data[2]);
                $amount = trim($data[3]);
                $invoice = strtolower(trim($data[4])) === 'yes';
                $promotion = strtolower(trim($data[5])) === 'yes';
                $dashboard = strtolower(trim($data[6])) === 'yes';
                $classesInput = trim($data[7]);
                $sectionsInput = count($data) > 8 ? trim($data[8]) : '';
                
                if (empty($feeName) || empty($periodName) || !is_numeric($amount)) {
                    $errors[] = "Row $row: Missing required fields or invalid amount.";
                    continue;
                }

                $period = FeePeriod::where('school_id', $school->id)->where('academic_session_id', $sessionId)->where('name', $periodName)->first();
                if (!$period) {
                    $errors[] = "Row $row: Period '$periodName' not found in this session.";
                    continue;
                }
                
                $studentType = 'all';
                $studentTypeId = null;
                $stLower = strtolower($studentTypeInput);
                if ($stLower === 'all students') {
                    $studentType = 'all';
                } elseif ($stLower === 'new admissions') {
                    $studentType = 'new_admission';
                } elseif ($stLower === 'existing students') {
                    $studentType = 'existing';
                } else {
                    $stModel = StudentType::where('school_id', $school->id)->where('academic_session_id', $sessionId)->where('name', $studentTypeInput)->first();
                    if ($stModel) {
                        $studentType = 'custom';
                        $studentTypeId = $stModel->id;
                    } else {
                        $errors[] = "Row $row: Student Type '$studentTypeInput' not found in this session.";
                        continue;
                    }
                }
                
                // Classes
                $classNames = array_filter(array_map('trim', explode(',', $classesInput)));
                $classIds = SchoolClass::where('school_id', $school->id)->where('academic_session_id', $sessionId)->whereIn('name', $classNames)->pluck('id')->toArray();
                
                if (count($classIds) !== count($classNames)) {
                    $errors[] = "Row $row: One or more classes not found in this session.";
                    continue;
                }

                // Sections
                $sectionNames = array_filter(array_map('trim', explode(',', $sectionsInput)));
                $sectionIds = \App\Models\Section::whereIn('school_class_id', $classIds)->whereIn('name', $sectionNames)->pluck('id')->toArray();

                $feeType = FeeType::updateOrCreate([
                    'school_id' => $school->id,
                    'academic_session_id' => $sessionId,
                    'name' => $feeName,
                ], [
                    'fee_period_id' => $period->id,
                    'student_type' => $studentType,
                    'student_type_id' => $studentTypeId,
                    'amount' => $amount,
                    'invoice_on_admission' => $invoice,
                    'include_on_promotion' => $promotion,
                    'dashboard_disabled' => $dashboard,
                ]);

                $feeType->classes()->sync($classIds);
                if (!empty($sectionIds)) {
                    $feeType->sections()->sync($sectionIds);
                } else {
                    $feeType->sections()->detach();
                }

                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }

        if (count($errors) > 0) {
            $errStr = implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>...and more.' : '');
            return redirect()->route('accounting.fee-types.index', ['session_id' => $sessionId])->with('error', "Imported $imported fees with errors:<br>$errStr");
        }

        return redirect()->route('accounting.fee-types.index', ['session_id' => $sessionId])->with('success', "Successfully imported $imported fee types.");
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\FeeType::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}