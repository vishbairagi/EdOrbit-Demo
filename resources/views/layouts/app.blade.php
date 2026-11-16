<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>{{ config('app.name', 'EdOrbit') }} - School Management</title>
    
    <!-- Tailwind CSS (Universal CDN Fallback) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#B47A42',
                        cream: '#F5F1EB',
                        card: '#FFFCF7',
                        beige: '#EDE5DC',
                        border: '#DED5CB',
                        heading: '#211B17',
                        body: '#766C63',
                    }
                }
            }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    @php
        $themeColor = \App\Models\SystemSetting::getSetting('theme_color', '#B47A42');
        $showExamination = \App\Models\SystemSetting::getSetting('show_examination_menu', '1') == '1';
        $showTransport = \App\Models\SystemSetting::getSetting('show_transport_menu', '1') == '1';
        $showLibrary = \App\Models\SystemSetting::getSetting('show_library_menu', '1') == '1';
        $showAccounting = \App\Models\SystemSetting::getSetting('show_accounting_menu', '1') == '1';
        $showWebsiteSettings = \App\Models\SystemSetting::getSetting('show_website_settings_menu', '1') == '1';
    @endphp
    <style>
        /* ── Sidebar brand colours ── */
        :root {
            --color-primary: {{ $themeColor }};
            --sb-bg:        #171411;  /* Sidebar Main Dark     */
            --sb-sec:       #211C18;  /* Sidebar Secondary     */
            --sb-hover:     #2a231e;  /* hover tint            */
            --sb-active:    {{ $themeColor }};  /* Gold Accent — active item */
            --sb-active-tx: #ffffff;  /* text on active item */
            --sb-border:    #2e2520;
            --sb-text:      #e8ddd5;  /* warm off-white text   */
            --sb-muted:     #9e8f84;  /* muted secondary text  */
            --sb-gold:      {{ $themeColor }};  /* Gold Accent           */
        }

        html {
            font-size: 80%; /* Scales the entire Tailwind UI down to match 80% zoom */
        }

        aside#sidebar            { background: var(--sb-bg); color: var(--sb-text); }
        aside#sidebar .logo-bar  { background: var(--sb-sec); border-bottom: 1px solid var(--sb-border); color: #fff; }

        /* parent menu buttons */
        .sb-parent {
            display: flex; align-items: center; justify-content: space-between;
            width: 100%; padding: 5px 10px; border-radius: 5px;
            font-size: 0.8rem; color: var(--sb-muted);
            background: transparent; border: none; cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .sb-parent:hover { background: var(--sb-hover); color: var(--sb-text); }

        /* child links */
        .sb-child {
            display: block; padding: 5px 10px; border-radius: 4px;
            font-size: 0.8125rem; color: var(--sb-muted);
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .sb-child:hover  { background: var(--sb-hover); color: var(--sb-text); }
        .sb-child.active { background: var(--sb-active); color: var(--sb-active-tx); font-weight: 600; }

        /* active school parent button */
        .sb-school-active {
            background: rgba(180, 122, 66, 0.12);
            border-left: 3px solid var(--sb-gold);
            color: #e8c99a;
            padding-left: 7px;
        }

        /* resizer */
        #resizer { width: 5px; cursor: ew-resize; }
        #resizer:hover, #resizer.active { background: rgba(212,162,76,0.4); }

        .sidebar-text { transition: opacity 0.2s; }

        /* ══════════════════════════════════════════════════════════════════
           ── UNIFIED GLOBAL TABLE DESIGN SYSTEM ──
           ══════════════════════════════════════════════════════════════════ */
        /* Table Containers */
        .table-container,
        .overflow-x-auto:has(> table) {
            border-radius: 0.75rem;
            border: 1px solid #E8E0D8;
            background: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(33, 27, 23, 0.05);
            margin-bottom: 1.25rem;
        }

        /* All Standard Content Tables */
        table:not(.no-theme-table) {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            text-align: left !important;
            font-size: 0.8125rem !important; /* 13px */
        }

        /* Universal Table Headers */
        table:not(.no-theme-table) thead tr {
            background: var(--color-primary, #B47A42) !important;
            color: #ffffff !important;
        }
        table:not(.no-theme-table) thead th {
            background: var(--color-primary, #B47A42) !important;
            color: #ffffff !important;
            font-size: 0.75rem !important; /* 12px */
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 0.65rem 1rem !important;
            vertical-align: middle !important;
            border-top: none !important;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08) !important;
            white-space: nowrap !important;
        }
        table:not(.no-theme-table) thead th input[type="checkbox"] {
            margin: 0 auto;
            display: block;
        }

        /* Universal Table Body Rows */
        table:not(.no-theme-table) tbody tr {
            background: #ffffff;
            transition: background-color 0.15s ease-in-out;
            border-bottom: 1px solid #EAE3DC !important;
        }
        table:not(.no-theme-table) tbody tr:last-child td {
            border-bottom: none !important;
        }
        table:not(.no-theme-table) tbody tr:hover,
        table:not(.no-theme-table) tbody tr.hover-row:hover {
            background-color: #F8F5F0 !important; /* Warm luxury gold hover tint */
        }
        table:not(.no-theme-table) tbody tr.bg-red-50,
        table:not(.no-theme-table) tbody tr.row-selected {
            background-color: #FEF2F2 !important; /* Selected row red tint */
        }

        /* Universal Table Cells */
        table:not(.no-theme-table) tbody td {
            padding: 0.65rem 1rem !important;
            font-size: 0.8125rem !important;
            color: #211B17 !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #EAE3DC !important;
        }

        /* Checkboxes */
        input[type="checkbox"].row-checkbox,
        input[type="checkbox"]#selectAll,
        input[type="checkbox"]#selectAllCb,
        input[type="checkbox"].select-all-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
            border: 1px solid #CBD5E1;
            cursor: pointer;
            accent-color: #DC2626 !important;
        }

        /* Bulk Delete Toolbar Styling */
        #bulkToolbar, .bulk-toolbar {
            background: #FEF2F2 !important;
            border: 1px solid #FECACA !important;
            border-radius: 0.75rem !important;
            padding: 0.625rem 1rem !important;
            margin-bottom: 0.75rem !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.2s ease-in-out;
        }
        #bulkToolbar.hidden, .bulk-toolbar.hidden {
            display: none !important;
        }
        #bulkToolbar #selectedCount, .bulk-toolbar .selected-count {
            color: #B91C1C !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
        }
    </style>
</head>
<body class="bg-cream text-body font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    @php
    $guard = session('auth_guard');
    $user = null;
    if ($guard === 'school_user') {
        $user = auth('school_user')->user() ?? \App\Models\SchoolUser::find(session('auth_user_id'));
    } elseif ($guard === 'staff') {
        $user = auth('staff')->user() ?? \App\Models\Staff::find(session('auth_user_id'));
        if ($user) $user->load('role.permissions');
    }
    
    $hasPerm = function($key) use ($guard, $user) {
        if ($guard === 'super_admin') return true;
        // Admin/Principal (school_user) has access to all menus in their school
        if ($guard === 'school_user') return true; 
        
        if ($guard === 'staff' && $user) {
            $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
            static $isClassTeacher = null;
            if ($isClassTeacher === null) {
                $isClassTeacher = \App\Models\ClassTeacher::where('staff_id', $user->id)
                                    ->where('school_id', session('selected_school_id'))
                                    ->where('session_id', $sessionId)->exists();
            }
            if ($isClassTeacher && $key === 'academic.class-teachers') {
                return false;
            }
            if ($isClassTeacher) {
                $teacherAllowed = ['sm-student.dashboard', 'sm-student.students', 'sm-student.print-id-cards', 'academic.dashboard', 'academic.subjects', 'academic.noticeboards', 'academic.homework', 'academic.class-timetable', 'sm-administrator.live-classes'];
                if (in_array($key, $teacherAllowed)) {
                    return true;
                }
            }
        }
        if ($user && $user->hasPermission($key)) return true;
        return false;
    };
@endphp
<aside id="sidebar" class="relative flex flex-col" style="width: 14rem; min-width: 200px; max-width: 420px; flex-shrink: 0;">

        <!-- Logo -->
        <div class="logo-bar h-16 flex items-center px-5 font-bold text-base tracking-wide overflow-hidden whitespace-nowrap">
            <i data-lucide="graduation-cap" class="w-5 h-5 mr-2 flex-shrink-0" style="color:var(--sb-gold)"></i>
            <span class="sidebar-text" style="color:#fff">EdOrbit</span>
        </div>

        <nav class="flex-1 py-3 px-2 overflow-y-auto overflow-x-hidden space-y-0.5">

            @if($guard === 'super_admin')
            <!-- ── School Management ── -->
            <div>
                <button type="button" id="schoolMenuToggle" class="sb-parent">
                    <div class="flex items-center">
                        <i data-lucide="building-2" class="w-4 h-4 mr-2 flex-shrink-0"></i>
                        <span class="sidebar-text text-xs font-semibold uppercase tracking-widest">School Management</span>
                    </div>
                    <i data-lucide="chevron-down" id="schoolMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text"></i>
                </button>

                <div id="schoolMenu" class="pl-6 pr-1 py-0.5 space-y-0.5">
                    <a href="{{ route('school-management.dashboard') }}"
                       class="sb-child {{ request()->routeIs('school-management.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    <a href="{{ route('school-management.schools.index') }}"
                       class="sb-child {{ request()->routeIs('school-management.schools.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Schools</span>
                    </a>
                    <a href="{{ route('school-management.sessions.index') }}"
                       class="sb-child {{ request()->routeIs('school-management.sessions.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Sessions</span>
                    </a>
                    <a href="{{ route('school-management.settings.index') }}"
                       class="sb-child {{ request()->routeIs('school-management.settings.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Settings</span>
                    </a>
                </div>
            </div>
            @endif

            @if($hasPerm('sm-school.dashboard') || $hasPerm('sm-school.class-groups') || $hasPerm('sm-school.classes') || $hasPerm('sm-school.settings'))
            <!-- ── School (SM School) ── -->
            @php
                $selectedSchoolId = session('selected_school_id');
                $selectedSchool   = $selectedSchoolId ? \App\Models\School::find($selectedSchoolId) : null;
            @endphp

            <div style="margin-top: 12px;">
                <!-- divider -->
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>

                <button type="button" id="smSchoolMenuToggle" class="sb-parent {{ request()->routeIs('sm-school.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="school" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">
                            @if($selectedSchool)
                                {{ $selectedSchool->name }}
                            @else
                                School
                            @endif
                        </span>
                    </div>
                    <i data-lucide="chevron-down" id="smSchoolMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="smSchoolMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ ($selectedSchool || request()->routeIs('sm-school.*')) ? '' : 'hidden' }}">
                    <a href="{{ $selectedSchool ? route('sm-school.dashboard') : '#' }}"
                       class="sb-child {{ request()->routeIs('sm-school.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    <a href="{{ $selectedSchool ? route('sm-school.class-groups.index') : '#' }}"
                       class="sb-child {{ request()->routeIs('sm-school.class-groups.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Class Groups</span>
                    </a>
                    <a href="{{ $selectedSchool ? route('sm-school.classes.index') : '#' }}"
                       class="sb-child {{ request()->routeIs('sm-school.classes.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Classes</span>
                    </a>
                    <a href="{{ $selectedSchool ? route('sm-school.settings.index') : '#' }}"
                       class="sb-child {{ request()->routeIs('sm-school.settings.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Settings</span>
                    </a>
                </div>
            </div>
            @endif

            @if($hasPerm('academic.dashboard') || $hasPerm('academic.class-teachers') || $hasPerm('academic.class-sections') || $hasPerm('academic.mediums') || $hasPerm('academic.subjects') || $hasPerm('academic.subject-types') || $hasPerm('academic.study-materials') || $hasPerm('academic.noticeboards') || $hasPerm('academic.homework'))
            <!-- ── SM Academic ── -->
            <div style="margin-top: 12px;">
                <!-- divider -->
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>

                <button type="button" id="smAcademicMenuToggle" class="sb-parent {{ request()->routeIs('academic.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="graduation-cap" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Academic</span>
                    </div>
                    <i data-lucide="chevron-down" id="smAcademicMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="smAcademicMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('academic.*') ? '' : 'hidden' }}">
                    @if($hasPerm('academic.dashboard'))
                    <a href="{{ route('academic.dashboard') }}"
                       class="sb-child {{ request()->routeIs('academic.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.class-teachers'))
                    <a href="{{ route('academic.class-teachers.index') }}"
                       class="sb-child {{ request()->routeIs('academic.class-teachers.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Class Teachers</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.class-sections'))
                    <a href="{{ route('academic.class-sections.index') }}"
                       class="sb-child {{ request()->routeIs('academic.class-sections.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Manage Class Sections</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.mediums'))
                    <a href="{{ route('academic.mediums.index') }}"
                       class="sb-child {{ request()->routeIs('academic.mediums.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Manage Medium</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.subjects'))
                    <a href="{{ route('academic.subjects.index') }}"
                       class="sb-child {{ request()->routeIs('academic.subjects.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Subjects</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.subjects'))
                    <a href="{{ route('academic.assign-subjects.index') }}"
                       class="sb-child {{ request()->routeIs('academic.assign-subjects.*') && !request()->routeIs('academic.assign-subjects-bulk.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Assign Subject</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.subjects'))
                    <a href="{{ route('academic.assign-subjects-bulk.index') }}"
                       class="sb-child {{ request()->routeIs('academic.assign-subjects-bulk.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Assign Subject in Bulk</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.subject-types'))
                    <a href="{{ route('academic.subject-types.index') }}"
                       class="sb-child {{ request()->routeIs('academic.subject-types.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Subject Types</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.class-timetable'))
                    <a href="{{ route('academic.class-timetable.index') }}"
                       class="sb-child {{ request()->routeIs('academic.class-timetable.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Class Timetable</span>
                    </a>
                    @endif
                    <a href="{{ route('academic.attendance.index') }}"
                       class="sb-child {{ request()->routeIs('academic.attendance.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Attendance</span>
                    </a>
                    <a href="{{ route('academic.student-leaves.index') }}"
                       class="sb-child {{ request()->routeIs('academic.student-leaves.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Student Leaves</span>
                    </a>
                    @if($hasPerm('academic.study-materials'))
                    <a href="{{ route('academic.study-materials.index') }}"
                       class="sb-child {{ request()->routeIs('academic.study-materials.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Study Materials</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.noticeboards'))
                    <a href="{{ route('academic.noticeboards.index') }}"
                       class="sb-child {{ request()->routeIs('academic.noticeboards.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Notice Board</span>
                    </a>
                    @endif
                    @if($hasPerm('academic.homework'))
                    <a href="{{ route('academic.homework.index') }}"
                       class="sb-child {{ request()->routeIs('academic.homework.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Homework</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if($showExamination && ($hasPerm('examination.dashboard') || $hasPerm('examination.exams') || $hasPerm('examination.groups') || $hasPerm('examination.admit-cards') || $hasPerm('examination.results')))
            <div style="margin-top: 12px;">
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>
                <button type="button" id="examinationMenuToggle" class="sb-parent {{ request()->routeIs('examination.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Examination</span>
                    </div>
                    <i data-lucide="chevron-down" id="examinationMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>
                <div id="examinationMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('examination.*') ? '' : 'hidden' }}">
                    @if($hasPerm('examination.dashboard'))
                    <a href="{{ route('examination.dashboard') }}" class="sb-child {{ request()->routeIs('examination.dashboard') ? 'active' : '' }}"><span class="sidebar-text">Dashboard</span></a>
                    @endif
                    @if($hasPerm('examination.exams'))
                    <a href="{{ route('examination.exams.index') }}" class="sb-child {{ request()->routeIs('examination.exams.*') ? 'active' : '' }}"><span class="sidebar-text">Manage Exams</span></a>
                    @endif

                    @if($hasPerm('examination.admit-cards'))
                    <a href="{{ route('examination.admit-cards.index') }}" class="sb-child {{ request()->routeIs('examination.admit-cards.*') ? 'active' : '' }}"><span class="sidebar-text">Admit Cards</span></a>
                    @endif
                    @if($hasPerm('examination.results'))
                    <a href="{{ route('examination.results.index') }}" class="sb-child {{ request()->routeIs('examination.results.*') ? 'active' : '' }}"><span class="sidebar-text">Exam Results</span></a>
                    @endif
                </div>
            </div>
            @endif

            @if($hasPerm('sm-student.dashboard') || $hasPerm('sm-student.students') || $hasPerm('sm-student.print-id-cards') || $hasPerm('sm-student.promote') || $hasPerm('sm-student.transfer'))
            <!-- ── SM Student ── -->
            <div style="margin-top: 12px;">
                <!-- divider -->
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>

                <button type="button" id="smStudentMenuToggle" class="sb-parent {{ request()->routeIs('sm-student.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="users" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Student</span>
                    </div>
                    <i data-lucide="chevron-down" id="smStudentMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="smStudentMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('sm-student.*') ? '' : 'hidden' }}">
                    @if($hasPerm('sm-student.dashboard'))
                    <a href="{{ route('sm-student.dashboard') }}"
                       class="sb-child {{ request()->routeIs('sm-student.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-student.students'))
                    <a href="{{ route('sm-student.students.index') }}"
                       class="sb-child {{ request()->routeIs('sm-student.students.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Students</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-student.print-id-cards'))
                    <a href="{{ route('sm-student.print-id-cards.index') }}"
                       class="sb-child {{ request()->routeIs('sm-student.print-id-cards.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Print ID Cards</span>
                    </a>
                    @endif
                    <a href="#" class="sb-child opacity-50 cursor-not-allowed">
                        <span class="sidebar-text">ID Card Layout</span>
                    </a>
                    @if($hasPerm('sm-student.promote'))
                    <a href="{{ route('sm-student.promote.index') }}"
                       class="sb-child {{ request()->routeIs('sm-student.promote.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Promote Students</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-student.transfer'))
                    <a href="{{ route('sm-student.transfer.index') }}"
                       class="sb-child {{ request()->routeIs('sm-student.transfer.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Transfer Student</span>
                    </a>
                    @endif
                    <a href="{{ route('sm-student.student-logins.index') }}"
                       class="sb-child {{ request()->routeIs('sm-student.student-logins.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Student Login</span>
                    </a>
                </div>
            </div>
            @endif

            @if($hasPerm('sm-administrator.dashboard') || $hasPerm('sm-administrator.admins') || $hasPerm('sm-administrator.roles') || $hasPerm('sm-administrator.staff') || $hasPerm('sm-administrator.attendance') || $hasPerm('sm-administrator.leaves') || $hasPerm('sm-administrator.id-cards') || $hasPerm('sm-administrator.payroll') || $hasPerm('sm-administrator.users') || $hasPerm('sm-administrator.live-classes'))
            <!-- ── SM Administrator ── -->
            <div style="margin-top: 12px;">
                <!-- divider -->
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>

                <button type="button" id="smAdminMenuToggle" class="sb-parent {{ request()->routeIs('sm-administrator.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="shield-check" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Administrator</span>
                    </div>
                    <i data-lucide="chevron-down" id="smAdminMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="smAdminMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('sm-administrator.*') ? '' : 'hidden' }}">
                    @if($hasPerm('sm-administrator.dashboard'))
                    <a href="{{ route('sm-administrator.dashboard') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.admins'))
                    <a href="{{ route('sm-administrator.admins.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.admins.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Admins</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.roles'))
                    <a href="{{ route('sm-administrator.roles.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.roles.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Roles</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.staff'))
                    <a href="{{ route('sm-administrator.staff.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.staff.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Staff List</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.attendance'))
                    <a href="{{ route('sm-administrator.attendance.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.attendance.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Staff Attendance</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.leaves'))
                    <a href="{{ route('sm-administrator.leaves.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.leaves.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Staff Leaves</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.id-cards'))
                    <a href="{{ route('sm-administrator.id-cards.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.id-cards.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Staff ID Cards</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.payroll'))
                    <a href="{{ route('sm-administrator.payroll.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.payroll.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Staff Payroll</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.users'))
                    <a href="{{ route('sm-administrator.users.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.users.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Users</span>
                    </a>
                    @endif
                    @if($hasPerm('sm-administrator.live-classes'))
                    <a href="{{ route('sm-administrator.live-classes.index') }}"
                       class="sb-child {{ request()->routeIs('sm-administrator.live-classes.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Live Class Settings</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if($showWebsiteSettings && ($guard === 'super_admin' || $guard === 'staff'))
            <!-- ── Website Settings ── -->
            <div style="margin-top: 12px;">
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>
                <button type="button" id="websiteSettingsMenuToggle" class="sb-parent {{ request()->routeIs('website-settings.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="layout" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Website Settings</span>
                    </div>
                    <i data-lucide="chevron-down" id="websiteSettingsMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="websiteSettingsMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('website-settings.*') ? '' : 'hidden' }}">
                    <a href="{{ route('website-settings.header') }}"
                       class="sb-child {{ request()->routeIs('website-settings.header') ? 'active' : '' }}">
                        <span class="sidebar-text">Header</span>
                    </a>
                    <a href="{{ route('website-settings.homepage') }}"
                       class="sb-child {{ request()->routeIs('website-settings.homepage') ? 'active' : '' }}">
                        <span class="sidebar-text">Home Page</span>
                    </a>
                    <a href="{{ route('website-settings.footer') }}"
                       class="sb-child {{ request()->routeIs('website-settings.footer') ? 'active' : '' }}">
                        <span class="sidebar-text">Footer</span>
                    </a>
                    <a href="{{ route('website-settings.pages.index') }}"
                       class="sb-child {{ request()->routeIs('website-settings.pages.*') ? 'active' : '' }}">
                        <span class="sidebar-text">Pages</span>
                    </a>
                </div>
            </div>
            @endif

            @if($showAccounting && ($hasPerm('accounting.dashboard') || $hasPerm('accounting.fee-types') || $hasPerm('accounting.fee-invoices') || $hasPerm('accounting.collect-payment') || $hasPerm('accounting.bulk-invoice-print')))
            <div style="margin-top: 12px;">
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>
                <button type="button" id="accountingMenuToggle" class="sb-parent {{ request()->routeIs('accounting.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="wallet-cards" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Accounting</span>
                    </div>
                    <i data-lucide="chevron-down" id="accountingMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>
                <div id="accountingMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('accounting.*') ? '' : 'hidden' }}">
                    @if($hasPerm('accounting.dashboard'))
                    <a href="{{ route('accounting.dashboard') }}" class="sb-child {{ request()->routeIs('accounting.dashboard') ? 'active' : '' }}"><span class="sidebar-text">Dashboard</span></a>
                    @endif
                    @if($hasPerm('accounting.fee-types'))
                    <a href="{{ route('accounting.fee-types.index') }}" class="sb-child {{ request()->routeIs('accounting.fee-types.*') ? 'active' : '' }}"><span class="sidebar-text">Fee Types</span></a>
                    @endif
                    @if($hasPerm('accounting.fee-invoices'))
                    <a href="{{ route('accounting.fee-invoices.index') }}" class="sb-child {{ request()->routeIs('accounting.fee-invoices.*') ? 'active' : '' }}"><span class="sidebar-text">Fee Invoices</span></a>
                    @endif
                    <a href="{{ route('accounting.transport-invoices.index') }}" class="sb-child {{ request()->routeIs('accounting.transport-invoices.*') ? 'active' : '' }}"><span class="sidebar-text">Transport Invoices</span></a>
                    @if($hasPerm('accounting.fee-invoices'))
                    @if($hasPerm('accounting.collect-payment'))
                    <a href="{{ route('accounting.collect-payment.index') }}" class="sb-child {{ request()->routeIs('accounting.collect-payment.*') ? 'active' : '' }}"><span class="sidebar-text">Collect Payment</span></a>
                    @endif
                    @endif

                </div>
            </div>
            @endif
            @if($showTransport && ($hasPerm('transport.dashboard') || $hasPerm('transport.vehicles') || $hasPerm('transport.routes') || $hasPerm('transport.report')))
            {{-- ── SM Transport ── --}}
            <div style="margin-top: 12px;">
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>

                <button type="button" id="transportMenuToggle" class="sb-parent {{ request()->routeIs('transport.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="bus" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Transport</span>
                    </div>
                    <i data-lucide="chevron-down" id="transportMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="transportMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('transport.*') ? '' : 'hidden' }}">
                    <a href="{{ route('transport.dashboard') }}" class="sb-child {{ request()->routeIs('transport.dashboard') ? 'active' : '' }}"><span class="sidebar-text">Dashboard</span></a>
                    <a href="{{ route('transport.vehicles.index') }}" class="sb-child {{ request()->routeIs('transport.vehicles.*') ? 'active' : '' }}"><span class="sidebar-text">Vehicles</span></a>
                    <a href="{{ route('transport.routes.index') }}" class="sb-child {{ request()->routeIs('transport.routes.*') ? 'active' : '' }}"><span class="sidebar-text">Routes</span></a>
                    <a href="{{ route('transport.assign-students.index') }}" class="sb-child {{ request()->routeIs('transport.assign-students.*') ? 'active' : '' }}"><span class="sidebar-text">Assign Students</span></a>
                    <a href="{{ route('transport.report') }}" class="sb-child {{ request()->routeIs('transport.report') ? 'active' : '' }}"><span class="sidebar-text">Report</span></a>
                </div>
            </div>
            @endif


            @if($showLibrary && ($hasPerm('library.dashboard') || $hasPerm('library.books') || $hasPerm('library.book-issue') || $hasPerm('library.library-cards')))
            {{-- ── SM Library ── --}}
            <div style="margin-top: 12px;">
                <div class="px-3 py-1" style="border-top: 1px solid var(--sb-border); margin-bottom: 4px;"></div>

                <button type="button" id="libraryMenuToggle" class="sb-parent {{ request()->routeIs('library.*') ? 'sb-school-active' : '' }}">
                    <div class="flex items-center min-w-0">
                        <i data-lucide="book-open" class="w-4 h-4 mr-2.5 flex-shrink-0 opacity-80"></i>
                        <span class="sidebar-text truncate text-xs font-semibold uppercase tracking-wider">Library</span>
                    </div>
                    <i data-lucide="chevron-down" id="libraryMenuIcon" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 sidebar-text ml-1 opacity-60"></i>
                </button>

                <div id="libraryMenu" class="pl-6 pr-1 py-0.5 space-y-0.5 {{ request()->routeIs('library.*') ? '' : 'hidden' }}">
                    <a href="{{ route('library.dashboard') }}"
                       class="sb-child {{ request()->routeIs('library.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    <a href="{{ route('library.books') }}"
                       class="sb-child {{ request()->routeIs('library.books*') ? 'active' : '' }}">
                        <span class="sidebar-text">All Books</span>
                    </a>
                    <a href="{{ route('library.book-issue') }}"
                       class="sb-child {{ request()->routeIs('library.book-issue*') ? 'active' : '' }}">
                        <span class="sidebar-text">Book Issue</span>
                    </a>
                    <a href="{{ route('library.library-cards') }}"
                       class="sb-child {{ request()->routeIs('library.library-cards*') ? 'active' : '' }}">
                        <span class="sidebar-text">Library Cards</span>
                    </a>
                </div>
            </div>
            @endif

        </nav>

        <!-- Resize Handle -->
        <div id="resizer" class="absolute top-0 right-0 h-full z-50"></div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">

        <!-- Top Navbar -->
        <header class="h-16 flex items-center justify-between px-6 z-10 flex-shrink-0"
                style="background:#FFFCF7; border-bottom: 1px solid #DED5CB; box-shadow: 0 1px 3px rgba(33,27,23,.06);">
            <div class="flex items-center">
                <button id="globalSidebarToggle" class="mr-4 focus:outline-none" style="color:#766C63;">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h2 class="text-lg font-semibold" style="color:#211B17;">
                    @yield('header', 'Dashboard')
                </h2>
            </div>

            <div class="flex items-center space-x-3">
                @php
                    $globalCurrentSessionId = \App\Models\SystemSetting::getSetting('current_session_id');
                    $currentSessionId = session('selected_session_id') ?: $globalCurrentSessionId;
                    $currentSession   = $currentSessionId ? \App\Models\AcademicSession::find($currentSessionId) : null;
                @endphp
                @if($currentSession)
                    <span class="hidden md:inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                          style="background:#f5ece0; color:#211B17; border: 1px solid #DED5CB;">
                        <i data-lucide="calendar" class="w-3 h-3 mr-1.5" style="color:#B47A42"></i>
                        {{ $currentSession->name }}
                    </span>
                @endif
                @php $authGuard = session('auth_guard'); @endphp
                @if($authGuard === 'staff' || $authGuard === 'school_user')
                <a href="{{ route('staff.dashboard') }}" class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                   style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    My Dashboard
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                            style="background:#fef0f0; color:#B4232A; border:1px solid #f5c6c8;">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span class="hidden md:inline">Logout</span>
                    </button>
                </form>
                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-sm font-bold"
                     style="background:#B47A42;">{{ strtoupper(substr(session('super_admin_data.name', session('auth_guard', 'A')), 0, 1)) }}</div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content-area flex-1 overflow-auto p-6" style="background:#F5F1EB;">

            @if(session('success'))
                <div class="mb-5 p-4 rounded-lg flex items-start shadow-sm"
                     style="background:#eef7f0; border-left: 4px solid #4F7D5A;">
                    <i data-lucide="check-circle" class="h-5 w-5 flex-shrink-0" style="color:#4F7D5A;"></i>
                    <p class="ml-3 text-sm font-medium" style="color:#2d5434;">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-4 rounded-lg flex items-start shadow-sm"
                     style="background:#fef0f0; border-left: 4px solid #B4232A;">
                    <i data-lucide="alert-circle" class="h-5 w-5 flex-shrink-0" style="color:#B4232A;"></i>
                    <p class="ml-3 text-sm font-medium" style="color:#7a181c;">{{ session('error') }}</p>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        lucide.createIcons();

        // ── School Management Submenu ──
        const schoolMenuToggle = document.getElementById('schoolMenuToggle');
        const schoolMenu       = document.getElementById('schoolMenu');
        const schoolMenuIcon   = document.getElementById('schoolMenuIcon');
        if (schoolMenuToggle && schoolMenu && schoolMenuIcon) {
            schoolMenuToggle.addEventListener('click', () => {
                schoolMenu.classList.toggle('hidden');
                schoolMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── School (SM) Submenu ──
        const smSchoolMenuToggle = document.getElementById('smSchoolMenuToggle');
        const smSchoolMenu       = document.getElementById('smSchoolMenu');
        const smSchoolMenuIcon   = document.getElementById('smSchoolMenuIcon');
        if (smSchoolMenuToggle && smSchoolMenu && smSchoolMenuIcon) {
            smSchoolMenuToggle.addEventListener('click', () => {
                smSchoolMenu.classList.toggle('hidden');
                smSchoolMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── SM Academic Submenu ──
        const smAcademicMenuToggle = document.getElementById('smAcademicMenuToggle');
        const smAcademicMenu       = document.getElementById('smAcademicMenu');
        const smAcademicMenuIcon   = document.getElementById('smAcademicMenuIcon');
        if (smAcademicMenuToggle && smAcademicMenu && smAcademicMenuIcon) {
            smAcademicMenuToggle.addEventListener('click', () => {
                smAcademicMenu.classList.toggle('hidden');
                smAcademicMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── Examination Submenu ──
        const examinationMenuToggle = document.getElementById('examinationMenuToggle');
        const examinationMenu       = document.getElementById('examinationMenu');
        const examinationMenuIcon   = document.getElementById('examinationMenuIcon');
        if (examinationMenuToggle && examinationMenu && examinationMenuIcon) {
            examinationMenuToggle.addEventListener('click', () => {
                examinationMenu.classList.toggle('hidden');
                examinationMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── SM Student Submenu ──
        const smStudentMenuToggle = document.getElementById('smStudentMenuToggle');
        const smStudentMenu       = document.getElementById('smStudentMenu');
        const smStudentMenuIcon   = document.getElementById('smStudentMenuIcon');
        if (smStudentMenuToggle && smStudentMenu && smStudentMenuIcon) {
            smStudentMenuToggle.addEventListener('click', () => {
                smStudentMenu.classList.toggle('hidden');
                smStudentMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── SM Administrator Submenu ──
        const smAdminMenuToggle = document.getElementById('smAdminMenuToggle');
        const smAdminMenu       = document.getElementById('smAdminMenu');
        const smAdminMenuIcon   = document.getElementById('smAdminMenuIcon');
        if (smAdminMenuToggle && smAdminMenu && smAdminMenuIcon) {
            smAdminMenuToggle.addEventListener('click', () => {
                smAdminMenu.classList.toggle('hidden');
                smAdminMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── Accounting Submenu ──
        const accountingMenuToggle = document.getElementById('accountingMenuToggle');
        const accountingMenu       = document.getElementById('accountingMenu');
        const accountingMenuIcon   = document.getElementById('accountingMenuIcon');
        if (accountingMenuToggle && accountingMenu && accountingMenuIcon) {
            accountingMenuToggle.addEventListener('click', () => {
                accountingMenu.classList.toggle('hidden');
                accountingMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── Library Submenu ──
        const libraryMenuToggle = document.getElementById('libraryMenuToggle');
        const libraryMenu       = document.getElementById('libraryMenu');
        const libraryMenuIcon   = document.getElementById('libraryMenuIcon');
        if (libraryMenuToggle && libraryMenu && libraryMenuIcon) {
            libraryMenuToggle.addEventListener('click', () => {
                libraryMenu.classList.toggle('hidden');
                libraryMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── Transport Submenu ──
        const transportMenuToggle = document.getElementById('transportMenuToggle');
        const transportMenu       = document.getElementById('transportMenu');
        const transportMenuIcon   = document.getElementById('transportMenuIcon');
        if (transportMenuToggle && transportMenu && transportMenuIcon) {
            transportMenuToggle.addEventListener('click', () => {
                transportMenu.classList.toggle('hidden');
                transportMenuIcon.classList.toggle('rotate-180');
            });
        }

        const websiteSettingsMenuToggle = document.getElementById('websiteSettingsMenuToggle');
        const websiteSettingsMenu       = document.getElementById('websiteSettingsMenu');
        const websiteSettingsMenuIcon   = document.getElementById('websiteSettingsMenuIcon');
        if (websiteSettingsMenuToggle && websiteSettingsMenu && websiteSettingsMenuIcon) {
            websiteSettingsMenuToggle.addEventListener('click', () => {
                websiteSettingsMenu.classList.toggle('hidden');
                websiteSettingsMenuIcon.classList.toggle('rotate-180');
            });
        }

        // ── Sidebar Resizer ──
        const sidebar  = document.getElementById('sidebar');
        const resizer  = document.getElementById('resizer');
        let isResizing = false;
        let isSidebarCollapsed = false;
        let previousWidth = parseInt(sidebar.style.width) || 224;

        if (resizer && sidebar) {
            resizer.addEventListener('mousedown', () => {
                if (isSidebarCollapsed) return;
                isResizing = true;
                resizer.classList.add('active');
                document.body.style.userSelect = 'none';
                document.body.style.cursor = 'ew-resize';
            });
            document.addEventListener('mousemove', (e) => {
                if (!isResizing) return;
                sidebar.style.width = Math.min(Math.max(e.clientX, 180), 420) + 'px';
                previousWidth = parseInt(sidebar.style.width);
            });
            document.addEventListener('mouseup', () => {
                if (isResizing) {
                    isResizing = false;
                    resizer.classList.remove('active');
                    document.body.style.userSelect = '';
                    document.body.style.cursor = 'default';
                }
            });
        }

        // ── Global Sidebar Toggle ──
        const globalSidebarToggle = document.getElementById('globalSidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        if (globalSidebarToggle && sidebar) {
            globalSidebarToggle.addEventListener('click', () => {
                isSidebarCollapsed = !isSidebarCollapsed;
                if (isSidebarCollapsed) {
                    sidebarTexts.forEach(el => el.style.opacity = '0');
                    sidebar.style.minWidth = '0px';
                    sidebar.style.width = '48px';
                    setTimeout(() => sidebarTexts.forEach(el => el.style.display = 'none'), 200);
                } else {
                    sidebarTexts.forEach(el => el.style.display = '');
                    sidebar.style.width = previousWidth + 'px';
                    sidebar.style.minWidth = '200px';
                    setTimeout(() => sidebarTexts.forEach(el => el.style.opacity = '1'), 50);
                }
            });
        }

        // ══════════════════════════════════════════════════════════════════
        // ── UNIVERSAL BULK SELECT & DELETE SYSTEM ──
        // ══════════════════════════════════════════════════════════════════
        function toggleSelectAll(cb) {
            const table = cb.closest('table') || document;
            table.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.checked = cb.checked;
                r.closest('tr')?.classList.toggle('bg-red-50', cb.checked);
            });
            updateBulkToolbar();
        }

        function updateBulkToolbar() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const all     = document.querySelectorAll('.row-checkbox');
            const toolbars = document.querySelectorAll('#bulkToolbar, .bulk-toolbar');
            const countEls = document.querySelectorAll('#selectedCount, .selected-count');
            const selectAlls = document.querySelectorAll('#selectAll, #selectAllCb, .select-all-checkbox');

            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.closest('tr')?.classList.toggle('bg-red-50', r.checked);
            });

            if (checked.length > 0) {
                toolbars.forEach(tb => tb.classList.remove('hidden'));
                countEls.forEach(el => el.textContent = checked.length + ' item(s) selected');
            } else {
                toolbars.forEach(tb => tb.classList.add('hidden'));
            }

            selectAlls.forEach(sa => {
                if (sa) {
                    sa.indeterminate = checked.length > 0 && checked.length < all.length;
                    sa.checked = all.length > 0 && checked.length === all.length;
                }
            });
        }

        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.checked = false;
                r.closest('tr')?.classList.remove('bg-red-50');
            });
            document.querySelectorAll('#selectAll, #selectAllCb, .select-all-checkbox').forEach(sa => {
                if (sa) { sa.checked = false; sa.indeterminate = false; }
            });
            document.querySelectorAll('#bulkToolbar, .bulk-toolbar').forEach(tb => tb.classList.add('hidden'));
        }

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return false;
            if (!confirm('Are you sure you want to permanently delete ' + checked.length + ' selected item(s)?')) return false;
            const container = document.getElementById('bulkIdsContainer') || document.getElementById('bulkDeleteInputs');
            if (container) {
                container.innerHTML = '';
                checked.forEach(function(cb) {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'ids[]';
                    inp.value = cb.value;
                    container.appendChild(inp);
                });
            }
            return true;
        }

        window.toggleSelectAll   = toggleSelectAll;
        window.updateBulkToolbar = updateBulkToolbar;
        window.clearSelection    = clearSelection;
        window.confirmBulkDelete = confirmBulkDelete;

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.row-checkbox').forEach(function(cb) {
                cb.addEventListener('change', updateBulkToolbar);
            });
            document.querySelectorAll('#selectAll, #selectAllCb, .select-all-checkbox').forEach(function(sa) {
                sa.addEventListener('change', function() { toggleSelectAll(this); });
            });
        });
    </script>
</body>
</html>
