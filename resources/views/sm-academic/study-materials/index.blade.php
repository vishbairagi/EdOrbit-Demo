@extends('layouts.app')

@section('header', 'Study Materials')

@section('content')

{{-- Page Header --}}
{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Study Materials</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Materials: {{ $materials->total() }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" onclick="openMaterialAnalysisModal('types')"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="pie-chart" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> View Analysis
        </button>
        <a href="{{ route('academic.study-materials.export', request()->all()) }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs" title="Export to CSV">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Export CSV
        </a>
        <button type="button" onclick="document.getElementById('importMaterialModal').classList.remove('hidden')"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs" title="Import from CSV">
            <i data-lucide="upload-cloud" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Import CSV
        </button>
        <button type="button" onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
           class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Add Material
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
         style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('import_errors'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-r-xl shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Some rows were skipped during import:</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Search & Class Filter Bar --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('academic.study-materials.index') }}" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
            {{-- Class Dropdown --}}
            <div class="w-full md:w-56">
                <label class="block text-xs font-medium text-body mb-1">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Material Type Dropdown --}}
            <div class="w-full md:w-44">
                <label class="block text-xs font-medium text-body mb-1">Material Type:</label>
                <select name="type" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Types</option>
                    <option value="pdf" {{ request('type') === 'pdf' ? 'selected' : '' }}>PDF Documents</option>
                    <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Images / Diagrams</option>
                    <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video Lessons</option>
                    <option value="description" {{ request('type') === 'description' ? 'selected' : '' }}>Notes / Description</option>
                </select>
            </div>

            {{-- Keyword Search --}}
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-body mb-1">Search Material:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Book name, topic, description..."
                           class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('academic.study-materials.index', array_filter(['class_id' => request('class_id'), 'type' => request('type'), 'per_page' => request('per_page')])) }}"
                           class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Per-Page Dropdown (Pagination functionality) --}}
            <div class="w-full md:w-32">
                <label class="block text-xs font-medium text-body mb-1">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white cursor-pointer"
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
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition bg-navy">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request('class_id') || request('type') || request('search') || (request('per_page') && request('per_page') != 10))
                    <a href="{{ route('academic.study-materials.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium text-body hover:bg-gray-50 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        {{-- View Mode Switcher (All vs Grouped by Class) --}}
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg self-end md:self-center">
            <button type="button" onclick="switchView('table')" id="btnViewTable"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy">
                <i data-lucide="list" class="w-3.5 h-3.5 inline-block mr-1"></i> Single Table
            </button>
            <button type="button" onclick="switchView('grouped')" id="btnViewGrouped"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 inline-block mr-1"></i> Grouped by Class
            </button>
        </div>
    </form>
</div>

{{-- Empty State --}}
@if($materials->isEmpty())
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="file-text" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No study materials found.</p>
        <p class="text-sm text-body mt-1">Try adjusting your filters or upload new materials.</p>
        <button onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
           class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i> Add Material
        </button>
    </div>
@else

    {{-- VIEW 1: SINGLE TABLE VIEW --}}
    <div id="viewTableContainer" class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2" style="background:#F5F1EB;">
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-body">
                    Showing: <strong>{{ $materials->firstItem() ?? 0 }} - {{ $materials->lastItem() ?? 0 }}</strong> of <strong>{{ $materials->total() }}</strong> study materials
                </span>
                <span class="text-xs text-stone-400">|</span>
                <span class="text-xs text-stone-600 font-medium">Session: <strong>{{ $session->name }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                @if(request('class_id'))
                    @php $activeFilteredClass = $classes->firstWhere('id', request('class_id')); @endphp
                    <span class="text-xs font-semibold text-navy bg-white px-2.5 py-1 rounded-md border border-gray-200">
                        Class: {{ $activeFilteredClass?->name ?? 'ID '.request('class_id') }}
                    </span>
                @endif
                @if(request('type'))
                    <span class="text-xs font-semibold text-stone-700 bg-white px-2.5 py-1 rounded-md border border-gray-200 uppercase">
                        Type: {{ request('type') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ══ Bulk Delete Toolbar ══ --}}
        <div id="bulkToolbar" class="hidden m-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
            <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
            <form id="bulkDeleteForm" method="POST" action="{{ route('academic.study-materials.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
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
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Book / Material Name</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Class</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Section</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-center text-white font-semibold text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($materials as $material)
                        <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $material->id }}">
                            <td class="px-4 py-3 text-center w-10">
                                <input type="checkbox" value="{{ $material->id }}" onchange="updateBulkToolbar()"
                                    class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-heading">{{ $material->book_name }}</p>
                                @if($material->title)
                                    <p class="text-xs text-body mt-0.5">{{ $material->title }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i data-lucide="graduation-cap" class="w-3 h-3 mr-1"></i>
                                    {{ $material->schoolClass?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-body text-xs font-medium">{{ $material->section?->name ?? 'All Sections' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $typeColors = [
                                        'pdf'         => 'bg-red-100 text-red-700',
                                        'image'       => 'bg-green-100 text-green-700',
                                        'video'       => 'bg-purple-100 text-purple-700',
                                        'description' => 'bg-blue-100 text-blue-700',
                                    ];
                                    $typeIcons = ['pdf' => 'file-text', 'image' => 'image', 'video' => 'video', 'description' => 'align-left'];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeColors[$material->type] ?? 'bg-gray-100 text-gray-600' }}">
                                    <i data-lucide="{{ $typeIcons[$material->type] ?? 'file' }}" class="w-3 h-3"></i>
                                    {{ ucfirst($material->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-body text-xs truncate max-w-[150px]" title="{{ $material->description }}">
                                {{ $material->description ?: '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if($material->file_path)
                                        @php $url = str_starts_with($material->file_path, 'http') ? $material->file_path : Storage::url($material->file_path); @endphp
                                        <a href="{{ $url }}" target="_blank"
                                           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i> View
                                        </a>
                                    @endif
                                    <button onclick='openEditMaterialModal(@json($material))'
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                                        <i data-lucide="edit" class="w-3.5 h-3.5 mr-1"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('academic.study-materials.destroy', $material) }}"
                                          onsubmit="return confirm('Delete this study material?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg border border-gray-300 text-red-600 bg-white hover:bg-gray-50 transition shadow-2xs">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-1"></i> Delete
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
        @if($materials->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-white">
                {{ $materials->links() }}
            </div>
        @else
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>Showing <strong class="text-gray-700 font-semibold">{{ $materials->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $materials->total() }}</strong> study materials</span>
                <span class="text-gray-400">Page 1 of 1</span>
            </div>
        @endif
    </div>

    {{-- VIEW 2: GROUPED BY CLASS VIEW --}}
    <div id="viewGroupedContainer" class="hidden space-y-6 mb-6">
        @foreach($materialsByClass as $className => $classMaterials)
            <div class="bg-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between" style="background:#F5F1EB;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-navy flex items-center justify-center text-white text-xs font-bold">
                            <i data-lucide="book" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-base text-heading">{{ $className }}</h3>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                            {{ $classMaterials->count() }} material(s)
                        </span>
                    </div>
                    <button onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
                       class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white rounded-md hover:opacity-90 transition bg-navy">
                        <i data-lucide="plus" class="w-3 h-3"></i> Add Material
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                                <th class="px-4 py-2.5 text-left">Book / Material Name</th>
                                <th class="px-4 py-2.5 text-left">Section</th>
                                <th class="px-4 py-2.5 text-left">Type</th>
                                <th class="px-4 py-2.5 text-left">Description</th>
                                <th class="px-4 py-2.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($classMaterials as $idx => $material)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-heading">{{ $material->book_name }}</p>
                                        @if($material->title)
                                            <p class="text-xs text-body mt-0.5">{{ $material->title }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-body text-xs">{{ $material->section?->name ?? 'All Sections' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $typeColors = [
                                                'pdf'         => 'bg-red-100 text-red-700',
                                                'image'       => 'bg-green-100 text-green-700',
                                                'video'       => 'bg-purple-100 text-purple-700',
                                                'description' => 'bg-blue-100 text-blue-700',
                                            ];
                                            $typeIcons = ['pdf' => 'file-text', 'image' => 'image', 'video' => 'video', 'description' => 'align-left'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeColors[$material->type] ?? 'bg-gray-100 text-gray-600' }}">
                                            <i data-lucide="{{ $typeIcons[$material->type] ?? 'file' }}" class="w-3 h-3"></i>
                                            {{ ucfirst($material->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-body text-xs truncate max-w-[150px]" title="{{ $material->description }}">
                                        {{ $material->description ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($material->file_path)
                                                @php $url = str_starts_with($material->file_path, 'http') ? $material->file_path : Storage::url($material->file_path); @endphp
                                                <a href="{{ $url }}" target="_blank"
                                                   class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i> View
                                                </a>
                                            @endif
                                            <button onclick='openEditMaterialModal(@json($material))'
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                                                <i data-lucide="edit" class="w-3.5 h-3.5 mr-1"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('academic.study-materials.destroy', $material) }}"
                                                  onsubmit="return confirm('Delete this study material?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg border border-gray-300 text-red-600 bg-white hover:bg-gray-50 transition shadow-2xs">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════════
     INTERACTIVE DRILLDOWN MODAL: SESSION STUDY MATERIALS ANALYSIS
     ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="materialAnalysisModal" class="hidden fixed inset-0 z-[120] flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-5">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-stone-200">
        
        {{-- Modal Top Banner Header --}}
        <div class="bg-navy text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold leading-tight">Session Study Materials & Curriculum Assets Analysis</h3>
                    <p class="text-xs text-white/70 mt-0.5 flex items-center gap-2">
                        <span>Session: <strong class="text-white">{{ $session->name }}</strong></span>
                        <span>&bull;</span>
                        <span>School: <strong class="text-white">{{ $school->name }}</strong></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <span class="text-amber-300">{{ $analytics['totalMaterials'] }} Materials</span>
                    <span class="text-white/40">|</span>
                    <span>{{ $analytics['classesWithMaterials'] }} Classes Covered</span>
                    <span class="text-white/40">|</span>
                    <span class="text-emerald-300">{{ $analytics['digitalResourceRate'] }}% Files</span>
                </div>
                <button type="button" onclick="closeMaterialAnalysisModal()"
                        class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Modal Interactive Tabs & Quick Search Bar --}}
        <div class="bg-stone-50 border-b border-stone-200 px-6 py-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button type="button" id="analysisBtn_types" onclick="switchMaterialTab('types')"
                        class="material-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-navy text-white shadow-sm">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Material Type Breakdown</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($analytics['typeBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_classes" onclick="switchMaterialTab('classes')"
                        class="material-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>Class-wise Study Material</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-stone-200 text-stone-700">{{ count($analytics['classBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_assets" onclick="switchMaterialTab('assets')"
                        class="material-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                    <span>Digital Files vs Notes</span>
                </button>
            </div>

            {{-- Client-side quick search inside modal --}}
            <div class="relative w-full md:w-64">
                <input type="text" id="modalSearchInput" oninput="filterMaterialModalSearch(this.value)"
                       placeholder="Filter by book name, topic, or class..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                <i data-lucide="search" class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
            </div>
        </div>

        {{-- Modal Tab Content Scroll Area --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4" style="background: #faf7f2;">

            {{-- ── TAB 1: MATERIAL TYPE BREAKDOWN ── --}}
            <div id="materialTab_types" class="material-tab-pane space-y-4">
                <div class="bg-purple-50/70 border border-purple-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-purple-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-purple-700 flex-shrink-0"></i>
                        <span>Distribution of curriculum resources across material types (PDF, Video, Image, and Text Notes) in session <strong>{{ $session->name }}</strong>.</span>
                    </div>
                    <span class="font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">{{ $analytics['totalMaterials'] }} Total Assets</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($analytics['typeBreakdown'] as $tb)
                        <div class="modal-type-card bg-white rounded-xl border border-stone-200 p-4 shadow-sm hover:shadow transition-all"
                             data-type-name="{{ strtolower($tb['name']) }}"
                             data-materials="{{ strtolower($tb['materials']->pluck('book_name')->implode(' ')) }}">
                            
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-white shadow-sm"
                                         style="background: {{ $tb['type'] === 'pdf' ? '#e74c3c' : ($tb['type'] === 'video' ? '#8e44ad' : ($tb['type'] === 'image' ? '#27ae60' : '#2980b9')) }};">
                                        <i data-lucide="{{ $tb['type'] === 'pdf' ? 'file-text' : ($tb['type'] === 'video' ? 'video' : ($tb['type'] === 'image' ? 'image' : 'align-left')) }}" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading">{{ $tb['name'] }}</h4>
                                        <span class="text-[11px] text-stone-400">Taught in {{ count($tb['classes']) }} classes</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-navy">{{ $tb['count'] }}</span>
                                    <span class="text-[10px] text-stone-400 block font-semibold">{{ $tb['percentage'] }}% Share</span>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="w-full bg-stone-100 rounded-full h-2 my-3 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-700"
                                     style="background: {{ $tb['type'] === 'pdf' ? '#e74c3c' : ($tb['type'] === 'video' ? '#8e44ad' : ($tb['type'] === 'image' ? '#27ae60' : '#2980b9')) }}; width: {{ $tb['percentage'] }}%;"></div>
                            </div>

                            {{-- Classes using this type --}}
                            <div class="text-xs text-stone-600 mb-2">
                                <span class="font-bold text-stone-700">Taught in classes:</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @forelse($tb['classes'] as $clsName)
                                        <span class="px-2 py-0.5 rounded bg-stone-100 text-stone-700 text-[11px] font-medium border border-stone-200">
                                            {{ $clsName }}
                                        </span>
                                    @empty
                                        <span class="text-stone-400 text-xs italic">Not used in any class yet</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Materials Preview --}}
                            <div class="pt-2 border-t border-stone-100">
                                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider block mb-1">
                                    Study Materials ({{ $tb['count'] }}):
                                </span>
                                <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                    @forelse($tb['materials'] as $mItem)
                                        <span class="px-2 py-0.5 rounded bg-stone-50 text-stone-800 text-[11px] font-semibold border border-stone-200">
                                            {{ $mItem->book_name }} ({{ $mItem->schoolClass?->name ?? '—' }})
                                        </span>
                                    @empty
                                        <span class="text-stone-400 text-xs italic">No items recorded.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No study material types found.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 2: CLASS-WISE STUDY MATERIAL ── --}}
            <div id="materialTab_classes" class="material-tab-pane hidden space-y-4">
                <div class="bg-blue-50/70 border border-blue-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-blue-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-blue-700 flex-shrink-0"></i>
                        <span>Class-wise inventory showing total curriculum materials, media formats, and resource density per class.</span>
                    </div>
                    <span class="font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">Avg: {{ $analytics['avgMaterialsPerClass'] }} / Class</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($analytics['classBreakdown'] as $cb)
                        <div class="modal-class-card bg-white rounded-xl border border-stone-200 p-4 shadow-sm hover:shadow transition-all"
                             data-class-name="{{ strtolower($cb['name']) }}"
                             data-materials="{{ strtolower($cb['materials']->pluck('book_name')->implode(' ')) }}">
                            
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-navy text-white flex items-center justify-center text-xs font-bold">
                                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading">{{ $cb['name'] }}</h4>
                                        <span class="text-[11px] text-stone-400">{{ $cb['count'] }} Materials Uploaded</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-navy">{{ $cb['count'] }}</span>
                                    <span class="text-[10px] text-stone-400 block font-semibold">Total Assets</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2 my-3 text-center text-xs">
                                <div class="p-2 rounded bg-red-50/50 border border-red-100">
                                    <span class="text-[10px] text-red-600 uppercase block font-semibold">PDF</span>
                                    <strong class="text-red-700 text-sm font-black">{{ $cb['pdf_count'] }}</strong>
                                </div>
                                <div class="p-2 rounded bg-purple-50/50 border border-purple-100">
                                    <span class="text-[10px] text-purple-600 uppercase block font-semibold">Video</span>
                                    <strong class="text-purple-700 text-sm font-black">{{ $cb['video_count'] }}</strong>
                                </div>
                                <div class="p-2 rounded bg-green-50/50 border border-green-100">
                                    <span class="text-[10px] text-green-600 uppercase block font-semibold">Image</span>
                                    <strong class="text-green-700 text-sm font-black">{{ $cb['image_count'] }}</strong>
                                </div>
                                <div class="p-2 rounded bg-blue-50/50 border border-blue-100">
                                    <span class="text-[10px] text-blue-600 uppercase block font-semibold">Notes</span>
                                    <strong class="text-blue-700 text-sm font-black">{{ $cb['notes_count'] }}</strong>
                                </div>
                            </div>

                            {{-- Materials Preview --}}
                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-xs">
                                <span class="text-stone-500 text-[11px]">Class Directory:</span>
                                <button type="button" onclick="filterMainTableByClass({{ $cb['id'] }})"
                                        class="text-navy hover:underline font-semibold inline-flex items-center gap-1">
                                    Filter Table by this Class <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No classes found.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 3: DIGITAL FILES VS NOTES ── --}}
            <div id="materialTab_assets" class="material-tab-pane hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl border border-emerald-200 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                                <i data-lucide="download-cloud" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-heading">Downloadable Digital Files</h4>
                                <span class="text-xs text-stone-400">PDFs, videos, images stored in cloud/storage</span>
                            </div>
                        </div>
                        <h3 class="text-3xl font-black text-emerald-700">{{ $analytics['withFilesCount'] }}</h3>
                        <div class="w-full bg-stone-100 rounded-full h-2.5 my-3 overflow-hidden">
                            <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $analytics['digitalResourceRate'] }}%;"></div>
                        </div>
                        <span class="text-xs text-stone-500 font-semibold">{{ $analytics['digitalResourceRate'] }}% of all study materials have attached files.</span>
                    </div>

                    <div class="bg-white rounded-xl border border-blue-200 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-heading">Inline Descriptions & Notes</h4>
                                <span class="text-xs text-stone-400">Classroom notes, reference links & instructions</span>
                            </div>
                        </div>
                        <h3 class="text-3xl font-black text-blue-700">{{ $analytics['textOnlyCount'] }}</h3>
                        <div class="w-full bg-stone-100 rounded-full h-2.5 my-3 overflow-hidden">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $analytics['totalMaterials'] > 0 ? 100 - $analytics['digitalResourceRate'] : 0 }}%;"></div>
                        </div>
                        <span class="text-xs text-stone-500 font-semibold">{{ $analytics['totalMaterials'] > 0 ? 100 - $analytics['digitalResourceRate'] : 0 }}% text-based instructional guides.</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Add Material Modal --}}
<div id="addMaterialModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5 text-navy"></i>
                Add Study Material
            </h3>
            <button type="button" onclick="closeAddMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" action="{{ route('academic.study-materials.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                {{-- Book Name --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Book Name <span class="text-red-500">*</span></label>
                    <input type="text" name="book_name" required placeholder="e.g. Mathematics Part-1"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                {{-- Title / Topic --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Title / Topic (optional)</label>
                    <input type="text" name="title" placeholder="e.g. Chapter 1 - Number System"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Class --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Class <span class="text-red-500">*</span></label>
                        <select name="school_class_id" required onchange="loadSections(this.value)"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— Select Class —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Section (optional)</label>
                        <select name="section_id" id="modalSectionSelect"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— All Sections —</option>
                        </select>
                    </div>
                </div>

                {{-- Material Type --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Material Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1.5">
                        @foreach(['pdf' => 'PDF', 'image' => 'Image', 'video' => 'Video', 'description' => 'Notes/Text'] as $value => $label)
                            <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-navy has-[:checked]:bg-navy/5">
                                <input type="radio" name="type" value="{{ $value }}" onchange="handleTypeChange('{{ $value }}')"
                                       {{ $value === 'pdf' ? 'checked' : '' }}
                                       class="text-navy">
                                <span class="text-sm font-medium text-heading">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- File Upload --}}
                <div id="fileSection">
                    <label class="block text-sm font-medium text-heading mb-1">Upload File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" id="fileInput"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    <p class="text-xs text-body mt-1" id="fileHint">Max 50MB. PDF files only.</p>
                </div>

                {{-- Description / Notes --}}
                <div id="descriptionSection">
                    <label class="block text-sm font-medium text-heading mb-1">Notes / Description <span class="text-body font-normal text-xs">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Write your notes or description here..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Upload Material
                </button>
                <button type="button" onclick="closeAddMaterialModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import Material Modal --}}
<div id="importMaterialModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-navy"></i>
                Import Study Materials
            </h3>
            <button type="button" onclick="closeImportMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" action="{{ route('academic.study-materials.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-heading">Need the template?</p>
                        <p class="text-[11px] text-body">Download pre-formatted CSV</p>
                    </div>
                    <a href="{{ route('academic.study-materials.import-demo') }}" class="inline-flex items-center font-semibold hover:underline text-navy text-xs">
                        <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i> Demo CSV
                    </a>
                </div>

                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Upload CSV File <span class="text-red-500">*</span></label>
                    <input type="file" name="csv_file" required accept=".csv,.txt"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Import Materials
                </button>
                <button type="button" onclick="closeImportMaterialModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Material Modal --}}
<div id="editMaterialModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="edit" class="w-5 h-5 text-navy"></i>
                Edit Study Material
            </h3>
            <button type="button" onclick="closeEditMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" action="" id="editMaterialForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                {{-- Book Name --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Book Name <span class="text-red-500">*</span></label>
                    <input type="text" name="book_name" id="edit_book_name" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                {{-- Title / Topic --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Title / Topic (optional)</label>
                    <input type="text" name="title" id="edit_title"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Class --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Class <span class="text-red-500">*</span></label>
                        <select name="school_class_id" id="edit_school_class_id" required onchange="loadSections(this.value, 'editModalSectionSelect')"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div>
                        <label class="block text-sm font-medium text-heading mb-1">Section (optional)</label>
                        <select name="section_id" id="editModalSectionSelect"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— All Sections —</option>
                        </select>
                    </div>
                </div>

                {{-- Material Type --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Material Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1.5">
                        @foreach(['pdf' => 'PDF', 'image' => 'Image', 'video' => 'Video', 'description' => 'Notes/Text'] as $value => $label)
                            <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-navy has-[:checked]:bg-navy/5">
                                <input type="radio" name="type" id="edit_type_{{ $value }}" value="{{ $value }}" onchange="handleTypeChange('{{ $value }}', 'edit')"
                                       class="text-navy">
                                <span class="text-sm font-medium text-heading">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- File Upload --}}
                <div id="edit_fileSection">
                    <label class="block text-sm font-medium text-heading mb-1">Replace File (optional)</label>
                    <input type="file" name="file" id="edit_fileInput"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    <p class="text-xs text-body mt-1" id="edit_fileHint">Max 50MB.</p>
                </div>

                {{-- Description / Notes --}}
                <div id="edit_descriptionSection">
                    <label class="block text-sm font-medium text-heading mb-1">Notes / Description <span class="text-body font-normal text-xs">(optional)</span></label>
                    <textarea name="description" id="edit_description" rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Update Material
                </button>
                <button type="button" onclick="closeEditMaterialModal()" class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeAddMaterialModal() {
        document.getElementById('addMaterialModal').classList.add('hidden');
    }

    function closeImportMaterialModal() {
        document.getElementById('importMaterialModal').classList.add('hidden');
    }

    function closeEditMaterialModal() {
        document.getElementById('editMaterialModal').classList.add('hidden');
    }

    function handleTypeChange(type, prefix = '') {
        const fileSection = document.getElementById(prefix ? prefix + '_fileSection' : 'fileSection');
        const fileHint    = document.getElementById(prefix ? prefix + '_fileHint' : 'fileHint');

        if (type === 'description') {
            fileSection.classList.add('hidden');
        } else {
            fileSection.classList.remove('hidden');
            if (type === 'pdf') fileHint.textContent = 'Max 50MB. PDF files only.';
            else if (type === 'image') fileHint.textContent = 'Max 50MB. JPG, PNG, GIF etc.';
            else if (type === 'video') fileHint.textContent = 'Max 50MB. MP4, AVI etc.';
        }
    }

    function loadSections(classId, selectId = 'modalSectionSelect', selectedSectionId = null) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">— All Sections —</option>';
        if (!classId) return;

        fetch('/academic/study-materials/sections/' + classId)
            .then(r => r.json())
            .then(sections => {
                sections.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    if (selectedSectionId == s.id) opt.selected = true;
                    select.appendChild(opt);
                });
            });
    }

    function openEditMaterialModal(material) {
        document.getElementById('editMaterialForm').action = '/academic/study-materials/' + material.id;
        document.getElementById('edit_book_name').value = material.book_name || '';
        document.getElementById('edit_title').value = material.title || '';
        document.getElementById('edit_description').value = material.description || '';
        
        document.getElementById('edit_school_class_id').value = material.school_class_id;
        loadSections(material.school_class_id, 'editModalSectionSelect', material.section_id);

        document.getElementById('edit_type_' + material.type).checked = true;
        handleTypeChange(material.type, 'edit');

        document.getElementById('editMaterialModal').classList.remove('hidden');
    }

    function switchView(mode) {
        const table = document.getElementById('viewTableContainer');
        const grouped = document.getElementById('viewGroupedContainer');
        const btnTable = document.getElementById('btnViewTable');
        const btnGrouped = document.getElementById('btnViewGrouped');

        if (mode === 'grouped') {
            table?.classList.add('hidden');
            grouped?.classList.remove('hidden');
            btnGrouped.className = "px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy";
            btnTable.className = "px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all";
            localStorage.setItem('materials_view_mode', 'grouped');
        } else {
            table?.classList.remove('hidden');
            grouped?.classList.add('hidden');
            btnTable.className = "px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy";
            btnGrouped.className = "px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all";
            localStorage.setItem('materials_view_mode', 'table');
        }
    }

    // ── Interactive Material Analysis Modal Controller ──
    function openMaterialAnalysisModal(tab = 'types') {
        const modal = document.getElementById('materialAnalysisModal');
        if (modal) {
            modal.classList.remove('hidden');
            switchMaterialTab(tab);
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeMaterialAnalysisModal() {
        const modal = document.getElementById('materialAnalysisModal');
        if (modal) modal.classList.add('hidden');
    }

    function switchMaterialTab(tabName) {
        document.querySelectorAll('.material-tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.material-tab-btn').forEach(btn => {
            btn.classList.remove('bg-navy', 'text-white', 'shadow-sm');
            btn.classList.add('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
        });

        const targetPane = document.getElementById('materialTab_' + tabName);
        const targetBtn = document.getElementById('analysisBtn_' + tabName);

        if (targetPane) targetPane.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.remove('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
            targetBtn.classList.add('bg-navy', 'text-white', 'shadow-sm');
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function filterMaterialModalSearch(query) {
        query = (query || '').toLowerCase().trim();

        document.querySelectorAll('.modal-type-card').forEach(card => {
            const type = card.getAttribute('data-type-name') || '';
            const materials = card.getAttribute('data-materials') || '';
            const match = !query || type.includes(query) || materials.includes(query);
            card.style.display = match ? '' : 'none';
        });

        document.querySelectorAll('.modal-class-card').forEach(card => {
            const cls = card.getAttribute('data-class-name') || '';
            const materials = card.getAttribute('data-materials') || '';
            const match = !query || cls.includes(query) || materials.includes(query);
            card.style.display = match ? '' : 'none';
        });
    }

    function filterMainTableByClass(classId) {
        closeMaterialAnalysisModal();
        const select = document.querySelector('select[name="class_id"]');
        if (select) {
            select.value = classId;
            document.getElementById('filterForm').submit();
        }
    }

    // Modal background click or ESC key to close
    document.getElementById('materialAnalysisModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeMaterialAnalysisModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMaterialAnalysisModal();
            closeAddMaterialModal();
            closeEditMaterialModal();
            closeImportMaterialModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const pref = localStorage.getItem('materials_view_mode');
        if (pref === 'grouped') {
            switchView('grouped');
        }
        if (window.lucide) {
            window.lucide.createIcons();
        }
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
        if (!confirm('Are you sure you want to permanently delete ' + checked.length + ' study material(s)?')) return false;
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
