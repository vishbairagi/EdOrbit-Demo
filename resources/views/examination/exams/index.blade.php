@extends('layouts.app')

@section('header', 'Manage Exams')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-2 text-white shadow-md" style="background:var(--color-primary);">
    <h1 class="flex items-center gap-2 text-base font-bold"><i data-lucide="clock-3" class="h-4 w-4"></i> Manage Exams</h1>
    <a href="{{ route('examination.exams.create') }}" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-1.5 text-xs font-semibold hover:bg-white/15">
        <i data-lucide="plus-square" class="h-4 w-4"></i> Add New Exam
    </a>
</div>

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[950px] border-collapse text-xs">
            <thead style="background:var(--color-primary);" class="text-left text-white">
                <tr>
                    <th class="px-3 py-3">Exam Title</th>
                    <th class="px-3 py-3">Class / Sections</th>
                    <th class="px-3 py-3">Exam Center</th>
                    <th class="px-3 py-3">Start Date</th>
                    <th class="px-3 py-3">End Date</th>
                    <th class="px-3 py-3">Exam Group</th>
                    <th class="px-3 py-3">Type</th>
                    <th class="px-3 py-3">Papers</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($exams as $exam)
                    <tr class="align-top hover:bg-gray-50">
                        <td class="px-3 py-3 font-bold text-heading">{{ $exam->title }}</td>
                        <td class="px-3 py-3 text-body">
                            <span class="font-bold text-heading">{{ $exam->schoolClass->name }}</span>
                            <div class="mt-1 text-[11px]">{{ $exam->sections->pluck('name')->join(', ') }}</div>
                        </td>
                        <td class="px-3 py-3">{{ $exam->exam_center }}</td>
                        <td class="px-3 py-3">{{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                        <td class="px-3 py-3">{{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                        <td class="px-3 py-3">{{ $exam->examGroup?->name ?? '—' }}</td>
                        <td class="px-3 py-3">{{ $exam->exam_type === 'marks' ? 'Marks and Grade' : 'Grade Only' }}</td>
                        <td class="px-3 py-3">{{ $exam->papers_count }}</td>
                        <td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $exam->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($exam->status) }}</span></td>
                        <td class="px-3 py-3"><a href="{{ route('examination.exams.edit', $exam) }}" class="inline-flex items-center gap-1 rounded px-2.5 py-1.5 font-bold text-white" style="background:var(--color-primary);"><i data-lucide="pencil" class="h-3 w-3"></i> Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-12 text-center text-body">No exams have been created for {{ $session->name }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($exams->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $exams->links() }}</div>
    @endif
</div>
@endsection