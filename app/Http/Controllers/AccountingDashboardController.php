<?php

namespace App\Http\Controllers;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeType;
use Illuminate\View\View;

class AccountingDashboardController extends AccountingController
{
    public function index(): View
    {
        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $feeTypes = FeeType::with(['classes' => fn ($query) => $query->withCount([
            'students' => fn ($studentQuery) => $studentQuery
                ->where('school_id', $school->id)
                ->where('academic_session_id', $session->id),
        ])])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('dashboard_disabled', false)
            ->get();

        $projectedAmount = $feeTypes->sum(fn (FeeType $feeType): float => (float) $feeType->amount * $feeType->classes->sum('students_count')
        );
        $invoices = FeeInvoice::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);
            
        $tInvoices = \App\Models\TransportInvoice::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        $generatedAmount = (clone $invoices)->sum('total_amount') + (clone $tInvoices)->sum('total_amount');
        $pendingAmount = ((clone $invoices)->sum('total_amount') - (clone $invoices)->sum('amount_paid')) 
                       + ((clone $tInvoices)->sum('total_amount') - (clone $tInvoices)->sum('amount_paid'));
                       
        $collectedAmount = FeePayment::where('school_id', $school->id)
            ->whereHas('invoice', fn ($query) => $query->where('academic_session_id', $session->id))
            ->sum('amount') 
            + \App\Models\TransportPayment::whereHas('invoice', fn ($query) => $query->where('school_id', $school->id)->where('academic_session_id', $session->id))
            ->sum('amount');
            
        $invoiceCount = (clone $invoices)->count() + (clone $tInvoices)->count();
        $pendingInvoiceCount = (clone $invoices)->whereIn('status', ['unpaid', 'partial'])->count() 
                             + (clone $tInvoices)->whereIn('status', ['unpaid', 'partial'])->count();
        $recentPayments = FeePayment::with(['invoice.student', 'paymentMethod'])
            ->where('school_id', $school->id)
            ->whereHas('invoice', fn ($query) => $query->where('academic_session_id', $session->id))
            ->latest('paid_on')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('accounting.dashboard', compact(
            'school', 'session', 'projectedAmount', 'generatedAmount', 'pendingAmount',
            'collectedAmount', 'invoiceCount', 'pendingInvoiceCount', 'recentPayments'
        ));
    }
}
