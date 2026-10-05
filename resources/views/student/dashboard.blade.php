@extends('layouts.app')

@section('header', 'Student Dashboard')

@section('content')

{{-- School + Session Header --}}
<div class="mb-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center">
            <i data-lucide="users" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight">{{ $school?->name ?? 'School' }}</h2>
            <p class="text-xs text-body">Session: <span class="font-semibold text-navy">{{ $session?->name ?? '—' }}</span></p>
        </div>
    </div>

    <a href="{{ route('sm-student.students.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-semibold shadow-sm hover:opacity-90 transition"
       style="background: #B47A42;">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        Add Admission
    </a>
</div>

{{-- Banner --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center mb-6">
    <i data-lucide="users" class="w-5 h-5 text-white mr-2.5"></i>
    <span class="text-white font-bold text-base tracking-wide">Student</span>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">

    {{-- Total Students --}}
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-blue-500"></i>
            </div>
            <span class="text-2xl font-bold text-heading">{{ $totalStudents }}</span>
        </div>
        <p class="text-xs text-body">Total Students</p>
        <div class="flex gap-2 mt-2 flex-wrap">
            <a href="{{ route('sm-student.students.index') }}"
               class="text-xs px-2 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition">View Students</a>
            <a href="{{ route('sm-student.students.create') }}"
               class="text-xs px-2 py-1 rounded bg-gray-50 text-gray-600 hover:bg-gray-100 transition border border-gray-200">Add Admission</a>
        </div>
    </div>

    {{-- Student Status (Girls vs Boys) --}}
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-pink-50 flex items-center justify-center">
                <i data-lucide="venus" class="w-5 h-5 text-pink-500"></i>
            </div>
        </div>
        <p class="text-xs text-body mb-1">Student Status</p>
        <div class="flex gap-3 text-xs mt-1">
            <div class="flex flex-col items-center">
                <span class="text-xl font-bold text-blue-600">{{ $totalBoys }}</span>
                <span class="text-gray-500">Boys</span>
            </div>
            <div class="w-px bg-gray-200 self-stretch mx-1"></div>
            <div class="flex flex-col items-center">
                <span class="text-xl font-bold text-pink-500">{{ $totalGirls }}</span>
                <span class="text-gray-500">Girls</span>
            </div>
        </div>
    </div>

    {{-- Promoted --}}
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
            </div>
            <span class="text-2xl font-bold text-heading">{{ $promoted }}</span>
        </div>
        <p class="text-xs text-body">Promoted</p>
        <div class="mt-2">
            <a href="{{ route('sm-student.promote.index') }}"
               class="text-xs px-2 py-1 rounded bg-green-50 text-green-700 hover:bg-green-100 transition">Promote Students</a>
        </div>
    </div>

    {{-- Transferred --}}
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="arrow-right-left" class="w-5 h-5 text-amber-500"></i>
            </div>
            <span class="text-2xl font-bold text-heading">{{ $transferred }}</span>
        </div>
        <p class="text-xs text-body">Transferred Out</p>
        <div class="mt-2">
            <a href="{{ route('sm-student.transfer.index') }}"
               class="text-xs px-2 py-1 rounded bg-amber-50 text-amber-700 hover:bg-amber-100 transition">Transfer Student</a>
        </div>
    </div>

</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

    {{-- Print ID Cards --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="id-card" class="w-4 h-4 text-navy"></i>
            ID Cards
        </h4>
        <p class="text-xs text-body mb-3">Print ID cards for all students or selected ones.</p>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('sm-student.print-id-cards.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:opacity-90 transition">
                Print ID Cards
            </a>
        </div>
    </div>

    {{-- Promote --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="graduation-cap" class="w-4 h-4 text-navy"></i>
            Promote Students
        </h4>
        <p class="text-xs text-body mb-3">Move students to the next session and class.</p>
        <a href="{{ route('sm-student.promote.index') }}"
           class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:opacity-90 transition">
            Promote Students
        </a>
    </div>

    {{-- Transfer --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="arrow-right-from-line" class="w-4 h-4 text-navy"></i>
            Transfer Student
        </h4>
        <p class="text-xs text-body mb-3">Issue transfer certificate and mark student as transferred.</p>
        <a href="{{ route('sm-student.transfer.index') }}"
           class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:opacity-90 transition">
            Transfer Student
        </a>
    </div>

</div>

@endsection
