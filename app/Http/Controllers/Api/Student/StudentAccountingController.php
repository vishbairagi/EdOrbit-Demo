<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\SchoolSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAccountingController extends BaseApiController
{
    /**
     * Student Fee Structure API.
     */
    public function feeStructure(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolId = $school?->id;
        $sessionId = $session?->id;

        $feeTypes = FeeType::where('school_id', $schoolId)
            ->where(function ($q) use ($student) {
                if ($student->school_class_id) {
                    $q->whereHas('classes', function ($qc) use ($student) {
                        $qc->where('school_classes.id', $student->school_class_id);
                    })->orWhereDoesntHave('classes');
                }
            })
            ->get()
            ->map(function ($ft) {
                return [
                    'id' => $ft->id,
                    'name' => $ft->name,
                    'amount' => (float) $ft->amount,
                    'period' => $ft->feePeriod?->name ?? 'Annual',
                    'description' => $ft->description ?? '',
                ];
            });

        return $this->success($request, [
            'fee_structure' => $feeTypes,
            'currency' => SchoolSetting::getSetting($schoolId, $sessionId, 'currency_symbol', '₹'),
        ], 'Fee structure retrieved successfully');
    }

    /**
     * Session Fee Summary API.
     */
    public function sessionFeeSummary(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolId = $school?->id;
        $sessionId = $session?->id;

        $invoiceQuery = FeeInvoice::where('student_id', $student->id);
        if ($sessionId) {
            $invoiceQuery->where('academic_session_id', $sessionId);
        }

        $totalInvoiced = (float) $invoiceQuery->sum('total_amount');
        $totalPaid = (float) $invoiceQuery->sum('amount_paid');
        $totalDue = max(0, $totalInvoiced - $totalPaid);
        $totalDiscount = 0.0;

        $data = [
            'total_amount' => $totalInvoiced,
            'paid_amount' => $totalPaid,
            'amount_paid' => $totalPaid,
            'due_amount' => $totalDue,
            'discount_amount' => $totalDiscount,
            'currency' => SchoolSetting::getSetting($schoolId, $sessionId, 'currency_symbol', '₹'),
            'status' => $totalDue == 0 ? 'fully_paid' : ($totalPaid > 0 ? 'partially_paid' : 'unpaid'),
        ];

        return $this->success($request, ['summary' => $data], 'Session fee summary retrieved successfully');
    }

    /**
     * Fee Invoices List API.
     */
    public function feeInvoices(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $session = $this->getSession($request);

        $query = FeeInvoice::with(['payments'])
            ->where('student_id', $student->id);

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest('id')->paginate(15);

        $items = $invoices->getCollection()->map(function ($inv) {
            $paid = (float) ($inv->amount_paid ?? 0);
            $due = max(0, (float) $inv->total_amount - $paid);

            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number ?? ('INV-' . $inv->id),
                'title' => $inv->invoice_title ?? $inv->title ?? 'Tuition & Academic Fees',
                'description' => $inv->description ?? '',
                'total_amount' => (float) $inv->total_amount,
                'paid_amount' => $paid,
                'amount_paid' => $paid,
                'due_amount' => $inv->due_amount !== null ? (float) $inv->due_amount : $due,
                'status' => $inv->status,
                'due_date' => $inv->due_at instanceof \DateTimeInterface ? $inv->due_at->format('Y-m-d') : ($inv->due_at ?? ''),
                'created_at' => $inv->created_at?->format('Y-m-d') ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Fee invoices retrieved successfully',
            'data' => [
                'invoices' => [
                    'data' => $items,
                    'current_page' => $invoices->currentPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'last_page' => $invoices->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Fee Invoice Details API.
     */
    public function feeInvoiceDetails(Request $request, $id): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $invoice = FeeInvoice::with(['payments'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $paid = (float) ($invoice->amount_paid ?? 0);
        $due = $invoice->due_amount !== null ? (float) $invoice->due_amount : max(0, (float) $invoice->total_amount - $paid);

        $data = [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number ?? ('INV-' . $invoice->id),
            'title' => $invoice->invoice_title ?? $invoice->title ?? 'Tuition & Academic Fees',
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class_name' => $student->schoolClass?->name ?? '',
            'section_name' => $student->section?->name ?? '',
            'total_amount' => (float) $invoice->total_amount,
            'paid_amount' => $paid,
            'amount_paid' => $paid,
            'due_amount' => $due,
            'discount_amount' => 0.0,
            'status' => $invoice->status,
            'due_date' => $invoice->due_at instanceof \DateTimeInterface ? $invoice->due_at->format('Y-m-d') : ($invoice->due_at ?? ''),
            'school_name' => $school?->name ?? '',
            'school_logo' => $school?->logo_url ?? '',
            'payments' => $invoice->payments->map(function ($p) {
                return [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'payment_method' => $p->payment_method ?? 'Online',
                    'transaction_id' => $p->transaction_id ?? '',
                    'payment_date' => $p->paid_on ? (is_string($p->paid_on) ? $p->paid_on : $p->paid_on->format('Y-m-d')) : ($p->created_at?->format('Y-m-d') ?? ''),
                ];
            }),
        ];

        return $this->success($request, ['invoice' => $data], 'Fee invoice details retrieved successfully');
    }

    /**
     * Fee Pay Invoice API.
     */
    public function payInvoice(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'id'             => 'required|exists:fee_invoices,id',
            'amount'         => 'required|numeric|min:1',
            'transaction_id' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        $invoice = FeeInvoice::where('student_id', $student->id)->findOrFail($validated['id']);
        $payAmount = (float) $validated['amount'];

        $payment = FeePayment::create([
            'school_id' => $invoice->school_id ?? $school?->id ?? 1,
            'fee_invoice_id' => $invoice->id,
            'amount' => $payAmount,
            'transaction_id' => $validated['transaction_id'] ?? ('TXN-' . strtoupper(uniqid())),
            'authorized_by' => 'Student Self',
            'paid_on' => now()->toDateString(),
            'note' => 'Student self-payment via mobile app',
        ]);

        $newPaid = (float) ($invoice->amount_paid ?? 0) + $payAmount;
        $newDue = max(0, (float) $invoice->total_amount - $newPaid);
        $newStatus = $newDue <= 0 ? 'paid' : 'partially_paid';

        $invoice->update([
            'amount_paid' => $newPaid,
            'due_amount' => $newDue,
            'status' => $newStatus,
        ]);

        return $this->success($request, [
            'payment_id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'amount_paid' => $payAmount,
            'invoice_status' => $newStatus,
            'remaining_due' => max(0, (float) $invoice->total_amount - $newPaid),
        ], 'Payment recorded successfully');
    }

    /**
     * Payment History List API.
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $payments = FeePayment::whereHas('invoice', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->with('invoice')
        ->latest('id')
        ->get()
        ->map(function ($p) {
            return [
                'id' => $p->id,
                'invoice_id' => $p->fee_invoice_id,
                'invoice_number' => $p->invoice?->invoice_number ?? '',
                'amount' => (float) $p->amount,
                'payment_method' => $p->payment_method ?? 'Cash',
                'transaction_id' => $p->transaction_id ?? '',
                'date' => $p->created_at?->format('Y-m-d H:i:s') ?? '',
            ];
        });

        return $this->success($request, ['payments' => $payments], 'Payment history retrieved successfully');
    }

    /**
     * Payment Receipt Details API.
     */
    public function paymentReceipt(Request $request, $id): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $payment = FeePayment::whereHas('invoice', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->with('invoice')
        ->findOrFail($id);

        $data = [
            'receipt_no' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
            'payment_id' => $payment->id,
            'invoice_number' => $payment->invoice?->invoice_number ?? '',
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class_name' => $student->schoolClass?->name ?? '',
            'section_name' => $student->section?->name ?? '',
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'transaction_id' => $payment->transaction_id,
            'date' => $payment->created_at?->format('Y-m-d H:i:s') ?? '',
            'school_name' => $school?->name ?? '',
            'school_logo' => $school?->logo_url ?? '',
            'school_phone' => $school?->phone ?? '',
            'school_address' => $school?->address ?? '',
        ];

        return $this->success($request, ['receipt' => $data], 'Payment receipt retrieved successfully');
    }
}
