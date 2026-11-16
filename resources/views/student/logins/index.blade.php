@extends('layouts.app')
@section('header', 'Student Logins')

@section('content')

{{-- Header bar --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center">
        <i data-lucide="key" class="w-5 h-5 text-white mr-2.5"></i>
        <span class="text-white font-bold text-base tracking-wide">Student Logins</span>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Students Table --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <p class="text-sm text-body">Showing <strong>{{ count($students) }}</strong> students</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100" style="background:#F5F1EB;">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Student Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Admission No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Login Username</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-body">Login Email</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-body">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors row-item">
                        <td class="px-4 py-3 font-bold text-black">{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td class="px-4 py-3 font-mono font-bold">{{ $student->admission_no }}</td>
                        <td class="px-4 py-3">
                            @if($student->login_username)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span> 
                                <span class="ml-2 font-bold">{{ $student->login_username }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    No Login
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $student->login_email ?? '---' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($student->user_id)
                                    <button type="button" onclick="openModal('editModal{{ $student->user_id }}')" title="Edit Login" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" onclick="openModal('deleteModal{{ $student->user_id }}')" title="Delete Login" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                @else
                                    <button type="button" onclick="openModal('addModal{{ $student->id }}')" title="Add Login" class="inline-flex items-center justify-center px-2 py-1 rounded-md bg-green-50 text-green-600 hover:bg-green-100 transition-colors font-bold text-xs gap-1">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add User
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Add Modal -->
                    @if(!$student->user_id)
                    <div id="addModal{{ $student->id }}" class="custom-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h3 class="text-base font-bold text-heading">Add Login for {{ $student->first_name }}</h3>
                                <button type="button" onclick="closeModal('addModal{{ $student->id }}')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                            </div>
                            <form method="POST" action="{{ route('sm-student.student-logins.store') }}" class="p-6 space-y-4 text-left">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                                    <input type="text" name="username" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                    <input type="password" name="password" required minlength="6" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                                </div>
                                
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeModal('addModal{{ $student->id }}')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-opacity-90 text-white rounded-lg font-medium transition-colors">Add Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Edit Modal -->
                    @if($student->user_id)
                    <div id="editModal{{ $student->user_id }}" class="custom-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h3 class="text-base font-bold text-heading">Edit Login - {{ $student->first_name }}</h3>
                                <button type="button" onclick="closeModal('editModal{{ $student->user_id }}')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                            </div>
                            <form method="POST" action="{{ route('sm-student.student-logins.update', $student->user_id) }}" class="p-6 space-y-4 text-left">
                                @csrf
                                @method('PUT')
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Username (Cannot be changed)</label>
                                    <input type="text" value="{{ $student->login_username }}" disabled class="block w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" value="{{ $student->login_email }}" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-xs text-gray-400 font-normal">(leave blank to keep current)</span></label>
                                    <input type="password" name="password" minlength="6" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
                                </div>
                                
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeModal('editModal{{ $student->user_id }}')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-opacity-90 text-white rounded-lg font-medium transition-colors">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div id="deleteModal{{ $student->user_id }}" class="custom-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h3 class="text-base font-bold text-heading">Delete Login</h3>
                                <button type="button" onclick="closeModal('deleteModal{{ $student->user_id }}')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                            </div>
                            <form method="POST" action="{{ route('sm-student.student-logins.destroy', $student->user_id) }}" class="p-6 space-y-4 text-left">
                                @csrf
                                @method('DELETE')
                                <p class="text-sm text-gray-700">Are you sure you want to delete the login user <strong class="text-black">{{ $student->login_username }}</strong> for this student?</p>
                                <p class="text-xs text-red-500 font-medium">This action cannot be undone.</p>
                                
                                <div class="flex justify-end gap-3 pt-4">
                                    <button type="button" onclick="closeModal('deleteModal{{ $student->user_id }}')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-14 text-center text-body text-sm font-bold">
                            <div class="flex flex-col items-center gap-3">
                                <i data-lucide="users" class="w-12 h-12 text-gray-200"></i>
                                <p class="text-gray-500">No students found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.custom-modal').forEach(function(modal) {
                modal.classList.add('hidden');
            });
        }
    });
</script>

@endsection
