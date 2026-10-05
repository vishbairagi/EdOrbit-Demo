<?php

namespace App\Http\Controllers;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\PaymentMethod;
use App\Models\SchoolClass;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FeeInvoiceController extends AccountingController
{
    public function index(Request $request): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get();
        $invoices = FeeInvoice::with(['student.schoolClass', 'feeType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->when($request->filled('class_id'), fn (Builder $query) => $query->whereHas(
                'student', fn (Builder $studentQuery) => $studentQuery->where('school_class_id', $request->integer('class_id'))
            ))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('keyword'), fn (Builder $query) => $query->whereHas(
                'student', fn (Builder $studentQuery) => $studentQuery
                    ->where('first_name', 'like', '%'.$request->string('keyword').'%')
                    ->orWhere('last_name', 'like', '%'.$request->string('keyword').'%')
                    ->orWhere('admission_no', 'like', '%'.$request->string('keyword').'%')
            ))
            ->latest('issued_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $paymentMethods = PaymentMethod::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();
        $authorizers = $this->authorizers($school->id);

        return view('accounting.invoices.index', compact('school', 'session', 'classes', 'invoices', 'paymentMethods', 'authorizers'));
    }

    public function create(): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->with('sections')
            ->orderBy('name')
            ->get();

        return view('accounting.invoices.create', compact('school', 'session', 'classes'));
    }

    public function students(Request $request, SchoolClass $schoolClass): JsonResponse
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        abort_unless((int) $schoolClass->school_id === $school->id && (int) $schoolClass->academic_session_id === $session->id, 404);

        $students = Student::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $schoolClass->id)
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('section_id', $request->integer('section_id')))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'admission_no']);

        return response()->json($students->map(fn (Student $student): array => [
            'id' => $student->id,
            'name' => $student->full_name,
            'admission_no' => $student->admission_no,
        ]));
    }

    public function sections(SchoolClass $schoolClass): JsonResponse
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        abort_unless((int) $schoolClass->school_id === $school->id && (int) $schoolClass->academic_session_id === $session->id, 404);

        return response()->json($schoolClass->sections()->orderBy('name')->get(['id', 'name']));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $validated = $request->validate([
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')
                ->where('school_id', $school->id)
                ->where('academic_session_id', $session->id)],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->where('school_class_id', $request->integer('school_class_id'))],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', 'exists:students,id'],
            'invoice_title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'allow_partial_payment' => ['nullable', 'boolean'],
            'total_amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'due_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
        ]);

        if (isset($validated['due_amount']) && (float) $validated['due_amount'] > (float) $validated['total_amount']) {
            throw ValidationException::withMessages(['due_amount' => 'Due date amount cannot exceed the invoice total.']);
        }

        $studentIds = array_map('intval', $validated['student_ids']);
        $students = Student::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $validated['school_class_id'])
            ->when($request->filled('section_id'), fn (Builder $query) => $query->where('section_id', $validated['section_id']))
            ->whereIn('id', $studentIds)
            ->get();
        abort_if($students->count() !== count($studentIds), 422, 'Selected students must belong to the chosen class and session.');

        DB::transaction(function () use ($school, $session, $students, $validated, $request): void {
            foreach ($students as $student) {
                FeeInvoice::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $session->id,
                    'student_id' => $student->id,
                    'invoice_title' => trim($validated['invoice_title']),
                    'description' => $validated['description'] ?? null,
                    'allow_partial_payment' => $request->boolean('allow_partial_payment'),
                    'total_amount' => $validated['total_amount'],
                    'amount_paid' => 0,
                    'due_amount' => $validated['due_amount'] ?? $validated['total_amount'],
                    'issued_at' => $validated['issued_at'],
                    'due_at' => $validated['due_at'] ?? null,
                    'status' => 'unpaid',
                ]);
            }
        });

        if (\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_generated_email_enable') == '1') {
            $carrier = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'email_carrier');
            if ($carrier === 'smtp') {
                config(['mail.default' => 'smtp']);
                config(['mail.mailers.smtp.host' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_host')]);
                config(['mail.mailers.smtp.port' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_port')]);
                config(['mail.mailers.smtp.encryption' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_encryption')]);
                config(['mail.mailers.smtp.username' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username')]);
                config(['mail.mailers.smtp.password' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_password')]);
                config(['mail.from.address' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username')]);
                config(['mail.from.name' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'mail_from_name')]);
                app('mail.manager')->purge('smtp');
                
                $subject = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_generated_email_subject', 'Invoice Generated');
                $rawBody = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_generated_email_body', 'An invoice has been generated for [STUDENT_NAME].');
                
                foreach ($students as $student) {
                    if ($student->email) {
                        $replacements = [
                            '[STUDENT_NAME]' => $student->full_name,
                            '[CLASS]' => $student->schoolClass->name ?? '',
                            '[SECTION]' => $student->section->name ?? '',
                            '[ROLL_NUMBER]' => $student->roll_no ?? '',
                            '[ENROLLMENT_NUMBER]' => $student->enrollment_no ?? '',
                            '[ADMISSION_NUMBER]' => $student->admission_no ?? '',
                            '[SCHOOL_NAME]' => $school->name ?? '',
                            '[AMOUNT]' => $validated['total_amount'] ?? '',
                            '[TOTAL_AMOUNT]' => $validated['total_amount'] ?? '',
                            '[PAYABLE_AMOUNT]' => $validated['due_amount'] ?? $validated['total_amount'] ?? '',
                            '[ISSUE_DATE]' => $validated['issued_at'] ?? '',
                            '[DUE_DATE]' => $validated['due_at'] ?? 'N/A',
                            '[TRANSACTION_ID]' => 'N/A',
                        ];
                        $body = str_replace(array_keys($replacements), array_values($replacements), $rawBody);
                        
                        try {
                            \Illuminate\Support\Facades\Mail::mailer('smtp')->html(nl2br(e($body)), function ($message) use ($student, $subject) {
                                $message->to($student->email)->subject($subject);
                            });
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Invoice email failed: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        if ($request->input('action_type') === 'save_and_next') {
            return back()->with('success', 'Invoices created for '.$students->count().' student(s).')->with('reopen_add_modal', true);
        }

        return redirect()->route('accounting.fee-invoices.index')->with('success', 'Invoices created for '.$students->count().' student(s).');
    }

    public function show(FeeInvoice $feeInvoice): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        abort_unless((int) $feeInvoice->school_id === $school->id && (int) $feeInvoice->academic_session_id === $session->id, 404);
        $feeInvoice->load(['student.schoolClass', 'student.section', 'feeType', 'payments.paymentMethod']);
        $paymentMethods = PaymentMethod::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();
        $authorizers = $this->authorizers($school->id);
        $outstanding = max(0, (float) $feeInvoice->total_amount - (float) $feeInvoice->amount_paid);

        return view('accounting.invoices.show', compact('feeInvoice', 'paymentMethods', 'authorizers', 'outstanding'));
    }

    public function print(FeeInvoice $feeInvoice): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        abort_unless((int) $feeInvoice->school_id === $school->id && (int) $feeInvoice->academic_session_id === $session->id, 404);
        $feeInvoice->load(['student.schoolClass', 'student.section', 'feeType', 'payments.paymentMethod']);
        
        return view('accounting.invoices.print-single', compact('school', 'feeInvoice'));
    }

    public function collectPaymentIndex(): \Illuminate\View\View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        
        $feeInvoices = FeeInvoice::with(['student.schoolClass'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('status', ['unpaid', 'partial', 'pending'])
            ->get();
            
        $transportInvoices = \App\Models\TransportInvoice::with(['student.schoolClass'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('status', ['unpaid', 'partial', 'pending'])
            ->get();
            
        $allInvoices = $feeInvoices->map(function ($invoice) {
            return (object) [
                'type' => 'fee',
                'id' => $invoice->id,
                'student_name' => $invoice->student->full_name,
                'admission_no' => $invoice->student->admission_no,
                'class_name' => $invoice->student->schoolClass->name ?? '-',
                'invoice_title' => $invoice->invoice_title,
                'total_amount' => (float) $invoice->total_amount,
                'amount_paid' => (float) $invoice->amount_paid,
                'due_amount' => max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid),
                'status' => $invoice->status,
                'show_url' => route('accounting.fee-invoices.show', $invoice),
                'sort_date' => $invoice->due_at ?? $invoice->issued_at,
            ];
        })->concat($transportInvoices->map(function ($invoice) {
            return (object) [
                'type' => 'transport',
                'id' => $invoice->id,
                'student_name' => $invoice->student->first_name . ' ' . $invoice->student->last_name,
                'admission_no' => $invoice->student->admission_no,
                'class_name' => $invoice->student->schoolClass->name ?? '-',
                'invoice_title' => $invoice->invoice_title,
                'total_amount' => (float) $invoice->total_amount,
                'amount_paid' => (float) $invoice->amount_paid,
                'due_amount' => max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid),
                'status' => $invoice->status,
                'show_url' => route('accounting.transport-invoices.show', $invoice),
                'sort_date' => $invoice->issued_at,
            ];
        }))->sortByDesc('sort_date')->values();
        
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('invoice_page');
        $perPage = 15;
        $invoices = new \Illuminate\Pagination\LengthAwarePaginator(
            $allInvoices->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $allInvoices->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'invoice_page']
        );
        
        $feePayments = FeePayment::with(['invoice.student', 'paymentMethod'])
            ->where('school_id', $school->id)
            ->whereHas('invoice', fn ($q) => $q->where('academic_session_id', $session->id))
            ->get();
            
        $paymentMethods = \App\Models\PaymentMethod::where('school_id', $school->id)->get()->keyBy('id');
            
        $transportPayments = \App\Models\TransportPayment::with(['invoice.student'])
            ->whereHas('invoice', fn ($q) => $q->where('school_id', $school->id)->where('academic_session_id', $session->id))
            ->get();
            
        $allPayments = $feePayments->map(function ($payment) {
            return (object) [
                'type' => 'fee',
                'id' => $payment->id,
                'paid_on' => $payment->paid_on,
                'student_name' => $payment->invoice->student->full_name,
                'invoice_title' => $payment->invoice->invoice_title,
                'method' => $payment->paymentMethod?->name ?? '—',
                'transaction_id' => $payment->transaction_id ?? '—',
                'authorized_by' => $payment->authorized_by ?? '—',
                'amount' => (float) $payment->amount,
                'invoice_url' => route('accounting.fee-invoices.show', $payment->invoice),
                'receipt_url' => route('accounting.collect-payment.receipt', $payment),
            ];
        })->concat($transportPayments->map(function ($payment) use ($paymentMethods) {
            $pm = clone $paymentMethods;
            $pmName = $pm->has($payment->payment_method) ? $pm->get($payment->payment_method)->name : '—';
            return (object) [
                'type' => 'transport',
                'id' => $payment->id,
                'paid_on' => $payment->payment_date,
                'student_name' => $payment->invoice->student->first_name . ' ' . $payment->invoice->student->last_name,
                'invoice_title' => $payment->invoice->invoice_title,
                'method' => $pmName,
                'transaction_id' => '—',
                'authorized_by' => '—',
                'amount' => (float) $payment->amount,
                'invoice_url' => route('accounting.transport-invoices.show', $payment->invoice),
                'receipt_url' => route('accounting.transport-invoices.print', $payment->invoice),
            ];
        }))->sortByDesc('paid_on')->values();
        
        $pCurrentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('payment_page');
        $pPerPage = 20;
        $payments = new \Illuminate\Pagination\LengthAwarePaginator(
            $allPayments->slice(($pCurrentPage - 1) * $pPerPage, $pPerPage)->values(),
            $allPayments->count(),
            $pPerPage,
            $pCurrentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'payment_page']
        );

        return view('accounting.collect-payment.index', compact('school', 'session', 'invoices', 'payments'));
    }

    public function collectPaymentBulkPrint(Request $request): \Illuminate\View\View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        
        $feeIds = $request->input('fee_ids', []);
        $transportIds = $request->input('transport_ids', []);
        
        if (empty($feeIds) && empty($transportIds)) {
            abort(400, 'No invoices selected.');
        }
        
        $feeInvoices = FeeInvoice::with(['student.schoolClass', 'school'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('id', $feeIds)
            ->get();
            
        $transportInvoices = \App\Models\TransportInvoice::with(['student.schoolClass', 'student.section', 'transportRoute'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('id', $transportIds)
            ->get();

        return view('accounting.collect-payment.print-bulk', compact('feeInvoices', 'transportInvoices', 'school'));
    }

    public function collectPaymentBulkDestroy(Request $request)
    {
        $school = $this->selectedSchool();
        
        $feeIds = $request->input('fee_ids', []);
        $transportIds = $request->input('transport_ids', []);
        
        if (empty($feeIds) && empty($transportIds)) {
            return back()->with('error', 'No invoices selected.');
        }
        
        if (!empty($feeIds)) {
            FeeInvoice::where('school_id', $school->id)->whereIn('id', $feeIds)->delete();
        }
        
        if (!empty($transportIds)) {
            \App\Models\TransportInvoice::where('school_id', $school->id)->whereIn('id', $transportIds)->delete();
        }
        
        return back()->with('success', 'Selected invoices have been deleted.');
    }

    public function printPaymentReceipt(FeePayment $feePayment): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $feePayment->load(['invoice.student.schoolClass', 'invoice.academicSession', 'paymentMethod']);
        abort_unless(
            (int) $feePayment->school_id === $school->id
                && (int) $feePayment->invoice->academic_session_id === $session->id,
            404,
        );

        return view('accounting.payments.receipt', compact('school', 'feePayment'));
    }

    public function collect(Request $request, FeeInvoice $feeInvoice): RedirectResponse
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        abort_unless((int) $feeInvoice->school_id === $school->id && (int) $feeInvoice->academic_session_id === $session->id, 404);
        $authorizers = $this->authorizers($school->id);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')->where('school_id', $school->id)->where('is_active', true)],
            'transaction_id' => ['nullable', 'string', 'max:150'],
            'authorized_by' => ['required', Rule::in(array_keys($authorizers))],
            'paid_on' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($feeInvoice, $school, $session, $validated, $authorizers): void {
            $invoice = FeeInvoice::whereKey($feeInvoice->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $invoice->school_id === $school->id && (int) $invoice->academic_session_id === $session->id, 404);

            $outstandingCents = (int) round(((float) $invoice->total_amount - (float) $invoice->amount_paid) * 100);
            $amountCents = (int) round((float) $validated['amount'] * 100);
            if ($outstandingCents <= 0 || $amountCents > $outstandingCents) {
                throw ValidationException::withMessages(['amount' => 'Payment cannot exceed the outstanding invoice balance.']);
            }
            if (! $invoice->allow_partial_payment && $amountCents !== $outstandingCents) {
                throw ValidationException::withMessages(['amount' => 'This invoice requires payment of the full outstanding balance.']);
            }

            FeePayment::create([
                'school_id' => $school->id,
                'fee_invoice_id' => $invoice->id,
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $validated['amount'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'authorized_by' => $authorizers[$validated['authorized_by']],
                'paid_on' => $validated['paid_on'],
                'note' => $validated['note'] ?? null,
            ]);

            $paidCents = (int) round((float) $invoice->amount_paid * 100) + $amountCents;
            $invoice->amount_paid = number_format($paidCents / 100, 2, '.', '');
            $invoice->status = $paidCents >= (int) round((float) $invoice->total_amount * 100) ? 'paid' : 'partial';
            $invoice->save();
        });

        if (\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fee_received_email_enable') == '1') {
            $student = $feeInvoice->student;
            if ($student && $student->email) {
                $carrier = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'email_carrier');
                if ($carrier === 'smtp') {
                    config(['mail.default' => 'smtp']);
                    config(['mail.mailers.smtp.host' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_host')]);
                    config(['mail.mailers.smtp.port' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_port')]);
                    config(['mail.mailers.smtp.encryption' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_encryption')]);
                    config(['mail.mailers.smtp.username' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username')]);
                    config(['mail.mailers.smtp.password' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_password')]);
                    config(['mail.from.address' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username')]);
                    config(['mail.from.name' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'mail_from_name')]);
                    app('mail.manager')->purge('smtp');
                    
                    $subject = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fee_received_email_subject', 'Fee Received');
                    $rawBody = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fee_received_email_body', 'Fee received for [STUDENT_NAME].');
                    
                    $replacements = [
                        '[STUDENT_NAME]' => $student->full_name,
                        '[CLASS]' => $student->schoolClass->name ?? '',
                        '[SECTION]' => $student->section->name ?? '',
                        '[ROLL_NUMBER]' => $student->roll_no ?? '',
                        '[ENROLLMENT_NUMBER]' => $student->enrollment_no ?? '',
                        '[ADMISSION_NUMBER]' => $student->admission_no ?? '',
                        '[SCHOOL_NAME]' => $school->name ?? '',
                        '[AMOUNT]' => $validated['amount'] ?? '',
                        '[TOTAL_AMOUNT]' => $invoice->total_amount ?? '',
                        '[PAYABLE_AMOUNT]' => $validated['amount'] ?? '',
                        '[ISSUE_DATE]' => $invoice->issued_at ?? '',
                        '[DUE_DATE]' => $invoice->due_at ?? 'N/A',
                        '[TRANSACTION_ID]' => $validated['transaction_id'] ?? 'N/A',
                    ];
                    $body = str_replace(array_keys($replacements), array_values($replacements), $rawBody);
                    
                    try {
                        \Illuminate\Support\Facades\Mail::mailer('smtp')->html(nl2br(e($body)), function ($message) use ($student, $subject) {
                            $message->to($student->email)->subject($subject);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Fee receipt email failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('accounting.fee-invoices.index')->with('success', 'Payment collected successfully.');
    }


    public function printSelected(Request $request): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['required', 'integer', 'distinct', 'exists:fee_invoices,id'],
        ]);
        $invoiceIds = array_map('intval', $validated['invoice_ids']);
        $invoices = FeeInvoice::with(['student.schoolClass', 'school'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('id', $invoiceIds)
            ->orderBy('student_id')
            ->get();
        abort_if($invoices->count() !== count($invoiceIds), 404);

        return view('accounting.invoices.print', compact('invoices', 'school'));
    }

    /** @return array<string, string> */
    private function authorizers(int $schoolId): array
    {
        $staff = Staff::where('school_id', $schoolId)->where('status', 'Active')->orderBy('name')->get(['id', 'name']);
        $admins = SchoolUser::where('school_id', $schoolId)->where('status', 'Active')->orderBy('name')->get(['id', 'name']);

        return $admins->mapWithKeys(fn (SchoolUser $user): array => ['admin:'.$user->id => $user->name.' (Admin)'])
            ->union($staff->mapWithKeys(fn (Staff $user): array => ['staff:'.$user->id => $user->name.' (Staff)']))
            ->all();
    }

    public function edit(FeeInvoice $feeInvoice): View
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feeInvoice->school_id === $school->id, 404);

        $feeInvoice->load('student.schoolClass');

        return view('accounting.invoices.edit', [
            'school' => $school,
            'session' => $this->selectedSession(),
            'feeInvoice' => $feeInvoice,
        ]);
    }

    public function update(Request $request, FeeInvoice $feeInvoice): RedirectResponse
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feeInvoice->school_id === $school->id, 404);

        $validated = $request->validate([
            'invoice_title' => ['required', 'string', 'max:150'],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'due_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date'],
            'allow_partial_payment' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if ((float) $validated['total_amount'] < (float) $feeInvoice->amount_paid) {
            return back()->withInput()->with('error', 'Total amount cannot be less than the amount already paid.');
        }

        $dueAmount = $request->filled('due_amount') ? $validated['due_amount'] : $validated['total_amount'];
        $status = 'pending';
        if ((float) $feeInvoice->amount_paid >= (float) $validated['total_amount']) {
            $status = 'paid';
        } elseif ((float) $feeInvoice->amount_paid > 0) {
            $status = 'partial';
        }

        $feeInvoice->update([
            'invoice_title' => $validated['invoice_title'],
            'total_amount' => $validated['total_amount'],
            'due_amount' => $dueAmount,
            'issued_at' => $validated['issued_at'],
            'due_at' => $validated['due_at'],
            'allow_partial_payment' => $request->boolean('allow_partial_payment'),
            'description' => $validated['description'],
            'status' => $status,
        ]);

        return redirect()->route('accounting.fee-invoices.index')->with('success', 'Fee invoice updated successfully.');
    }

    public function destroy(FeeInvoice $feeInvoice): RedirectResponse
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feeInvoice->school_id === $school->id, 404);

        if ($feeInvoice->payments()->exists()) {
            return back()->with('error', 'Cannot delete invoice because it has associated payments.');
        }

        $feeInvoice->delete();

        return redirect()->route('accounting.fee-invoices.index')->with('success', 'Fee invoice deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $school = $this->selectedSchool();
        $sessionId = $this->selectedSession()->id;
        
        $invoices = FeeInvoice::with(['student.schoolClass', 'feeType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $sessionId)
            ->when($request->filled('class_id'), fn (Builder $query) => $query->whereHas(
                'student', fn (Builder $studentQuery) => $studentQuery->where('school_class_id', $request->integer('class_id'))
            ))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('issued_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_invoices.csv"',
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Invoice Title', 'Fee Type', 'Description', 'Total Amount', 'Amount Paid', 'Due Amount', 'Issued At', 'Due At', 'Allow Partial Payment', 'Status']);

            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $inv->student->admission_no ?? '',
                    $inv->student->full_name ?? '',
                    $inv->student->schoolClass->name ?? '',
                    $inv->invoice_title,
                    $inv->feeType->name ?? '',
                    $inv->description,
                    $inv->total_amount,
                    $inv->amount_paid,
                    $inv->due_amount,
                    $inv->issued_at?->format('Y-m-d'),
                    $inv->due_at?->format('Y-m-d'),
                    $inv->allow_partial_payment ? 'Yes' : 'No',
                    $inv->status,
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
            'Content-Disposition' => 'attachment; filename="fee_invoices_sample.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Invoice Title', 'Description', 'Total Amount', 'Issued At (YYYY-MM-DD)', 'Due At (YYYY-MM-DD)', 'Allow Partial Payment (Yes/No)']);
            fputcsv($file, ['ADM-2023-001', 'Tuition Fee - Term 1', 'Term 1 tuition fees for academic year', '500.00', '2023-08-01', '2023-08-15', 'Yes']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
        ]);

        $school = $this->selectedSchool();
        $session = $this->selectedSession();

        $file = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($file);
        
        if ($header) {
            $header = array_map('trim', $header);
        }

        $expectedHeader = ['Admission No', 'Invoice Title', 'Description', 'Total Amount', 'Issued At (YYYY-MM-DD)', 'Due At (YYYY-MM-DD)', 'Allow Partial Payment (Yes/No)'];
        
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
                if (count($data) < 7) continue;
                
                $admissionNo = trim($data[0]);
                $invoiceTitle = trim($data[1]);
                $description = trim($data[2]);
                $totalAmount = trim($data[3]);
                $issuedAt = trim($data[4]);
                $dueAt = trim($data[5]);
                $allowPartial = strtolower(trim($data[6])) === 'yes';
                
                if (empty($admissionNo) || empty($invoiceTitle) || !is_numeric($totalAmount) || empty($issuedAt) || empty($dueAt)) {
                    $errors[] = "Row $row: Missing required fields or invalid amount.";
                    continue;
                }

                $student = Student::where('school_id', $school->id)->where('academic_session_id', $session->id)->where('admission_no', $admissionNo)->first();
                if (!$student) {
                    $errors[] = "Row $row: Student with Admission No '$admissionNo' not found in this session.";
                    continue;
                }

                FeeInvoice::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $session->id,
                    'student_id' => $student->id,
                    'invoice_title' => $invoiceTitle,
                    'description' => $description,
                    'total_amount' => $totalAmount,
                    'due_amount' => $totalAmount,
                    'amount_paid' => 0,
                    'issued_at' => $issuedAt,
                    'due_at' => $dueAt,
                    'allow_partial_payment' => $allowPartial,
                    'status' => 'pending',
                ]);

                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }

        if (count($errors) > 0) {
            $errStr = implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>...and more.' : '');
            return redirect()->route('accounting.fee-invoices.index')->with('error', "Imported $imported invoices with errors:<br>$errStr");
        }

        return redirect()->route('accounting.fee-invoices.index')->with('success', "Successfully imported $imported fee invoices.");
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\FeeInvoice::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}