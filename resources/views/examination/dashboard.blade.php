@extends('layouts.app')

@section('header', 'Examination Dashboard')

@section('content')
<div class="mb-5 rounded-xl px-6 py-5 text-center shadow-sm" style="background:var(--color-card);">
    <h1 class="flex items-center justify-center gap-2 text-xl font-semibold text-heading">
        <i data-lucide="school" class="h-5 w-5" style="color:var(--color-primary)"></i>
        {{ $school->name }}
        <span class="text-base font-normal text-body">{{ $session->name }}</span>
    </h1>
    <p class="mt-2 text-xs text-body">Current Session: <span class="font-semibold text-heading">{{ $session->name }}</span></p>
</div>

<div class="mb-6 rounded-lg px-4 py-2 text-center text-base font-bold text-white shadow-md" style="background:var(--color-primary);">
    <i data-lucide="clock-3" class="mr-1 inline h-4 w-4"></i> Examination
</div>

<div class="grid gap-5 md:grid-cols-3">
    @foreach([
        ['title' => 'Exams', 'detail' => 'Published timetables', 'count' => $examCount, 'icon' => 'calendar-days', 'tone' => '#e8f0ff', 'ink' => '#2871d8', 'href' => route('examination.exams.index'), 'action' => 'View Exams', 'secondary' => ['Add New Exam', route('examination.exams.create')]],
        ['title' => 'Admit Cards', 'detail' => 'Generated admit cards', 'count' => $admitCardCount, 'icon' => 'contact-round', 'tone' => '#e7f5ed', 'ink' => '#25844a', 'href' => route('examination.admit-cards.index'), 'action' => 'View Admit Cards'],
        ['title' => 'Exam Results', 'detail' => 'Saved student results', 'count' => $resultCount, 'icon' => 'table-properties', 'tone' => '#fff5dc', 'ink' => '#a97500', 'href' => route('examination.results.index'), 'action' => 'View Exam Results'],
    ] as $card)
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg" style="background:{{ $card['tone'] }};color:{{ $card['ink'] }}">
                        <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-heading">{{ $card['title'] }}</h2>
                        <p class="text-[11px] text-body">{{ $card['detail'] }}</p>
                    </div>
                </div>
                <span class="text-lg font-bold text-heading">{{ $card['count'] }}</span>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                <a href="{{ $card['href'] }}" class="inline-flex items-center rounded px-3 py-1.5 text-xs font-semibold text-white" style="background:var(--color-primary);">{{ $card['action'] }}</a>
                @isset($card['secondary'])
                    <a href="{{ $card['secondary'][1] }}" class="inline-flex items-center rounded border px-3 py-1.5 text-xs font-medium" style="border-color:var(--color-primary);color:var(--color-primary);">{{ $card['secondary'][0] }}</a>
                @endisset
            </div>
        </section>
    @endforeach
</div>
@endsection