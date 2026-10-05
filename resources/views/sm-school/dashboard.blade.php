@extends('layouts.app')

@section('header', 'School Dashboard')

@section('content')
{{-- Top Session Header --}}
<div class="mb-4 text-center flex flex-col items-center justify-center">
    <div class="flex items-center justify-center text-xl font-bold text-heading mb-1">
        <i data-lucide="school" class="w-6 h-6 mr-2 text-navy"></i>
        <span>{{ $school->name }} <span class="text-gray-500 font-normal ml-1">{{ $currentSession ? $currentSession->name : 'No Session' }}</span></span>
    </div>

    <div class="flex items-center justify-center text-sm mt-1 gap-2">
        <span class="text-gray-600">Current Session:</span>
        <form method="GET" action="{{ route('sm-school.dashboard') }}" class="inline-block">
            <select name="session_id" onchange="this.form.submit()" class="border border-gray-300 rounded-md py-1 px-3 bg-white text-gray-800 text-sm focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ ($currentSession && $currentSession->id == $session->id) ? 'selected' : '' }}>
                        {{ $session->name }}{{ ($globalCurrentSession && $globalCurrentSession->id == $session->id) ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        @if($globalCurrentSession && $currentSession && $globalCurrentSession->id !== $currentSession->id)
            <a href="{{ route('sm-school.dashboard', ['session_id' => $globalCurrentSession->id]) }}" class="text-xs text-navy underline hover:text-primary-dark">
                Back to current ({{ $globalCurrentSession->name }})
            </a>
        @endif
    </div>
</div>

{{-- Action Bar --}}
<div class="bg-navy rounded-xl shadow-md px-4 py-2.5 flex flex-col md:flex-row items-center justify-between mb-4">
    <div class="flex items-center text-white font-bold text-base mb-3 md:mb-0">
        <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>
        School Dashboard
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('sm-school.classes.index') }}" class="inline-flex items-center px-3 py-1.5 bg-transparent border border-white hover:bg-white/10 text-white text-xs font-medium rounded transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5"></i> Add Class
        </a>
        <a href="{{ route('sm-school.class-groups.index') }}" class="inline-flex items-center px-3 py-1.5 bg-transparent border border-white hover:bg-white/10 text-white text-xs font-medium rounded transition-colors">
            <i data-lucide="layers" class="w-3.5 h-3.5 mr-1.5"></i> Manage Sections
        </a>
    </div>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">

    {{-- Active Inquiries --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="inbox" class="w-4 h-4 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Active Inquiries</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">0</div>{{-- Active Inquiries: no inquiry module yet --}}
    </div>

    {{-- Active Students --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="graduation-cap" class="w-4 h-4 text-green-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Active Students</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalActiveStudents ?? 0 }}</div>
    </div>

    {{-- Total Classes --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="layers" class="w-4 h-4 text-yellow-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Total Classes</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalClasses }}</div>
    </div>

    {{-- Total Staff --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="users" class="w-4 h-4 text-pink-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Total Staff</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalStaff }}</div>
    </div>

    {{-- Total Income --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Total Income</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-base font-bold text-heading ml-1">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($totalIncome, 2) }}</div>
    </div>

    {{-- Fees Collected --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="indian-rupee" class="w-4 h-4 text-green-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Fees Collected</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($feesCollected, 2) }}</div>
    </div>

    {{-- Pending Dues --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="alert-circle" class="w-4 h-4 text-yellow-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Pending Dues</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-base font-bold text-heading ml-1">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($pendingDues, 2) }}</div>
    </div>

    {{-- Unpaid Invoices --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Unpaid Invoices</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $unpaidInvoices }}</div>
    </div>

    {{-- Total Expenses --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="receipt" class="w-4 h-4 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Total Expenses</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($totalExpenses, 2) }}</div>
    </div>

    {{-- Total Books --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="book" class="w-4 h-4 text-green-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Total Books</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $totalBooks }}</div>
    </div>

    {{-- Pending Student Leaves --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="clock" class="w-4 h-4 text-yellow-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Pending Student Leaves</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $pendingStudentLeaves }}</div>
    </div>

    {{-- Pending Staff Leaves --}}
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="user-minus" class="w-4 h-4 text-red-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Pending Staff Leaves</p>
                <p class="text-xs text-gray-400">{{ $currentSession ? $currentSession->name : '' }}</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $pendingStaffLeaves }}</div>
    </div>

</div>
@endsection
