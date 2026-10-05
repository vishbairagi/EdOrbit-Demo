@extends('layouts.app')

@section('header', 'Academic Dashboard')

@section('content')

{{-- School + Session Header --}}
<div class="mb-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center">
            <i data-lucide="graduation-cap" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight">{{ $school->name }}</h2>
            <p class="text-xs text-body">Session: <span class="font-semibold text-navy">{{ $session->name }}</span></p>
        </div>
    </div>
</div>

{{-- Green Academic Banner --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center mb-6">
    <i data-lucide="graduation-cap" class="w-5 h-5 text-white mr-2.5"></i>
    <span class="text-white font-bold text-base tracking-wide">Academic</span>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
            <i data-lucide="layers" class="w-5 h-5 text-blue-500"></i>
        </div>
        <p class="text-2xl font-bold text-heading">{{ $totalClasses }}</p>
        <p class="text-xs text-body mt-1">Total Classes</p>
    </div>
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3">
            <i data-lucide="columns-3" class="w-5 h-5 text-green-500"></i>
        </div>
        <p class="text-2xl font-bold text-heading">{{ $totalSections }}</p>
        <p class="text-xs text-body mt-1">Total Sections</p>
    </div>
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center mb-3">
            <i data-lucide="book-open" class="w-5 h-5 text-purple-500"></i>
        </div>
        <p class="text-2xl font-bold text-heading">{{ $totalSubjects }}</p>
        <p class="text-xs text-body mt-1">Total Subjects</p>
    </div>
    <div class="bg-card rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
            <i data-lucide="file-text" class="w-5 h-5 text-amber-500"></i>
        </div>
        <p class="text-2xl font-bold text-heading">{{ $totalMaterials }}</p>
        <p class="text-xs text-body mt-1">Study Materials</p>
    </div>
</div>

{{-- Quick Action Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

    {{-- Class Sections --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="layout-grid" class="w-4 h-4 text-navy"></i>
            Class Sections
        </h4>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('academic.class-sections.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:bg-primary-dark transition-colors">
                Add Sections / Manage
            </a>
        </div>
    </div>

    {{-- Subjects --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="book-open" class="w-4 h-4 text-navy"></i>
            Subjects
        </h4>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('academic.subjects.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:bg-primary-dark transition-colors">
                View Subjects
            </a>
            <a href="{{ route('academic.subjects.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-heading text-xs font-medium rounded-md hover:bg-gray-50 transition-colors">
                Add New Subject
            </a>
        </div>
    </div>

    {{-- Study Materials --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4 text-navy"></i>
            Study Materials
        </h4>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('academic.study-materials.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:bg-primary-dark transition-colors">
                Study Materials
            </a>
            <a href="{{ route('academic.study-materials.create') }}"
               class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-heading text-xs font-medium rounded-md hover:bg-gray-50 transition-colors">
                Add New Material
            </a>
        </div>
    </div>

    {{-- Manage Medium --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="languages" class="w-4 h-4 text-navy"></i>
            Manage Medium
        </h4>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('academic.mediums.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-navy text-white text-xs font-medium rounded-md hover:bg-primary-dark transition-colors">
                View / Manage
            </a>
        </div>
    </div>

    {{-- Attendance (Coming Soon) --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 opacity-60">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="clipboard-check" class="w-4 h-4 text-gray-400"></i>
            Attendance
        </h4>
        <span class="text-xs text-gray-400 italic">Coming soon…</span>
    </div>

    {{-- Homework (Coming Soon) --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 opacity-60">
        <h4 class="text-base font-bold text-heading mb-3 flex items-center gap-2">
            <i data-lucide="pencil-line" class="w-4 h-4 text-gray-400"></i>
            Homework
        </h4>
        <span class="text-xs text-gray-400 italic">Coming soon…</span>
    </div>

</div>
@endsection
