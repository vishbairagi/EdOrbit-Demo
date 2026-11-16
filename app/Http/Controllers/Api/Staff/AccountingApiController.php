<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\PaymentMethod;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingApiController extends BaseApiController
{
    // ==========================================
    // 1. FEE TYPES (accounting.fee-types)
    // ==========================================

    public function feeTypesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = FeeType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $feeTypes = $query->orderBy('name')->get();

        return $this->success($request, $feeTypes, 'Fee types retrieved successfully');
    }

    public function feeTypeStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0',
            'fee_period_id' => 'nullable|exists:fee_periods,id',
            'student_type_id' => 'nullable|exists:student_types,id',
            'invoice_on_admission' => 'nullable|boolean',
            'include_on_promotion' => 'nullable|boolean',
        ]);

        $feeType = FeeType::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'fee_period_id' => $validated['fee_period_id'] ?? null,
            'student_type_id' => $validated['student_type_id'] ?? null,
            'invoice_on_admission' => $validated['invoice_on_admission'] ?? false,
            'include_on_promotion' => $validated['include_on_promotion'] ?? false,
        ]);

        return $this->success($request, $feeType, 'Fee type created successfully', 201);
    }

    public function feeTypeShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $feeType = FeeType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $feeType) {
            return $this->error('Fee type not found.', 404);
        }

        return $this->success($request, $feeType, 'Fee type details retrieved');
    }

    public function feeTypeUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $feeType = FeeType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $feeType) {
            return $this->error('Fee type not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'amount' => 'sometimes|required|numeric|min:0',
            'fee_period_id' => 'nullable|exists:fee_periods,id',
            'student_type_id' => 'nullable|exists:student_types,id',
            'invoice_on_admission' => 'nullable|boolean',
            'include_on_promotion' => 'nullable|boolean',
        ]);

        $feeType->update($validated);

        return $this->success($request, $feeType, 'Fee type updated successfully');
    }

    public function feeTypeDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $feeType = FeeType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $feeType) {
            return $this->error('Fee type not found.', 404);
        }

        $feeType->delete();

        return $this->success($request, null, 'Fee type deleted successfully');
    }

    // ==========================================
    // 2. FEE INVOICES (accounting.fee-invoices)
    // ==========================================

    public function invoicesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = FeeInvoice::with(['student.schoolClass', 'student.section', 'payments'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($studentId = $request->input('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Fee invoices retrieved');
    }

    public function invoiceStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'total_amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $count = FeeInvoice::where('school_id', $school->id)->count() + 1;
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $invoice = FeeInvoice::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'student_id' => $validated['student_id'],
            'invoice_number' => $invoiceNumber,
            'total_amount' => $validated['total_amount'],
            'paid_amount' => 0,
            'status' => 'Unpaid',
            'due_date' => $validated['due_date'],
            'description' => $validated['description'] ?? null,
        ]);

        $invoice->load(['student', 'payments']);

        return $this->success($request, $invoice, 'Fee invoice created successfully', 201);
    }

    public function invoiceShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $invoice = FeeInvoice::with(['student.schoolClass', 'student.section', 'payments'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $invoice) {
            return $this->error('Invoice not found.', 404);
        }

        return $this->success($request, $invoice, 'Invoice details retrieved');
    }

    public function invoiceUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $invoice = FeeInvoice::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $invoice) {
            return $this->error('Invoice not found.', 404);
        }

        $validated = $request->validate([
            'total_amount' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'status' => 'nullable|in:Paid,Unpaid,Partially Paid,Cancelled',
            'description' => 'nullable|string|max:500',
        ]);

        $invoice->update($validated);
        $invoice->load(['student', 'payments']);

        return $this->success($request, $invoice, 'Invoice updated successfully');
    }

    public function invoiceDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $invoice = FeeInvoice::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $invoice) {
            return $this->error('Invoice not found.', 404);
        }

        $invoice->payments()->delete();
        $invoice->delete();

        return $this->success($request, null, 'Invoice deleted successfully');
    }

    // ==========================================
    // 3. COLLECT PAYMENT (accounting.collect-payment)
    // ==========================================

    public function collectPayment(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'fee_invoice_id' => 'required|exists:fee_invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        $invoice = FeeInvoice::where('school_id', $school->id)->findOrFail($validated['fee_invoice_id']);

        $payment = FeePayment::create([
            'fee_invoice_id' => $invoice->id,
            'school_id' => $school->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payment_date' => $validated['payment_date'] ?? date('Y-m-d'),
            'note' => $validated['note'] ?? null,
        ]);

        // Update invoice paid amount & status
        $newPaidAmount = $invoice->payments()->sum('amount');
        $newStatus = ($newPaidAmount >= $invoice->total_amount) ? 'Paid' : 'Partially Paid';

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $newStatus,
        ]);

        return $this->success($request, [
            'payment' => $payment,
            'invoice' => $invoice->fresh(['payments']),
        ], 'Payment recorded successfully', 201);
    }

    public function paymentHistory(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $query = FeePayment::with(['invoice.student'])
            ->where('school_id', $school?->id);

        if ($invoiceId = $request->input('invoice_id')) {
            $query->where('fee_invoice_id', $invoiceId);
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Payment history retrieved');
    }
}
