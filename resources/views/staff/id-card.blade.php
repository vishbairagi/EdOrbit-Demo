<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My ID Card</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5ece0, #ede5d8); padding: 24px; }
        .id-card { width: 340px; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 40px rgba(33,27,23,.2); }
        .card-header { background: linear-gradient(135deg, #B47A42, #D4A055); padding: 20px; text-align: center; color: #fff; }
        .school-name { font-size: 1rem; font-weight: 800; }
        .card-label  { font-size: 0.65rem; opacity: 0.7; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px; }
        .card-body { padding: 24px; text-align: center; }
        .photo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #B47A42; margin: 0 auto 12px; display: block; }
        .photo-placeholder { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f5ece0, #DED5CB); border: 3px solid #B47A42; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 800; color: #B47A42; }
        .name { font-size: 1.1rem; font-weight: 700; color: #211B17; margin-bottom: 4px; }
        .role { font-size: 0.75rem; font-weight: 600; color: #B47A42; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 16px; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #F0EAE3; font-size: 0.8rem; }
        .info-row .label { color: #9e8f84; }
        .info-row .value { color: #211B17; font-weight: 600; }
        .card-footer { background: linear-gradient(135deg, #211B17, #2d2118); padding: 12px; text-align: center; color: #B47A42; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em; }
        .back-btn { display: block; text-align: center; margin-top: 20px; padding: 10px 24px; background: linear-gradient(135deg, #B47A42, #D4A055); color: #fff; border-radius: 8px; font-weight: 600; font-size: 0.875rem; text-decoration: none; max-width: 340px; }
    </style>
</head>
<body>
    <div>
        @php $school = \App\Models\School::find($person->school_id); @endphp
        <div class="id-card">
            <div class="card-header">
                <div class="school-name">{{ $school?->name ?? 'School Name' }}</div>
                <div class="card-label">Staff Identity Card</div>
            </div>
            <div class="card-body">
                @if($person->photo)
                    <img src="{{ asset('storage/'.$person->photo) }}" class="photo" alt="{{ $person->name }}">
                @else
                    <div class="photo-placeholder">{{ strtoupper(substr($person->name, 0, 1)) }}</div>
                @endif
                <div class="name">{{ $person->name }}</div>
                <div class="role">
                    @if(isset($person->role) && is_string($person->role))
                        {{ $person->role }}
                    @elseif(isset($person->role) && is_object($person->role))
                        {{ $person->role->name }}
                    @else
                        {{ $person->designation ?? 'Staff' }}
                    @endif
                </div>
                <div class="info-row"><span class="label">Employee ID</span><span class="value">{{ $person->employee_id ?? 'N/A' }}</span></div>
                @if($person->phone)<div class="info-row"><span class="label">Phone</span><span class="value">{{ $person->phone }}</span></div>@endif
                @if($person->login_email ?? $person->email)<div class="info-row"><span class="label">Email</span><span class="value">{{ $person->login_email ?? $person->email }}</span></div>@endif
                @if($person->joining_date)<div class="info-row"><span class="label">Joined</span><span class="value">{{ $person->joining_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</span></div>@endif
            </div>
            <div class="card-footer">{{ $person->employee_id ?? 'N/A' }} • {{ $school?->name ?? '' }}</div>
        </div>
        <a href="{{ route('staff.dashboard') }}" class="back-btn" style="margin-top:16px;">← Back to Dashboard</a>
    </div>
</body>
</html>
