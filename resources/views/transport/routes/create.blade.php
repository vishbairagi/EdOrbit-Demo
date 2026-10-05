@extends('layouts.app')

@section('header', 'Add Route')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Add New Transport Route</h2>
        <p class="text-xs text-body">{{ $school->name ?? 'School' }} · {{ $session->name ?? 'Session' }}</p>
    </div>
    <a href="{{ route('transport.routes.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to routes</a>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white">
    <form action="{{ route('transport.routes.store') }}" method="POST" class="space-y-5 p-5">
        @csrf
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="route_name" class="mb-1 block text-sm font-medium text-body">Route Name <span class="text-red-500">*</span></label>
                <input type="text" name="route_name" id="route_name" value="{{ old('route_name') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('route_name')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            
            <div>
                <label for="route_fare" class="mb-1 block text-sm font-medium text-body">Route Fare <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="route_fare" id="route_fare" value="{{ old('route_fare') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                @error('route_fare')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            
            <div>
                <label for="period_id" class="mb-1 block text-sm font-medium text-body">Period <span class="text-red-500">*</span></label>
                <select name="period_id" id="period_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Period</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ old('period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
                @error('period_id')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            
            <div>
                <label for="vehicles" class="mb-1 block text-sm font-medium text-body">Transport Vehicles</label>
                <select name="vehicles[]" id="vehicles" multiple class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm h-32 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} - {{ $vehicle->driver_name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-500 mt-1">Hold Ctrl/Command to select multiple.</p>
                @error('vehicles')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
            
        </div>
        
        <div class="flex justify-end gap-2 border-t border-gray-200 pt-5">
            <a href="{{ route('transport.routes.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Save Route</button>
        </div>
    </form>
</section>
@endsection
