@extends('layouts.app')

@section('header', 'Accounting Dashboard')

@section('content')
<style>
.dashboard-grid-2 {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 1.5rem !important;
}
@media (min-width: 1024px) {
    .dashboard-grid-2 {
        grid-template-columns: 1fr 1fr !important;
    }
}
.dashboard-grid-3 {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 1.5rem !important;
}
@media (min-width: 768px) {
    .dashboard-grid-3 {
        grid-template-columns: 1fr 1fr !important;
    }
}
@media (min-width: 1024px) {
    .dashboard-grid-3 {
        grid-template-columns: 1fr 1fr 1fr !important;
    }
}
</style>

<div class="space-y-6 pb-12">

    {{-- ── Top School Header & Session Switcher Banner ── --}}
    <div class="rounded-2xl p-6 lg:p-7 shadow-sm border transition-all"
         style="background: #ffffff; border-color: #E7E0D8; box-shadow: 0 1px 3px rgba(33,27,23,0.05);">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold"
                     style="background: rgba(180, 122, 66, 0.1); color: #B47A42; border: 1px solid rgba(180, 122, 66, 0.2);">
                    <span class="w-2 h-2 rounded-full" style="background: #10B981;"></span>
                    <span>Financial Analytics, Fee Collections & Invoice Settlement Hub</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                        <i data-lucide="wallet" class="w-5 h-5" style="color: #B47A42;"></i>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black tracking-tight" style="color: #211B17;">
                            Accounting Overview — {{ $school?->name ?? 'School Management' }}
                        </h1>
                        <p class="text-xs font-medium" style="color: #766C63;">
                            Managing institutional revenue, fee invoices, payment receipts, outstanding balances, and financial health.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Session Selector Form --}}
                <div class="rounded-xl px-3.5 py-2 flex items-center gap-2.5"
                     style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                    <i data-lucide="calendar" class="w-4 h-4" style="color: #B47A42;"></i>
                    <span class="text-xs font-bold" style="color: #766C63;">Session:</span>
                    <form method="GET" action="{{ route('accounting.dashboard') }}" class="inline-block m-0">
                        <select name="session_id" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-lg py-1 px-2.5 bg-white cursor-pointer focus:outline-none focus:ring-1"
                                style="border: 1px solid #DED5CB; color: #211B17;">
                            @foreach($sessions as $ses)
                                <option value="{{ $ses->id }}" {{ ($session && $session->id == $ses->id) ? 'selected' : '' }}>
                                    {{ $ses->name }}{{ ($globalCurrentSession && $globalCurrentSession->id == $ses->id) ? ' ★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Quick Buttons --}}
                <a href="{{ route('accounting.collect-payment.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #FAF6F0; border: 1px solid #E7E0D8; color: #211B17;">
                    <i data-lucide="hand-coins" class="w-3.5 h-3.5" style="color: #B47A42;"></i>
                    <span>Collect Payment</span>
                </a>

                <a href="{{ route('accounting.fee-invoices.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #B47A42; color: #ffffff;">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>Create Invoice</span>
                </a>
            </div>

        </div>

        @if($globalCurrentSession && $session && $globalCurrentSession->id !== $session->id)
        <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #E7E0D8;">
            <span class="text-amber-700 font-medium">
                Viewing archived financial records for <strong>{{ $session->name }}</strong>
            </span>
            <a href="{{ route('accounting.dashboard', ['session_id' => $globalCurrentSession->id]) }}"
               class="text-xs font-bold underline" style="color: #B47A42;">
                Back to Current ({{ $globalCurrentSession->name }})
            </a>
        </div>
        @endif
    </div>

    {{-- ── 4 KEY ACCOUNTING KPI CIRCULAR METRIC CARDS (Animated SVG Rings) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Collection Efficiency Rate Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Collection Rate</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $collectionRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            {{ $currency }}{{ number_format($collectedAmount, 2) }}
                        </span>
                        <span class="text-stone-400 font-medium">Collected</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $collectionRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $collectionRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Invoices Settlement Rate Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Invoices Settled</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $settlementRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #EFF6FF; color: #1D4ED8;">
                            {{ $paidInvoiceCount }} of {{ $invoiceCount }}
                        </span>
                        <span class="text-stone-400 font-medium">Invoices</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $settlementRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $settlementRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Outstanding / Pending Ratio Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Outstanding Ratio</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $pendingRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #FFFBEB; color: #B45309;">
                            {{ $currency }}{{ number_format($pendingAmount, 2) }}
                        </span>
                        <span class="text-stone-400 font-medium">Balance</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-amber-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $pendingRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $pendingRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Fee Types & Projected Scale Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Fee Structure</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $feeTypesCount }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F3E8FF; color: #7E22CE;">
                            {{ $currency }}{{ number_format($projectedAmount, 2) }}
                        </span>
                        <span class="text-stone-400 font-medium">Projected</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-purple-600 transition-all duration-1000 ease-out" stroke-dasharray="100, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $feeTypesCount }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY ACCOUNTING ANALYSIS CIRCLES (Fee Structure Donut + Financial Multi-Radial Gauge) ── --}}
    <div class="dashboard-grid-2">

        {{-- Circle 1: Fee Structure & Head Distribution (Interactive Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="tags" class="w-4 h-4"></i>
                            Revenue Composition
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Fee Heads & Structure Distribution</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $feeTypesCount }} Fee Heads
                    </span>
                </div>

                {{-- Interactive Donut Chart Container --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="feeHeadsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($feeHeadsData as $fItem)
                    <div class="p-2.5 rounded-xl border flex items-center justify-between"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $fItem['color'] }};"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold text-stone-800 block truncate">{{ $fItem['name'] }}</span>
                                <span class="text-[10px] text-stone-500 font-medium">{{ $fItem['percentage'] }}%</span>
                            </div>
                        </div>
                        <span class="text-xs font-black text-stone-900 ml-1">{{ $currency }}{{ number_format($fItem['amount'], 0) }}</span>
                    </div>
                    @empty
                    <div class="col-span-3 text-xs text-stone-400 py-3 text-center">No fee heads configured</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Configured tuition, admission & service fee heads</span>
                <a href="{{ route('accounting.fee-types.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View all fee heads</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        {{-- Circle 2: Financial Health & Collections Pipeline (Multi-Radial Gauge) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                            Liquidity Health
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Collections & Realization Pipeline</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $currency }}{{ number_format($generatedAmount, 2) }} Total
                    </span>
                </div>

                {{-- Interactive Radial Meter --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="financialHealthRadialChart" class="w-full flex justify-center"></div>
                </div>

                {{-- 3 Key Financial Stat Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Collected</span>
                        <span class="text-base font-black text-emerald-600 block mt-0.5">{{ $currency }}{{ number_format($collectedAmount, 0) }}</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $collectionRate }}% Rate</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Outstanding</span>
                        <span class="text-base font-black block mt-0.5" style="color: #B47A42;">{{ $currency }}{{ number_format($pendingAmount, 0) }}</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $pendingRate }}% Pending</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Invoices Paid</span>
                        <span class="text-base font-black text-blue-600 block mt-0.5">{{ $settlementRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $paidInvoiceCount }} of {{ $invoiceCount }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Real-time revenue settlement and cash-flow health</span>
                <a href="{{ route('accounting.fee-invoices.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View all invoices</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: SECONDARY ACCOUNTING ANALYSIS CIRCLES (3-COLUMN GRID) ── --}}
    <div class="dashboard-grid-3">

        {{-- Circle 3A: Revenue Streams Allocation (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                            Revenue Streams
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Revenue Streams Share</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        2 Streams
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="revenueStreamsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Legend & Stream Breakdown --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-blue-50/70 border border-blue-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span class="font-bold text-blue-900">Academic & Tuition Fees</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-blue-900">{{ $currency }}{{ number_format($academicGenerated, 0) }}</span>
                            <span class="text-[10px] text-blue-600">({{ $academicShareRate }}%)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-purple-50/70 border border-purple-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                            <span class="font-bold text-purple-900">Transport & Bus Fees</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-purple-900">{{ $currency }}{{ number_format($transportGenerated, 0) }}</span>
                            <span class="text-[10px] text-purple-600">({{ $transportShareRate }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Tuition vs transportation</span>
                <a href="{{ route('accounting.transport-invoices.index') }}" class="font-bold underline" style="color: #B47A42;">Transport Bills</a>
            </div>
        </div>

        {{-- Circle 3B: Invoices Settlement Status (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            Invoice Resolution
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Settlement Pipeline</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $invoiceCount }} Invoices
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="invoiceSettlementDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Status Breakdown Stats --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-emerald-50/70 border border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-emerald-900">Paid In Full</span>
                        </div>
                        <span class="font-black text-emerald-900">{{ $paidInvoiceCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-amber-50/70 border border-amber-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="font-bold text-amber-900">Partially Paid</span>
                        </div>
                        <span class="font-black text-amber-900">{{ $partialInvoiceCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-rose-50/70 border border-rose-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="font-bold text-rose-900">Unpaid / Due</span>
                        </div>
                        <span class="font-black text-rose-900">{{ $unpaidInvoiceCount }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Invoice aging & status</span>
                <a href="{{ route('accounting.fee-invoices.index') }}" class="font-bold underline" style="color: #B47A42;">Manage Invoices</a>
            </div>
        </div>

        {{-- Circle 3C: Financial Realization Share (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-amber-600">
                            <i data-lucide="circle-dollar-sign" class="w-4 h-4"></i>
                            Cash-Flow
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Realization Balance</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #FFFBEB; color: #B45309;">
                        {{ $collectionRate }}% Realized
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="realizationShareDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Stats --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-emerald-50/70 border border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-emerald-900">Realized Collections</span>
                        </div>
                        <span class="font-black text-emerald-900">{{ $currency }}{{ number_format($collectedAmount, 0) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-amber-50/70 border border-amber-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-bold text-amber-900">Outstanding Balance</span>
                        </div>
                        <span class="font-black text-amber-900">{{ $currency }}{{ number_format($pendingAmount, 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Total revenue clearance</span>
                <a href="{{ route('accounting.collect-payment.index') }}" class="font-bold underline" style="color: #B47A42;">Collect Payment</a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: OPERATIONS & MANAGEMENT HUB (4 Quick Action Cards) ── --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-stone-900">Accounting Operations Hub</h2>
                <p class="text-xs text-stone-500">Direct management shortcuts for billing, fee collection, and financial records</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Fee Invoices --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #EFF6FF; color: #1D4ED8;">
                        <i data-lucide="receipt-text" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $invoiceCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Fee Invoices</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Generate, issue, and manage academic billing and fee challans</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('accounting.fee-invoices.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View Invoices</a>
                    <a href="{{ route('accounting.fee-invoices.create') }}" class="text-xs font-bold px-2 py-1 rounded-md text-white" style="background: #B47A42;">+ Create</a>
                </div>
            </div>

            {{-- 2. Collect Payment --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #ECFDF5; color: #047857;">
                        <i data-lucide="hand-coins" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $paidInvoiceCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Collect Payment</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Record payments by student ID, cash, bank transfer, or online</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('accounting.collect-payment.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Receive Money</a>
                    <span class="text-[11px] font-semibold text-stone-400">Instant</span>
                </div>
            </div>

            {{-- 3. Transport Invoices --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #F3E8FF; color: #7E22CE;">
                        <i data-lucide="bus" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $currency }}{{ number_format($transportGenerated, 0) }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Transport Invoices</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Route-based transportation fee schedules and bus receipts</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('accounting.transport-invoices.index') }}" class="text-xs font-bold text-purple-700 hover:underline">Transport Bills</a>
                    <span class="text-[11px] font-semibold text-stone-400">Routes</span>
                </div>
            </div>

            {{-- 4. Fee Types & Structure --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #FFFBEB; color: #B45309;">
                        <i data-lucide="tags" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $feeTypesCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Fee Types & Rates</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Define tuition, examination, lab, library, and sports fees</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('accounting.fee-types.index') }}" class="text-xs font-bold text-amber-700 hover:underline">Fee Master</a>
                    <span class="text-[11px] font-semibold text-stone-400">Structure</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: RECENT FEE INVOICES & PAYMENTS DIRECTORY ── --}}
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden" style="border-color: #E7E0D8;">
        <div class="p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3" style="border-color: #F0EAE1;">
            <div>
                <h3 class="text-base font-bold text-stone-900">Recent Session Fee Invoices</h3>
                <p class="text-xs text-stone-500">Billing records and invoice status for {{ $session->name }}</p>
            </div>
            <a href="{{ route('accounting.fee-invoices.index') }}"
               class="text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border hover:bg-stone-50 transition"
               style="border-color: #DED5CB; color: #211B17;">
                <span>View Full Register ({{ $invoiceCount }})</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b bg-stone-50/70 text-stone-600 font-bold uppercase tracking-wider text-[11px]" style="border-color: #F0EAE1;">
                        <th class="py-3 px-4">Invoice Title</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Fee Head</th>
                        <th class="py-3 px-4 text-right">Total Amount</th>
                        <th class="py-3 px-4 text-right">Amount Paid</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-medium text-stone-800">
                    @forelse($recentInvoices as $inv)
                    <tr class="hover:bg-stone-50/60 transition">
                        <td class="py-3.5 px-4 font-bold text-stone-900">
                            {{ $inv->invoice_title }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-stone-900 block">{{ $inv->student?->full_name ?? '—' }}</span>
                            <span class="text-[10px] text-stone-400">Adm: {{ $inv->student?->admission_no ?? '—' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold text-[11px]"
                                  style="background: #EFF6FF; color: #1D4ED8;">
                                {{ $inv->feeType?->name ?? 'Tuition Fee' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-stone-900">
                            {{ $currency }}{{ number_format($inv->total_amount, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-emerald-600">
                            {{ $currency }}{{ number_format($inv->amount_paid, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(strtolower((string)$inv->status) === 'paid')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Paid
                                </span>
                            @elseif(strtolower((string)$inv->status) === 'partial')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Partial
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-rose-100 text-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Unpaid
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('accounting.fee-invoices.show', $inv) }}"
                                   class="px-2.5 py-1 rounded text-[11px] font-bold border hover:bg-stone-100 text-stone-700"
                                   style="border-color: #DED5CB;">
                                    View
                                </a>
                                @if(strtolower((string)$inv->status) !== 'paid')
                                <a href="{{ route('accounting.collect-payment.index', ['invoice_id' => $inv->id]) }}"
                                   class="px-2.5 py-1 rounded text-[11px] font-bold text-white hover:brightness-105"
                                   style="background: #B47A42;">
                                    Collect
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-stone-400">
                            No fee invoices created for this session yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── ApexCharts Script ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ── 1. Fee Structure & Head Distribution Donut Chart ──
    const feeHeadsChartEl = document.querySelector("#feeHeadsDonutChart");
    if (feeHeadsChartEl) {
        const headsData = @json($feeHeadsData);
        const series = headsData.map(item => item.amount);
        const labels = headsData.map(item => item.name);
        const colors = headsData.map(item => item.color);
        const totalHeads = {{ (int) $feeTypesCount }};

        if (series.length > 0 && series.reduce((a, b) => a + b, 0) > 0) {
            const options = {
                series: series,
                labels: labels,
                colors: colors.length > 0 ? colors : ['#B47A42', '#3B82F6', '#10B981', '#8B5CF6'],
                chart: { type: 'donut', height: 230, animations: { enabled: true } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { show: false },
                                value: { 
                                    show: true, 
                                    fontSize: '18px', 
                                    fontWeight: 800, 
                                    color: '#211B17', 
                                    offsetY: 6,
                                    formatter: function (val) {
                                        return '{{ $currency }}' + Number(val).toLocaleString();
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Fee Heads',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalHeads;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                stroke: { width: 2, colors: ['#ffffff'] },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return '{{ $currency }}' + Number(val).toLocaleString();
                        }
                    }
                }
            };
            const chart = new ApexCharts(feeHeadsChartEl, options);
            chart.render();
        } else {
            feeHeadsChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No fee heads configured</div>';
        }
    }

    // ── 2. Financial Collections Multi-Radial Bar ──
    const healthChartEl = document.querySelector("#financialHealthRadialChart");
    if (healthChartEl) {
        const collectRate = {{ (float) $collectionRate }};
        const settleRate = {{ (float) $settlementRate }};
        const acadRate = {{ (float) $academicShareRate }};

        const options = {
            series: [collectRate, settleRate, acadRate],
            chart: {
                height: 230,
                type: 'radialBar',
                animations: { enabled: true }
            },
            plotOptions: {
                radialBar: {
                    hollow: { size: '32%' },
                    track: { background: '#F5F2EC', strokeWidth: '100%' },
                    dataLabels: {
                        name: { fontSize: '11px', fontWeight: 600, color: '#766C63', offsetY: -6 },
                        value: { fontSize: '16px', fontWeight: 800, color: '#211B17', offsetY: 4, formatter: val => val + '%' },
                        total: {
                            show: true,
                            label: 'Efficiency',
                            color: '#766C63',
                            fontSize: '11px',
                            fontWeight: 700,
                            formatter: function (w) {
                                return collectRate + '%';
                            }
                        }
                    }
                }
            },
            colors: ['#10B981', '#3B82F6', '#B47A42'],
            labels: ['Collected', 'Invoices Paid', 'Academic Share'],
            stroke: { lineCap: 'round' }
        };
        const chart = new ApexCharts(healthChartEl, options);
        chart.render();
    }

    // ── 3. Revenue Streams Allocation Donut Chart ──
    const streamsChartEl = document.querySelector("#revenueStreamsDonutChart");
    if (streamsChartEl) {
        const academicAmt = {{ (float) $academicGenerated }};
        const transportAmt = {{ (float) $transportGenerated }};
        const totalAmt = {{ (float) $generatedAmount }};

        const hasRevenue = totalAmt > 0;
        const series = hasRevenue ? [academicAmt, transportAmt] : [1];
        const labels = hasRevenue ? ['Academic Fees', 'Transport Fees'] : ['No Revenue'];
        const colors = hasRevenue ? ['#3B82F6', '#8B5CF6'] : ['#E5E7EB'];

        const options = {
            series: series,
            labels: labels,
            colors: colors,
            chart: { type: 'donut', height: 180, animations: { enabled: true } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { 
                                show: true, 
                                fontSize: '16px', 
                                fontWeight: 800, 
                                color: '#211B17', 
                                offsetY: 6,
                                formatter: function (val) {
                                    return '{{ $currency }}' + Number(val).toLocaleString();
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Generated',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return '{{ $currency }}' + totalAmt.toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return '{{ $currency }}' + Number(val).toLocaleString();
                    }
                }
            }
        };
        const chart = new ApexCharts(streamsChartEl, options);
        chart.render();
    }

    // ── 4. Invoices Settlement Donut Chart ──
    const invoiceStatusChartEl = document.querySelector("#invoiceSettlementDonutChart");
    if (invoiceStatusChartEl) {
        const paidCount = {{ (int) $paidInvoiceCount }};
        const partialCount = {{ (int) $partialInvoiceCount }};
        const unpaidCount = {{ (int) $unpaidInvoiceCount }};
        const totalInvoices = {{ (int) $invoiceCount }};

        const hasInvoices = totalInvoices > 0;
        const series = hasInvoices ? [paidCount, partialCount, unpaidCount] : [1];
        const labels = hasInvoices ? ['Paid', 'Partial', 'Unpaid'] : ['No Invoices'];
        const colors = hasInvoices ? ['#10B981', '#F59E0B', '#EF4444'] : ['#E5E7EB'];

        const options = {
            series: series,
            labels: labels,
            colors: colors,
            chart: { type: 'donut', height: 180, animations: { enabled: true } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: true, fontSize: '18px', fontWeight: 800, color: '#211B17', offsetY: 6 },
                            total: {
                                show: true,
                                showAlways: true,
                                label: hasInvoices ? 'Settled' : 'Status',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return hasInvoices ? '{{ $settlementRate }}%' : '0';
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return hasInvoices ? val + ' Invoices' : 'No data';
                    }
                }
            }
        };
        const chart = new ApexCharts(invoiceStatusChartEl, options);
        chart.render();
    }

    // ── 5. Realization Share Donut Chart ──
    const realizationChartEl = document.querySelector("#realizationShareDonutChart");
    if (realizationChartEl) {
        const collected = {{ (float) $collectedAmount }};
        const pending = {{ (float) $pendingAmount }};
        const total = {{ (float) $generatedAmount }};

        const hasAmount = total > 0;
        const series = hasAmount ? [collected, pending] : [1];
        const labels = hasAmount ? ['Collected', 'Outstanding'] : ['No Bills'];
        const colors = hasAmount ? ['#10B981', '#F59E0B'] : ['#E5E7EB'];

        const options = {
            series: series,
            labels: labels,
            colors: colors,
            chart: { type: 'donut', height: 180, animations: { enabled: true } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { 
                                show: true, 
                                fontSize: '16px', 
                                fontWeight: 800, 
                                color: '#211B17', 
                                offsetY: 6,
                                formatter: function (val) {
                                    return '{{ $currency }}' + Number(val).toLocaleString();
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Realized',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return hasAmount ? '{{ $collectionRate }}%' : '0%';
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return '{{ $currency }}' + Number(val).toLocaleString();
                    }
                }
            }
        };
        const chart = new ApexCharts(realizationChartEl, options);
        chart.render();
    }

});
</script>
@endsection
