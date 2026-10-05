@extends('layouts.app')

@section('header', 'Add Session')

@section('content')
<div class="mb-4">
    <a href="{{ route('school-management.sessions.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-navy">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Sessions
    </a>
</div>

<form action="{{ route('school-management.sessions.store') }}" method="POST">
    @csrf

    <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl mx-auto">
        <div class="px-6 py-4 border-b border-gray-100 bg-white">
            <h3 class="text-lg font-medium text-heading">Session Details</h3>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Session Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" placeholder="e.g. 2025-2026" value="{{ old('name') }}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-navy focus:border-navy sm:text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-navy focus:border-navy sm:text-sm">
                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-navy focus:border-navy sm:text-sm">
                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-navy focus:border-navy sm:text-sm rounded-md">
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('school-management.sessions.index') }}" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy">
                    Save Session
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
