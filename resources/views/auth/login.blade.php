<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Edu-Verse School Management</title>
    <meta name="description" content="Login to Edu-Verse School Management System">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #1a1208 0%, #2d1f0e 40%, #1a1208 100%);
            overflow: hidden;
        }

        /* Background pattern */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(180,122,66,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(180,122,66,0.1) 0%, transparent 40%);
            pointer-events: none;
        }

        .login-container {
            display: flex; width: 100%; min-height: 100vh;
        }

        /* Left panel – decorative */
        .login-left {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }
        .login-left::after {
            content: '';
            position: absolute; right: 0; top: 0; bottom: 0;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(180,122,66,0.4), transparent);
        }
        .brand-logo {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #B47A42, #D4A055);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 40px rgba(180,122,66,0.4);
            animation: pulse-glow 3s ease-in-out infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 40px rgba(180,122,66,0.4); }
            50% { box-shadow: 0 0 60px rgba(180,122,66,0.6); }
        }
        .brand-name {
            font-size: 2.5rem; font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }
        .brand-tagline {
            color: rgba(232,221,213,0.6);
            font-size: 1rem;
            text-align: center;
            max-width: 300px;
            line-height: 1.6;
        }
        .features {
            margin-top: 3rem;
            display: flex; flex-direction: column; gap: 1rem;
        }
        .feature-item {
            display: flex; align-items: center; gap: 0.75rem;
            color: rgba(232,221,213,0.7);
            font-size: 0.9rem;
        }
        .feature-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #B47A42;
            flex-shrink: 0;
        }

        /* Right panel – form */
        .login-right {
            width: 480px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 3rem 3.5rem;
            background: rgba(255,252,247,0.05);
            backdrop-filter: blur(20px);
        }
        .form-card {
            width: 100%;
        }
        .form-title {
            font-size: 1.75rem; font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .form-subtitle {
            color: rgba(232,221,213,0.55);
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .alert-error {
            background: rgba(180,35,42,0.15);
            border: 1px solid rgba(180,35,42,0.4);
            border-radius: 10px;
            padding: 0.875rem 1rem;
            color: #ff8080;
            font-size: 0.875rem;
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .field-group { margin-bottom: 1.25rem; }
        .field-label {
            display: block;
            color: rgba(232,221,213,0.8);
            font-size: 0.8125rem; font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .field-input-wrap {
            position: relative;
        }
        .field-input-wrap .field-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: rgba(180,122,66,0.7);
            width: 16px; height: 16px;
            pointer-events: none;
        }
        .field-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(232,221,213,0.15);
            border-radius: 10px;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9375rem;
            transition: border-color 0.2s, background 0.2s;
            outline: none;
        }
        .field-input::placeholder { color: rgba(232,221,213,0.3); }
        .field-input:focus {
            border-color: rgba(180,122,66,0.6);
            background: rgba(255,255,255,0.1);
        }
        .toggle-pass {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: rgba(232,221,213,0.4);
            transition: color 0.2s;
            padding: 0;
        }
        .toggle-pass:hover { color: rgba(180,122,66,0.8); }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #B47A42, #D4A055);
            border: none; border-radius: 10px;
            color: #1a1208; font-family: 'Outfit', sans-serif;
            font-size: 1rem; font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #C88A52, #E4B065);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(180,122,66,0.4);
        }
        .btn-login:active { transform: translateY(0); }

        .hint {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(180,122,66,0.08);
            border: 1px solid rgba(180,122,66,0.2);
            border-radius: 10px;
        }
        .hint-title {
            color: rgba(180,122,66,0.9);
            font-size: 0.75rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .hint-text {
            color: rgba(232,221,213,0.5);
            font-size: 0.8rem; line-height: 1.5;
        }

        @media (max-width: 768px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="login-container">

    <!-- Left Panel -->
    <div class="login-left">
        <div class="brand-logo">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>
        <h1 class="brand-name">Edu-Verse</h1>
        <p class="brand-tagline">Complete School Management System for the modern era</p>

        <div class="features">
            <div class="feature-item">
                <div class="feature-dot"></div>
                <span>Multi-School Management</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"></div>
                <span>Role-Based Access Control</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"></div>
                <span>Staff & Payroll Management</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"></div>
                <span>Student Academic Tracking</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"></div>
                <span>Attendance & Leave System</span>
            </div>
        </div>
    </div>

    <!-- Right Panel (Form) -->
    <div class="login-right">
        <div class="form-card">
            <h2 class="form-title">Welcome back 👋</h2>
            <p class="form-subtitle">Sign in with your credentials to continue</p>

            @if(session('error'))
                <div class="alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                @csrf

                <div class="field-group">
                    <label class="field-label" for="login">Username / Email / Phone</label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="field-input"
                            placeholder="Enter username, email or phone"
                            value="{{ old('login') }}"
                            required
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="field-input"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-pass" id="togglePass" aria-label="Toggle password visibility">
                            <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Sign In
                </button>
            </form>

            <div class="hint">
                <div class="hint-title">🔐 Login Roles</div>
                <div class="hint-text">
                    • <strong style="color:rgba(180,122,66,0.9)">Super Admin</strong> – Full access to all schools<br>
                    • <strong style="color:rgba(180,122,66,0.9)">Principal / Admin</strong> – School-level management<br>
                    • <strong style="color:rgba(180,122,66,0.9)">Staff</strong> – Access based on assigned role permissions
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Toggle password visibility
    const togglePass = document.getElementById('togglePass');
    const passInput  = document.getElementById('password');
    const eyeIcon    = document.getElementById('eyeIcon');

    togglePass.addEventListener('click', () => {
        const isText = passInput.type === 'text';
        passInput.type = isText ? 'password' : 'text';
        eyeIcon.innerHTML = isText
            ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
            : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    });

    // Submit loading state
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<svg class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Signing in...';
        btn.disabled = true;
    });
</script>
</body>
</html>
