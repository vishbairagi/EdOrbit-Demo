@extends('layouts.app')

@section('header', 'Administrator – Dashboard')

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">

    {{-- Session Banner --}}
    @php
        $school = session('selected_school_id') ? \App\Models\School::find(session('selected_school_id')) : null;
        $currentSessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
        $currentSession   = $currentSessionId ? \App\Models\AcademicSession::find($currentSessionId) : null;
    @endphp

    @if($school)
    <div class="flex items-center justify-center mb-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold" style="color:#211B17;">
                <span style="color:#B47A42;">🏫</span> {{ $school->name }}
                @if($currentSession)
                    <span class="ml-2 text-lg font-medium" style="color:#766C63;">{{ $currentSession->name }}</span>
                @endif
            </h1>
            @if($currentSession)
            <div class="mt-1 text-sm" style="color:#9e8f84;">
                Current Session: <span class="font-semibold" style="color:#B47A42;">{{ $currentSession->name }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Section Header --}}
    <div class="flex items-center justify-center mb-6">
        <div class="px-8 py-3 rounded-lg font-semibold text-white text-lg tracking-wide flex items-center gap-2"
             style="background: linear-gradient(135deg, #B47A42, #D4A055); box-shadow: 0 4px 12px rgba(180,122,66,0.35);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Administrator
        </div>
    </div>

    @php
    $dashboardCards = [
        [
            'title' => 'Admins',
            'subtitle' => 'Manage Admins',
            'count' => $adminsCount ?? 0,
            'icon' => 'shield-check',
            'color' => 'blue',
            'url' => route('sm-administrator.admins.index')
        ],
        [
            'title' => 'Roles',
            'subtitle' => 'Manage Roles',
            'count' => $rolesCount ?? 0,
            'icon' => 'shield',
            'color' => 'indigo',
            'url' => route('sm-administrator.roles.index')
        ],
        [
            'title' => 'Staff',
            'subtitle' => 'Active Staff',
            'count' => $staffCount ?? 0,
            'icon' => 'users',
            'color' => 'purple',
            'url' => route('sm-administrator.staff.index')
        ],
        [
            'title' => 'Staff ID Cards',
            'subtitle' => 'Print ID Cards',
            'count' => null,
            'icon' => 'id-card',
            'color' => 'cyan',
            'url' => route('sm-administrator.id-cards.index')
        ],
        [
            'title' => 'Staff Leaves',
            'subtitle' => 'Leaves Pending',
            'count' => $leavesCount ?? 0,
            'icon' => 'calendar-off',
            'color' => 'rose',
            'url' => route('sm-administrator.leaves.index')
        ],
        [
            'title' => 'Staff Payroll',
            'subtitle' => 'Salary Management',
            'count' => null,
            'icon' => 'circle-dollar-sign',
            'color' => 'emerald',
            'url' => route('sm-administrator.payroll.index')
        ],
        [
            'title' => 'Users',
            'subtitle' => 'All System Users',
            'count' => null,
            'icon' => 'user-check',
            'color' => 'amber',
            'url' => route('sm-administrator.users.index')
        ],
    ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
        @foreach($dashboardCards as $card)
            <a href="{{ $card['url'] }}" class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow group">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-{{ $card['color'] }}-50 flex items-center justify-center mr-2.5 flex-shrink-0 group-hover:bg-{{ $card['color'] }}-100 transition-colors">
                        <i data-lucide="{{ $card['icon'] }}" class="w-4 h-4 text-{{ $card['color'] }}-500"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-heading leading-tight">{{ $card['title'] }}</p>
                        <p class="text-xs text-gray-400">{{ $card['subtitle'] }}</p>
                    </div>
                </div>
                @if(!is_null($card['count']))
                    <div class="text-lg font-bold text-heading ml-1">{{ $card['count'] }}</div>
                @else
                    <div class="text-gray-300 ml-1 group-hover:text-{{ $card['color'] }}-500 transition-colors">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endsection
