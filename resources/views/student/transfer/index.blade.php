@extends('layouts.app')

@section('header', 'Transfer Student')

@section('content')

{{-- Banner --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center mb-6">
    <i data-lucide="arrow-right-from-line" class="w-5 h-5 text-white mr-2.5"></i>
    <span class="text-white font-bold text-base tracking-wide">Transfer Student</span>
</div>

{{-- Students Table --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <p class="text-sm font-semibold text-heading">Active Students</p>
        <p class="text-xs text-body">{{ $students->count() }} student(s)</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100" style="background:#F5F1EB;">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Student Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Admission No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Class</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Section</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Father's Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-heading">{{ $student->full_name }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->admission_no }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->schoolClass?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->section?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->father_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-body">{{ $student->phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1.5">
                                {{-- View Certificate form --}}
                                <a href="{{ route('sm-student.transfer.certificate', $student) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-white hover:opacity-90 transition"
                                   style="background:#B47A42;">
                                    <i data-lucide="file-text" class="w-3 h-3"></i>
                                    Certificate
                                </a>
                                <form method="POST" action="{{ route('sm-student.transfer.store', $student) }}"
                                      onsubmit="return confirm('Mark {{ $student->full_name }} as Transferred?')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium border border-amber-300 text-amber-700 hover:bg-amber-50 transition">
                                        <i data-lucide="arrow-right-from-line" class="w-3 h-3"></i>
                                        Transfer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-body text-sm">
                            <i data-lucide="users" class="w-10 h-10 text-gray-300 mx-auto mb-3 block"></i>
                            No active students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
