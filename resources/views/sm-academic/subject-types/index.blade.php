@extends('layouts.app')

@section('header', 'Subject Types')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Subject Types</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Types: {{ $subjectTypes->total() }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Session Analysis Trigger Button --}}
        <button type="button" onclick="openTypeAnalysisModal()"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="pie-chart" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
            Type Analysis
        </button>

        <a href="{{ route('academic.subject-types.export') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
            Export CSV
        </a>
        <button type="button" onclick="document.getElementById('importTypeModal').classList.remove('hidden')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
            Import CSV
        </button>
        <button type="button" onclick="document.getElementById('addTypeModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
            Add Type
        </button>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
    {{ session('error') }}
</div>
@endif
@if(isset($errors) && $errors->any())
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs" style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <ul class="list-disc ml-4 space-y-0.5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Search & Filter Bar --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 mb-3.5">
    <form method="GET" action="{{ route('academic.subject-types.index') }}" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">

            {{-- Keyword Search --}}
            <div class="w-full md:w-72">
                <label class="block text-xs font-semibold text-heading mb-1">Search Subject Type:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Type name or description..."
                           class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 pr-8 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    @if(request('search'))
                        <a href="{{ route('academic.subject-types.index', array_filter(['status' => request('status'), 'per_page' => request('per_page')])) }}"
                           class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="w-full md:w-44">
                <label class="block text-xs font-semibold text-heading mb-1">Status:</label>
                <select name="status" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white cursor-pointer">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            {{-- Per-Page Dropdown --}}
            <div class="w-full md:w-32">
                <label class="block text-xs font-semibold text-heading mb-1">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white cursor-pointer"
                        title="Records per page">
                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / page</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 / page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / page</option>
                </select>
            </div>

            <div class="flex items-end mt-auto gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:bg-primary-dark transition bg-navy shadow-2xs">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Search
                </button>
                @if(request('search') || request('status') || (request('per_page') && request('per_page') != 10))
                    <a href="{{ route('academic.subject-types.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-medium text-red-600 hover:bg-gray-100 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        <div class="text-xs text-gray-500 self-end md:self-center">
            Total for Session <strong>{{ $session->name }}</strong>: <strong class="text-gray-900 font-bold">{{ $subjectTypes->total() }}</strong> types
        </div>
    </form>
</div>

{{-- Empty State --}}
@if($subjectTypes->isEmpty())
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="tag" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        @if(request('search') || request('status'))
            <p class="text-lg font-medium text-heading">No subject types matching your search in session <span class="text-navy">{{ $session->name }}</span>.</p>
            <p class="text-sm text-body mt-1">Try adjusting your keyword or status filter.</p>
            <a href="{{ route('academic.subject-types.index') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Clear Search Filters
            </a>
        @else
            <p class="text-lg font-medium text-heading">No subject types found for session <span class="text-navy">{{ $session->name }}</span>.</p>
            <p class="text-sm text-body mt-1">Subject types are isolated per session. Add types like Theory, Practical, Core, Elective for this session.</p>
            <button onclick="document.getElementById('addTypeModal').classList.remove('hidden')"
                    class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
                <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i> Add First Type
            </button>
        @endif
    </div>
@else

    {{-- Main Table View --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-4 py-2.5 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-gray-600">
                    Showing: <strong class="text-gray-900 font-bold">{{ $subjectTypes->firstItem() ?? 0 }} - {{ $subjectTypes->lastItem() ?? 0 }}</strong> of <strong class="text-gray-900 font-bold">{{ $subjectTypes->total() }}</strong> types
                </span>
                <span class="text-xs text-gray-300">|</span>
                <span class="text-xs text-gray-600 font-medium">Session: <strong class="text-gray-900">{{ $session->name }}</strong></span>
            </div>
            @if(request('search'))
                <span class="text-xs font-medium text-gray-800 bg-white px-2.5 py-0.5 rounded-md border border-gray-300">
                    Search: "{{ request('search') }}"
                </span>
            @endif
        </div>

        {{-- ══ Bulk Delete Toolbar ══ --}}
        <div id="bulkToolbar" class="hidden m-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
            <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
            <form id="bulkDeleteForm" method="POST" action="{{ route('academic.subject-types.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
                @csrf
                <div id="bulkIdsContainer"></div>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
                </button>
            </form>
            <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy">
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                        </th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Type Name</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Subjects in Session</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-white font-semibold text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($subjectTypes as $index => $type)
                        <tr class="hover:bg-gray-50/80 transition-colors" data-id="{{ $type->id }}">
                            <td class="px-4 py-2.5 text-center w-10">
                                <input type="checkbox" value="{{ $type->id }}" onchange="updateBulkToolbar()"
                                    class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-2.5 font-bold text-gray-900 text-xs">
                                {{ $type->name }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 text-xs font-medium">{{ $type->description ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                @if($type->subjects_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i data-lucide="book-open" class="w-3 h-3 mr-1 text-blue-600"></i>
                                        {{ $type->subjects_count }} {{ Str::plural('subject', $type->subjects_count) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-400">
                                        0 subjects
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <form method="POST" action="{{ route('academic.subject-types.update-status', $type) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $type->is_active ? 0 : 1 }}">
                                    <button type="submit"
                                            title="Click to toggle status"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold transition-transform hover:scale-105 {{ $type->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openEditType({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ addslashes($type->description ?? '') }}', {{ $type->is_active ? 1 : 0 }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                            title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-600"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('academic.subject-types.destroy', $type) }}"
                                          onsubmit="return confirm('Delete type {{ addslashes($type->name) }}? Subjects linked to this type will be unlinked.')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 bg-white border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 transition-colors shadow-2xs"
                                                title="Delete">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Links Bar --}}
        @if($subjectTypes->hasPages())
            <div class="px-4 py-2.5 border-t border-gray-200 bg-white">
                {{ $subjectTypes->links() }}
            </div>
        @else
            <div class="px-4 py-2.5 border-t border-gray-200 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>Showing <strong class="text-gray-700 font-semibold">{{ $subjectTypes->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $subjectTypes->total() }}</strong> subject types</span>
                <span class="text-gray-400">Page 1 of 1</span>
            </div>
        @endif
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- ── MODAL BOX: PARTICULAR SESSION SUBJECT TYPE ANALYSIS (SHOWMODEL BOX) ── --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="subjectTypeAnalysisModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-6"
     style="background:rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-150">

        {{-- Modal Top Banner Header --}}
        <div class="bg-navy text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold leading-tight">Session Subject Type Analytics</h3>
                    <p class="text-xs text-white/70 mt-0.5 flex items-center gap-2">
                        <span>Session: <strong class="text-white">{{ $session->name }}</strong></span>
                        <span>&bull;</span>
                        <span>School: <strong class="text-white">{{ $school->name }}</strong></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <span class="text-amber-300">{{ $analytics['totalTypes'] }} Types</span>
                    <span class="text-white/40">|</span>
                    <span>{{ $analytics['totalAssignedSubjects'] }} Typed Subjects</span>
                </div>
                <button type="button" onclick="closeTypeAnalysisModal()"
                        class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Quick Search Inside Modal --}}
        <div class="bg-stone-50 border-b border-stone-200 px-6 py-3 flex items-center justify-between gap-3 flex-shrink-0">
            <div class="text-xs text-stone-600 font-medium">
                Analysis of <strong>{{ count($analytics['typesAnalysis']) }}</strong> subject types and their assigned courses in <strong>{{ $session->name }}</strong>:
            </div>
            <div class="relative w-full max-w-xs">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" id="typeModalSearchInput" oninput="filterTypeModalCards(this.value)"
                       placeholder="Filter types, subjects, classes..."
                       class="w-full rounded-lg border border-stone-200 text-xs pl-8 pr-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
            </div>
        </div>

        {{-- Modal Content Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-white">
            <div id="typeModalCardsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($analytics['typesAnalysis'] as $tItem)
                    <div class="type-modal-card border border-stone-200 rounded-xl p-4 bg-white hover:border-navy/40 hover:shadow-sm transition-all"
                         data-type-id="{{ $tItem['id'] }}">

                        {{-- Top Header Row --}}
                        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-navy/10 text-navy flex items-center justify-center font-bold text-xs">
                                    <i data-lucide="tag" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-heading">{{ $tItem['name'] }}</h4>
                                    <span class="text-[11px] text-stone-400">{{ $tItem['description'] ?? 'Educational Category' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $tItem['subjects_count'] > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-stone-100 text-stone-500' }}">
                                    {{ $tItem['subjects_count'] }} {{ Str::plural('Subject', $tItem['subjects_count']) }}
                                </span>
                            </div>
                        </div>

                        {{-- Progress Bar showing percentage of total curriculum --}}
                        <div class="my-3">
                            <div class="flex items-center justify-between text-[11px] text-stone-500 mb-1">
                                <span>Curriculum Share:</span>
                                <strong class="text-navy">{{ $tItem['percentage'] }}%</strong>
                            </div>
                            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-navy h-2 rounded-full transition-all duration-700" style="width: {{ $tItem['percentage'] }}%;"></div>
                            </div>
                        </div>

                        {{-- Classes teaching this type --}}
                        @if(count($tItem['classes']) > 0)
                        <div class="mb-2 text-xs">
                            <span class="font-bold text-stone-700 text-[11px]">Taught in Classes:</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($tItem['classes'] as $clsName)
                                    <span class="px-2 py-0.5 rounded bg-stone-100 text-stone-700 text-[11px] font-medium border border-stone-200">
                                        {{ $clsName }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Subject pills under this type --}}
                        @if($tItem['subjects_count'] > 0)
                        <div class="mt-2.5 pt-2 border-t border-stone-100">
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block mb-1">Assigned Subjects ({{ $tItem['subjects_count'] }}):</span>
                            <div class="flex flex-wrap gap-1 max-h-28 overflow-y-auto">
                                @foreach($tItem['subjects'] as $sub)
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 text-[11px] font-semibold border border-blue-100">
                                        {{ $sub->name }} @if($sub->schoolClass)<span class="text-blue-500 font-normal">({{ $sub->schoolClass->name }})</span>@endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="my-3 py-3 text-center bg-stone-50 rounded-lg border border-dashed border-stone-200">
                            <p class="text-xs text-stone-500">No subjects currently assigned to this type.</p>
                            <a href="{{ route('academic.subjects.index') }}" class="text-xs text-navy font-bold hover:underline inline-flex items-center gap-1 mt-1">
                                <i data-lucide="plus" class="w-3 h-3"></i> Assign to subjects
                            </a>
                        </div>
                        @endif

                        {{-- Card Footer --}}
                        <div class="pt-2.5 mt-2.5 border-t border-stone-100 flex items-center justify-between text-xs">
                            <span class="text-[11px] font-semibold {{ $tItem['is_active'] ? 'text-emerald-600' : 'text-stone-400' }}">
                                &bull; {{ $tItem['is_active'] ? 'Active Type' : 'Inactive Type' }}
                            </span>
                            <a href="{{ route('academic.subjects.index') }}" class="text-xs font-bold text-navy hover:underline inline-flex items-center gap-1">
                                <span>Go to Subjects</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-8 text-stone-400">No subject types found for this session.</div>
                @endforelse
            </div>

            {{-- Untyped Subjects Alert Box --}}
            @if($analytics['untypedSubjects'] > 0)
            <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/60 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                        <i data-lucide="info" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h5 class="text-xs font-bold text-amber-900">{{ $analytics['untypedSubjects'] }} Untyped Subjects Found in Session {{ $session->name }}</h5>
                        <p class="text-[11px] text-amber-700">These subjects have not been categorized into any Subject Type yet.</p>
                    </div>
                </div>
                <a href="{{ route('academic.subjects.index') }}"
                   class="px-3 py-1.5 bg-amber-700 hover:bg-amber-800 text-white rounded-lg text-xs font-bold whitespace-nowrap transition">
                    Categorize Subjects &rarr;
                </a>
            </div>
            @endif
        </div>

        {{-- Modal Footer --}}
        <div class="bg-stone-50 border-t border-stone-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div class="text-xs text-stone-500">
                Subject Types isolated for session <strong>{{ $session->name }}</strong>
            </div>
            <button type="button" onclick="closeTypeAnalysisModal()"
                    class="px-4 py-2 bg-stone-200 hover:bg-stone-300 text-stone-700 text-xs font-bold rounded-lg transition-colors">
                Close Analysis
            </button>
        </div>

    </div>
</div>

{{-- Add Type Modal --}}
<div id="addTypeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="tag" class="w-5 h-5 text-navy"></i>
                Add Subject Type
            </h3>
            <button onclick="document.getElementById('addTypeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('academic.subject-types.store') }}">
            @csrf
            <div class="space-y-4">
                {{-- Session Info Notice --}}
                <div class="p-2.5 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-800 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-blue-600 flex-shrink-0"></i>
                    <span>This type will be created for Session: <strong>{{ $session->name }}</strong></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Type Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Theory, Practical, Core, Language"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Description (optional)</label>
                    <input type="text" name="description" placeholder="Short description"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition">
                    Create Type
                </button>
                <button type="button" onclick="document.getElementById('addTypeModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Type Modal --}}
<div id="editTypeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="pencil" class="w-5 h-5 text-navy"></i>
                Edit Subject Type
            </h3>
            <button onclick="document.getElementById('editTypeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editTypeForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div class="p-2.5 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-800 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-blue-600 flex-shrink-0"></i>
                    <span>Session: <strong>{{ $session->name }}</strong></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Type Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editTypeName" name="name" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Description (optional)</label>
                    <input type="text" id="editTypeDesc" name="description"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="editTypeActive" name="is_active" value="1" class="rounded">
                    <label for="editTypeActive" class="text-sm font-medium text-heading">Active</label>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition">
                    Update Type
                </button>
                <button type="button" onclick="document.getElementById('editTypeModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="importTypeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background:#fff;">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Subject Types for {{ $session->name }}
            </div>
            <button onclick="document.getElementById('importTypeModal').classList.add('hidden')"
                    class="text-white opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            {{-- Sample download --}}
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-sm font-semibold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-xs mt-0.5" style="color:#a07040;">Use this template to prepare CSV</p>
                </div>
                <a href="{{ route('academic.subject-types.import-demo') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Sample CSV
                </a>
            </div>

            {{-- Column legend --}}
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-semibold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <span>1. Type Name *</span>
                    <span>2. Description</span>
                    <span>3. Status</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Status: Active or Inactive. Types will be assigned to Session {{ $session->name }}.</p>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('academic.subject-types.import') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-sm font-medium mb-2 text-gray-800">Select CSV File</label>
                <div id="typeDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('typeCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleTypeDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-sm font-medium" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="typeFileName" class="text-xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="typeCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('typeFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('importTypeModal').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border"
                            style="border-color:#ddd; color:#555; background:#fff;">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-white bg-navy">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });

    // ── Analysis Modal Controller ──
    function openTypeAnalysisModal() {
        const modal = document.getElementById('subjectTypeAnalysisModal');
        if (modal) {
            modal.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeTypeAnalysisModal() {
        const modal = document.getElementById('subjectTypeAnalysisModal');
        if (modal) modal.classList.add('hidden');
    }

    function filterTypeModalCards(query) {
        query = (query || '').toLowerCase().trim();
        document.querySelectorAll('.type-modal-card').forEach(function (card) {
            var text = card.textContent.toLowerCase();
            card.style.display = (!query || text.includes(query)) ? '' : 'none';
        });
    }

    // Modal background click or ESC key to close
    document.getElementById('subjectTypeAnalysisModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeTypeAnalysisModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeTypeAnalysisModal();
    });

    function openEditType(id, name, desc, isActive) {
        document.getElementById('editTypeForm').action = '/academic/subject-types/' + id;
        document.getElementById('editTypeName').value = name;
        document.getElementById('editTypeDesc').value = desc;
        document.getElementById('editTypeActive').checked = isActive === 1;
        document.getElementById('editTypeModal').classList.remove('hidden');
    }

    function handleTypeDrop(e) {
        e.preventDefault();
        document.getElementById('typeDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('typeCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('typeFileName').textContent = file.name;
        }
    }
    document.getElementById('importTypeModal')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>

{{-- ══ Bulk Select JS ══ --}}
<script>
(function() {
    function toggleSelectAll(cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = cb.checked;
            r.closest('tr').classList.toggle('bg-red-50', cb.checked);
        });
        updateBulkToolbar();
    }
    function updateBulkToolbar() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        var all     = document.querySelectorAll('.row-checkbox');
        var toolbar = document.getElementById('bulkToolbar');
        var countEl = document.getElementById('selectedCount');
        var sa      = document.getElementById('selectAll');
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.closest('tr').classList.toggle('bg-red-50', r.checked);
        });
        if (checked.length > 0) {
            if(toolbar) toolbar.classList.remove('hidden');
            if(countEl) countEl.textContent = checked.length + ' selected';
        } else {
            if(toolbar) toolbar.classList.add('hidden');
        }
        if (sa) {
            sa.indeterminate = checked.length > 0 && checked.length < all.length;
            sa.checked = all.length > 0 && checked.length === all.length;
        }
    }
    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = false;
            r.closest('tr').classList.remove('bg-red-50');
        });
        var sa = document.getElementById('selectAll');
        if (sa) { sa.checked = false; sa.indeterminate = false; }
        var toolbar = document.getElementById('bulkToolbar');
        if(toolbar) toolbar.classList.add('hidden');
    }
    function confirmBulkDelete() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm(checked.length + ' item(s) permanently delete honge. Sure hain?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }
    window.toggleSelectAll   = toggleSelectAll;
    window.updateBulkToolbar = updateBulkToolbar;
    window.clearSelection    = clearSelection;
    window.confirmBulkDelete = confirmBulkDelete;
})();
</script>
@endsection
