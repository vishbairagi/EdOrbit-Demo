<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\SchoolSetting;
use App\Models\SystemSetting;
use App\Models\TransportInvoice;
use App\Models\TransportPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingDashboardController extends AccountingController
{
    public function index(Request $request): View
    {
        if ($request->filled('session_id')) {
            session([
                'selected_session_id' => $request->session_id,
                'selected_academic_session_id' => $request->session_id,
            ]);
        }

        $school = $this->selectedSchool();
        $session = $this->selectedSession();
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $globalCurrentSessionId = SystemSetting::getSetting('current_session_id');
        $globalCurrentSession = AcademicSession::find($globalCurrentSessionId);

        // ── 1. Fee Types & Projected Revenue ─────────────────────────────────
        $feeTypes = FeeType::with(['classes' => fn ($query) => $query->withCount([
            'students' => fn ($studentQuery) => $studentQuery
                ->where('school_id', $school->id)
                ->where('academic_session_id', $session->id),
        ])])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('dashboard_disabled', false)
            ->get();

        $feeTypesCount = $feeTypes->count();
        $projectedAmount = (float) $feeTypes->sum(fn (FeeType $feeType): float => 
            (float) $feeType->amount * $feeType->classes->sum('students_count')
        );

        // Fee Heads Breakdown for Donut Chart
        $palette = ['#B47A42', '#3B82F6', '#10B981', '#8B5CF6', '#EC4899', '#F59E0B', '#06B6D4', '#6366F1'];
        $feeHeadsGrouped = $feeTypes->groupBy('name');
        $feeHeadsData = [];
        $hIdx = 0;
        $totalHeadsAmount = (float) $feeTypes->sum('amount');
        foreach ($feeHeadsGrouped as $headName => $items) {
            $sumAmt = (float) $items->sum('amount');
            $pct = $totalHeadsAmount > 0 ? round(($sumAmt / $totalHeadsAmount) * 100, 1) : 0;
            $feeHeadsData[] = [
                'name' => $headName,
                'amount' => $sumAmt,
                'percentage' => $pct,
                'color' => $palette[$hIdx % count($palette)],
            ];
            $hIdx++;
        }

        // ── 2. Invoices & Revenue Streams ────────────────────────────────────
        $invoices = FeeInvoice::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);
            
        $tInvoices = TransportInvoice::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        $academicGenerated = (float) (clone $invoices)->sum('total_amount');
        $academicPaid = (float) (clone $invoices)->sum('amount_paid');
        $academicPending = max(0, $academicGenerated - $academicPaid);

        $transportGenerated = (float) (clone $tInvoices)->sum('total_amount');
        $transportPaid = (float) (clone $tInvoices)->sum('amount_paid');
        $transportPending = max(0, $transportGenerated - $transportPaid);

        $generatedAmount = $academicGenerated + $transportGenerated;
        $collectedAmount = $academicPaid + $transportPaid;
        $pendingAmount = $academicPending + $transportPending;

        $collectionRate = $generatedAmount > 0 ? round(($collectedAmount / $generatedAmount) * 100, 1) : 0;
        $pendingRate = $generatedAmount > 0 ? round(($pendingAmount / $generatedAmount) * 100, 1) : 0;
        $academicShareRate = $generatedAmount > 0 ? round(($academicGenerated / $generatedAmount) * 100, 1) : 0;
        $transportShareRate = $generatedAmount > 0 ? round(($transportGenerated / $generatedAmount) * 100, 1) : 0;

        // ── 3. Invoices Status & Settlements ─────────────────────────────────
        $invoiceCount = (clone $invoices)->count() + (clone $tInvoices)->count();
        $paidInvoiceCount = (clone $invoices)->where('status', 'paid')->count() + (clone $tInvoices)->where('status', 'paid')->count();
        $partialInvoiceCount = (clone $invoices)->where('status', 'partial')->count() + (clone $tInvoices)->where('status', 'partial')->count();
        $unpaidInvoiceCount = (clone $invoices)->where('status', 'unpaid')->count() + (clone $tInvoices)->where('status', 'unpaid')->count();
        $pendingInvoiceCount = $partialInvoiceCount + $unpaidInvoiceCount;
        $settlementRate = $invoiceCount > 0 ? round(($paidInvoiceCount / $invoiceCount) * 100, 1) : 0;

        // ── 4. Recent Payments & Recent Invoices ─────────────────────────────
        $recentPayments = FeePayment::with(['invoice.student', 'paymentMethod'])
            ->where('school_id', $school->id)
            ->whereHas('invoice', fn ($query) => $query->where('academic_session_id', $session->id))
            ->latest('paid_on')
            ->latest('id')
            ->limit(8)
            ->get();

        $recentInvoices = FeeInvoice::with(['student', 'feeType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->latest('id')
            ->limit(8)
            ->get();

        $currency = SchoolSetting::getCurrencySymbol();

        return view('accounting.dashboard', compact(
            'school',
            'session',
            'sessions',
            'globalCurrentSession',
            'currency',
            'projectedAmount',
            'generatedAmount',
            'collectedAmount',
            'pendingAmount',
            'collectionRate',
            'pendingRate',
            'academicGenerated',
            'academicPaid',
            'academicPending',
            'transportGenerated',
            'transportPaid',
            'transportPending',
            'academicShareRate',
            'transportShareRate',
            'feeTypesCount',
            'feeHeadsData',
            'invoiceCount',
            'paidInvoiceCount',
            'partialInvoiceCount',
            'unpaidInvoiceCount',
            'pendingInvoiceCount',
            'settlementRate',
            'recentPayments',
            'recentInvoices'
        ));
    }
}
