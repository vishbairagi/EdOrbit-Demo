@extends('layouts.app')
@section('header', 'Homework')
@section('content')

    <style>
        .print-only {
            display: none;
        }

        @media print {
            /* Force exact background and color printing across all browsers */
            *, *::before, *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            @page {
                margin: 12mm 10mm;
                size: landscape;
            }

            /* Reset body and page wrappers to prevent clipping and enable multi-page table print */
            html, body {
                background: #ffffff !important;
                color: #111827 !important;
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                font-size: 11px !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            /* Hide all navigation, sidebars, headers, cards, buttons, filter bars and modals */
            aside#sidebar,
            #resizer,
            header,
            .no-print,
            .col-actions,
            #bulkToolbar,
            #createModal,
            #editModal,
            #viewModal,
            #importModal {
                display: none !important;
            }

            main {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            main > div.flex-1 {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
            }

            .w-full {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .homework-table-card {
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
                width: 100% !important;
            }

            /* Crisp, clean table styling for print */
            .homework-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 4px !important;
                font-size: 11px !important;
            }

            .homework-table thead tr {
                background-color: #B47A42 !important;
                color: #ffffff !important;
            }

            .homework-table thead th {
                background-color: #B47A42 !important;
                color: #ffffff !important;
                border: 1px solid #9a6633 !important;
                padding: 6px 8px !important;
                font-size: 10px !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
            }

            .homework-table tbody td {
                border: 1px solid #d1d5db !important;
                padding: 6px 8px !important;
                color: #1f2937 !important;
                vertical-align: middle !important;
                background: #ffffff !important;
            }

            .homework-table tbody tr:nth-child(even) td {
                background-color: #fcfaf8 !important;
            }

            .homework-table tbody tr {
                page-break-inside: avoid !important;
            }

            .homework-table button {
                background: none !important;
                border: none !important;
                padding: 0 !important;
                color: #111827 !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                text-align: left !important;
                cursor: default !important;
                box-shadow: none !important;
            }

            .homework-table a {
                text-decoration: none !important;
                color: inherit !important;
            }

            a[href]::after {
                content: none !important;
            }

            .col-checkbox {
                width: 34px !important;
                text-align: center !important;
            }

            /* Print-only elements */
            .print-only {
                display: block !important;
            }

            span.print-only {
                display: inline-block !important;
            }

            /* When bulk printing selected rows only */
            body.print-selected-mode .homework-table tbody tr:not(.is-selected-row) {
                display: none !important;
            }
        }
    </style>

    <div class="w-full">
        {{-- Top Header Section --}}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2 no-print">
            <div>
                <h3 class="text-base font-bold text-heading">Homework</h3>
                <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp; Total Homework: {{ $homeworks->total() }} &nbsp;·&nbsp; Active: {{ $activeHomeworkCount }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('academic.homework.export', request()->all()) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                    <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Export CSV
                </a>
                <button type="button" onclick="openImportModal()" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                    <i data-lucide="upload" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Import CSV
                </button>
                <a href="{{ route('academic.homework.sample-csv') }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Sample CSV
                </a>
                <button type="button" onclick="printHomeworkTable()" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Print
                </button>
                <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition shadow-2xs gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Create Homework
                </button>
            </div>
        </div>

        {{-- Flash Notifications --}}
        @if(session('success'))
            <div class="mb-5 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs no-print"
                 style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-800 hover:text-emerald-950">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs bg-red-50 text-red-700 border border-red-200 no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-950">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 rounded-xl text-xs bg-red-50 text-red-700 border border-red-200 shadow-2xs no-print">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Please check form errors:
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif



        {{-- ══ Full-Width Homeworks Table Card ══ --}}
        <div class="w-full">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden homework-table-card">

                {{-- Compact Single-Row Search & Filter Toolbar --}}
                <div class="px-3.5 py-2.5 border-b border-gray-200 bg-gray-50/40 no-print">
                    <form method="GET" action="{{ route('academic.homework.index') }}" class="flex flex-wrap items-center gap-2">
                        {{-- Search Input (Compact) --}}
                        <div class="relative min-w-[200px] flex-1 max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, subject, teacher..."
                                   class="w-full rounded-lg border border-gray-300 text-[11px] pl-7 pr-6 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                            @if(request('search'))
                                <a href="{{ route('academic.homework.index', request()->except('search', 'page')) }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </a>
                            @endif
                        </div>

                        {{-- Class Filter --}}
                        <select name="class_id" id="filter_class_id" onchange="handleClassChange(this.value, 'filter'); this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2.5 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="">All Classes</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>

                        {{-- Section Filter --}}
                        <select name="section_id" id="filter_section_id" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2.5 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="">All Sections</option>
                        </select>

                        {{-- Subject Filter --}}
                        <select name="subject_id" id="filter_subject_id" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2.5 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="">All Subjects</option>
                        </select>

                        {{-- Status Filter --}}
                        <select name="status" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2.5 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active / Due Soon</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Past Due</option>
                        </select>

                        {{-- Per Page --}}
                        <select name="per_page" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                        </select>

                        {{-- Filter Button --}}
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-white text-[11px] font-semibold bg-navy hover:bg-primary-dark transition shadow-2xs">
                            <i data-lucide="filter" class="w-3 h-3"></i>
                            Filter
                        </button>

                        {{-- Reset Button --}}
                        @if(request('class_id') || request('section_id') || request('subject_id') || request('status') || request('search') || (request('per_page') && request('per_page') != 10))
                            <a href="{{ route('academic.homework.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-gray-300 text-[11px] font-medium hover:bg-gray-100 text-red-600 transition shadow-2xs" title="Reset Filters">
                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                Reset
                            </a>
                        @endif

                        {{-- Create New Homework Button (Right-aligned in toolbar) --}}
                        <button type="button" onclick="openCreateModal()" class="ml-auto inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-white text-[11px] font-bold bg-navy hover:bg-primary-dark transition shadow-2xs shrink-0">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            Create Homework
                        </button>
                    </form>
                </div>

                {{-- Bulk Actions Toolbar (Delete, Export, Print) --}}
                <div id="bulkToolbar" class="hidden flex flex-wrap items-center justify-between gap-3 bg-red-50/90 border-b border-red-200 px-4 py-2.5 no-print">
                    <div class="flex items-center gap-2">
                        <span id="selectedCount" class="text-xs font-bold text-red-700">0 selected</span>
                        <button type="button" onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="bulkExportSelected()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-semibold rounded-lg shadow-2xs transition-colors">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Selected
                        </button>

                        <button type="button" onclick="bulkPrintSelected()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-semibold rounded-lg shadow-2xs transition-colors">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Selected
                        </button>

                        <form id="bulkDeleteForm" method="POST" action="{{ route('academic.homework.bulk-delete') }}" onsubmit="return confirmBulkDelete()">
                            @csrf
                            <div id="bulkIdsContainer"></div>
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-2xs">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Print Only Header --}}
                <div class="print-only mb-3 px-1">
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; border-bottom:2px solid #B47A42; padding-bottom:8px; margin-bottom:10px;">
                        <div>
                            <h2 style="font-size:17px; font-weight:700; color:#211B17; margin:0;">{{ $school->name ?? 'EdOrbit' }}</h2>
                            <p style="font-size:12px; font-weight:600; color:#B47A42; margin:2px 0 0 0;">Academic Homework List</p>
                        </div>
                        <div style="text-align:right; font-size:10px; color:#555;">
                            <div><strong>Session:</strong> {{ $session->name ?? 'Current Session' }}</div>
                            <div><strong>Printed On:</strong> {{ date('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Homework Table (Full Width, Compact, Crisp Typography) --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse homework-table">
                        <thead>
                            <tr class="bg-navy text-white text-[11px] uppercase tracking-wider font-semibold">
                                <th class="px-3 py-2.5 w-9 text-center col-checkbox">
                                    <span class="no-print">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                               class="w-3.5 h-3.5 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                                    </span>
                                    <span class="print-only font-bold">#</span>
                                </th>
                                <th class="px-3 py-2.5">Homework Title</th>
                                <th class="px-3 py-2.5 whitespace-nowrap">Class / Section</th>
                                <th class="px-3 py-2.5 whitespace-nowrap">Schedule</th>
                                <th class="px-3 py-2.5 whitespace-nowrap">Added By</th>
                                <th class="px-3 py-2.5 text-right whitespace-nowrap col-actions no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-xs">
                            @forelse($homeworks as $hw)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    {{-- Row Checkbox / S.No in print --}}
                                    <td class="px-3 py-2 text-center align-middle col-checkbox">
                                        <span class="no-print">
                                            <input type="checkbox" value="{{ $hw->id }}" class="row-checkbox w-3.5 h-3.5 rounded border-gray-300 cursor-pointer accent-red-600" onchange="updateBulkToolbar()">
                                        </span>
                                        <span class="print-only font-semibold text-gray-700">{{ $loop->iteration }}</span>
                                    </td>

                                    {{-- Homework Title & Subject Tag --}}
                                    <td class="px-3 py-2 align-middle">
                                        <div class="max-w-xl">
                                            <button type="button" onclick="openViewModal({{ $hw->id }})" class="font-semibold text-xs text-heading hover:text-navy text-left block transition-colors leading-snug" title="Click to view full details">
                                                {{ $hw->title }}
                                            </button>
                                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                @if($hw->subject)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[10px] font-medium" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                                                        <i data-lucide="book" class="w-2.5 h-2.5"></i>
                                                        {{ $hw->subject->name }}
                                                    </span>
                                                @endif
                                                @if($hw->attachments_count > 0)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200" title="{{ $hw->attachments_count }} attached file(s)">
                                                        <i data-lucide="paperclip" class="w-2.5 h-2.5"></i>
                                                        {{ $hw->attachments_count }}
                                                    </span>
                                                @endif
                                                @if($hw->attachment_url)
                                                    <a href="{{ $hw->attachment_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-0.5 text-[10px] text-indigo-600 hover:text-indigo-800" title="External Attachment Link">
                                                        <i data-lucide="external-link" class="w-2.5 h-2.5"></i> Link
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Class & Section --}}
                                    <td class="px-3 py-2 whitespace-nowrap align-middle">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium" style="background:#f5ece0; color:#211B17; border: 1px solid #DED5CB;">
                                            <span>{{ $hw->schoolClass?->name ?? 'N/A' }}</span>
                                            <span class="text-stone-400">&bull;</span>
                                            <span class="text-stone-600">{{ $hw->section?->name ?? 'All Sections' }}</span>
                                        </span>
                                    </td>

                                    {{-- Schedule (Due Date & Status + Assigned Date) --}}
                                    <td class="px-3 py-2 whitespace-nowrap align-middle">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-xs text-heading">
                                                {{ $hw->due_date ? $hw->due_date->format('M d, Y') : '-' }}
                                            </span>
                                            @if($hw->due_date)
                                                @if($hw->is_overdue)
                                                    <span class="inline-flex items-center gap-0.5 text-[9px] font-bold text-red-600 bg-red-50 px-1.5 py-0.2 rounded-full border border-red-200">
                                                        <i data-lucide="clock" class="w-2.5 h-2.5"></i> Past Due
                                                    </span>
                                                @elseif($hw->days_remaining === 0)
                                                    <span class="inline-flex items-center gap-0.5 text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded-full border border-amber-200">
                                                        <i data-lucide="alert-circle" class="w-2.5 h-2.5"></i> Due Today
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded-full border border-emerald-200">
                                                        <i data-lucide="calendar" class="w-2.5 h-2.5"></i> {{ $hw->days_remaining }}d left
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            Assigned: {{ $hw->homework_date ? $hw->homework_date->format('M d, Y') : '-' }}
                                        </div>
                                    </td>

                                    {{-- Added By --}}
                                    <td class="px-3 py-2 whitespace-nowrap align-middle">
                                        <span class="font-medium text-xs text-heading truncate max-w-[130px]">{{ $hw->added_by ?: 'Teacher' }}</span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-3 py-2 text-right whitespace-nowrap align-middle col-actions no-print">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- View Details Modal Button --}}
                                            <button type="button" onclick="openViewModal({{ $hw->id }})" class="p-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-2xs transition-colors" title="View Full Details">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            </button>

                                            {{-- Edit Button --}}
                                            <button type="button" onclick="openEditModal({{ $hw->id }})" class="p-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-2xs transition-colors" title="Edit">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            </button>

                                            {{-- Delete Button --}}
                                            <form method="POST" action="{{ route('academic.homework.destroy', $hw) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this homework? Attached files will also be removed.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-red-600 shadow-2xs transition-colors" title="Delete">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center py-14 text-xs text-gray-500">
                                        <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                                        <p class="font-bold text-gray-700 text-sm">No homework assignments found</p>
                                        <p class="text-3xs text-gray-400 mt-1">Click the "+ Create New Homework" button above or import via CSV to add assignments.</p>
                                        <button type="button" onclick="openCreateModal()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-navy hover:bg-primary-dark text-white rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                            Create First Homework
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Pagination Footer --}}
                @if($homeworks->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 bg-white no-print">
                        {{ $homeworks->links() }}
                    </div>
                @else
                    <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 no-print">
                        <span>Showing <strong class="text-gray-700 font-semibold">{{ $homeworks->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $homeworks->total() }}</strong> assignments</span>
                        <span class="text-gray-400">Page 1 of 1</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── CREATE HOMEWORK 2-STEP MODAL DIALOG ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="createModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-3 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden my-4 border border-gray-100">
            {{-- Modal Header --}}
            <div class="px-5 py-3 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-heading">Create New Homework</h3>
                        <p class="text-[10px] text-gray-400">Step-by-step homework assignment</p>
                    </div>
                </div>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- 2-Step Navigation Tabs --}}
            <div class="flex border-b border-gray-200 bg-white">
                <button type="button" id="createTab1" onclick="switchCreateStep(1)" class="flex-1 py-2 px-3 text-center border-b-2 border-navy text-navy font-bold text-xs flex items-center justify-center gap-1.5 bg-amber-50/30 transition-colors">
                    <span class="w-4 h-4 rounded-full bg-navy text-white text-[10px] flex items-center justify-center font-bold">1</span>
                    <span>Basic Details</span>
                </button>
                <button type="button" id="createTab2" onclick="switchCreateStep(2)" class="flex-1 py-2 px-3 text-center border-b-2 border-transparent text-gray-400 hover:text-gray-600 font-medium text-xs flex items-center justify-center gap-1.5 transition-colors">
                    <span class="w-4 h-4 rounded-full bg-gray-200 text-gray-600 text-[10px] flex items-center justify-center font-bold">2</span>
                    <span>Instructions &amp; Files</span>
                </button>
            </div>

            <form method="POST" action="{{ route('academic.homework.store') }}" id="createHomeworkForm" enctype="multipart/form-data" class="p-5">
                @csrf

                {{-- ══ STEP 1: Basic Assignment Information ══ --}}
                <div id="createStep1" class="space-y-3">
                    {{-- Title --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Homework Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="create_title" required
                               value="{{ old('title') }}"
                               placeholder="e.g. Chapter 4: Quadratic Equations Exercise 4.2"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                    </div>

                    {{-- Class Selection & Class Section (2 Columns) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select name="school_class_id" id="create_class_id" required onchange="handleClassChange(this.value, 'create')"
                                    class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                                <option value="">-- Choose Class --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ old('school_class_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Class Section
                            </label>
                            <select name="section_id" id="create_section_id"
                                    class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                                <option value="">All Sections</option>
                            </select>
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Optional (all sections by default)</span>
                        </div>
                    </div>

                    {{-- Subject & Due Date (2 Columns) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <select name="subject_id" id="create_subject_id" required
                                    class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                                <option value="">-- Choose Subject --</option>
                            </select>
                            <span class="text-[10px] text-gray-400 mt-0.5 block" id="create_subject_hint">Select class first</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="due_date" id="create_due_date" required
                                   value="{{ old('due_date', date('Y-m-d', strtotime('+3 days'))) }}"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                        </div>
                    </div>

                    {{-- Assigned Date --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Assigned Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="homework_date" id="create_homework_date" required
                               value="{{ old('homework_date', date('Y-m-d')) }}"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                    </div>

                    {{-- Step 1 Footer Buttons --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-4">
                        <button type="button" onclick="closeCreateModal()" class="px-3 py-1.5 text-xs font-semibold border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="action" value="save" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 transition-colors" title="Save basic homework without instructions or files">
                                Quick Save
                            </button>
                            <button type="button" onclick="switchCreateStep(2)" class="px-3.5 py-1.5 text-xs font-bold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs flex items-center gap-1.5">
                                Next: Instructions &amp; Files
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══ STEP 2: Instructions, Files & External Link ══ --}}
                <div id="createStep2" class="space-y-3 hidden">
                    <div class="p-2.5 rounded-lg bg-amber-50/60 border border-amber-200/80 text-[11px] text-amber-900 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-navy shrink-0"></i>
                        <span>Step 2: Add homework description and upload worksheets or links (optional).</span>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Description / Instructions
                        </label>
                        <textarea name="description" id="create_description" rows="3"
                                  placeholder="Type homework instructions, exercises, textbook page numbers, or questions..."
                                  class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">{{ old('description') }}</textarea>
                    </div>

                    {{-- Homework File Attachments (Compact Dropzone) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-gray-700">
                                Attachments
                            </label>
                            <span class="text-[10px] text-gray-400">Images, PDFs, Videos, Word, Excel</span>
                        </div>

                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-2.5 text-center bg-gray-50/70 hover:bg-gray-50 transition-colors relative cursor-pointer" id="dropzone_container">
                            <input type="file" name="attachments[]" id="create_attachments" multiple
                                   accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.zip,.rar"
                                   onchange="renderSelectedFiles(this, 'create_file_list')"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <div class="flex items-center justify-center gap-2 py-1">
                                <div class="w-6 h-6 rounded-full bg-amber-50 text-navy flex items-center justify-center border border-amber-200 shrink-0">
                                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5 text-navy"></i>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs font-semibold text-heading">
                                        Click or drag files to attach
                                    </p>
                                    <p class="text-[10px] text-gray-400">
                                        Multi-format files supported (Max 100MB per file)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div id="create_file_list" class="mt-2 space-y-1 max-h-28 overflow-y-auto"></div>
                    </div>

                    {{-- Attachment URL (Optional) --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Attachment URL <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <input type="url" name="attachment_url" id="create_attachment_url"
                                   value="{{ old('attachment_url') }}"
                                   placeholder="https://drive.google.com/... or YouTube link"
                                   class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading shadow-2xs">
                        </div>
                    </div>

                    {{-- Step 2 Footer Buttons --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-4">
                        <button type="button" onclick="switchCreateStep(1)" class="px-3 py-1.5 text-xs font-semibold border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-1">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                            Back
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="action" value="save_and_next" class="px-3.5 py-1.5 text-xs font-bold rounded-lg text-white bg-amber-700 hover:bg-amber-800 transition-colors shadow-2xs flex items-center gap-1.5" title="Save this homework and immediately create the next one for this class">
                                <i data-lucide="fast-forward" class="w-3.5 h-3.5"></i>
                                Save &amp; Next
                            </button>
                            <button type="submit" name="action" value="save" class="px-4 py-1.5 text-xs font-bold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Save Homework
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── IMPORT HOMEWORK CSV MODAL ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden my-6">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-sm font-bold text-heading flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4 text-navy"></i>
                    Import Homework from CSV
                </h3>
                <button type="button" onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('academic.homework.import') }}" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Select CSV File <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required
                           class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 border border-gray-300 rounded-lg p-1 bg-white">
                    <p class="text-3xs text-gray-400 mt-1">Accepts standard .csv file up to 5MB.</p>
                </div>

                <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl space-y-1.5 text-xs text-amber-900">
                    <div class="font-bold flex items-center gap-1.5 text-2xs text-amber-800 uppercase tracking-wider">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> CSV File Format
                    </div>
                    <p class="text-2xs text-amber-800 leading-relaxed">
                        CSV header must include: <code class="bg-white px-1 py-0.5 rounded border border-amber-200">title</code>, <code class="bg-white px-1 py-0.5 rounded border border-amber-200">class</code>, <code class="bg-white px-1 py-0.5 rounded border border-amber-200">subject</code>, <code class="bg-white px-1 py-0.5 rounded border border-amber-200">homework_date</code>, <code class="bg-white px-1 py-0.5 rounded border border-amber-200">due_date</code>. Optional: <code class="bg-white px-1 py-0.5 rounded border border-amber-200">section</code>, <code class="bg-white px-1 py-0.5 rounded border border-amber-200">description</code>, <code class="bg-white px-1 py-0.5 rounded border border-amber-200">attachment_url</code>.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('academic.homework.sample-csv') }}" class="inline-flex items-center gap-1 text-2xs font-bold text-navy hover:underline">
                            <i data-lucide="download" class="w-3 h-3"></i> Download Sample CSV Template
                        </a>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-navy hover:bg-primary-dark transition shadow-2xs">
                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                        Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── VIEW HOMEWORK DETAILS MODAL ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="viewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-heading" id="view_modal_title">Homework Details</h3>
                        <p class="text-3xs text-gray-400" id="view_modal_meta"></p>
                    </div>
                </div>
                <button type="button" onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                {{-- Badges row --}}
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full font-semibold" style="background:#f5ece0; color:#211B17; border: 1px solid #DED5CB;" id="view_class_sec_badge"></span>
                    <span class="px-2.5 py-1 rounded-full font-semibold bg-blue-50 text-blue-700 border border-blue-200" id="view_subject_badge"></span>
                    <span class="px-2.5 py-1 rounded-full font-semibold" id="view_due_status_badge"></span>
                </div>

                {{-- Dates and Creator Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <div>
                        <span class="text-3xs uppercase font-semibold text-gray-400 block">Assigned Date</span>
                        <strong class="text-gray-800" id="view_date_text">-</strong>
                    </div>
                    <div>
                        <span class="text-3xs uppercase font-semibold text-gray-400 block">Due Date</span>
                        <strong class="text-gray-800" id="view_due_date_text">-</strong>
                    </div>
                    <div>
                        <span class="text-3xs uppercase font-semibold text-gray-400 block">Added By</span>
                        <strong class="text-gray-800" id="view_added_by_text">-</strong>
                    </div>
                </div>

                {{-- Description Box --}}
                <div>
                    <h5 class="text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="align-left" class="w-3.5 h-3.5 text-navy"></i> Instructions &amp; Description
                    </h5>
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-700 whitespace-pre-wrap leading-relaxed" id="view_description_box"></div>
                </div>

                {{-- External Link Box if any --}}
                <div id="view_url_section" class="hidden">
                    <h5 class="text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="link" class="w-3.5 h-3.5 text-navy"></i> Attachment URL
                    </h5>
                    <a href="#" id="view_url_link" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition-colors break-all">
                        <i data-lucide="external-link" class="w-3.5 h-3.5 shrink-0"></i>
                        <span id="view_url_text"></span>
                    </a>
                </div>

                {{-- Attached Files List --}}
                <div>
                    <h5 class="text-xs font-bold text-gray-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5 text-navy"></i> Attached Files
                        </span>
                        <span class="text-3xs text-gray-400" id="view_files_count_label"></span>
                    </h5>
                    <div id="view_attachments_container" class="space-y-2"></div>
                </div>
            </div>

            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button type="button" onclick="closeViewModal()" class="px-4 py-2 text-xs font-semibold border border-gray-300 rounded-lg text-gray-700 hover:bg-white transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── EDIT HOMEWORK MODAL ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden my-6">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-sm font-bold text-heading flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-navy"></i>
                    Edit Homework Assignment
                </h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" id="editHomeworkForm" action="" enctype="multipart/form-data" class="p-6 space-y-3.5 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Homework Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="edit_title" required
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                </div>

                {{-- Class --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Class <span class="text-red-500">*</span>
                    </label>
                    <select name="school_class_id" id="edit_class_id" required onchange="handleClassChange(this.value, 'edit')"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                        <option value="">-- Choose Class --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Class Section & Subject --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Class Section
                        </label>
                        <select name="section_id" id="edit_section_id"
                                class="w-full px-2.5 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                            <option value="">All Sections</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <select name="subject_id" id="edit_subject_id" required
                                class="w-full px-2.5 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                            <option value="">-- Choose Subject --</option>
                        </select>
                    </div>
                </div>

                {{-- Date & Due Date --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="homework_date" id="edit_homework_date" required
                               class="w-full px-2.5 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="due_date" id="edit_due_date" required
                               class="w-full px-2.5 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Description / Instructions
                    </label>
                    <textarea name="description" id="edit_description" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading"></textarea>
                </div>

                {{-- Existing Attachments List with Delete option --}}
                <div id="edit_existing_attachments_wrapper" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Current Attached Files
                    </label>
                    <div id="edit_existing_attachments_list" class="space-y-1.5 p-2.5 bg-gray-50 rounded-xl border border-gray-200"></div>
                </div>

                {{-- Add More Attachments --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Upload Additional Files
                    </label>
                    <input type="file" name="attachments[]" id="edit_attachments" multiple
                           accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.zip,.rar"
                           onchange="renderSelectedFiles(this, 'edit_file_list')"
                           class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs bg-white text-heading">
                    <div id="edit_file_list" class="mt-2 space-y-1.5 max-h-24 overflow-y-auto"></div>
                </div>

                {{-- Attachment URL --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Attachment URL <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <input type="url" name="attachment_url" id="edit_attachment_url"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-navy hover:bg-primary-dark transition shadow-2xs">
                        Update Homework
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── INLINE JAVASCRIPT STATE MANAGEMENT ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <script>
        // Preloaded Classes, Sections, and Subjects from Server
        const allClassesData = @json($classes);
        const initialClassId = "{{ request('class_id') }}";
        const initialSectionId = "{{ request('section_id') }}";
        const initialSubjectId = "{{ request('subject_id') }}";
        const oldClassId = "{{ old('school_class_id') }}";
        const oldSectionId = "{{ old('section_id') }}";
        const oldSubjectId = "{{ old('subject_id') }}";

        // Escape HTML helper
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Dynamic Class -> Sections & Subjects population
        function handleClassChange(classId, context) {
            const cls = allClassesData.find(c => String(c.id) === String(classId));

            let secSelectId, subSelectId, hintId;
            if (context === 'create') {
                secSelectId = 'create_section_id';
                subSelectId = 'create_subject_id';
                hintId = 'create_subject_hint';
            } else if (context === 'edit') {
                secSelectId = 'edit_section_id';
                subSelectId = 'edit_subject_id';
                hintId = null;
            } else if (context === 'filter') {
                secSelectId = 'filter_section_id';
                subSelectId = 'filter_subject_id';
                hintId = null;
            }

            const secSelect = document.getElementById(secSelectId);
            const subSelect = document.getElementById(subSelectId);
            const hintEl = hintId ? document.getElementById(hintId) : null;

            if (!cls) {
                if (secSelect) {
                    secSelect.innerHTML = '<option value="">All Sections</option>';
                }
                if (subSelect) {
                    subSelect.innerHTML = context === 'filter' ? '<option value="">All Subjects</option>' : '<option value="">-- Choose Subject --</option>';
                }
                if (hintEl) hintEl.textContent = 'Select class first';
                return;
            }

            // Populate Sections
            if (secSelect) {
                let secOptions = '<option value="">All Sections</option>';
                if (cls.sections && cls.sections.length > 0) {
                    cls.sections.forEach(sec => {
                        secOptions += `<option value="${sec.id}">${escapeHtml(sec.name)}</option>`;
                    });
                }
                secSelect.innerHTML = secOptions;
            }

            // Populate Subjects
            if (subSelect) {
                let subOptions = context === 'filter' ? '<option value="">All Subjects</option>' : '<option value="">-- Choose Subject --</option>';
                if (cls.subjects && cls.subjects.length > 0) {
                    cls.subjects.forEach(sub => {
                        const code = sub.code ? ` (${sub.code})` : '';
                        subOptions += `<option value="${sub.id}">${escapeHtml(sub.name)}${escapeHtml(code)}</option>`;
                    });
                    if (hintEl) hintEl.textContent = `${cls.subjects.length} subjects available`;
                } else {
                    subOptions += '<option value="" disabled>No subjects found for this class</option>';
                    if (hintEl) hintEl.textContent = 'No subjects configured for this class';
                }
                subSelect.innerHTML = subOptions;
            }
        }

        // Interactive File Upload Preview
        function renderSelectedFiles(input, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = '';

            if (!input.files || input.files.length === 0) return;

            Array.from(input.files).forEach((file, idx) => {
                const sizeKb = (file.size / 1024).toFixed(1);
                const sizeStr = file.size > 1048576 ? (file.size / 1048576).toFixed(1) + ' MB' : sizeKb + ' KB';
                const ext = file.name.split('.').pop().toLowerCase();

                let iconName = 'file';
                let iconColor = '#6B7280';
                if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(ext)) {
                    iconName = 'image';
                    iconColor = '#10B981';
                } else if (['mp4', 'webm', 'mov', 'mkv', 'avi'].includes(ext)) {
                    iconName = 'video';
                    iconColor = '#8B5CF6';
                } else if (ext === 'pdf') {
                    iconName = 'file-text';
                    iconColor = '#EF4444';
                } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(ext)) {
                    iconName = 'file-spreadsheet';
                    iconColor = '#3B82F6';
                }

                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200 text-xs shadow-2xs';
                item.innerHTML = `
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <div class="w-6 h-6 rounded flex items-center justify-center shrink-0 bg-gray-100">
                            <i data-lucide="${iconName}" class="w-3.5 h-3.5" style="color: ${iconColor};"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-heading truncate text-2xs">${escapeHtml(file.name)}</p>
                            <p class="text-3xs text-gray-400">${sizeStr} &bull; ${ext.toUpperCase()}</p>
                        </div>
                    </div>
                    <span class="text-3xs font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 shrink-0">Ready</span>
                `;
                container.appendChild(item);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // ══ View Modal ══
        function openViewModal(id) {
            const modal = document.getElementById('viewModal');
            modal.classList.remove('hidden');

            fetch(`{{ url('academic/homework') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('view_modal_title').textContent = data.title;
                document.getElementById('view_modal_meta').textContent = `Created: ${data.created_at || 'Recently'}`;

                document.getElementById('view_class_sec_badge').textContent = `${data.class_name} • ${data.section_name}`;
                document.getElementById('view_subject_badge').textContent = `Subject: ${data.subject_name}${data.subject_code ? ' (' + data.subject_code + ')' : ''}`;

                const dueBadge = document.getElementById('view_due_status_badge');
                if (data.is_overdue) {
                    dueBadge.className = 'px-2.5 py-1 rounded-full font-semibold bg-red-50 text-red-700 border border-red-200';
                    dueBadge.textContent = 'Status: Past Due';
                } else {
                    dueBadge.className = 'px-2.5 py-1 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                    dueBadge.textContent = 'Status: Active';
                }

                document.getElementById('view_date_text').textContent = data.homework_date_formatted || data.homework_date || '-';
                document.getElementById('view_due_date_text').textContent = data.due_date_formatted || data.due_date || '-';
                document.getElementById('view_added_by_text').textContent = data.added_by || 'Teacher';

                const descBox = document.getElementById('view_description_box');
                descBox.textContent = data.description || 'No description provided for this homework assignment.';

                // Attachment URL
                const urlSec = document.getElementById('view_url_section');
                if (data.attachment_url) {
                    urlSec.classList.remove('hidden');
                    const link = document.getElementById('view_url_link');
                    link.href = data.attachment_url;
                    document.getElementById('view_url_text').textContent = data.attachment_url;
                } else {
                    urlSec.classList.add('hidden');
                }

                // Attached Files
                const filesContainer = document.getElementById('view_attachments_container');
                const filesCountLabel = document.getElementById('view_files_count_label');
                filesContainer.innerHTML = '';

                const attachments = data.attachments || [];
                filesCountLabel.textContent = `${attachments.length} ${attachments.length === 1 ? 'file' : 'files'}`;

                if (attachments.length === 0) {
                    filesContainer.innerHTML = '<p class="text-xs text-gray-400 italic p-3 text-center bg-gray-50 rounded-xl">No files uploaded with this homework.</p>';
                } else {
                    attachments.forEach((att, idx) => {
                        const downloadUrl = `{{ url('academic/homework') }}/${data.id}/download/${att.id || idx}`;
                        const type = att.type || 'other';

                        let icon = 'file';
                        let typeColor = '#6B7280';
                        let previewBtn = '';

                        if (type === 'image') {
                            icon = 'image';
                            typeColor = '#10B981';
                            previewBtn = `<a href="${att.url}" target="_blank" class="p-1 rounded text-stone-600 hover:text-stone-900 hover:bg-stone-100" title="Preview image"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>`;
                        } else if (type === 'video') {
                            icon = 'video';
                            typeColor = '#8B5CF6';
                            previewBtn = `<a href="${att.url}" target="_blank" class="p-1 rounded text-stone-600 hover:text-stone-900 hover:bg-stone-100" title="Watch video"><i data-lucide="play-circle" class="w-3.5 h-3.5"></i></a>`;
                        } else if (type === 'pdf') {
                            icon = 'file-text';
                            typeColor = '#EF4444';
                            previewBtn = `<a href="${att.url}" target="_blank" class="p-1 rounded text-stone-600 hover:text-stone-900 hover:bg-stone-100" title="Open PDF"><i data-lucide="external-link" class="w-3.5 h-3.5"></i></a>`;
                        } else if (type === 'doc') {
                            icon = 'file-spreadsheet';
                            typeColor = '#3B82F6';
                        }

                        const card = document.createElement('div');
                        card.className = 'flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs shadow-2xs hover:bg-white transition-colors';
                        card.innerHTML = `
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 bg-white border border-gray-200">
                                    <i data-lucide="${icon}" class="w-4 h-4" style="color: ${typeColor};"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-heading truncate text-xs">${escapeHtml(att.original_name)}</p>
                                    <p class="text-3xs text-gray-400">${att.size || ''} &bull; ${escapeHtml(type.toUpperCase())}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                ${previewBtn}
                                <a href="${downloadUrl}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-navy hover:bg-primary-dark text-white shadow-2xs transition-colors">
                                    <i data-lucide="download" class="w-3 h-3"></i> Download
                                </a>
                            </div>
                        `;
                        filesContainer.appendChild(card);
                    });
                }

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                console.error(err);
                alert('Failed to load homework details.');
                closeViewModal();
            });
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }
        document.getElementById('viewModal')?.addEventListener('click', function(e) { if (e.target === this) closeViewModal(); });

        // ══ Edit Modal ══
        function openEditModal(id) {
            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');

            fetch(`{{ url('academic/homework') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                const form = document.getElementById('editHomeworkForm');
                form.action = `{{ url('academic/homework') }}/${data.id}`;

                document.getElementById('edit_title').value = data.title;
                document.getElementById('edit_homework_date').value = data.homework_date;
                document.getElementById('edit_due_date').value = data.due_date;
                document.getElementById('edit_description').value = data.description || '';
                document.getElementById('edit_attachment_url').value = data.attachment_url || '';

                // Set Class and trigger Section & Subject population
                const classSelect = document.getElementById('edit_class_id');
                classSelect.value = data.class_id;
                handleClassChange(data.class_id, 'edit');

                // Set selected section & subject
                setTimeout(() => {
                    const secSelect = document.getElementById('edit_section_id');
                    const subSelect = document.getElementById('edit_subject_id');
                    if (secSelect) secSelect.value = data.section_id || '';
                    if (subSelect) subSelect.value = data.subject_id || '';
                }, 50);

                // Existing attachments list with deletion option
                const existingWrap = document.getElementById('edit_existing_attachments_wrapper');
                const existingList = document.getElementById('edit_existing_attachments_list');
                existingList.innerHTML = '';

                const attachments = data.attachments || [];
                if (attachments.length > 0) {
                    existingWrap.classList.remove('hidden');
                    attachments.forEach((att, idx) => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200 text-xs';
                        row.innerHTML = `
                            <div class="flex items-center gap-2 min-w-0 pr-2">
                                <i data-lucide="paperclip" class="w-3.5 h-3.5 text-stone-400 shrink-0"></i>
                                <span class="truncate font-medium text-heading text-2xs">${escapeHtml(att.original_name)}</span>
                                <span class="text-3xs text-gray-400">(${att.size || ''})</span>
                            </div>
                            <label class="inline-flex items-center gap-1 text-2xs text-red-600 cursor-pointer hover:underline shrink-0">
                                <input type="checkbox" name="deleted_attachment_ids[]" value="${att.id || idx}" class="rounded border-gray-300 accent-red-600">
                                Delete
                            </label>
                        `;
                        existingList.appendChild(row);
                    });
                } else {
                    existingWrap.classList.add('hidden');
                }

                // Clear any newly selected file list preview
                document.getElementById('edit_file_list').innerHTML = '';
                document.getElementById('edit_attachments').value = '';

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                console.error(err);
                alert('Failed to load homework for edit.');
                closeEditModal();
            });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        document.getElementById('editModal')?.addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });

        // ══ Bulk Checkbox Selection ══
        function toggleSelectAll(master) {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
            updateBulkToolbar();
        }

        function updateBulkToolbar() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const toolbar = document.getElementById('bulkToolbar');
            const countSpan = document.getElementById('selectedCount');
            const container = document.getElementById('bulkIdsContainer');
            const selectAll = document.getElementById('selectAll');

            if (!toolbar) return;

            if (checked.length > 0) {
                toolbar.classList.remove('hidden');
                countSpan.textContent = `${checked.length} selected`;
                let hiddenInputs = '';
                checked.forEach(cb => {
                    hiddenInputs += `<input type="hidden" name="ids[]" value="${cb.value}">`;
                });
                container.innerHTML = hiddenInputs;
            } else {
                toolbar.classList.add('hidden');
                container.innerHTML = '';
                if (selectAll) selectAll.checked = false;
            }
        }

        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(cb => { cb.checked = false; });
            const selectAll = document.getElementById('selectAll');
            if (selectAll) selectAll.checked = false;
            updateBulkToolbar();
        }

        function confirmBulkDelete() {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            return confirm(`Are you sure you want to delete the ${count} selected homework assignments and their attached files?`);
        }

        // ══ Bulk Export Selected ══
        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        }

        function bulkExportSelected() {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                alert('Please select at least one homework assignment to export.');
                return;
            }
            window.location.href = "{{ route('academic.homework.export') }}?ids=" + ids.join(',');
        }

        function printHomeworkTable() {
            document.body.classList.remove('print-selected-mode');
            window.print();
        }

        function bulkPrintSelected() {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                alert('Please select at least one homework assignment to print.');
                return;
            }
            // Mark rows according to selection
            document.querySelectorAll('.homework-table tbody tr').forEach(row => {
                const cb = row.querySelector('.row-checkbox');
                if (cb && cb.checked) {
                    row.classList.add('is-selected-row');
                } else {
                    row.classList.remove('is-selected-row');
                }
            });
            document.body.classList.add('print-selected-mode');
            window.print();
            setTimeout(() => {
                document.body.classList.remove('print-selected-mode');
            }, 1000);
        }

        window.addEventListener('afterprint', () => {
            document.body.classList.remove('print-selected-mode');
        });

        // ══ Import Modal ══
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }
        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }
        document.getElementById('importModal')?.addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });

        // ══ Create 2-Step Modal ══
        function switchCreateStep(step) {
            const step1 = document.getElementById('createStep1');
            const step2 = document.getElementById('createStep2');
            const tab1 = document.getElementById('createTab1');
            const tab2 = document.getElementById('createTab2');
            if (!step1 || !step2) return;

            if (step === 2) {
                // Validate Step 1 Required Fields
                const titleInput = document.getElementById('create_title');
                const classSelect = document.getElementById('create_class_id');
                const subjectSelect = document.getElementById('create_subject_id');
                const hwDateInput = document.getElementById('create_homework_date');
                const dueDateInput = document.getElementById('create_due_date');

                if (!titleInput || !titleInput.value.trim()) {
                    alert('Please enter the Homework Title before proceeding.');
                    titleInput?.focus();
                    return;
                }
                if (!classSelect || !classSelect.value) {
                    alert('Please choose a Class before proceeding.');
                    classSelect?.focus();
                    return;
                }
                if (!subjectSelect || !subjectSelect.value) {
                    alert('Please choose a Subject before proceeding.');
                    subjectSelect?.focus();
                    return;
                }
                if (!hwDateInput?.value || !dueDateInput?.value) {
                    alert('Please enter both Assigned Date and Due Date.');
                    return;
                }

                step1.classList.add('hidden');
                step2.classList.remove('hidden');

                if (tab1 && tab2) {
                    tab1.className = "flex-1 py-2 px-3 text-center border-b-2 border-transparent text-gray-400 hover:text-gray-600 font-medium text-xs flex items-center justify-center gap-1.5 transition-colors";
                    const span1 = tab1.querySelector('span:first-child');
                    if (span1) span1.className = "w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 text-[10px] flex items-center justify-center font-bold";

                    tab2.className = "flex-1 py-2 px-3 text-center border-b-2 border-navy text-navy font-bold text-xs flex items-center justify-center gap-1.5 bg-amber-50/30 transition-colors";
                    const span2 = tab2.querySelector('span:first-child');
                    if (span2) span2.className = "w-4 h-4 rounded-full bg-navy text-white text-[10px] flex items-center justify-center font-bold";
                }
            } else {
                step2.classList.add('hidden');
                step1.classList.remove('hidden');

                if (tab1 && tab2) {
                    tab1.className = "flex-1 py-2 px-3 text-center border-b-2 border-navy text-navy font-bold text-xs flex items-center justify-center gap-1.5 bg-amber-50/30 transition-colors";
                    const span1 = tab1.querySelector('span:first-child');
                    if (span1) span1.className = "w-4 h-4 rounded-full bg-navy text-white text-[10px] flex items-center justify-center font-bold";

                    tab2.className = "flex-1 py-2 px-3 text-center border-b-2 border-transparent text-gray-400 hover:text-gray-600 font-medium text-xs flex items-center justify-center gap-1.5 transition-colors";
                    const span2 = tab2.querySelector('span:first-child');
                    if (span2) span2.className = "w-4 h-4 rounded-full bg-gray-200 text-gray-600 text-[10px] flex items-center justify-center font-bold";
                }
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function openCreateModal(step = 1) {
            switchCreateStep(step);
            document.getElementById('createModal')?.classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal')?.classList.add('hidden');
        }

        document.getElementById('createModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });

        // ══ Initialize Defaults & Prefill on Page Load ══
        document.addEventListener('DOMContentLoaded', function() {
            const prefillClassId = "{{ request('prefill_class_id') }}";
            const prefillSectionId = "{{ request('prefill_section_id') }}";
            const prefillSubjectId = "{{ request('prefill_subject_id') }}";
            const prefillHwDate = "{{ request('prefill_homework_date') }}";
            const prefillDueDate = "{{ request('prefill_due_date') }}";

            // If prefill parameters present (from Save & Next), auto-open create modal!
            if (prefillClassId) {
                openCreateModal();
            }

            // Initialize Create Form Class -> Sections & Subjects
            const createClassSelect = document.getElementById('create_class_id');
            if (createClassSelect) {
                const selectedClassId = prefillClassId || oldClassId || createClassSelect.value;
                if (selectedClassId) {
                    createClassSelect.value = selectedClassId;
                    handleClassChange(selectedClassId, 'create');

                    const targetSection = prefillSectionId || oldSectionId;
                    if (targetSection) {
                        setTimeout(() => {
                            const secSelect = document.getElementById('create_section_id');
                            if (secSelect) secSelect.value = targetSection;
                        }, 50);
                    }

                    const targetSubject = prefillSubjectId || oldSubjectId;
                    if (targetSubject) {
                        setTimeout(() => {
                            const subSelect = document.getElementById('create_subject_id');
                            if (subSelect) subSelect.value = targetSubject;
                        }, 50);
                    }

                    if (prefillHwDate) {
                        const dInput = document.getElementById('create_homework_date');
                        if (dInput) dInput.value = prefillHwDate;
                    }
                    if (prefillDueDate) {
                        const dueInput = document.getElementById('create_due_date');
                        if (dueInput) dueInput.value = prefillDueDate;
                    }
                }
            }

            // Initialize Filter Toolbar Class -> Sections & Subjects if requested
            if (initialClassId) {
                handleClassChange(initialClassId, 'filter');
                setTimeout(() => {
                    const fSec = document.getElementById('filter_section_id');
                    const fSub = document.getElementById('filter_subject_id');
                    if (fSec && initialSectionId) fSec.value = initialSectionId;
                    if (fSub && initialSubjectId) fSub.value = initialSubjectId;
                }, 50);
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>

@endsection
