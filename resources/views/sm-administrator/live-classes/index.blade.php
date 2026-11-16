@extends('layouts.app')

@section('title', 'Live Class Settings — Google Meet')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('sm-administrator.dashboard') }}" class="hover:text-navy transition-colors">Administrator</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">Live Class Settings</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-heading flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                    </svg>
                </div>
                Live Class Settings — Google Meet
            </h1>
            <p class="text-xs text-gray-500 mt-1">Manage Google Meet credentials and live video class authorization for teachers assigned to classes.</p>
        </div>

        <!-- Session Switcher -->
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('sm-administrator.live-classes.index') }}" class="flex items-center gap-2">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <label for="session_id_select" class="text-xs font-semibold text-gray-600">Session:</label>
                <select id="session_id_select" name="session_id" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    @foreach($sessions as $sess)
                        <option value="{{ $sess->id }}" {{ $session->id === $sess->id ? 'selected' : '' }}>{{ $sess->name }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('sm-school.settings.index', ['tab' => 'live-classes', 'session_id' => $session->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Google Meet Setup Guide
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Google Meet Disabled Warning Banner -->
    @if(! $googleMeetEnabled)
        <div class="rounded-xl border border-amber-300 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 p-4 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">Google Meet is Disabled for Session {{ $session->name }}</h4>
                        <p class="text-xs text-amber-700 mt-0.5">Live video classes are currently disabled in School Settings for this session. To allow teachers to take live classes, please enable Google Meet.</p>
                    </div>
                </div>
                <a href="{{ route('sm-school.settings.index', ['tab' => 'live-classes', 'session_id' => $session->id]) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-700 transition-colors">
                    Enable in School Settings
                </a>
            </div>
        </div>
    @endif

    <!-- 4 Overview Metric Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- 1. Feature Status -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Google Meet Status</p>
                    <div class="mt-2 flex items-center gap-2">
                        @if($googleMeetEnabled)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Enabled Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 border border-gray-200">
                                <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                Disabled
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $googleMeetEnabled ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-[11px] text-gray-500 border-t border-gray-100 pt-2">
                <span>Session: {{ $session->name }}</span>
                <a href="{{ route('sm-school.settings.index', ['tab' => 'live-classes', 'session_id' => $session->id]) }}" class="text-blue-600 hover:underline">Change</a>
            </div>
        </div>

        <!-- 2. Assigned Teachers -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Teachers</p>
                    <p class="mt-1 text-2xl font-extrabold text-heading">{{ $allAssignedStaffCount }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-gray-500 border-t border-gray-100 pt-2">Staff with assigned classes in this session</p>
        </div>

        <!-- 3. Google Meet Connected -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Connected Accounts</p>
                    <p class="mt-1 text-2xl font-extrabold text-heading">{{ $connectedCount }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-gray-500 border-t border-gray-100 pt-2">
                {{ $allAssignedStaffCount > 0 ? round(($connectedCount / $allAssignedStaffCount) * 100) : 0 }}% of teaching staff connected
            </p>
        </div>

        <!-- 4. Default Redirect URI Info -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Redirect URI</p>
                    <p class="mt-1 text-xs font-mono font-bold text-gray-800 truncate max-w-[170px]" title="{{ $defaultRedirectUri }}">/google-meet/oauth/callback</p>
                </div>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $defaultRedirectUri }}'); alert('Redirect URI copied to clipboard!');" class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors" title="Copy Redirect URL">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </div>
            <p class="mt-3 text-[11px] text-gray-500 border-t border-gray-100 pt-2">Fixed Google Cloud callback URL</p>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <!-- Filter & Search Toolbar -->
        <div class="border-b border-gray-200 bg-gray-50/75 p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-gray-900">Teaching Staff Assigned to Classes</h3>
                <span class="rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold text-gray-700">{{ $staffList->total() }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search & Per Page Form -->
                <form method="GET" action="{{ route('sm-administrator.live-classes.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                    
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, phone..." class="w-56 sm:w-64 rounded-lg border border-gray-300 bg-white pl-8 pr-3 py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <svg class="absolute left-2.5 top-2 h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="per_page" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" title="Items per page">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / page</option>
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / page</option>
                    </select>

                    <button type="submit" class="rounded-lg bg-navy px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-navy/90 transition-colors">Search</button>

                    @if($search || (request('per_page') && request('per_page') != 10))
                        <a href="{{ route('sm-administrator.live-classes.index', ['session_id' => $session->id]) }}" class="text-xs text-gray-500 hover:text-gray-700 underline">Reset</a>
                    @endif
                </form>

                <!-- Bulk Export Button -->
                <a href="{{ route('sm-administrator.live-classes.export', array_filter(['session_id' => $session->id, 'search' => $search])) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" title="Export all teaching staff live class credentials for this session as CSV">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div id="bulkToolbar" class="hidden border-b border-red-200 bg-gradient-to-r from-red-50 via-amber-50 to-red-50 px-5 py-3 transition-all duration-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white text-xs font-extrabold" id="bulkCountBadge">0</span>
                    <span class="text-xs font-bold text-gray-900" id="bulkCountText">0 staff member(s) selected</span>
                    <span class="text-xs text-gray-300">|</span>
                    <button type="button" onclick="clearSelection()" class="text-xs text-gray-600 hover:text-gray-900 underline font-medium">Clear Selection</button>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Bulk Export Selected Form -->
                    <form id="bulkExportForm" action="{{ route('sm-administrator.live-classes.bulk-export') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session->id }}">
                        <div id="bulkExportIdsContainer"></div>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700 shadow-sm hover:bg-emerald-50 transition-colors">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export Selected (<span class="selectedCountNumber">0</span>)
                        </button>
                    </form>

                    <!-- Bulk Delete Credentials Form -->
                    <form id="bulkDeleteForm" action="{{ route('sm-administrator.live-classes.bulk-delete') }}" method="POST" onsubmit="return confirmBulkDelete();" class="inline">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session->id }}">
                        <div id="bulkDeleteIdsContainer"></div>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete Credentials (<span class="selectedCountNumber">0</span>)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($staffList->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h4 class="text-base font-bold text-gray-900">No Teaching Staff Found</h4>
                <p class="mt-1 text-xs text-gray-500 max-w-sm">
                    Only staff members who have been assigned to classes/sections appear in this list. Assign teachers to classes under <strong>Academic &gt; Class Teachers</strong> to configure live classes.
                </p>
                <a href="{{ route('academic.class-teachers.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-navy px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-navy/90">
                    Go to Class Teachers
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50/50 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="w-10 px-4 py-3.5 text-center">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy cursor-pointer" title="Select all on this page">
                            </th>
                            <th class="w-12 px-3 py-3.5 text-center">#</th>
                            <th class="px-5 py-3.5">Staff Member</th>
                            <th class="px-5 py-3.5">Role / Employee ID</th>
                            <th class="px-5 py-3.5">Assigned Classes</th>
                            <th class="px-5 py-3.5">Google Client ID</th>
                            <th class="px-5 py-3.5">Connection Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($staffList as $staff)
                            @php
                                $cred = $credentials->get($staff->id);
                                $assignments = $classAssignments->get($staff->id, collect());
                                $hasCred = !empty($cred) && $cred->isConfigured();
                                $isConnected = !empty($cred) && $cred->is_connected;
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <!-- Row Checkbox -->
                                <td class="w-10 px-4 py-4 text-center">
                                    <input type="checkbox" value="{{ $staff->id }}" onchange="updateBulkToolbar()" class="staff-row-checkbox h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy cursor-pointer">
                                </td>
                                <!-- Row Index -->
                                <td class="w-12 px-3 py-4 text-center text-xs font-mono text-gray-400">
                                    {{ $staffList->firstItem() + $loop->index }}
                                </td>
                                <!-- Staff Info -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100">
                                            @if($staff->photo)
                                                <img src="{{ asset('storage/' . $staff->photo) }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center font-bold text-gray-600">
                                                    {{ strtoupper(substr($staff->name ?: 'T', 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $staff->name }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-2">
                                                @if($staff->email)
                                                    <span>{{ $staff->email }}</span>
                                                @endif
                                                @if($staff->phone)
                                                    <span>&bull;</span>
                                                    <span>{{ $staff->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role & Employee ID -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                        {{ $staff->role?->name ?: ($staff->designation ?: 'Teacher') }}
                                    </span>
                                    <div class="text-[11px] text-gray-400 mt-1 font-mono">{{ $staff->employee_id ?: 'ID: #' . $staff->id }}</div>
                                </td>

                                <!-- Assigned Classes -->
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @forelse($assignments as $ct)
                                            <span class="inline-flex items-center rounded bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 border border-blue-200">
                                                {{ $ct->schoolClass?->name ?? 'Class' }}
                                                @if($ct->section)
                                                    ({{ $ct->section->name }})
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-gray-400 italic">No assigned classes</span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Client ID Preview -->
                                <td class="px-5 py-4">
                                    @if(!empty($cred?->google_client_id))
                                        <div class="font-mono text-[11px] text-gray-700 truncate max-w-[150px]" title="{{ $cred->google_client_id }}">
                                            {{ Str::limit($cred->google_client_id, 20) }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">Client Secret saved</div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Not entered</span>
                                    @endif
                                </td>

                                <!-- Connection Status -->
                                <td class="px-5 py-4">
                                    @if($isConnected)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Connected
                                        </span>
                                        @if($cred->token_expires_at)
                                            <div class="text-[10px] text-gray-400 mt-0.5">Expires {{ $cred->token_expires_at->diffForHumans() }}</div>
                                        @endif
                                    @elseif($hasCred)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Credentials Saved
                                        </span>
                                        <div class="text-[10px] text-amber-600 mt-0.5">Click Google Connect</div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">
                                            Not Configured
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(! $hasCred)
                                            <!-- Add Credential Button -->
                                            <button type="button" onclick="openCredentialModal({{ json_encode([
                                                'staff_id' => $staff->id,
                                                'staff_name' => $staff->name,
                                                'google_client_id' => '',
                                                'google_client_secret' => '',
                                                'google_redirect_uri' => $defaultRedirectUri,
                                                'google_access_token' => '',
                                                'google_refresh_token' => '',
                                            ]) }})" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                Add Credential
                                            </button>
                                        @else
                                            <!-- Google Connect Button -->
                                            <form action="{{ route('sm-administrator.live-classes.connect', $staff->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" title="{{ $isConnected ? 'Re-authorize Google Meet' : 'Connect with Google Account' }}">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24">
                                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                                    </svg>
                                                    {{ $isConnected ? 'Re-connect' : 'Connect' }}
                                                </button>
                                            </form>

                                            <!-- Edit Button -->
                                            <button type="button" onclick="openCredentialModal({{ json_encode([
                                                'staff_id' => $staff->id,
                                                'staff_name' => $staff->name,
                                                'google_client_id' => $cred->google_client_id ?? '',
                                                'google_client_secret' => $cred->google_client_secret ?? '',
                                                'google_redirect_uri' => $cred->google_redirect_uri ?: $defaultRedirectUri,
                                                'google_access_token' => $cred->google_access_token ?? '',
                                                'google_refresh_token' => $cred->google_refresh_token ?? '',
                                            ]) }})" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" title="Edit credentials">
                                                <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('sm-administrator.live-classes.destroy-credential', $staff->id) }}" method="POST" onsubmit="return confirm('Delete Google Meet credentials for {{ $staff->name }}?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 bg-white p-1.5 text-xs font-semibold text-red-600 shadow-sm hover:bg-red-50 hover:border-red-300 transition-colors" title="Delete credentials">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="border-t border-gray-200 bg-white px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-xs text-gray-500">
                    Showing <strong class="font-semibold text-gray-800">{{ $staffList->firstItem() ?? 0 }}</strong> to <strong class="font-semibold text-gray-800">{{ $staffList->lastItem() ?? 0 }}</strong> of <strong class="font-semibold text-gray-800">{{ $staffList->total() }}</strong> staff members
                </div>
                <div>
                    @if($staffList->hasPages())
                        {{ $staffList->links() }}
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200">
                            Page 1 of 1
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL: ADD / EDIT CREDENTIALS ================= -->
<div id="credentialModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
            <!-- Modal Header -->
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 border border-blue-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="modalStaffTitle">Google Meet Credentials</h3>
                        <p class="text-xs text-gray-500">Configure OAuth 2.0 Client & Tokens for live video classes</p>
                    </div>
                </div>
                <button type="button" onclick="closeCredentialModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="credentialForm" action="{{ route('sm-administrator.live-classes.store-credential') }}" method="POST">
                @csrf
                <input type="hidden" name="staff_id" id="modal_staff_id" value="">
                <input type="hidden" name="session_id" value="{{ $session->id }}">

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Google Client ID -->
                    <div>
                        <label for="modal_google_client_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Google Client ID: <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="google_client_id" id="modal_google_client_id" required placeholder="e.g. 123456789-xxxxxxxx.apps.googleusercontent.com" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-mono text-gray-800 placeholder-gray-400 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy shadow-sm">
                        <p class="mt-1 text-[11px] text-gray-500">From Google Cloud Console &gt; APIs & Services &gt; Credentials &gt; OAuth 2.0 Client ID.</p>
                    </div>

                    <!-- Google Client Secret -->
                    <div>
                        <label for="modal_google_client_secret" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Google Client Secret: <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="google_client_secret" id="modal_google_client_secret" required placeholder="e.g. GOCSPX-xxxxxxxxxxxxxxxx" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 pr-10 text-xs font-mono text-gray-800 placeholder-gray-400 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy shadow-sm">
                            <button type="button" onclick="toggleSecretVisibility()" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600">
                                <svg id="secretEyeIcon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm7.5 0s-3.5 6-10.5 6S1.5 12 1.5 12s3.5-6 10.5-6 10.5 6 10.5 6z"/></svg>
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500">Keep this secret secure. Do not share publicly.</p>
                    </div>

                    <!-- Google Redirect URI -->
                    <div>
                        <label for="modal_google_redirect_uri" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Google Redirect URI:
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="google_redirect_uri" id="modal_google_redirect_uri" readonly value="{{ $defaultRedirectUri }}" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono text-gray-700 shadow-sm cursor-text select-all">
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('modal_google_redirect_uri').value); alert('Redirect URI copied!');" class="shrink-0 rounded-lg bg-gray-100 border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition-colors">
                                Copy
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-blue-600 font-medium">This exact URL must be added in Google Cloud Console under "Authorized redirect URIs".</p>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-3.5 text-xs text-blue-900 space-y-1.5">
                            <div class="font-bold flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Automatic Google Connect vs Manual Tokens:
                            </div>
                            <p class="text-[11px] text-blue-800 leading-relaxed">
                                You can enter Client ID and Client Secret, then click <strong>"Google Connect"</strong> below to automatically authorize with Google and fetch the Access and Refresh tokens. Alternatively, if you already have tokens, you can paste them manually into the fields below.
                            </p>
                        </div>
                    </div>

                    <!-- Google Access Token (Auto-generated or Manual) -->
                    <div>
                        <label for="modal_google_access_token" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Google Access Token (Auto-generated or Manual):
                        </label>
                        <textarea name="google_access_token" id="modal_google_access_token" rows="2" placeholder="Auto-generated when you click Google Connect, or enter manually..." class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-mono text-gray-800 placeholder-gray-400 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy shadow-sm"></textarea>
                    </div>

                    <!-- Google Refresh Token (Auto-generated or Manual) -->
                    <div>
                        <label for="modal_google_refresh_token" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Google Refresh Token (Auto-generated or Manual):
                        </label>
                        <input type="text" name="google_refresh_token" id="modal_google_refresh_token" placeholder="Auto-generated on Google Connect, or enter manually (1//xxxx...)" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-mono text-gray-800 placeholder-gray-400 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy shadow-sm">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <!-- Google Connect Button -->
                    <button type="button" onclick="submitGoogleConnect()" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        Google Connect
                    </button>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" onclick="closeCredentialModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg bg-navy px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-navy/90 transition-colors">
                            Save Credentials
                        </button>
                    </div>
                </div>
            </form>

            <!-- Hidden Form for Google Connect Initiation -->
            <form id="googleConnectForm" method="POST" action="" class="hidden">
                @csrf
                <input type="hidden" name="google_client_id" id="gc_client_id">
                <input type="hidden" name="google_client_secret" id="gc_client_secret">
            </form>
        </div>
    </div>
</div>

<script>
    function openCredentialModal(data) {
        document.getElementById('modal_staff_id').value = data.staff_id;
        document.getElementById('modalStaffTitle').textContent = 'Google Meet Credentials — ' + data.staff_name;
        document.getElementById('modal_google_client_id').value = data.google_client_id || '';
        document.getElementById('modal_google_client_secret').value = data.google_client_secret || '';
        document.getElementById('modal_google_redirect_uri').value = data.google_redirect_uri || '{{ $defaultRedirectUri }}';
        document.getElementById('modal_google_access_token').value = data.google_access_token || '';
        document.getElementById('modal_google_refresh_token').value = data.google_refresh_token || '';

        document.getElementById('credentialModal').classList.remove('hidden');
    }

    function closeCredentialModal() {
        document.getElementById('credentialModal').classList.add('hidden');
    }

    function toggleSecretVisibility() {
        const input = document.getElementById('modal_google_client_secret');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function submitGoogleConnect() {
        const staffId = document.getElementById('modal_staff_id').value;
        const clientId = document.getElementById('modal_google_client_id').value.trim();
        const clientSecret = document.getElementById('modal_google_client_secret').value.trim();

        if (!clientId || !clientSecret) {
            alert('Please enter both Google Client ID and Google Client Secret before connecting.');
            return;
        }

        const form = document.getElementById('googleConnectForm');
        form.action = '/sm-administrator/live-classes/' + staffId + '/connect';
        document.getElementById('gc_client_id').value = clientId;
        document.getElementById('gc_client_secret').value = clientSecret;
        form.submit();
    }

    // Bulk Selection & Actions
    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.staff-row-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const checkboxes = document.querySelectorAll('.staff-row-checkbox:checked');
        const count = checkboxes.length;
        const total = document.querySelectorAll('.staff-row-checkbox').length;
        const toolbar = document.getElementById('bulkToolbar');
        const countBadge = document.getElementById('bulkCountBadge');
        const countText = document.getElementById('bulkCountText');
        const masterCheckbox = document.getElementById('selectAllCheckbox');

        if (masterCheckbox) {
            masterCheckbox.checked = (total > 0 && count === total);
            masterCheckbox.indeterminate = (count > 0 && count < total);
        }

        document.querySelectorAll('.selectedCountNumber').forEach(el => el.textContent = count);

        if (count > 0) {
            if (toolbar) toolbar.classList.remove('hidden');
            if (countBadge) countBadge.textContent = count;
            if (countText) countText.textContent = `${count} staff member(s) selected`;

            const deleteContainer = document.getElementById('bulkDeleteIdsContainer');
            const exportContainer = document.getElementById('bulkExportIdsContainer');
            if (deleteContainer) {
                deleteContainer.innerHTML = Array.from(checkboxes).map(cb => `<input type="hidden" name="ids[]" value="${cb.value}">`).join('');
            }
            if (exportContainer) {
                exportContainer.innerHTML = Array.from(checkboxes).map(cb => `<input type="hidden" name="ids[]" value="${cb.value}">`).join('');
            }
        } else {
            if (toolbar) toolbar.classList.add('hidden');
        }
    }

    function clearSelection() {
        document.querySelectorAll('.staff-row-checkbox').forEach(cb => cb.checked = false);
        const master = document.getElementById('selectAllCheckbox');
        if (master) {
            master.checked = false;
            master.indeterminate = false;
        }
        updateBulkToolbar();
    }

    function confirmBulkDelete() {
        const checkboxes = document.querySelectorAll('.staff-row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one staff member.');
            return false;
        }
        return confirm(`Are you sure you want to delete Google Meet credentials for the ${checkboxes.length} selected staff member(s)?`);
    }

    // Close modal on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCredentialModal();
        }
    });
</script>
@endsection
