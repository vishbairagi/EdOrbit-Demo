<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentLoginController extends Controller
{
    public function index()
    {
        // Get all students along with their login credentials (if any)
        $students = DB::table('students')
            ->leftJoin('users', 'students.id', '=', 'users.student_id')
            ->select('students.*', 'users.username as login_username', 'users.email as login_email', 'users.id as user_id')
            ->orderBy('students.id', 'desc')
            ->get();
            
        return view('student.logins.index', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $student = DB::table('students')->where('id', $request->student_id)->first();
        if (!$student) {
            return back()->with('error', 'Student not found');
        }

        DB::table('users')->insert([
            'name' => trim($student->first_name . ' ' . $student->last_name),
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'student',
            'student_id' => $request->student_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Student login added successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6'
        ]);

        $data = [
            'email' => $request->email,
            'updated_at' => now()
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);

        return back()->with('success', 'Student login updated successfully');
    }

    public function destroy($id)
    {
        DB::table('users')->where('id', $id)->delete();
        return back()->with('success', 'Student login deleted successfully');
    }
}
