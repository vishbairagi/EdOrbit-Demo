@extends('layouts.app')

@section('header', 'Notice Board')

@section('content')

{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Notice Board</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp; Total Notices: {{ $notices->total() ?? 0 }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('academic.noticeboards.export', request()->query()) }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs"
           title="Export filtered or all notices to CSV">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
            Export CSV
        </a>
        <button type="button" onclick="openImportNoticeModal()"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs"
                title="Bulk import notices from CSV">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
            Import CSV
        </button>
        <button type="button" onclick="openAddNoticeModal()"
                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
            Add New Notice
        </button>
    </div>
</div>

{{-- Success & Error Alerts --}}
@if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-5 rounded-r-xl shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2.5 text-emerald-800 text-sm font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-r-xl shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2.5 text-red-800 text-sm font-medium">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-r-xl shadow-sm">
        <div class="flex">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-2 flex-shrink-0"></i>
            <div>
                <h4 class="text-sm font-semibold text-red-800">Please correct the following errors:</h4>
                <ul class="list-disc pl-5 mt-1 text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if(session('import_errors'))
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-5 rounded-r-xl shadow-sm flex items-start justify-between">
        <div class="flex items-start gap-2.5">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mr-2 flex-shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-semibold text-amber-900">Some CSV rows could not be imported:</h4>
                <ul class="list-disc pl-5 mt-1 text-xs text-amber-800 space-y-0.5 max-h-48 overflow-y-auto">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

{{-- Mini Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mb-5">
    <div class="bg-card rounded-xl p-3.5 border border-gray-100 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i data-lucide="bell" class="w-4.5 h-4.5 text-blue-600"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-heading leading-tight">{{ $totalNotices }}</p>
            <p class="text-xs text-body">Total Notices</p>
        </div>
    </div>
    <div class="bg-card rounded-xl p-3.5 border border-gray-100 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <i data-lucide="check-circle-2" class="w-4.5 h-4.5 text-emerald-600"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-heading leading-tight">{{ $activeNotices }}</p>
            <p class="text-xs text-body">Active / Approved</p>
        </div>
    </div>
    <div class="bg-card rounded-xl p-3.5 border border-gray-100 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
            <i data-lucide="paperclip" class="w-4.5 h-4.5 text-amber-600"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-heading leading-tight">{{ $attachmentNotices }}</p>
            <p class="text-xs text-body">With Attachments</p>
        </div>
    </div>
    <div class="bg-card rounded-xl p-3.5 border border-gray-100 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
            <i data-lucide="link" class="w-4.5 h-4.5 text-purple-600"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-heading leading-tight">{{ $urlNotices }}</p>
            <p class="text-xs text-body">With URLs</p>
        </div>
    </div>
</div>

{{-- Filters and Search Bar --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('academic.noticeboards.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 flex-1">
            {{-- Class Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Link To Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Link To Type:</label>
                <select name="link_type" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Link Types</option>
                    <option value="none" {{ request('link_type') === 'none' ? 'selected' : '' }}>None</option>
                    <option value="attachment" {{ request('link_type') === 'attachment' ? 'selected' : '' }}>Attachment</option>
                    <option value="url" {{ request('link_type') === 'url' ? 'selected' : '' }}>URL</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Status:</label>
                <select name="status" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active / Approved</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            {{-- Per Page --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="10" {{ ($perPage ?? 15) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15 / page</option>
                    <option value="25" {{ ($perPage ?? 15) == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }}>100 / page</option>
                </select>
            </div>

            {{-- Search Keyword --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Search Notice:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, description, added by..."
                           class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 pr-7 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('academic.noticeboards.index', array_filter(['class_id' => request('class_id'), 'link_type' => request('link_type'), 'status' => request('status'), 'per_page' => request('per_page')])) }}"
                           class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1 md:pt-4">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-sm">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['class_id', 'link_type', 'status', 'search', 'per_page']))
                <a href="{{ route('academic.noticeboards.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-100 text-heading text-xs font-medium hover:bg-gray-200 transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Bulk Selection Bar (Hidden by default) --}}
<div id="bulkToolbar" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 mb-4 flex items-center justify-between animate-fadeIn">
    <div class="flex items-center gap-2 text-sm text-red-800 font-medium">
        <i data-lucide="check-square" class="w-4 h-4 text-red-600"></i>
        <span id="selectedCount">0 selected</span>
    </div>
    <form method="POST" action="{{ route('academic.noticeboards.bulk-delete') }}" onsubmit="return confirmBulkDelete(event)" class="flex items-center gap-2">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="button" onclick="clearSelection()"
                class="px-2.5 py-1 text-xs rounded bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
            Cancel
        </button>
        <button type="submit"
                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded bg-red-600 text-white hover:bg-red-700 transition">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            Delete Selected
        </button>
    </form>
</div>

{{-- Notices Table Card --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
    @if($notices->total() > 0)
        <div class="px-5 py-2.5 bg-gray-50/70 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-body">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-navy/10 text-navy font-semibold text-[11px]">
                    Page {{ $notices->currentPage() }} of {{ $notices->lastPage() }}
                </span>
                <span>
                    Showing <strong>{{ $notices->firstItem() }}</strong> to <strong>{{ $notices->lastItem() }}</strong> of <strong>{{ $notices->total() }}</strong> notices
                </span>
            </div>
            @if(request()->hasAny(['class_id', 'link_type', 'status', 'search']))
                <span class="text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full text-[11px] font-medium">
                    Filtered results
                </span>
            @endif
        </div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-body uppercase tracking-wider">
                    <th class="py-3 px-4 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                               class="rounded border-gray-300 text-navy focus:ring-navy">
                    </th>
                    <th class="py-3 px-4">Notice Title</th>
                    <th class="py-3 px-4">Link To</th>
                    <th class="py-3 px-4 text-center">Is Active</th>
                    <th class="py-3 px-4">Created Date</th>
                    <th class="py-3 px-4">Added By</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @forelse($notices as $notice)
                    <tr class="hover:bg-gray-50/70 transition-colors notice-row" id="notice-row-{{ $notice->id }}">
                        {{-- Row Checkbox --}}
                        <td class="py-3 px-4 text-center">
                            <input type="checkbox" value="{{ $notice->id }}" onchange="updateBulkToolbar()"
                                   class="row-checkbox rounded border-gray-300 text-navy focus:ring-navy">
                        </td>

                        {{-- Notice Title Column --}}
                        <td class="py-3 px-4">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-navy/5 text-navy flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-heading text-sm leading-tight hover:text-navy cursor-pointer"
                                        onclick="openViewNoticeModal({{ json_encode($notice) }})">
                                        {{ $notice->title }}
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="graduation-cap" class="w-3 h-3 mr-1"></i>
                                            {{ $notice->schoolClass?->name ?? 'All Classes' }}
                                        </span>
                                        @if($notice->section)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                Sec: {{ $notice->section->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-normal bg-gray-50 text-gray-500 border border-gray-100">
                                                All Sections
                                            </span>
                                        @endif
                                    </div>
                                    @if($notice->description)
                                        <p class="text-gray-500 line-clamp-1 mt-1 text-[11.5px] max-w-md">
                                            {{ Str::limit($notice->description, 75) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Link To (none, attachment, url) Column --}}
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($notice->link_type === 'attachment' && $notice->attachment_path)
                                @php
                                    $ext = $notice->attachment_extension;
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                    $isPdf = ($ext === 'pdf');
                                @endphp
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        @if($isPdf)
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-red-600"></i>
                                        @elseif($isImg)
                                            <i data-lucide="image" class="w-3.5 h-3.5 text-blue-600"></i>
                                        @else
                                            <i data-lucide="paperclip" class="w-3.5 h-3.5 text-amber-700"></i>
                                        @endif
                                        <span>Attachment</span>
                                        @if($notice->attachment_size)
                                            <span class="text-[10px] text-amber-600">({{ $notice->attachment_size }})</span>
                                        @endif
                                    </span>
                                    <a href="{{ route('academic.noticeboards.download', $notice->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[11px] font-medium text-navy hover:underline truncate max-w-[180px]"
                                       title="{{ $notice->attachment_original_name ?: 'Download Attachment' }}">
                                        <i data-lucide="download" class="w-3 h-3 flex-shrink-0"></i>
                                        <span class="truncate">{{ $notice->attachment_original_name ?: 'Download File' }}</span>
                                    </a>
                                </div>
                            @elseif($notice->link_type === 'url' && $notice->link_url)
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-purple-600"></i>
                                        <span>URL</span>
                                    </span>
                                    <a href="{{ $notice->link_url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 hover:text-blue-800 hover:underline truncate max-w-[180px]"
                                       title="{{ $notice->link_url }}">
                                        <i data-lucide="globe" class="w-3 h-3 flex-shrink-0"></i>
                                        <span class="truncate">{{ Str::limit($notice->link_url, 28) }}</span>
                                    </a>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                    <i data-lucide="minus" class="w-3 h-3 mr-1"></i>
                                    None
                                </span>
                            @endif
                        </td>

                        {{-- Is Active Column --}}
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <form method="POST" action="{{ route('academic.noticeboards.update-status', $notice->id) }}" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Click to toggle status"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all {{ $notice->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    <span class="w-2 h-2 rounded-full {{ $notice->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    <span>{{ $notice->is_active ? 'Approved / Active' : 'Inactive' }}</span>
                                </button>
                            </form>
                        </td>

                        {{-- Created Date Column --}}
                        <td class="py-3 px-4 whitespace-nowrap text-heading">
                            <div class="flex items-center gap-1.5 text-xs">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span class="font-medium">{{ $notice->created_at->format('d M Y') }}</span>
                            </div>
                            <span class="text-[11px] text-body pl-5 block">
                                {{ $notice->created_at->format('h:i A') }}
                            </span>
                        </td>

                        {{-- Added By Column --}}
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-navy/10 text-navy flex items-center justify-center font-bold text-[10px]">
                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="font-medium text-heading text-xs">
                                    {{ $notice->added_by ?: 'Administrator' }}
                                </span>
                            </div>
                        </td>

                        {{-- Actions Column --}}
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- View Details Button --}}
                                <button type="button" onclick="openViewNoticeModal({{ json_encode($notice) }})"
                                        class="p-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-2xs transition"
                                        title="View Notice Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>

                                {{-- Edit Button --}}
                                <button type="button" onclick="openEditNoticeModal({{ json_encode($notice) }})"
                                        class="p-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-2xs transition"
                                        title="Edit Notice">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>

                                {{-- Delete Button --}}
                                <form method="POST" action="{{ route('academic.noticeboards.destroy', $notice->id) }}"
                                      onsubmit="return confirm('Are you sure you want to delete notice: {{ addslashes($notice->title) }}?')"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-red-600 shadow-2xs transition"
                                            title="Delete Notice">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-gray-400">
                                    <i data-lucide="bell-off" class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-sm font-bold text-heading">No Notices Found</h4>
                                <p class="text-xs text-body mt-1">
                                    No notices have been published for session <strong>{{ $session->name }}</strong> yet.
                                </p>
                                <button type="button" onclick="openAddNoticeModal()"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-xs font-semibold rounded-lg hover:bg-primary-dark transition shadow-sm">
                                    <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5"></i>
                                    Create First Notice
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notices->hasPages())
        <div class="p-4 bg-gray-50/70 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-body">
                Showing <strong>{{ $notices->firstItem() }}</strong> to <strong>{{ $notices->lastItem() }}</strong> of <strong>{{ $notices->total() }}</strong> notices
            </span>
            <div class="notice-pagination">
                {{ $notices->links() }}
            </div>
        </div>
    @elseif($notices->total() > 0)
        <div class="p-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between text-xs text-body">
            <span>Showing all <strong>{{ $notices->total() }}</strong> notice(s) for session <strong>{{ $session->name }}</strong></span>
            <span class="text-gray-400">EdOrbit Academic Notice Board</span>
        </div>
    @endif
</div>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- 1. ADD NEW NOTICE MODAL DIALOG POPUP                              --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<div id="addNoticeModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden animate-scaleUp">
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-navy text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <i data-lucide="bell-ring" class="w-4 h-4 text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Add New Notice</h3>
                    <p class="text-xs text-white/70">Session: {{ $session->name }} &bull; {{ $school->name }}</p>
                </div>
            </div>
            <button type="button" onclick="closeAddNoticeModal()" class="text-white/70 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Modal Form Content --}}
        <form method="POST" action="{{ route('academic.noticeboards.store') }}" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-4">
            @csrf

            {{-- Notice Title (*) --}}
            <div>
                <label for="add_title" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                    Notice Title <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="title" id="add_title" required
                           placeholder="Enter notice title (e.g. Annual Sports Day 2026, Holiday Announcement)"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm text-heading placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
            </div>

            {{-- Class (*) & Class Section Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Class (*) --}}
                <div>
                    <label for="add_school_class_id" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Class <span class="text-red-500">*</span>
                    </label>
                    <select name="school_class_id" id="add_school_class_id" required
                            onchange="loadSections(this.value, 'add_section_id')"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <option value="">— Select Class —</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" data-sections="{{ json_encode($c->sections) }}">
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-body mt-0.5 block">Classes for session {{ $session->name }}</span>
                </div>

                {{-- Class Section --}}
                <div>
                    <label for="add_section_id" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Class Section
                    </label>
                    <select name="section_id" id="add_section_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <option value="">— All Sections —</option>
                    </select>
                    <span class="text-[11px] text-body mt-0.5 block">Select specific section or leave for all</span>
                </div>
            </div>

            {{-- Notice Description --}}
            <div>
                <label for="add_description" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                    Notice Description
                </label>
                <textarea name="description" id="add_description" rows="3"
                          placeholder="Write complete notice description, instructions, date/time guidelines..."
                          class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm text-heading placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
            </div>

            {{-- Link To (none, attachment, url) Dropdown --}}
            <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200/80 space-y-3.5">
                <div>
                    <label for="add_link_type" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Link To <span class="text-red-500">*</span>
                    </label>
                    <select name="link_type" id="add_link_type" required onchange="handleLinkTypeChange(this.value, 'add')"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <option value="none">None (Text Only Notice)</option>
                        <option value="attachment">Attachment (PDF, DOCX, Image, Excel...)</option>
                        <option value="url">URL (External Link / Webpage)</option>
                    </select>
                </div>

                {{-- Conditional Field: Attachment --}}
                <div id="add_attachment_wrapper" class="hidden animate-fadeIn">
                    <label for="add_attachment" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Upload Document / Attachment <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-navy transition bg-white">
                        <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-gray-400 mb-2"></i>
                        <input type="file" name="attachment" id="add_attachment"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.gif,.xls,.xlsx,.ppt,.pptx,.txt"
                               class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-navy file:text-white hover:file:opacity-90 cursor-pointer">
                        <p class="text-[11px] text-body mt-2">
                            Allowed formats: <strong>PDF, DOC, DOCX, JPG, PNG, WEBP, XLS, XLSX, TXT</strong> (Max 50MB)
                        </p>
                    </div>
                </div>

                {{-- Conditional Field: URL --}}
                <div id="add_url_wrapper" class="hidden animate-fadeIn">
                    <label for="add_link_url" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Website / Document URL <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </span>
                        <input type="url" name="link_url" id="add_link_url" placeholder="https://example.com/details-link"
                               class="w-full rounded-lg border border-gray-200 pl-9 pr-3.5 py-2.5 text-sm text-heading placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                    <span class="text-[11px] text-body mt-0.5 block">Enter full URL including https://</span>
                </div>
            </div>

            {{-- Status: approved/active, inactive --}}
            <div>
                <label class="block text-xs font-bold text-heading uppercase tracking-wider mb-2">
                    Notice Status <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
                        <input type="radio" name="status" value="active" checked class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-900 block">Approved / Active</span>
                            <span class="text-[11px] text-emerald-700">Visible on student & staff notice boards</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-gray-400 has-[:checked]:bg-gray-100">
                        <input type="radio" name="status" value="inactive" class="text-gray-600 focus:ring-gray-500">
                        <div>
                            <span class="text-xs font-bold text-gray-800 block">Inactive / Draft</span>
                            <span class="text-[11px] text-gray-500">Hidden until activated</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeAddNoticeModal()"
                        class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-md flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Publish Notice
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- 2. EDIT NOTICE MODAL DIALOG POPUP                                 --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<div id="editNoticeModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden animate-scaleUp">
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-navy text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <i data-lucide="edit" class="w-4 h-4 text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Edit Notice</h3>
                    <p class="text-xs text-white/70">Update notice details, class target or attachment</p>
                </div>
            </div>
            <button type="button" onclick="closeEditNoticeModal()" class="text-white/70 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Modal Form Content --}}
        <form id="editNoticeForm" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-4">
            @csrf
            @method('PUT')

            {{-- Notice Title (*) --}}
            <div>
                <label for="edit_title" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                    Notice Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="edit_title" required
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm text-heading placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
            </div>

            {{-- Class (*) & Class Section Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Class (*) --}}
                <div>
                    <label for="edit_school_class_id" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Class <span class="text-red-500">*</span>
                    </label>
                    <select name="school_class_id" id="edit_school_class_id" required
                            onchange="loadSections(this.value, 'edit_section_id')"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <option value="">— Select Class —</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Class Section --}}
                <div>
                    <label for="edit_section_id" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Class Section
                    </label>
                    <select name="section_id" id="edit_section_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <option value="">— All Sections —</option>
                    </select>
                </div>
            </div>

            {{-- Notice Description --}}
            <div>
                <label for="edit_description" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                    Notice Description
                </label>
                <textarea name="description" id="edit_description" rows="3"
                          class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm text-heading placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
            </div>

            {{-- Link To (none, attachment, url) Dropdown --}}
            <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200/80 space-y-3.5">
                <div>
                    <label for="edit_link_type" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Link To <span class="text-red-500">*</span>
                    </label>
                    <select name="link_type" id="edit_link_type" required onchange="handleLinkTypeChange(this.value, 'edit')"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <option value="none">None (Text Only Notice)</option>
                        <option value="attachment">Attachment (PDF, DOCX, Image, Excel...)</option>
                        <option value="url">URL (External Link / Webpage)</option>
                    </select>
                </div>

                {{-- Current Attachment Info --}}
                <div id="edit_current_attachment_info" class="hidden p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="paperclip" class="w-4 h-4 text-amber-600"></i>
                        <span>Current file: <strong id="edit_current_filename">file.pdf</strong></span>
                    </div>
                    <span class="text-[11px] text-amber-600">Will be kept unless new file uploaded</span>
                </div>

                {{-- Conditional Field: Attachment --}}
                <div id="edit_attachment_wrapper" class="hidden animate-fadeIn">
                    <label for="edit_attachment" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Upload New Document (Leave blank to keep current)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-3 text-center bg-white">
                        <input type="file" name="attachment" id="edit_attachment"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.gif,.xls,.xlsx,.ppt,.pptx,.txt"
                               class="w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-navy file:text-white hover:file:opacity-90 cursor-pointer">
                    </div>
                </div>

                {{-- Conditional Field: URL --}}
                <div id="edit_url_wrapper" class="hidden animate-fadeIn">
                    <label for="edit_link_url" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1">
                        Website / Document URL
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </span>
                        <input type="url" name="link_url" id="edit_link_url" placeholder="https://example.com/details-link"
                               class="w-full rounded-lg border border-gray-200 pl-9 pr-3.5 py-2.5 text-sm text-heading placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>
            </div>

            {{-- Status: approved/active, inactive --}}
            <div>
                <label class="block text-xs font-bold text-heading uppercase tracking-wider mb-2">
                    Notice Status <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
                        <input type="radio" name="status" id="edit_status_active" value="active" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-900 block">Approved / Active</span>
                            <span class="text-[11px] text-emerald-700">Published</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-gray-400 has-[:checked]:bg-gray-100">
                        <input type="radio" name="status" id="edit_status_inactive" value="inactive" class="text-gray-600 focus:ring-gray-500">
                        <div>
                            <span class="text-xs font-bold text-gray-800 block">Inactive / Draft</span>
                            <span class="text-[11px] text-gray-500">Hidden</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditNoticeModal()"
                        class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-md flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update Notice
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- 3. VIEW NOTICE DETAILS MODAL DIALOG POPUP                         --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<div id="viewNoticeModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[92vh] flex flex-col overflow-hidden animate-scaleUp">
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-navy text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <i data-lucide="eye" class="w-4 h-4 text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Notice Details</h3>
                    <p class="text-xs text-white/70">Session: {{ $session->name }}</p>
                </div>
            </div>
            <button type="button" onclick="closeViewNoticeModal()" class="text-white/70 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Content --}}
        <div class="p-6 overflow-y-auto space-y-4">
            <div class="flex items-start justify-between gap-3">
                <h2 id="view_modal_title" class="text-lg font-bold text-heading"></h2>
                <span id="view_modal_status_badge"></span>
            </div>

            {{-- Metadata Badges --}}
            <div class="flex flex-wrap items-center gap-2 text-xs py-2 border-y border-gray-100">
                <span class="inline-flex items-center gap-1 text-body">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-navy"></i>
                    Class: <strong id="view_modal_class" class="text-heading"></strong>
                </span>
                <span class="text-gray-300">&bull;</span>
                <span class="inline-flex items-center gap-1 text-body">
                    <i data-lucide="columns-3" class="w-3.5 h-3.5 text-navy"></i>
                    Section: <strong id="view_modal_section" class="text-heading"></strong>
                </span>
                <span class="text-gray-300">&bull;</span>
                <span class="inline-flex items-center gap-1 text-body">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-navy"></i>
                    Added By: <strong id="view_modal_added_by" class="text-heading"></strong>
                </span>
                <span class="text-gray-300">&bull;</span>
                <span class="inline-flex items-center gap-1 text-body">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-navy"></i>
                    Date: <strong id="view_modal_date" class="text-heading"></strong>
                </span>
            </div>

            {{-- Description --}}
            <div>
                <h4 class="text-xs font-bold text-heading uppercase tracking-wider mb-1.5">Description / Content</h4>
                <div id="view_modal_description" class="p-4 rounded-xl bg-gray-50 border border-gray-100 text-sm text-heading leading-relaxed whitespace-pre-wrap"></div>
            </div>

            {{-- Link / Attachment Area --}}
            <div id="view_modal_link_area" class="pt-2"></div>
        </div>

        {{-- Footer --}}
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
            <button type="button" onclick="closeViewNoticeModal()"
                    class="px-4 py-2 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- 4. BULK IMPORT NOTICES MODAL DIALOG POPUP                          --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<div id="importNoticeModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden animate-scaleUp">
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-navy text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-emerald-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Bulk Import Notices</h3>
                    <p class="text-xs text-white/70">Upload notices from CSV spreadsheet</p>
                </div>
            </div>
            <button type="button" onclick="closeImportNoticeModal()" class="text-white/70 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Form Content --}}
        <form method="POST" action="{{ route('academic.noticeboards.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            {{-- Step 1: Download Sample CSV Banner --}}
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold flex-shrink-0">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-amber-900 leading-tight">Need the Sample Template?</h4>
                        <p class="text-[11px] text-amber-700">Download formatted sample .csv with instructions</p>
                    </div>
                </div>
                <a href="{{ route('academic.noticeboards.sample-csv') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-sm flex-shrink-0">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Sample .CSV
                </a>
            </div>

            {{-- Column Format Guidelines --}}
            <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200 text-xs text-body space-y-1.5">
                <p class="font-bold text-heading text-xs">Required & Optional CSV Columns:</p>
                <ul class="list-disc pl-4 space-y-0.5 text-[11.5px]">
                    <li><strong class="text-heading">Notice Title</strong> <span class="text-red-500 font-semibold">*Required</span></li>
                    <li><strong class="text-heading">Class Name</strong> (e.g. <code>Class 1</code>, leave blank for All Classes)</li>
                    <li><strong class="text-heading">Section Name</strong> (e.g. <code>A</code>, optional)</li>
                    <li><strong class="text-heading">Description</strong> (Optional message content)</li>
                    <li><strong class="text-heading">Link Type</strong> (<code>none</code>, <code>attachment</code>, <code>url</code>)</li>
                    <li><strong class="text-heading">Link URL or Attachment URL</strong> (Optional external URL or document link)</li>
                    <li><strong class="text-heading">Status</strong> (<code>active</code> or <code>inactive</code>)</li>
                </ul>
            </div>

            {{-- Step 2: File Upload --}}
            <div>
                <label for="notice_csv_file" class="block text-xs font-bold text-heading uppercase tracking-wider mb-1.5">
                    Select CSV File <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-navy transition bg-white">
                    <i data-lucide="upload" class="w-7 h-7 mx-auto text-gray-400 mb-1.5"></i>
                    <input type="file" name="csv_file" id="notice_csv_file" required accept=".csv,.txt"
                           class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-navy file:text-white hover:file:opacity-90 cursor-pointer">
                    <p class="text-[11px] text-body mt-2">
                        Supported format: <strong>.CSV</strong> (Max 5MB)
                    </p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeImportNoticeModal()"
                        class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-md flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT HANDLERS                                               --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<script>
    // Class Sections Cache
    const classesData = @json($classes);

    function loadSections(classId, selectElementId, selectedSectionId = null) {
        const select = document.getElementById(selectElementId);
        if (!select) return;
        select.innerHTML = '<option value="">— All Sections —</option>';
        if (!classId) return;

        // First check in embedded classesData
        const found = classesData.find(c => c.id == classId);
        if (found && found.sections && found.sections.length > 0) {
            found.sections.forEach(sec => {
                const opt = document.createElement('option');
                opt.value = sec.id;
                opt.textContent = sec.name;
                if (selectedSectionId == sec.id) opt.selected = true;
                select.appendChild(opt);
            });
            return;
        }

        // AJAX Fallback
        fetch('/academic/noticeboards/sections/' + classId)
            .then(res => res.json())
            .then(sections => {
                sections.forEach(sec => {
                    const opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = sec.name;
                    if (selectedSectionId == sec.id) opt.selected = true;
                    select.appendChild(opt);
                });
            })
            .catch(err => console.error('Error loading sections:', err));
    }

    // Modal Control: Add Notice
    function openAddNoticeModal() {
        document.getElementById('addNoticeModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeAddNoticeModal() {
        document.getElementById('addNoticeModal').classList.add('hidden');
    }

    // Modal Control: Edit Notice
    function openEditNoticeModal(notice) {
        document.getElementById('editNoticeForm').action = '/academic/noticeboards/' + notice.id;
        document.getElementById('edit_title').value = notice.title || '';
        document.getElementById('edit_description').value = notice.description || '';
        document.getElementById('edit_school_class_id').value = notice.school_class_id || '';

        loadSections(notice.school_class_id, 'edit_section_id', notice.section_id);

        document.getElementById('edit_link_type').value = notice.link_type || 'none';
        handleLinkTypeChange(notice.link_type || 'none', 'edit');

        if (notice.link_type === 'url') {
            document.getElementById('edit_link_url').value = notice.link_url || '';
        } else {
            document.getElementById('edit_link_url').value = '';
        }

        const currAttInfo = document.getElementById('edit_current_attachment_info');
        if (notice.link_type === 'attachment' && notice.attachment_original_name) {
            document.getElementById('edit_current_filename').textContent = notice.attachment_original_name + (notice.attachment_size ? ' (' + notice.attachment_size + ')' : '');
            currAttInfo.classList.remove('hidden');
        } else {
            currAttInfo.classList.add('hidden');
        }

        if (notice.status === 'inactive' || !notice.is_active) {
            document.getElementById('edit_status_inactive').checked = true;
        } else {
            document.getElementById('edit_status_active').checked = true;
        }

        document.getElementById('editNoticeModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeEditNoticeModal() {
        document.getElementById('editNoticeModal').classList.add('hidden');
    }

    // Modal Control: View Notice Details
    function openViewNoticeModal(notice) {
        document.getElementById('view_modal_title').textContent = notice.title;
        document.getElementById('view_modal_class').textContent = notice.school_class ? notice.school_class.name : 'All Classes';
        document.getElementById('view_modal_section').textContent = notice.section ? notice.section.name : 'All Sections';
        document.getElementById('view_modal_added_by').textContent = notice.added_by || 'Admin';

        // Format Date
        let dateStr = notice.created_at;
        try {
            const d = new Date(notice.created_at);
            dateStr = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        } catch(e) {}
        document.getElementById('view_modal_date').textContent = dateStr;

        // Status badge
        const badgeContainer = document.getElementById('view_modal_status_badge');
        if (notice.is_active) {
            badgeContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Approved / Active</span>';
        } else {
            badgeContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Inactive</span>';
        }

        // Description
        document.getElementById('view_modal_description').textContent = notice.description || 'No description provided.';

        // Link / Attachment preview
        const linkArea = document.getElementById('view_modal_link_area');
        linkArea.innerHTML = '';

        if (notice.link_type === 'attachment' && notice.attachment_path) {
            linkArea.innerHTML = `
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-900 leading-tight">${notice.attachment_original_name || 'Attached File'}</p>
                            <p class="text-[11px] text-amber-700">${notice.attachment_size || 'Document'}</p>
                        </div>
                    </div>
                    <a href="/academic/noticeboards/${notice.id}/download" target="_blank"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Download / View
                    </a>
                </div>
            `;
        } else if (notice.link_type === 'url' && notice.link_url) {
            linkArea.innerHTML = `
                <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </div>
                        <div class="max-w-xs truncate">
                            <p class="text-xs font-bold text-purple-900 leading-tight">External Link</p>
                            <p class="text-[11px] text-purple-700 truncate">${notice.link_url}</p>
                        </div>
                    </div>
                    <a href="${notice.link_url}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 transition shadow-sm">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        Open Link
                    </a>
                </div>
            `;
        }

        document.getElementById('viewNoticeModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeViewNoticeModal() {
        document.getElementById('viewNoticeModal').classList.add('hidden');
    }

    // Modal Control: Bulk Import Notices
    function openImportNoticeModal() {
        document.getElementById('importNoticeModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeImportNoticeModal() {
        document.getElementById('importNoticeModal').classList.add('hidden');
    }

    // Dynamic Link To fields toggle
    function handleLinkTypeChange(type, prefix = 'add') {
        const attWrapper = document.getElementById(prefix + '_attachment_wrapper');
        const urlWrapper = document.getElementById(prefix + '_url_wrapper');
        const fileInput  = document.getElementById(prefix + '_attachment');
        const urlInput   = document.getElementById(prefix + '_link_url');

        if (type === 'attachment') {
            attWrapper.classList.remove('hidden');
            urlWrapper.classList.add('hidden');
            if (prefix === 'add' && fileInput) fileInput.required = true;
            if (urlInput) urlInput.required = false;
        } else if (type === 'url') {
            urlWrapper.classList.remove('hidden');
            attWrapper.classList.add('hidden');
            if (urlInput) urlInput.required = true;
            if (fileInput) fileInput.required = false;
        } else {
            attWrapper.classList.add('hidden');
            urlWrapper.classList.add('hidden');
            if (fileInput) fileInput.required = false;
            if (urlInput) urlInput.required = false;
        }
        lucide.createIcons();
    }

    // Bulk selection handlers
    function toggleSelectAll(cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = cb.checked;
            r.closest('tr').classList.toggle('bg-red-50/50', cb.checked);
        });
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const all     = document.querySelectorAll('.row-checkbox');
        const toolbar = document.getElementById('bulkToolbar');
        const countEl = document.getElementById('selectedCount');
        const sa      = document.getElementById('selectAll');

        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.closest('tr').classList.toggle('bg-red-50/50', r.checked);
        });

        if (checked.length > 0) {
            if (toolbar) toolbar.classList.remove('hidden');
            if (countEl) countEl.textContent = checked.length + ' notice(s) selected';
        } else {
            if (toolbar) toolbar.classList.add('hidden');
        }

        if (sa) {
            sa.indeterminate = checked.length > 0 && checked.length < all.length;
            sa.checked = all.length > 0 && checked.length === all.length;
        }
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = false;
            r.closest('tr').classList.remove('bg-red-50/50');
        });
        const sa = document.getElementById('selectAll');
        if (sa) { sa.checked = false; sa.indeterminate = false; }
        const toolbar = document.getElementById('bulkToolbar');
        if (toolbar) toolbar.classList.add('hidden');
    }

    function confirmBulkDelete(e) {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm('Are you sure you want to permanently delete ' + checked.length + ' selected notice(s)?')) {
            e.preventDefault();
            return false;
        }
        const c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'selected_ids[]';
            inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    // Auto-open Add modal or Import modal if specified in query string
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('open_add') === '1') {
            openAddNoticeModal();
        } else if (urlParams.get('open_import') === '1') {
            openImportNoticeModal();
        }
    });
</script>

<style>
    @keyframes scaleUp {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-scaleUp {
        animation: scaleUp 0.18s ease-out forwards;
    }
</style>

@endsection
