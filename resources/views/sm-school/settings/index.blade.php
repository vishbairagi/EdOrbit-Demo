@extends('layouts.app')

@section('header', 'School Settings')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-navy text-white">
            <i data-lucide="settings-2" class="h-5 w-5"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold leading-tight text-heading">School Settings</h2>
            <p class="text-xs text-body">{{ $school->name }} - {{ $session->name }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <label for="sessionFilter" class="text-xs font-medium text-body">Session</label>
        <select id="sessionFilter" class="rounded-lg border border-gray-200 py-1.5 pl-3 pr-8 text-xs focus:border-navy focus:ring-1 focus:ring-navy" onchange="window.location.href='?tab={{ $activeTab }}&session_id='+this.value">
            @foreach($sessions as $s)
                <option value="{{ $s->id }}" @selected($s->id === $session->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        @foreach($errors->all() as $message)
            <p>{{ $message }}</p>
        @endforeach
    </div>
@endif

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
<nav class="col-span-full flex gap-2 overflow-x-auto border-b border-gray-200 pb-2">
    @php
        $tabs = [
            'general' => 'General',
            'email-carrier' => 'Email Carrier',
            'email-templates' => 'Email Templates',
            'payment-methods' => 'Payment Methods',
            'registration' => 'Registration',
            'dashboard' => 'Dashboard',
            'live-classes' => 'Live Classes',
            'card-background' => 'Card Background',
            'examination' => 'Examination',
        ];
    @endphp
    @foreach($tabs as $tab => $label)
        <a href="{{ route('sm-school.settings.index', ['tab' => $tab, 'session_id' => $session->id]) }}" class="shrink-0 rounded-md px-3 py-2 text-xs font-semibold {{ $activeTab === $tab ? 'bg-navy text-white' : 'text-body hover:bg-gray-100' }}">{{ $label }}</a>
    @endforeach
</nav>

@if($activeTab === 'registration')
<form action="{{ route('sm-school.settings.update') }}" method="POST" class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white mb-6">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Registration Settings</h3>
    </div>
    @csrf
    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Category:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_student_category" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_student_category', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Student Category Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Type:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_student_type" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_student_type', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Student Type Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Middle Name:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_middle_name" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_middle_name', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Middle Name Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Blood Group:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_blood_group" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_blood_group', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Blood Group Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Religion:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_religion" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_religion', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Religion Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Caste / Sub Caste:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_caste" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_caste', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Caste Field</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Medium:</label>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_medium" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'show_medium', '1') == '1' ? 'checked' : '' }}> <span class="text-sm">Show Medium Field</span>
            </div>
        </div>
        <div class="flex justify-end pt-4">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Options</button>
        </div>
    </div>
</form>
<div class="col-span-full grid grid-cols-1 gap-5 xl:grid-cols-2">
    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Current Category Options</h3>
            <p class="mt-1 text-xs text-body">Student categories available for {{ $school->name }}.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($categories as $category)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <i data-lucide="grip-vertical" class="h-4 w-4 shrink-0 text-gray-400"></i>
                            <span class="truncate rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold lowercase text-gray-600">{{ \Illuminate\Support\Str::slug($category->name, '_') }}</span>
                            <span class="truncate text-sm font-semibold text-heading">{{ $category->name }}</span>
                            <span class="shrink-0 text-xs text-body">{{ $category->students_count }} students</span>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <button type="button" onclick="document.getElementById('edit-category-{{ $category->id }}').classList.toggle('hidden')" class="text-blue-600 transition hover:text-blue-800" title="Edit {{ $category->name }}">
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </button>
                            <form action="{{ route('sm-school.settings.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($category->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 transition hover:text-red-800" title="Delete {{ $category->name }}">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <form id="edit-category-{{ $category->id }}" action="{{ route('sm-school.settings.categories.update', $category) }}" method="POST" class="mt-3 hidden rounded-md bg-gray-50 p-3">
                        @csrf
                        @method('PUT')
                        <label for="category-name-{{ $category->id }}" class="mb-1 block text-xs font-medium text-body">Display Label</label>
                        <div class="flex flex-wrap gap-2">
                            <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
                            <input id="category-name-{{ $category->id }}" type="text" name="name" value="{{ $category->name }}" required maxlength="100" class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-navy px-3 py-2 text-xs font-semibold text-white hover:bg-primary-dark">
                                <i data-lucide="check" class="h-3.5 w-3.5"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <i data-lucide="tags" class="mx-auto mb-2 h-9 w-9 text-gray-300"></i>
                    <p class="text-sm font-medium text-heading">No categories configured.</p>
                    <p class="mt-1 text-xs text-body">Add an option to make it available on student forms.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Add New Option</h3>
            <p class="mt-1 text-xs text-body">Create a category shown in student admission and edit forms.</p>
        </div>
        <form action="{{ route('sm-school.settings.categories.store') }}" method="POST" class="space-y-4 p-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="new-category-name" class="mb-1 block text-xs font-medium text-body">Display Label</label>
                    <input id="new-category-name" type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g., OBC-A" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                </div>
                <div>
                    <label for="new-category-session" class="mb-1 block text-xs font-medium text-body">Session</label>
                    <select id="new-category-session" name="academic_session_id" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" required>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" @selected($s->id === $session->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Add Option
            </button>
        </form>
    </section>
</div>

<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Student Types</h3>
        <p class="mt-1 text-xs text-body">Manage the options shown in student admission and edit forms for {{ $school->name }}.</p>
    </div>
    <form action="{{ route('sm-school.settings.student-types.store') }}" method="POST" class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-end">
        @csrf
        <div class="flex-1">
            <label for="new-student-type" class="mb-1 block text-xs font-medium text-body">Student Type Name</label>
            <input id="new-student-type" type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g., Day Scholar" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
        </div>
        <div class="w-40">
            <label for="new-student-type-session" class="mb-1 block text-xs font-medium text-body">Session</label>
            <select id="new-student-type-session" name="academic_session_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" required>
                @foreach($sessions as $s)
                    <option value="{{ $s->id }}" @selected($s->id === $session->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Add Student Type</button>
    </form>
    <div class="divide-y divide-gray-100">
        @forelse($studentTypes as $studentType)
            <div class="px-5 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3"><span class="truncate text-sm font-semibold text-heading">{{ $studentType->name }}</span><span class="shrink-0 text-xs text-body">{{ $studentType->students_count }} students</span></div>
                    <div class="flex shrink-0 items-center gap-3">
                        <button type="button" onclick="document.getElementById('edit-student-type-{{ $studentType->id }}').classList.toggle('hidden')" class="text-blue-700 hover:text-blue-900" title="Edit student type"><i data-lucide="pencil" class="h-4 w-4"></i></button>
                        <form action="{{ route('sm-school.settings.student-types.destroy', $studentType) }}" method="POST" onsubmit="return confirm('Delete this student type?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800" title="Delete student type"><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                    </div>
                </div>
                <form id="edit-student-type-{{ $studentType->id }}" action="{{ route('sm-school.settings.student-types.update', $studentType) }}" method="POST" class="mt-3 hidden flex flex-wrap items-end gap-2 rounded-md bg-gray-50 p-3">
                    @csrf @method('PUT')
                    <div class="min-w-48 flex-1">
                        <label for="student-type-name-{{ $studentType->id }}" class="mb-1 block text-xs font-medium text-body">Student Type Name</label>
                        <input id="student-type-name-{{ $studentType->id }}" type="text" name="name" value="{{ $studentType->name }}" required maxlength="100" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
                    </div>
                    <button type="submit" class="rounded-md bg-navy px-4 py-2 text-xs font-semibold text-white hover:bg-primary-dark">Save Changes</button>
                </form>
            </div>
        @empty
            <div class="px-5 py-10 text-center"><i data-lucide="users-round" class="mx-auto mb-2 h-8 w-8 text-gray-300"></i><p class="text-sm font-medium text-heading">No student types configured.</p><p class="mt-1 text-xs text-body">Add a type to make it available during admission.</p></div>
        @endforelse
    </div>
</section>

<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Fee Periods</h3>
        <p class="mt-1 text-xs text-body">Configure billing intervals for each academic session.</p>
    </div>
    <form action="{{ route('sm-school.settings.fee-periods.store') }}" method="POST" class="grid grid-cols-1 items-end gap-4 border-b border-gray-200 bg-gray-50 p-5 md:grid-cols-4">
        @csrf
        <div><label for="periodName" class="mb-1 block text-xs font-medium text-body">Period Name</label><input id="periodName" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Monthly Tuition" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"></div>
        <div><label for="periodSession" class="mb-1 block text-xs font-medium text-body">Session</label><select id="periodSession" name="academic_session_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">@foreach($sessions as $academicSession)<option value="{{ $academicSession->id }}">{{ $academicSession->name }}</option>@endforeach</select></div>
        <div><label for="periodDuration" class="mb-1 block text-xs font-medium text-body">Period Duration</label><select id="periodDuration" name="duration_months" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><option value="1">Monthly · 1 month</option><option value="3">Quarterly · 3 months</option><option value="4">4 months</option><option value="6">Half-yearly · 6 months</option><option value="12">Annual · 12 months</option></select></div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Add Period</button>
    </form>
    <div class="divide-y divide-gray-100">
        @forelse($feePeriods as $period)
            <div class="px-5 py-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-sm font-semibold text-heading">{{ $period->name }}</p><p class="mt-1 text-xs text-body">{{ $period->academicSession->name }} · Every {{ $period->duration_months }} month(s)</p></div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="document.getElementById('edit-period-{{ $period->id }}').classList.toggle('hidden')" class="text-blue-700 hover:text-blue-900" title="Edit period"><i data-lucide="pencil" class="h-4 w-4"></i></button>
                        <form action="{{ route('sm-school.settings.fee-periods.destroy', $period) }}" method="POST" onsubmit="return confirm('Delete this period?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800" title="Delete period"><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                    </div>
                </div>
                <form id="edit-period-{{ $period->id }}" action="{{ route('sm-school.settings.fee-periods.update', $period) }}" method="POST" class="mt-3 hidden grid grid-cols-1 gap-3 rounded-md bg-gray-50 p-3 md:grid-cols-4">
                    @csrf @method('PUT')
                    <input name="name" value="{{ $period->name }}" required maxlength="100" aria-label="Period name" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <select name="academic_session_id" aria-label="Academic session" class="rounded-md border border-gray-300 px-3 py-2 text-sm">@foreach($sessions as $academicSession)<option value="{{ $academicSession->id }}" {{ $period->academic_session_id === $academicSession->id ? 'selected' : '' }}>{{ $academicSession->name }}</option>@endforeach</select>
                    <select name="duration_months" aria-label="Duration in months" class="rounded-md border border-gray-300 px-3 py-2 text-sm">@foreach([1, 3, 4, 6, 12] as $months)<option value="{{ $months }}" {{ $period->duration_months === $months ? 'selected' : '' }}>{{ $months }} month(s)</option>@endforeach</select>
                    <button class="rounded-md bg-navy px-3 py-2 text-sm font-semibold text-white">Save Changes</button>
                </form>
            </div>
        @empty
            <p class="px-5 py-10 text-center text-sm text-body">No fee periods configured.</p>
        @endforelse
    </div>
</section>
@elseif($activeTab === 'payment-methods')
<section class="col-span-full space-y-8" id="payment-methods-section">
    <!-- Static Payment Methods (Bank, UPI, Cash, Check) -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Standard Payment Methods</h3>
            <p class="mt-1 text-xs text-body">Configure built-in payment methods.</p>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Method Boxes -->
                <button type="button" onclick="togglePaymentMethod('bank')" id="btn-method-bank" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="building-2" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Bank Transfer</span>
                </button>
                <button type="button" onclick="togglePaymentMethod('upi')" id="btn-method-upi" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="smartphone" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">UPI Transfer</span>
                </button>
                <button type="button" onclick="togglePaymentMethod('cash')" id="btn-method-cash" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="banknote" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Cash</span>
                </button>
                <button type="button" onclick="togglePaymentMethod('check')" id="btn-method-check" class="payment-method-btn flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl shadow-sm transition-all hover:border-navy hover:shadow-md bg-white">
                    <i data-lucide="file-signature" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Check</span>
                </button>
            </div>

            <form action="{{ route('sm-school.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 border border-gray-200 rounded-xl p-5 hidden" id="payment-method-form">
                @csrf
                <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
                
                <!-- Bank Form -->
                <div id="method-bank" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Bank Transfer Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Bank Transfer:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_bank_transfer" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_bank_transfer', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Branch Code:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_branch_code" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_branch_code') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Account No.:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_account_no" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_account_no') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Name:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_name" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="bank_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'bank_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- UPI Form -->
                <div id="method-upi" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">UPI Transfer Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable UPI Transfer:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_upi_transfer" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_upi_transfer', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">QR Code:</label>
                        <div class="col-span-2">
                            <input type="file" name="upi_qr_code" class="block w-full text-sm">
                            @if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_qr_code'))
                            <p class="text-xs text-green-600 mt-1">QR Code already uploaded.</p>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">UPI ID:</label>
                        <div class="col-span-2">
                            <input type="text" name="upi_id" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_id') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Name:</label>
                        <div class="col-span-2">
                            <input type="text" name="upi_name" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="upi_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'upi_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Cash Form -->
                <div id="method-cash" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Cash Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Cash:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_cash_payment" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_cash_payment', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="cash_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'cash_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Check Form -->
                <div id="method-check" class="space-y-4 payment-method-detail hidden">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Check Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Check:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_check_payment" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'enable_check_payment', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="check_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'check_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Standard Methods</button>
                </div>
            </form>
        </div>
    </div>

</section>

<script>
    function togglePaymentMethod(method) {
        const form = document.getElementById('payment-method-form');
        const details = document.querySelectorAll('.payment-method-detail');
        const buttons = document.querySelectorAll('.payment-method-btn');
        
        let currentlyVisible = false;
        
        details.forEach(d => {
            if(d.id === 'method-' + method && d.classList.contains('hidden')) {
                d.classList.remove('hidden');
                currentlyVisible = true;
            } else {
                d.classList.add('hidden');
            }
        });

        buttons.forEach(b => {
            if(b.id === 'btn-method-' + method && currentlyVisible) {
                b.classList.remove('border-gray-200');
                b.classList.add('border-navy', 'ring-1', 'ring-navy');
            } else {
                b.classList.add('border-gray-200');
                b.classList.remove('border-navy', 'ring-1', 'ring-navy');
            }
        });

        if(currentlyVisible) {
            form.classList.remove('hidden');
        } else {
            form.classList.add('hidden');
        }
    }
</script>
@elseif($activeTab === 'general')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">General Settings</h3>
    </div>
    <form action="{{ route('sm-school.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
    <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Set Currency:</label>
            <div class="col-span-2">
                @php 
                    $currency = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'currency', 'USD');
                    $currencies = ['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'INR' => 'INR (₹)', 'AUD' => 'AUD ($)', 'CAD' => 'CAD ($)', 'SGD' => 'SGD ($)', 'JPY' => 'JPY (¥)', 'NGN' => 'NGN (₦)', 'ZAR' => 'ZAR (R)'];
                @endphp
                <select name="currency" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary sm:text-sm">
                    @foreach($currencies as $k => $v)
                    <option value="{{ $k }}" {{ $currency === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Invoice Copy:</label>
            <div class="col-span-2">
                @php $invoiceCopy = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_copy', 'No'); @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="invoice_copy" value="Yes" {{ $invoiceCopy === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="invoice_copy" value="No" {{ $invoiceCopy === 'No' ? 'checked' : '' }}> No</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Admin will be able to print school and student copies of invoices.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Registration Invoice Type:</label>
            <div class="col-span-2">
                @php $regInvType = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'registration_invoice_type', 'Combined Invoice'); @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="registration_invoice_type" value="Combined Invoice" {{ $regInvType === 'Combined Invoice' ? 'checked' : '' }}> Combined Invoice</label>
                    <label><input type="radio" name="registration_invoice_type" value="Separate Invoices" {{ $regInvType === 'Separate Invoices' ? 'checked' : '' }}> Separate Invoices</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Choose whether to create a single combined invoice with all fee types or separate invoices for each fee type when a new student is registered.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Auto Generate Invoice:</label>
            <div class="col-span-2">
                @php $autoInv = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'auto_generate_invoice', 'No'); @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="auto_generate_invoice" value="Yes" {{ $autoInv === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="auto_generate_invoice" value="No" {{ $autoInv === 'No' ? 'checked' : '' }}> No</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">New - Monthly invoices will be auto generated.</p>
                @if($autoInv === 'Yes')
                <p class="text-xs font-semibold text-green-600 mt-1">Next cron job will run on: {{ now()->addMonth()->startOfMonth()->format('d M Y') }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload School Logo:</label>
            <div class="col-span-2">
                <input type="file" name="school_logo" class="block w-full text-sm">
                @if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'school_logo'))
                <p class="text-xs text-green-600 mt-1">Logo already uploaded.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload School Signature:</label>
            <div class="col-span-2">
                <input type="file" name="school_signature" class="block w-full text-sm">
                @if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'school_signature'))
                <p class="text-xs text-green-600 mt-1">Signature already uploaded.</p>
                @endif
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>

@elseif($activeTab === 'dashboard' || $activeTab === 'live-classes')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="p-12 text-center text-gray-500 text-sm font-semibold">
        {{ ucfirst(str_replace('-', ' ', $activeTab)) }} settings are blank/upcoming.
    </div>
</section>

@elseif($activeTab === 'card-background')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Card Background Settings</h3>
    </div>
    <form action="{{ route('sm-school.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
    <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Id Card Background:</label>
            <div class="col-span-2">
                <input type="file" name="id_card_background" class="block w-full text-sm">
                @if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'id_card_background'))
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-green-600">Background already uploaded.</p>
                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_id_card_background" value="1"> Remove
                    </label>
                </div>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Invoice Background:</label>
            <div class="col-span-2">
                <input type="file" name="invoice_background" class="block w-full text-sm">
                @if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'invoice_background'))
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-green-600">Background already uploaded.</p>
                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_invoice_background" value="1"> Remove
                    </label>
                </div>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Examination background image ( admit card and result ) :</label>
            <div class="col-span-2">
                <input type="file" name="examination_background" class="block w-full text-sm">
                @if(\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'examination_background'))
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-green-600">Background already uploaded.</p>
                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_examination_background" value="1"> Remove
                    </label>
                </div>
                @endif
            </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>

@elseif($activeTab === 'examination')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Examination Settings</h3>
    </div>
    <form action="{{ route('sm-school.settings.update') }}" method="POST" class="p-6 space-y-6">
        @csrf
    <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Admit Card Photo:</label>
            <div class="col-span-2">
                @php $admitPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'student_admit_card_photo', '0') == '1'; @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="student_admit_card_photo" value="1" {{ $admitPhoto ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="student_admit_card_photo" value="0" {{ !$admitPhoto ? 'checked' : '' }}> No</label>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Exam Result Photo:</label>
            <div class="col-span-2">
                @php $resultPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exam_result_photo', '0') == '1'; @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="exam_result_photo" value="1" {{ $resultPhoto ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="exam_result_photo" value="0" {{ !$resultPhoto ? 'checked' : '' }}> No</label>
                </div>
            </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>
@elseif($activeTab === 'email-carrier')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Email Carrier Settings</h3>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3">
        <form action="{{ route('sm-school.settings.update') }}" method="POST" class="p-6 space-y-6 lg:col-span-2 border-r border-gray-200">
            @csrf
            <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">Email Carrier:</label>
                <div class="col-span-2">
                    <select name="email_carrier" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary sm:text-sm">
                        <option value="smtp" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'email_carrier') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">From Name:</label>
                <div class="col-span-2">
                    <input type="text" name="mail_from_name" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'mail_from_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="From Name">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">SMTP Host:</label>
                <div class="col-span-2">
                    <input type="text" name="smtp_host" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_host') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="SMTP Host">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">SMTP Username:</label>
                <div class="col-span-2">
                    <input type="text" name="smtp_username" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="SMTP Username">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">SMTP Password:</label>
                <div class="col-span-2">
                    <input type="password" name="smtp_password" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_password') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="SMTP Password">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">SMTP Encryption:</label>
                <div class="col-span-2">
                    <input type="text" name="smtp_encryption" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_encryption', 'tls') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="SMTP Encryption">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <label class="text-sm font-bold text-heading">SMTP Port:</label>
                <div class="col-span-2">
                    <input type="text" name="smtp_port" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_port', '587') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="SMTP Port">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[#f28123] text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save</button>
            </div>
        </form>

        <div class="p-6 bg-gray-50 lg:col-span-1 text-sm text-gray-700 space-y-4">
            <p>You can use SMTP as email carrier.</p>
            <p class="text-red-600 font-semibold">From May 30, 2022, Google no longer supports the use of third-party apps or devices which ask you to sign in to your Google Account using only your username and password.</p>
            <p>To use Gmail SMTP, you need to generate an App Password. You can do this here: <br><a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-600 underline hover:text-blue-800 break-all">https://myaccount.google.com/apppasswords</a></p>
            
            <hr class="border-gray-200 my-4">
            
            <p>For example, to use Gmail as SMTP provider, you need to set SMTP host, encryption, port number, username and password.</p>
            <ul class="space-y-2 mt-4">
                <li><strong>Host:</strong> smtp.gmail.com</li>
                <li><strong>Username:</strong> Your Gmail account email.</li>
                <li><strong>Password:</strong> Your Gmail account password (App Password).</li>
                <li><strong>Encryption:</strong> tls</li>
                <li><strong>Port:</strong> 587</li>
            </ul>
        </div>
    </div>
</section>

@elseif($activeTab === 'email-templates')
<section class="col-span-full space-y-4">
    @php
        $templates = [
            'student_admission' => 'Student Admission Email Template',
            'invoice_generated' => 'Invoice Generated Email Template',
            'fee_received' => 'Fee Received Payment Email Template',
            'leave_application' => 'Leave Application Email Template',
            'attendance_taken' => 'Attendance Taken Notification Email Template',
            'absent_student' => 'Absent Student Notification Email Template',
            'due_date' => 'Due Date Email Template',
            'exam_admit_card' => 'Exam Admit Card Template',
            'exam_result' => 'Exam Result Announce Email Template',
        ];
    @endphp

    @foreach($templates as $key => $title)
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <button type="button" onclick="document.getElementById('template_{{ $key }}').classList.toggle('hidden')" class="w-full flex items-center justify-between bg-[#f28123] px-5 py-3 text-white">
            <h3 class="text-sm font-bold text-center w-full">{{ $title }}</h3>
        </button>
        <div id="template_{{ $key }}" class="hidden p-6 border-t border-gray-200">
            <form action="{{ route('sm-school.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
                
                <div class="flex items-center gap-4 mb-4">
                    <label class="text-sm font-bold text-heading">Enable Email:</label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="{{ $key }}_email_enable" value="1" {{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, $key.'_email_enable') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-medium">Yes</span>
                    </label>
                </div>

                <div class="mb-4">
                    <p class="text-xs font-semibold mb-2">You can use the following variables:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-gray-600 border border-gray-200 rounded p-3 bg-gray-50">
                        <div>Student Name<br/><b>[STUDENT_NAME]</b></div>
                        <div>Class<br/><b>[CLASS]</b></div>
                        <div>Section<br/><b>[SECTION]</b></div>
                        <div>Roll Number<br/><b>[ROLL_NUMBER]</b></div>
                        <div>Enrollment Number<br/><b>[ENROLLMENT_NUMBER]</b></div>
                        <div>Admission Number<br/><b>[ADMISSION_NUMBER]</b></div>
                        <div>School Name<br/><b>[SCHOOL_NAME]</b></div>
                        @if(in_array($key, ['invoice_generated', 'fee_received']))
                        <div>Amount<br/><b>[AMOUNT]</b></div>
                        <div>Total Amount<br/><b>[TOTAL_AMOUNT]</b></div>
                        <div>Payable Amount<br/><b>[PAYABLE_AMOUNT]</b></div>
                        <div>Issue Date<br/><b>[ISSUE_DATE]</b></div>
                        <div>Due Date<br/><b>[DUE_DATE]</b></div>
                        <div>Transaction ID<br/><b>[TRANSACTION_ID]</b></div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 mb-4">
                    <label class="text-sm font-bold text-heading">Email Subject:</label>
                    <input type="text" name="{{ $key }}_email_subject" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, $key.'_email_subject') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="Email Subject">
                </div>

                <div class="grid grid-cols-1 gap-2 mb-4">
                    <label class="text-sm font-bold text-heading">Email Body:</label>
                    <textarea name="{{ $key }}_email_body" rows="6" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="Email Body">{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, $key.'_email_body') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center border-t border-gray-100 pt-4 gap-4">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="email" name="test_email" placeholder="Enter email" class="block w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <button type="submit" formaction="{{ route('sm-school.settings.test-email', ['template' => $key]) }}" class="px-4 py-2 border border-orange-500 text-orange-500 bg-white text-sm font-bold rounded-md hover:bg-orange-50 whitespace-nowrap">Send Test Email</button>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-[#f28123] text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</section>
@endif
@endsection