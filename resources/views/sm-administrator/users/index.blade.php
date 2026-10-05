@extends('layouts.app')
@section('header', 'Administrator – All Users')
@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            All Users
        </div>
        <form method="GET" action="{{ route('sm-administrator.users.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, username, email…"
                   class="px-4 py-2 rounded-lg border text-sm" style="border-color:#DED5CB; background:#FFFCF7; color:#211B17; width:280px;">
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Search</button>
        </form>
    </div>

    <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        <div class="overflow-x-auto">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Full Name</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Username</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Role</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Employee ID</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Email</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Mobile</th>
                        <th class="text-left text-white font-semibold text-sm px-4 py-3">Status</th>
                        <th class="text-center text-white font-semibold text-sm px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $isAdmin = $user['user_type'] === 'Admin/Principal';
                        $editRoute = $isAdmin
                            ? route('sm-administrator.admins.edit', $user['id'])
                            : route('sm-administrator.staff.edit', $user['id']);
                    @endphp
                    <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($user['photo']))
                                    <img src="{{ asset('storage/'.$user['photo']) }}" class="w-9 h-9 rounded-full object-cover">
                                @else
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold"
                                         style="background:linear-gradient(135deg,{{ $isAdmin ? '#667eea,#764ba2' : '#f093fb,#f5576c' }});">
                                        {{ strtoupper(substr($user['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-sm font-bold" style="color:#211B17;">{{ $user['name'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-mono font-bold" style="color:#211B17;">{{ $user['username'] }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-bold"
                                  style="background:{{ $isAdmin ? '#f0e8f8' : '#eef7f0' }}; color:{{ $isAdmin ? '#6a1b9a' : '#2d5434' }};">
                                {{ $user['user_type'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-mono font-bold" style="color:#766C63;">{{ $user['employee_id'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;">{{ $user['login_email'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;">{{ $user['login_phone'] ?? $user['phone'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-bold"
                                  style="background:{{ ($user['status'] ?? '') === 'Active' ? '#eef7f0' : '#fef0f0' }}; color:{{ ($user['status'] ?? '') === 'Active' ? '#2d5434' : '#7a181c' }};">
                                {{ $user['status'] ?? 'Active' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center">
                                <a href="{{ $editRoute }}" class="p-1.5 rounded-lg" style="background:#f5ece0; color:#B47A42;" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-12 text-sm font-bold" style="color:#9e8f84;">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.hover-row:hover { background:#FEFAF6; }</style>
@endsection
