@extends('layouts.app')

@section('header', 'School Details')

@section('content')
<style>
    @media print {
        aside, header, .no-print { display: none !important; }
        main { overflow: visible !important; height: auto !important; }
        body { background: white !important; }
        .print-area { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>

<div class="mb-4 flex items-center justify-between no-print">
    <a href="{{ route('school-management.schools.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-navy">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Schools
    </a>
    <div class="flex space-x-2">
        <button onclick="window.print()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
        </button>
        <a href="{{ route('school-management.schools.edit', $school) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Edit School
        </a>
    </div>
</div>

<div class="space-y-6 print-area">
    <!-- School Information -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
            <h3 class="text-lg font-medium text-heading flex items-center">
                <i data-lucide="building-2" class="w-5 h-5 mr-2 text-burgundy"></i>
                School Information
            </h3>
            @if($school->status === 'active')
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Active
                </span>
            @else
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    Inactive
                </span>
            @endif
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">School Name</dt>
                    <dd class="mt-1 text-sm text-heading">{{ $school->name }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                    <dd class="mt-1 text-sm text-heading">{{ $school->registration_number ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-heading">{{ $school->phone ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-heading">{{ $school->email ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-heading">{{ $school->address ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-heading">{{ $school->description ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Enrollment Configuration -->
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white">
                <h3 class="text-lg font-medium text-heading flex items-center">
                    <i data-lucide="users" class="w-5 h-5 mr-2 text-burgundy"></i>
                    Enrollment Configuration
                </h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Prefix</dt>
                        <dd class="mt-1 text-sm text-heading">{{ $school->enrollment_prefix ?? 'None' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Base Number</dt>
                        <dd class="mt-1 text-sm text-heading">{{ $school->enrollment_base_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Padding</dt>
                        <dd class="mt-1 text-sm text-heading">{{ $school->enrollment_base_padding }} digits</dd>
                    </div>
                    <div class="pt-4 border-t border-gray-100">
                        <dt class="text-sm font-medium text-gray-500 mb-1">First Enrollment Preview</dt>
                        <dd class="text-lg font-bold text-navy tracking-wider">
                            @php
                                $baseStr = (string)($school->enrollment_base_number + 1);
                                $padded = str_pad($baseStr, $school->enrollment_base_padding, '0', STR_PAD_LEFT);
                                echo ($school->enrollment_prefix ?? '') . $padded;
                            @endphp
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Admission Configuration -->
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white">
                <h3 class="text-lg font-medium text-heading flex items-center">
                    <i data-lucide="user-check" class="w-5 h-5 mr-2 text-burgundy"></i>
                    Admission Configuration
                </h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Prefix</dt>
                        <dd class="mt-1 text-sm text-heading">{{ $school->admission_prefix ?? 'None' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Base Number</dt>
                        <dd class="mt-1 text-sm text-heading">{{ $school->admission_base_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Padding</dt>
                        <dd class="mt-1 text-sm text-heading">{{ $school->admission_base_padding }} digits</dd>
                    </div>
                    <div class="pt-4 border-t border-gray-100">
                        <dt class="text-sm font-medium text-gray-500 mb-1">First Admission Preview</dt>
                        <dd class="text-lg font-bold text-navy tracking-wider">
                            @php
                                $baseStr = (string)($school->admission_base_number);
                                $padded = str_pad($baseStr, $school->admission_base_padding, '0', STR_PAD_LEFT);
                                echo ($school->admission_prefix ?? '') . $padded;
                            @endphp
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
