@extends('layouts.app')

@section('header', 'Transport Dashboard')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">{{ $school->name ?? 'School' }}</h2>
        <p class="text-xs text-body">Transport overview · {{ $session->name ?? 'Session' }}</p>
    </div>
</div>

<div class="mb-5 rounded-lg bg-navy px-5 py-3 text-sm font-semibold text-white">Transport Summary</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="truck" class="w-4 h-4 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Vehicles</p>
                <p class="text-xs text-gray-400">Fleet Size</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $vehiclesCount }}</div>
    </div>
    
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="map" class="w-4 h-4 text-green-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Routes</p>
                <p class="text-xs text-gray-400">Active Routes</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $routesCount }}</div>
    </div>
    
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="users" class="w-4 h-4 text-amber-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Students</p>
                <p class="text-xs text-gray-400">Assigned</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $studentsCount }}</div>
    </div>
    
    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-heading leading-tight">Invoices</p>
                <p class="text-xs text-gray-400">Generated</p>
            </div>
        </div>
        <div class="text-lg font-bold text-heading ml-1">{{ $invoicesCount }}</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
    <a href="{{ route('transport.vehicles.index') }}" class="group flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-6 text-center transition-all hover:border-navy hover:shadow-md">
        <div class="mb-3 rounded-full bg-gray-50 p-3 text-navy group-hover:bg-navy group-hover:text-white transition-colors">
            <i data-lucide="bus" class="h-6 w-6"></i>
        </div>
        <h3 class="text-sm font-bold text-heading">Manage Vehicles</h3>
        <p class="mt-1 text-xs text-body">Add, edit, or view transport vehicles</p>
    </a>
    
    <a href="{{ route('transport.routes.index') }}" class="group flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-6 text-center transition-all hover:border-navy hover:shadow-md">
        <div class="mb-3 rounded-full bg-gray-50 p-3 text-navy group-hover:bg-navy group-hover:text-white transition-colors">
            <i data-lucide="route" class="h-6 w-6"></i>
        </div>
        <h3 class="text-sm font-bold text-heading">Manage Routes</h3>
        <p class="mt-1 text-xs text-body">Create routes and assign students</p>
    </a>
    
    <a href="{{ route('transport.report') }}" class="group flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-6 text-center transition-all hover:border-navy hover:shadow-md">
        <div class="mb-3 rounded-full bg-gray-50 p-3 text-navy group-hover:bg-navy group-hover:text-white transition-colors">
            <i data-lucide="clipboard-list" class="h-6 w-6"></i>
        </div>
        <h3 class="text-sm font-bold text-heading">Transport Report</h3>
        <p class="mt-1 text-xs text-body">View students assigned to routes</p>
    </a>
    
    <a href="{{ route('accounting.transport-invoices.index') }}" class="group flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-6 text-center transition-all hover:border-navy hover:shadow-md">
        <div class="mb-3 rounded-full bg-gray-50 p-3 text-navy group-hover:bg-navy group-hover:text-white transition-colors">
            <i data-lucide="receipt" class="h-6 w-6"></i>
        </div>
        <h3 class="text-sm font-bold text-heading">Transport Invoices</h3>
        <p class="mt-1 text-xs text-body">Generate invoices and collect payments</p>
    </a>
</div>
@endsection
