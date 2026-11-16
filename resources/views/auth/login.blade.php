<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Access Portal – EdOrbit School Management</title>
    <meta name="description" content="EdOrbit Multi-School Management System Live Demo">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: #140d2b;
            color: #1e293b;
            position: relative;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ── Rich Dashboard Mockup Background (Matches reference image) ── */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
            background: radial-gradient(circle at 50% 20%, rgba(99, 102, 241, 0.25) 0%, rgba(20, 13, 43, 0.98) 70%),
                        linear-gradient(135deg, #180e33 0%, #0d0720 100%);
        }

        /* Ambient glowing orbs */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
        }
        .glow-orb-1 { width: 500px; height: 500px; background: #6366f1; top: -100px; left: 10%; }
        .glow-orb-2 { width: 450px; height: 450px; background: #8b5cf6; bottom: -80px; right: 15%; }
        .glow-orb-3 { width: 350px; height: 350px; background: #ec4899; top: 40%; right: 5%; opacity: 0.25; }

        /* Simulated Monitor & Screen Mockups in Background */
        .mockup-grid {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.22;
            transform: scale(0.96);
            filter: saturate(1.2) contrast(1.1);
        }

        .mockup-desktop {
            width: 1080px;
            height: 640px;
            background: linear-gradient(145deg, #2a1b54, #1b103c);
            border: 2px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
            display: flex;
            overflow: hidden;
            position: relative;
        }

        .mockup-sidebar {
            width: 220px;
            background: rgba(15, 9, 32, 0.85);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .mockup-bar {
            height: 12px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
        }
        .mockup-bar.short { width: 60%; }
        .mockup-bar.med { width: 80%; }

        .mockup-main {
            flex: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .mockup-header-bar {
            height: 48px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
        }

        .mockup-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .mockup-card {
            height: 90px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .mockup-charts {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            flex: 1;
        }

        .mockup-chart-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 1rem;
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        .mockup-bar-col {
            flex: 1;
            background: linear-gradient(to top, #6366f1, #818cf8);
            border-radius: 4px;
            opacity: 0.6;
        }

        .mockup-donut {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 20px solid #6366f1;
            border-right-color: #ec4899;
            border-bottom-color: #10b981;
            margin: auto;
            opacity: 0.55;
        }

        /* ── Foreground Center Portal ── */
        .portal-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .portal-card {
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            padding: 2.25rem 2.5rem;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.9);
            position: relative;
        }

        /* Top Logo & Header */
        .portal-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-icon-wrap {
            width: 68px;
            height: 68px;
            margin: 0 auto 0.75rem auto;
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .portal-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .portal-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.4;
        }

        /* ── Two Big Demo Action Buttons (Super Admin & School Admin) ── */
        .demo-roles-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .demo-role-btn {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.875rem 0.75rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.4rem;
            user-select: none;
            position: relative;
            outline: none;
        }

        .demo-role-btn:hover {
            border-color: #a5b4fc;
            background: #f5f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .demo-role-btn.active {
            border-color: #4f46e5;
            background: #eef2ff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18),
                        0 6px 16px rgba(79, 70, 229, 0.15);
        }

        .role-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }

        .demo-role-btn.active .role-icon-box {
            transform: scale(1.08);
            border-color: #c7d2fe;
            background: #ffffff;
        }

        .role-btn-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1e293b;
        }

        .demo-role-btn.active .role-btn-title {
            color: #4338ca;
        }

        .role-btn-badge {
            font-size: 0.6875rem;
            font-weight: 600;
            color: #64748b;
            background: rgba(0, 0, 0, 0.04);
            padding: 2px 7px;
            border-radius: 999px;
            line-height: 1.2;
        }

        .demo-role-btn.active .role-btn-badge {
            background: #c7d2fe;
            color: #3730a3;
        }

        /* Checkmark indicator for selected role */
        .check-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #4f46e5;
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .demo-role-btn.active .check-badge {
            display: flex;
        }

        /* ── School Switcher (Shown when School Admin is selected) ── */
        .school-select-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 0.875rem;
            margin-bottom: 1.25rem;
            animation: fadeIn 0.25s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .school-select-label {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .school-select-dropdown {
            width: 100%;
            padding: 0.55rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1e293b;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .school-select-dropdown:focus {
            border-color: #4f46e5;
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            margin: 1.25rem 0;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 0.875rem;
        }

        /* ── Form Inputs ── */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-input {
            width: 100%;
            height: 44px;
            padding: 0 1rem 0 2.6rem;
            font-size: 0.875rem;
            font-family: inherit;
            color: #0f172a;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .pass-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .pass-toggle-btn:hover {
            color: #475569;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            font-size: 0.8125rem;
            color: #475569;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox {
            width: 16px;
            height: 16px;
            accent-color: #4f46e5;
            cursor: pointer;
            border-radius: 4px;
        }

        /* ── Big Purple Log In Button ── */
        .btn-login-submit {
            width: 100%;
            height: 48px;
            background: #4f46e5;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            font-family: inherit;
        }

        .btn-login-submit:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }

        .btn-login-submit:active {
            transform: translateY(0);
        }

        /* Alert message */
        .login-alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.8125rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .login-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .login-alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        /* Active Credential Info Pill */
        .credential-status {
            margin-top: 0.875rem;
            padding: 0.6rem 0.875rem;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 0.75rem;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            line-height: 1.4;
        }
        .credential-status strong {
            color: #1e293b;
        }

        /* ── Footer Below Card ── */
        .portal-footer {
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.65);
        }

        .portal-footer a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .portal-footer a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .footer-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 520px) {
            .portal-card {
                padding: 1.75rem 1.25rem;
            }
            .demo-roles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Background Dashboard Silhouette (Matches reference image) -->
    <div class="bg-canvas">
        <div class="glow-orb glow-orb-1"></div>
        <div class="glow-orb glow-orb-2"></div>
        <div class="glow-orb glow-orb-3"></div>

        <div class="mockup-grid">
            <div class="mockup-desktop">
                <div class="mockup-sidebar">
                    <div class="mockup-bar" style="height:24px; width:70%; margin-bottom:1rem; background:rgba(255,255,255,0.25);"></div>
                    <div class="mockup-bar short"></div>
                    <div class="mockup-bar med"></div>
                    <div class="mockup-bar"></div>
                    <div class="mockup-bar med"></div>
                    <div class="mockup-bar short"></div>
                    <div class="mockup-bar" style="margin-top:auto;"></div>
                </div>
                <div class="mockup-main">
                    <div class="mockup-header-bar">
                        <div class="mockup-bar" style="width:160px;"></div>
                        <div class="mockup-bar short" style="width:90px;"></div>
                    </div>
                    <div class="mockup-cards">
                        <div class="mockup-card"></div>
                        <div class="mockup-card"></div>
                        <div class="mockup-card"></div>
                        <div class="mockup-card"></div>
                    </div>
                    <div class="mockup-charts">
                        <div class="mockup-chart-box">
                            <div class="mockup-bar-col" style="height: 40%;"></div>
                            <div class="mockup-bar-col" style="height: 75%;"></div>
                            <div class="mockup-bar-col" style="height: 55%;"></div>
                            <div class="mockup-bar-col" style="height: 90%;"></div>
                            <div class="mockup-bar-col" style="height: 60%;"></div>
                            <div class="mockup-bar-col" style="height: 85%;"></div>
                            <div class="mockup-bar-col" style="height: 95%;"></div>
                        </div>
                        <div class="mockup-chart-box" style="display:flex; align-items:center; justify-content:center;">
                            <div class="mockup-donut"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Floating Portal Card -->
    <div class="portal-wrapper">
        <div class="portal-card">

            <!-- Card Header & App Logo -->
            <div class="portal-header">
                <div class="logo-icon-wrap">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <h1 class="portal-title">Demo Access Portal</h1>
                <p class="portal-subtitle">Choose a demo account below for instant 1-click access</p>
            </div>

            <!-- Error / Success Alert -->
            @if(session('error'))
                <div class="login-alert login-alert-error">
                    <i data-lucide="alert-circle" style="width:16px; height:16px; flex-shrink:0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if(session('success'))
                <div class="login-alert login-alert-success">
                    <i data-lucide="check-circle-2" style="width:16px; height:16px; flex-shrink:0;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- ── ONLY 2 BUTTONS: Super Admin & School Admin (as explicitly requested) ── -->
            <div class="demo-roles-grid">
                <!-- 1. Super Admin Button -->
                <button type="button" class="demo-role-btn active" id="btnSuperAdmin" onclick="selectRole('super_admin')">
                    <div class="check-badge">
                        <i data-lucide="check" style="width:11px; height:11px;"></i>
                    </div>
                    <div class="role-icon-box">👑</div>
                    <span class="role-btn-title">Super Admin</span>
                    <span class="role-btn-badge">All 6 Schools Access</span>
                </button>

                <!-- 2. School Admin Button -->
                <button type="button" class="demo-role-btn" id="btnSchoolAdmin" onclick="selectRole('school_admin')">
                    <div class="check-badge">
                        <i data-lucide="check" style="width:11px; height:11px;"></i>
                    </div>
                    <div class="role-icon-box">🏫</div>
                    <span class="role-btn-title">School Admin</span>
                    <span class="role-btn-badge">Single School View</span>
                </button>
            </div>

            <!-- School Admin Selector (visible when School Admin role is chosen) -->
            <div class="school-select-panel" id="schoolSelectPanel" style="display: none;">
                <div class="school-select-label">
                    <span>Select School for Demo:</span>
                    <span style="color:#4f46e5; font-size:0.625rem;">6 Schools Available</span>
                </div>
                <select class="school-select-dropdown" id="schoolDropdown" onchange="switchSchoolAdmin(this.value)">
                    <option value="dps" data-user="admin@delhipublic.edu" data-school-id="1" selected>
                        1. Delhi Public Global School, New Delhi (CBSE)
                    </option>
                    <option value="xavier" data-user="admin@stxaviers.edu" data-school-id="2">
                        2. St. Xavier's International Academy, Mumbai (ICSE)
                    </option>
                    <option value="heritage" data-user="admin@heritagevalley.edu" data-school-id="3">
                        3. The Heritage Valley School, Bengaluru (CBSE/IB)
                    </option>
                    <option value="oakridge" data-user="admin@oakridge.edu" data-school-id="4">
                        4. Oakridge International School, Hyderabad (Cambridge)
                    </option>
                    <option value="silverbells" data-user="admin@silverbells.edu" data-school-id="5">
                        5. Silver Bells High School, Ahmedabad (CBSE)
                    </option>
                    <option value="greenwood" data-user="admin@greenwood.edu" data-school-id="6">
                        6. Greenwood Convent School, Jaipur (CBSE)
                    </option>
                </select>
            </div>

            <div class="divider">
                <span>or enter credentials</span>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                @csrf
                <input type="hidden" name="demo_role" id="demoRoleInput" value="super_admin">
                <input type="hidden" name="school_id" id="schoolIdInput" value="1">

                <div class="form-group">
                    <label class="form-label" for="login">Username or Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <i data-lucide="mail" style="width:16px; height:16px;"></i>
                        </span>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="form-input"
                            placeholder="Username or email address"
                            value="superadmin@edorbit.com"
                            required
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <i data-lucide="lock" style="width:16px; height:16px;"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Password"
                            value="password123"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="pass-toggle-btn" id="togglePassBtn" aria-label="Toggle password visibility">
                            <i data-lucide="eye" id="eyeIcon" style="width:16px; height:16px;"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" class="remember-checkbox" checked>
                        <span>Remember Me</span>
                    </label>
                    <span id="roleAccessScope" style="font-size:0.75rem; color:#4f46e5; font-weight:600;">
                        Full Multi-School Access
                    </span>
                </div>

                <button type="submit" class="btn-login-submit" id="btnSubmit">
                    <span>Log In</span>
                    <i data-lucide="arrow-right" style="width:18px; height:18px;"></i>
                </button>
            </form>

            <!-- Credential Info Pill -->
            <div class="credential-status" id="credentialStatusPill">
                <i data-lucide="shield-check" style="width:15px; height:15px; color:#4f46e5; flex-shrink:0;"></i>
                <div id="credentialStatusText">
                    <strong>Super Admin Mode:</strong> Master oversight of all 6 schools, fees, students & configuration.
                </div>
            </div>

        </div>

        <!-- Footer Links Below Card -->
        <div class="portal-footer">
            <a href="javascript:void(0)" onclick="alert('Demo Mode: Click Super Admin or School Admin above and press Log In. No external database server required!')">
                <i data-lucide="help-circle" style="width:14px; height:14px;"></i>
                Help Document
            </a>
            <span class="footer-dot"></span>
            <a href="javascript:void(0)" onclick="selectRole('super_admin')">
                <i data-lucide="layers" style="width:14px; height:14px;"></i>
                Super Admin
            </a>
            <span class="footer-dot"></span>
            <a href="javascript:void(0)" onclick="selectRole('school_admin')">
                <i data-lucide="building-2" style="width:14px; height:14px;"></i>
                School Admin
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // ── Credentials Presets ──
        const CREDENTIALS = {
            super_admin: {
                username: 'superadmin@edorbit.com',
                password: 'password123',
                scopeText: 'Full Multi-School Access',
                statusHtml: '<strong>Super Admin Mode:</strong> Master oversight across all 6 schools, aggregate fees, students & system settings.'
            },
            school_admin: {
                username: 'admin@delhipublic.edu',
                password: 'password123',
                scopeText: 'Single School Scoped Access',
                statusHtml: '<strong>School Admin Mode:</strong> Restricted strictly to viewing and managing this specific school.'
            }
        };

        const btnSuper = document.getElementById('btnSuperAdmin');
        const btnSchool = document.getElementById('btnSchoolAdmin');
        const schoolSelectPanel = document.getElementById('schoolSelectPanel');
        const schoolDropdown = document.getElementById('schoolDropdown');
        const loginInput = document.getElementById('login');
        const passInput = document.getElementById('password');
        const demoRoleInput = document.getElementById('demoRoleInput');
        const schoolIdInput = document.getElementById('schoolIdInput');
        const roleAccessScope = document.getElementById('roleAccessScope');
        const credentialStatusText = document.getElementById('credentialStatusText');

        // ── Role Selection Handler ──
        function selectRole(role) {
            demoRoleInput.value = role;

            if (role === 'super_admin') {
                btnSuper.classList.add('active');
                btnSchool.classList.remove('active');
                schoolSelectPanel.style.display = 'none';

                loginInput.value = CREDENTIALS.super_admin.username;
                passInput.value = CREDENTIALS.super_admin.password;
                roleAccessScope.innerText = CREDENTIALS.super_admin.scopeText;
                credentialStatusText.innerHTML = CREDENTIALS.super_admin.statusHtml;
                schoolIdInput.value = '1';
            } else {
                btnSchool.classList.add('active');
                btnSuper.classList.remove('active');
                schoolSelectPanel.style.display = 'block';

                // Read selected school admin from dropdown
                const selectedOption = schoolDropdown.options[schoolDropdown.selectedIndex];
                loginInput.value = selectedOption.getAttribute('data-user') || CREDENTIALS.school_admin.username;
                passInput.value = CREDENTIALS.school_admin.password;
                schoolIdInput.value = selectedOption.getAttribute('data-school-id') || '1';
                roleAccessScope.innerText = 'Scoped to ' + selectedOption.text.split('(')[0].trim();
                credentialStatusText.innerHTML = '<strong>School Admin Mode:</strong> Access strictly restricted to <em>' + selectedOption.text.split('(')[0].trim() + '</em>.';
            }

            // Quick focus pulse
            loginInput.focus();
            loginInput.select();
        }

        // ── Switch School Admin from Dropdown ──
        function switchSchoolAdmin(val) {
            const selectedOption = schoolDropdown.options[schoolDropdown.selectedIndex];
            const email = selectedOption.getAttribute('data-user');
            const sId = selectedOption.getAttribute('data-school-id');

            loginInput.value = email;
            passInput.value = 'password123';
            schoolIdInput.value = sId;

            roleAccessScope.innerText = 'Scoped to ' + selectedOption.text.split('(')[0].trim();
            credentialStatusText.innerHTML = '<strong>School Admin Mode:</strong> Access strictly restricted to <em>' + selectedOption.text.split('(')[0].trim() + '</em>.';
        }

        // ── Toggle Password Visibility ──
        const togglePassBtn = document.getElementById('togglePassBtn');
        const eyeIcon = document.getElementById('eyeIcon');
        togglePassBtn.addEventListener('click', () => {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            lucide.createIcons();
        });

        // ── Loading state on submission ──
        document.getElementById('loginForm').addEventListener('submit', () => {
            const btn = document.getElementById('btnSubmit');
            btn.innerHTML = '<svg class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Signing In...';
            btn.style.opacity = '0.85';
            btn.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>
