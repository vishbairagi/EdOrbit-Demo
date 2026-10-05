@extends('layouts.app')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Schools -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 flex items-center transition-transform hover:-translate-y-1">
        <div class="rounded-full bg-blue-50 p-3 mr-3">
            <i data-lucide="building-2" class="h-5 w-5 text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Schools</p>
            <h3 class="text-xl font-bold text-heading mt-0.5">{{ $totalSchools }}</h3>
        </div>
    </div>

    <!-- Active Schools -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 flex items-center transition-transform hover:-translate-y-1">
        <div class="rounded-full bg-green-50 p-3 mr-3">
            <i data-lucide="check-circle-2" class="h-5 w-5 text-green-600"></i>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Schools</p>
            <h3 class="text-xl font-bold text-heading mt-0.5">{{ $activeSchools }}</h3>
        </div>
    </div>

    <!-- Inactive Schools -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 flex items-center transition-transform hover:-translate-y-1">
        <div class="rounded-full bg-red-50 p-3 mr-3">
            <i data-lucide="x-circle" class="h-5 w-5 text-red-600"></i>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Inactive Schools</p>
            <h3 class="text-xl font-bold text-heading mt-0.5">{{ $inactiveSchools }}</h3>
        </div>
    </div>

    <!-- Total Sessions -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 flex items-center transition-transform hover:-translate-y-1">
        <div class="rounded-full bg-purple-50 p-3 mr-3">
            <i data-lucide="calendar-days" class="h-5 w-5 text-purple-600"></i>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sessions</p>
            <h3 class="text-xl font-bold text-heading mt-0.5">{{ $totalSessions }}</h3>
        </div>
    </div>
</div>

<div class="mb-6 flex justify-between items-center">
    <h3 class="text-xl font-bold text-heading">Recent Schools</h3>
    <a href="{{ route('school-management.schools.create') }}" class="inline-flex items-center px-4 py-2 bg-navy hover:bg-primary-dark text-white text-sm font-medium rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
        Add School
    </a>
</div>

@if($schools->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($schools as $school)
        <a href="{{ route('school-management.schools.switch', $school) }}" class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:bg-navy hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer">
            
            <div class="flex items-center mb-6">
                <div class="h-14 w-14 rounded-xl bg-gray-50 group-hover:bg-white/10 flex items-center justify-center mr-4 transition-colors duration-300">
                    <i data-lucide="school" class="w-7 h-7 text-burgundy group-hover:text-white transition-colors duration-300"></i>
                </div>
                <h4 class="text-lg font-bold text-heading group-hover:text-white italic transition-colors duration-300">
                    {{ $school->name }}
                </h4>
            </div>
            
            <div class="space-y-3">
                <div class="flex items-start text-sm text-body group-hover:text-gray-100 transition-colors duration-300">
                    <i data-lucide="phone" class="w-4 h-4 mr-3 mt-0.5 text-gray-400 group-hover:text-gray-300 transition-colors duration-300"></i>
                    <span><span class="font-medium text-heading group-hover:text-white transition-colors duration-300">Phone:</span> {{ $school->phone ?? 'N/A' }}</span>
                </div>
                
                <div class="flex items-start text-sm text-body group-hover:text-gray-100 transition-colors duration-300">
                    <i data-lucide="mail" class="w-4 h-4 mr-3 mt-0.5 text-gray-400 group-hover:text-gray-300 transition-colors duration-300"></i>
                    <span class="truncate pr-2"><span class="font-medium text-heading group-hover:text-white transition-colors duration-300">Email:</span> {{ $school->email ?? 'N/A' }}</span>
                </div>
                
                <div class="flex items-start text-sm text-body group-hover:text-gray-100 transition-colors duration-300">
                    <i data-lucide="map-pin" class="w-4 h-4 mr-3 mt-0.5 text-gray-400 group-hover:text-gray-300 transition-colors duration-300"></i>
                    <span><span class="font-medium text-heading group-hover:text-white transition-colors duration-300">Address:</span> {{ $school->address ?? 'N/A' }}</span>
                </div>
                
                <div class="flex items-center text-sm text-body group-hover:text-gray-100 transition-colors duration-300 pt-4 mt-2">
                    <i data-lucide="info" class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-300 transition-colors duration-300"></i>
                    <span class="font-medium text-heading group-hover:text-white mr-1 transition-colors duration-300">Status:</span> 
                    <span class="{{ $school->status === 'active' ? 'text-green-600 group-hover:text-green-300' : 'text-red-500 group-hover:text-red-300' }} font-bold transition-colors duration-300">
                        {{ ucfirst($school->status) }}
                    </span>
                </div>
            </div>
        </a>
    @endforeach
</div>
@else
<div class="bg-card rounded-xl shadow-sm border border-gray-100 p-10 text-center">
    <i data-lucide="school" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
    <p class="text-lg font-medium text-heading">No schools found.</p>
    <p class="mt-1 text-sm text-body">Create your first school to get started.</p>
    <a href="{{ route('school-management.schools.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-navy hover:bg-primary-dark text-white text-sm font-medium rounded-md shadow-sm transition-colors">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add School
    </a>
</div>
@endif
@endsection
