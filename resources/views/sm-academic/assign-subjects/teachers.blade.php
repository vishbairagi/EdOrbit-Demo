@extends('layouts.app')

@section('header', 'Assign Subject')

@section('content')

{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Subject: {{ $subject->name }} @if($subject->code) ({{ $subject->code }}) @endif</h3>
        <p class="text-xs text-gray-500 mt-0.5">Class: {{ $subject->schoolClass?->name ?? '—' }} &nbsp;·&nbsp; Session: {{ $session->name ?? 'Current Session' }}</p>
    </div>
    <a href="{{ route('academic.assign-subjects.index') }}"
       class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
        Back to Subjects
    </a>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Assigned Teachers Table --}}
    <div class="lg:col-span-2 bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2" style="background:#F5F1EB;">
            <i data-lucide="users" class="w-4 h-4 text-navy"></i>
            <span class="font-bold text-heading">Teachers</span>
        </div>
        
        <div class="p-4">
            {{-- Optional search inside table could be here --}}
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-navy">
                            <th class="px-4 py-2 text-left text-white font-semibold text-xs tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-white font-semibold text-xs tracking-wider">Phone</th>
                            <th class="px-4 py-2 text-left text-white font-semibold text-xs tracking-wider">Username</th>
                            <th class="px-4 py-2 text-left text-white font-semibold text-xs tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-white font-semibold text-xs tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($subject->teachers as $teacher)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 flex items-center gap-2 text-heading font-medium">
                                    <i data-lucide="plus-circle" class="w-4 h-4 text-navy"></i>
                                    {{ $teacher->name }}
                                </td>
                                <td class="px-4 py-3 text-body">{{ $teacher->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-body">{{ $teacher->username }}</td>
                                <td class="px-4 py-3 text-body">{{ $teacher->status }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('academic.assign-subjects.remove', [$subject, $teacher]) }}"
                                          onsubmit="return confirm('Remove teacher from this subject?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Remove">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    No teachers assigned to this subject yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                Showing 1 to {{ $subject->teachers->count() }} of {{ $subject->teachers->count() }} Records
            </div>
        </div>
    </div>

    {{-- Assign Teachers Form --}}
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm h-fit">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2" style="background:#F5F1EB;">
            <i data-lucide="plus-square" class="w-4 h-4 text-navy"></i>
            <span class="font-bold text-heading">Assign Teachers</span>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('academic.assign-subjects.assign', $subject) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-heading mb-2">Teacher:</label>
                    <select name="staff_ids[]" id="staffSelect" multiple class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy" required>
                        @foreach($availableTeachers as $teacher)
                            @if(!$subject->teachers->contains($teacher->id))
                                <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->employee_id }})</option>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select one or more teachers</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Assign Teachers
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Initialize Select2/TomSelect for multiple selection with search -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        new TomSelect("#staffSelect", {
            plugins: ['remove_button', 'dropdown_input'],
            placeholder: "Select one or more teachers...",
            searchField: ['text'],
            maxOptions: null, // show all options without limit
        });
    });
</script>
<style>
    /* Styling Tom Select to match the theme */
    .ts-control {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        box-shadow: none;
    }
    .ts-control.focus {
        border-color: #171411; /* Navy color */
        box-shadow: 0 0 0 2px rgba(23, 20, 17, 0.2);
    }
    .ts-dropdown {
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .ts-dropdown .option {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    .ts-dropdown .option.active {
        background-color: #f9fafb;
        color: #111827;
    }
    .ts-control .item {
        background-color: #F5F1EB;
        color: #171411;
        border: 1px solid #DED5CB;
        border-radius: 0.25rem;
        padding: 0.125rem 0.5rem;
    }
</style>

@endsection
