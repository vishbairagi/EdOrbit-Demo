<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffLiveClassCredential;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiveClassSettingController extends Controller
{
    private function currentSchool(): School
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');

        $school = School::findOrFail($schoolId);

        if (session('auth_guard') === 'school_user') {
            $user = SchoolUser::find(session('auth_user_id'));
            abort_if(! $user || (int) $user->school_id !== $school->id, 403);
        }

        if (session('auth_guard') === 'staff') {
            $user = Staff::find(session('auth_user_id'));
            abort_if(! $user || (int) $user->school_id !== $school->id, 403);
        }

        return $school;
    }

    private function currentSession(): AcademicSession
    {
        $sessionId = request('session_id') ?: session('selected_academic_session_id');
        if ($sessionId) {
            $session = AcademicSession::find($sessionId);
            if ($session) return $session;
        }

        $sysSessionId = SystemSetting::getSetting('current_session_id');
        if ($sysSessionId) {
            $session = AcademicSession::find($sysSessionId);
            if ($session) return $session;
        }

        return AcademicSession::latest('id')->firstOrFail();
    }

    public function index(Request $request): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        // Check if Google Meet is enabled in School Settings for this session
        $googleMeetEnabled = SchoolSetting::getSetting($school->id, $session->id, 'google_meet_enable', '0') == '1';

        // Retrieve teaching staff who have classes assigned in this school & session
        $assignedStaffIds = ClassTeacher::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->pluck('staff_id')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // If the logged in user is a staff member without admin permissions, they only see themselves if assigned
        $guard = session('auth_guard');
        $authUserId = session('auth_user_id');
        $isTeacherStaff = false;

        if ($guard === 'staff') {
            $authStaff = Staff::find($authUserId);
            if ($authStaff && ! $authStaff->hasPermission('sm-administrator.live-classes') && ! $authStaff->hasPermission('sm-administrator.staff')) {
                $isTeacherStaff = true;
                $assignedStaffIds = array_intersect($assignedStaffIds, [$authStaff->id]);
            }
        }

        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);
        if ($perPage < 1) $perPage = 10;
        if ($perPage > 200) $perPage = 200;

        $staffList = Staff::with('role')
            ->where('school_id', $school->id)
            ->whereIn('id', $assignedStaffIds)
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Class assignments for the loaded staff
        $classAssignments = ClassTeacher::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->whereIn('staff_id', $staffList->pluck('id'))
            ->get()
            ->groupBy('staff_id');

        // Credentials mapped by staff_id
        $credentials = StaffLiveClassCredential::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('staff_id', $staffList->pluck('id'))
            ->get()
            ->keyBy('staff_id');

        $allAssignedStaffCount = count($assignedStaffIds);
        $connectedCount = StaffLiveClassCredential::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('staff_id', $assignedStaffIds)
            ->where('is_connected', true)
            ->count();

        $defaultRedirectUri = url('/google-meet/oauth/callback');
        $sessions = AcademicSession::orderByDesc('name')->get();

        return view('sm-administrator.live-classes.index', compact(
            'school',
            'session',
            'sessions',
            'googleMeetEnabled',
            'staffList',
            'classAssignments',
            'credentials',
            'allAssignedStaffCount',
            'connectedCount',
            'defaultRedirectUri',
            'search',
            'isTeacherStaff'
        ));
    }

    /**
     * Store or update Google Meet credentials for a staff member.
     */
    public function storeCredential(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $validated = $request->validate([
            'staff_id' => 'required|integer|exists:staff,id',
            'google_client_id' => 'nullable|string|max:500',
            'google_client_secret' => 'nullable|string|max:500',
            'google_redirect_uri' => 'nullable|string|max:500',
            'google_access_token' => 'nullable|string',
            'google_refresh_token' => 'nullable|string',
        ]);

        $defaultRedirectUri = url('/google-meet/oauth/callback');
        $redirectUri = !empty($validated['google_redirect_uri']) ? $validated['google_redirect_uri'] : $defaultRedirectUri;

        $isConnected = !empty($validated['google_access_token']) || !empty($validated['google_refresh_token']);

        $credential = StaffLiveClassCredential::updateOrCreate([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'staff_id' => $validated['staff_id'],
        ], [
            'google_client_id' => trim((string) ($validated['google_client_id'] ?? '')),
            'google_client_secret' => trim((string) ($validated['google_client_secret'] ?? '')),
            'google_redirect_uri' => $redirectUri,
            'google_access_token' => trim((string) ($validated['google_access_token'] ?? '')),
            'google_refresh_token' => trim((string) ($validated['google_refresh_token'] ?? '')),
            'is_connected' => $isConnected,
        ]);

        $staff = Staff::find($validated['staff_id']);
        $staffName = $staff ? $staff->name : 'Staff';

        return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $session->id])
            ->with('success', "Google Meet credentials saved for {$staffName}.");
    }

    /**
     * Delete credentials for a staff member.
     */
    public function destroyCredential(int $staffId): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        StaffLiveClassCredential::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('staff_id', $staffId)
            ->delete();

        $staff = Staff::find($staffId);
        $staffName = $staff ? $staff->name : 'Staff';

        return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $session->id])
            ->with('success', "Google Meet credentials deleted for {$staffName}.");
    }

    /**
     * Bulk delete Google Meet credentials for selected staff.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Please select at least one staff member to delete credentials.');
        }

        $ids = array_map('intval', $ids);

        $deletedCount = StaffLiveClassCredential::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('staff_id', $ids)
            ->delete();

        return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $session->id])
            ->with('success', "Google Meet credentials deleted for {$deletedCount} staff member(s).");
    }

    /**
     * Export live class credentials and teacher assignments as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $assignedStaffIds = ClassTeacher::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->pluck('staff_id')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        $guard = session('auth_guard');
        $authUserId = session('auth_user_id');
        if ($guard === 'staff') {
            $authStaff = Staff::find($authUserId);
            if ($authStaff && ! $authStaff->hasPermission('sm-administrator.live-classes') && ! $authStaff->hasPermission('sm-administrator.staff')) {
                $assignedStaffIds = array_intersect($assignedStaffIds, [$authStaff->id]);
            }
        }

        // Check if specific IDs were provided (e.g. from bulk selection or query param)
        $selectedIds = $request->input('ids');
        if (is_string($selectedIds) && strlen(trim($selectedIds)) > 0) {
            $selectedIds = array_filter(explode(',', $selectedIds));
        }
        if (is_array($selectedIds) && count($selectedIds) > 0) {
            $assignedStaffIds = array_intersect($assignedStaffIds, array_map('intval', $selectedIds));
        }

        $search = $request->input('search');

        $staffMembers = Staff::with('role')
            ->where('school_id', $school->id)
            ->whereIn('id', $assignedStaffIds)
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        $classAssignments = ClassTeacher::with(['schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->whereIn('staff_id', $staffMembers->pluck('id'))
            ->get()
            ->groupBy('staff_id');

        $credentials = StaffLiveClassCredential::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('staff_id', $staffMembers->pluck('id'))
            ->get()
            ->keyBy('staff_id');

        $filename = 'live_classes_export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Employee ID',
            'Staff Name',
            'Role / Designation',
            'Email',
            'Phone',
            'Assigned Classes & Sections',
            'Google Meet Status',
            'Google Client ID',
            'Access Token Status',
            'Refresh Token Status',
            'Token Expiry',
            'Academic Session',
            'School Name',
        ];

        $callback = function () use ($staffMembers, $classAssignments, $credentials, $session, $school, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($staffMembers as $staff) {
                $cred = $credentials->get($staff->id);
                $assignments = $classAssignments->get($staff->id, collect());

                $classesStr = $assignments->map(function ($ct) {
                    $cName = $ct->schoolClass?->name ?? 'Class';
                    $sName = $ct->section?->name ? " ({$ct->section->name})" : '';
                    return $cName . $sName;
                })->implode(', ');

                $connStatus = 'Not Configured';
                if ($cred && $cred->is_connected) {
                    $connStatus = 'Connected';
                } elseif ($cred && $cred->isConfigured()) {
                    $connStatus = 'Credentials Saved (Not Connected)';
                }

                $accessTokenStatus = (!empty($cred?->google_access_token)) ? 'Generated' : 'None';
                $refreshTokenStatus = (!empty($cred?->google_refresh_token)) ? 'Generated' : 'None';
                $expiryStr = $cred?->token_expires_at ? $cred->token_expires_at->format('Y-m-d H:i:s') : 'N/A';

                fputcsv($file, [
                    $staff->employee_id ?: "ID #{$staff->id}",
                    $staff->name,
                    $staff->role?->name ?: ($staff->designation ?: 'Teacher'),
                    $staff->email ?: '',
                    $staff->phone ?: '',
                    $classesStr ?: 'No classes assigned',
                    $connStatus,
                    $cred?->google_client_id ?: '',
                    $accessTokenStatus,
                    $refreshTokenStatus,
                    $expiryStr,
                    $session->name,
                    $school->name,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Initiate Google OAuth2 flow for a staff member.
     */
    public function connect(Request $request, int $staffId): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $staff = Staff::where('school_id', $school->id)->findOrFail($staffId);

        // Fetch or create credential record
        $cred = StaffLiveClassCredential::firstOrNew([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'staff_id' => $staff->id,
        ]);

        if ($request->filled('google_client_id')) {
            $cred->google_client_id = trim((string) $request->input('google_client_id'));
        }
        if ($request->filled('google_client_secret')) {
            $cred->google_client_secret = trim((string) $request->input('google_client_secret'));
        }
        $defaultRedirectUri = url('/google-meet/oauth/callback');
        $cred->google_redirect_uri = $defaultRedirectUri;
        $cred->save();

        if (empty($cred->google_client_id) || empty($cred->google_client_secret)) {
            return back()->with('error', 'Please enter Google Client ID and Google Client Secret before connecting.');
        }

        // Prepare OAuth state
        $statePayload = json_encode([
            'staff_id' => $staff->id,
            'school_id' => $school->id,
            'session_id' => $session->id,
            'ts' => time(),
        ]);
        $state = base64_encode($statePayload);

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $cred->google_client_id,
            'redirect_uri' => $defaultRedirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent select_account',
            'state' => $state,
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth2 callback from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('sm-administrator.live-classes.index')
                ->with('error', 'Google authorization was cancelled or failed: ' . $request->query('error_description', $request->query('error')));
        }

        $code = $request->query('code');
        $state = $request->query('state');

        if (!$code || !$state) {
            return redirect()->route('sm-administrator.live-classes.index')
                ->with('error', 'Invalid Google authorization response. Code or state missing.');
        }

        $stateData = json_decode(base64_decode($state), true);
        if (!is_array($stateData) || empty($stateData['staff_id']) || empty($stateData['school_id']) || empty($stateData['session_id'])) {
            return redirect()->route('sm-administrator.live-classes.index')
                ->with('error', 'Invalid state parameter in Google callback.');
        }

        $cred = StaffLiveClassCredential::where('school_id', $stateData['school_id'])
            ->where('academic_session_id', $stateData['session_id'])
            ->where('staff_id', $stateData['staff_id'])
            ->first();

        if (!$cred || empty($cred->google_client_id) || empty($cred->google_client_secret)) {
            return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $stateData['session_id']])
                ->with('error', 'Google Meet credentials not found for this staff member.');
        }

        $defaultRedirectUri = url('/google-meet/oauth/callback');

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $cred->google_client_id,
                'client_secret' => $cred->google_client_secret,
                'redirect_uri' => $defaultRedirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $cred->update([
                    'google_access_token' => $tokenData['access_token'] ?? $cred->google_access_token,
                    'google_refresh_token' => $tokenData['refresh_token'] ?? $cred->google_refresh_token,
                    'token_expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds((int) $tokenData['expires_in']) : null,
                    'is_connected' => true,
                ]);

                $staff = Staff::find($stateData['staff_id']);
                $staffName = $staff ? $staff->name : 'Staff';

                return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $stateData['session_id']])
                    ->with('success', "Google Meet successfully connected for {$staffName}! Access token and refresh token generated.");
            } else {
                $errorMsg = $response->json('error_description') ?? $response->json('error') ?? $response->body();
                return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $stateData['session_id']])
                    ->with('error', 'Failed to retrieve Google tokens: ' . $errorMsg);
            }
        } catch (\Throwable $e) {
            return redirect()->route('sm-administrator.live-classes.index', ['session_id' => $stateData['session_id']])
                ->with('error', 'Error communicating with Google OAuth server: ' . $e->getMessage());
        }
    }
}
