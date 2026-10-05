@extends('layouts.app')

@section('header', 'Admit Cards')

@section('content')
<div class="mb-5 rounded-lg px-4 py-2 text-center text-base font-bold text-white shadow-md" style="background:var(--color-primary);">Manage Exam Admit Cards</div>

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-xs">
            <thead class="text-left text-white" style="background:var(--color-primary);">
                <tr><th class="p-3">Exam Title</th><th class="p-3">Class</th><th class="p-3">Exam Center</th><th class="p-3">Start Date</th><th class="p-3">End Date</th><th class="p-3">Generated Cards</th><th class="p-3">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($exams as $exam)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-bold text-heading">{{ $exam->title }}</td>
                        <td class="p-3">{{ $exam->schoolClass->name }}</td>
                        <td class="p-3">{{ $exam->exam_center }}</td>
                        <td class="p-3">{{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                        <td class="p-3">{{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</td>
                        <td class="p-3">{{ $exam->admit_cards_count }}</td>
                        <td class="p-3"><div class="flex flex-wrap gap-2"><a href="{{ route('examination.admit-cards.generate-form', $exam) }}" class="inline-flex items-center gap-1 rounded px-3 py-2 font-bold text-white" style="background:var(--color-primary);"><i data-lucide="contact-round" class="h-3.5 w-3.5"></i> Generate Admit Card</a>@if($exam->admit_cards_count > 0)<a href="{{ route('examination.admit-cards.view', $exam) }}" class="inline-flex items-center gap-1 rounded border border-gray-300 px-3 py-2 font-bold text-heading"><i data-lucide="eye" class="h-3.5 w-3.5"></i> View</a>@endif</div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-body">No exams found for {{ $session->name }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($exams->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $exams->links() }}</div>@endif
</section>
@endsection