@extends('layouts.app')

@section('header', 'Add Vehicle')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Add New Vehicle</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
    <a href="{{ route('transport.vehicles.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to vehicles</a>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white">
    <form action="{{ route('transport.vehicles.store') }}" method="POST" class="space-y-5 p-5">
        @csrf
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="vehicle_number" class="mb-1 block text-sm font-medium text-body">Vehicle Number <span class="text-red-500">*</span></label>
                <input type="text" name="vehicle_number" id="vehicle_number" value="{{ old('vehicle_number') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('vehicle_number')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            <div>
                <label for="vehicle_model" class="mb-1 block text-sm font-medium text-body">Vehicle Model</label>
                <input type="text" name="vehicle_model" id="vehicle_model" value="{{ old('vehicle_model') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('vehicle_model')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            <div>
                <label for="driver_name" class="mb-1 block text-sm font-medium text-body">Driver Name <span class="text-red-500">*</span></label>
                <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('driver_name')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            <div>
                <label for="driver_phone" class="mb-1 block text-sm font-medium text-body">Driver Phone <span class="text-red-500">*</span></label>
                <input type="text" name="driver_phone" id="driver_phone" value="{{ old('driver_phone') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('driver_phone')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            <div>
                <label for="license_number" class="mb-1 block text-sm font-medium text-body">License Number</label>
                <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('license_number')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="note" class="mb-1 block text-sm font-medium text-body">Note</label>
                <textarea name="note" id="note" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">{{ old('note') }}</textarea>
                @error('note')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
        </div>
        
        <div class="flex justify-end gap-2 border-t border-gray-200 pt-5">
            <a href="{{ route('transport.vehicles.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Save Vehicle</button>
        </div>
    </form>
</section>
@endsection
