<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Check credentials against users table
        $user = DB::table('users')
            ->where('user_type', 'student')
            ->where('username', $username)
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Get the associated student
        $student = DB::table('students')->where('id', $user->student_id)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student record not found for this login'
            ], 404);
        }

        // Generate JWT token manually
        $customClaims = [
            'sub' => (string) $student->id,
            'user' => [
                'id' => (string) $student->id
            ]
        ];
        
        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();

        // Fetch school details
        $school = DB::table('schools')->where('id', $student->school_id)->first();
        
        $schoolData = [
            'name' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'logo' => ''
        ];
        if ($school) {
            $schoolData = [
                'name' => (string) $school->name,
                'phone' => (string) $school->phone,
                'email' => (string) $school->email,
                'address' => (string) $school->address,
                'logo' => (string) ($school->logo ?? '')
            ];
        }

        // Format photo URL
        $photoUrl = $student->photo;
        if ($photoUrl && !filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            $photoUrl = asset('storage/' . ltrim($photoUrl, '/'));
        } elseif (!$photoUrl) {
            $photoUrl = '';
        }

        return response()->json([
            'token' => $token,
            'user_email' => $user->email,
            'user_nicename' => $student->first_name,
            'user_display_name' => trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name),
            'user_type' => 'student',
            'photo' => $photoUrl,
            'school' => $schoolData
        ]);
    }
}
