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
        $trashCount = isset($trashedStudents) ? $trashedStudents->count() : 0;
        $tabs = [
            'general' => 'General',
            'email-carrier' => 'Email Carrier',
            'email-templates' => 'Email Templates',
            'sms-provider' => 'SMS Provider',
            'sms-templates' => 'SMS Templates',
            'payment-methods' => 'Payment Methods',
            'registration' => 'Registration',
            'dashboard' => 'Dashboard',
            'live-classes' => 'Live Classes',
            'card-background' => 'Card Background',
            'examination' => 'Examination',
            'trash' => 'Trash' . ($trashCount > 0 ? " ({$trashCount})" : ''),
        ];
    @endphp
    @foreach($tabs as $tab => $label)
        <a href="{{ route('sm-school.settings.index', ['tab' => $tab, 'session_id' => $session->id]) }}" class="shrink-0 rounded-md px-3 py-2 text-xs font-semibold {{ $activeTab === $tab ? 'bg-navy text-white' : 'text-body hover:bg-gray-100' }} {{ $tab === 'trash' && $trashCount > 0 && $activeTab !== 'trash' ? 'text-red-600 font-bold' : '' }}">{{ $label }}</a>
    @endforeach
</nav>

@if($activeTab === 'registration')
<form action="{{ route('sm-school.settings.update') }}" method="POST" class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white mb-6">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Registration Settings</h3>
    </div>
    @csrf
    <input type="hidden" name="form_section" value="registration">
    <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
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
                <input type="hidden" name="form_section" value="payment_methods">
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

@elseif($activeTab === 'live-classes')
<section class="col-span-full space-y-6">
    <form action="{{ route('sm-school.settings.update') }}" method="POST" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @csrf
        <input type="hidden" name="form_section" value="live_classes">
        <input type="hidden" name="academic_session_id" value="{{ $session->id }}">

        <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Live Classes — Google Meet Settings</h3>
                    <p class="text-xs text-gray-500">Configure online live meeting integration for session <strong class="text-gray-700">{{ $session->name }}</strong></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $isGoogleMeetActive = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'google_meet_enable', '0') == '1';
                @endphp
                <span id="google_meet_header_badge" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isGoogleMeetActive ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                    {{ $isGoogleMeetActive ? '● Google Meet Active' : '○ Inactive / Disabled' }}
                </span>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Active / Inactive ON-OFF Toggle Switch Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-gray-300 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-gray-900 cursor-pointer select-none" onclick="toggleGoogleMeetSwitch()">Enable Google Meet for this Session</h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Session {{ $session->name }}</span>
                        </div>
                        <p class="text-xs text-gray-600 max-w-2xl leading-relaxed">
                            When enabled, teaching staff assigned to classes in session <strong>{{ $session->name }}</strong> can connect their Google Meet account to host live video classes. Each school and academic session maintains independent live class settings and teacher credentials.
                        </p>
                    </div>

                    <!-- Interactive Switch & Status Badge Container -->
                    <div class="flex flex-col items-end gap-1.5 shrink-0 self-start sm:self-center">
                        <div class="flex items-center gap-3">
                            <!-- Clickable Status Badge (Active / Inactive) -->
                            <button type="button" onclick="toggleGoogleMeetSwitch()" id="google_meet_status_badge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-extrabold transition-all cursor-pointer select-none {{ $isGoogleMeetActive ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm' : 'bg-gray-100 text-gray-600 border border-gray-300' }}" title="Click to toggle Active / Inactive">
                                <span id="google_meet_status_dot" class="h-2 w-2 rounded-full {{ $isGoogleMeetActive ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                <span id="google_meet_status_text">{{ $isGoogleMeetActive ? 'ACTIVE (ON)' : 'INACTIVE (OFF)' }}</span>
                            </button>

                            <!-- Hidden Checkbox for Form Submit -->
                            <input type="checkbox" id="google_meet_enable" name="google_meet_enable" value="1" class="hidden" {{ $isGoogleMeetActive ? 'checked' : '' }} onchange="updateGoogleMeetToggleUI(this.checked)">

                            <!-- Visible iOS-style ON/OFF Toggle Switch -->
                            <button type="button" role="switch" aria-checked="{{ $isGoogleMeetActive ? 'true' : 'false' }}" id="google_meet_switch_btn" onclick="toggleGoogleMeetSwitch()" class="relative inline-flex items-center cursor-pointer transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-inner" style="width: 68px; height: 34px; border-radius: 9999px; background-color: {{ $isGoogleMeetActive ? '#10b981' : '#9ca3af' }}; padding: 3px; border: 2px solid {{ $isGoogleMeetActive ? '#059669' : '#6b7280' }};" title="Click to toggle Active / Inactive">
                                <!-- Switch Sliding Knob with Text Indicator -->
                                <span id="google_meet_switch_thumb" class="bg-white shadow-md transition-transform duration-200 ease-in-out inline-flex items-center justify-center font-black text-[10px] select-none uppercase tracking-wide" style="width: 26px; height: 26px; border-radius: 9999px; transform: translateX({{ $isGoogleMeetActive ? '34px' : '0px' }}); color: {{ $isGoogleMeetActive ? '#059669' : '#6b7280' }};">
                                    <span id="google_meet_switch_thumb_text">{{ $isGoogleMeetActive ? 'ON' : 'OFF' }}</span>
                                </span>
                            </button>
                        </div>
                        <div id="google_meet_save_status" class="h-4 flex items-center pr-1"></div>
                    </div>
                </div>
            </div>

            <!-- Fixed Default Redirect URI -->
            <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">i</span>
                            <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Default Google OAuth Redirect URI</h4>
                        </div>
                        <p class="text-xs text-blue-700 mt-1">This is the fixed redirect URI generated for your system. Copy and paste this exact URL into Google Cloud Console under <strong>Authorized redirect URIs</strong>.</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <input type="text" id="google_redirect_uri_display" readonly value="{{ url('/google-meet/oauth/callback') }}" class="w-72 sm:w-80 px-3 py-1.5 text-xs font-mono bg-white border border-blue-300 rounded-lg text-gray-800 shadow-sm select-all">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('google_redirect_uri_display').value); alert('Redirect URI copied to clipboard!');" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 shadow-sm transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step by Step Setup Instructions (From Setup Guide) -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h4 class="text-sm font-bold text-gray-900">Google Meet API - Client ID & Client Secret Setup Guide</h4>
                    </div>
                    <a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800">
                        Open Google Cloud Console
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3.5">
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-white text-[10px]">1</span>
                            Open Google Cloud Console
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            Sign in to your Google / Workspace account and navigate to <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 underline">https://console.cloud.google.com/</a>.
                        </p>
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3.5">
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-white text-[10px]">2</span>
                            Create a New Project
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            Click the Project Selector at the top and click <strong>New Project</strong> (e.g. <em>EdOrbit Live Classes</em>). Make sure the newly created project is selected.
                        </p>
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3.5">
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-white text-[10px]">3</span>
                            Enable Google Meet & Calendar API
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            Go to <strong>APIs & Services &gt; Library</strong>. Search for <strong>Google Meet API</strong> and <strong>Google Calendar API</strong>, open each and click <strong>Enable</strong>.
                        </p>
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3.5">
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-white text-[10px]">4</span>
                            Configure OAuth Consent Screen
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            Go to <strong>APIs & Services &gt; OAuth consent screen</strong>. Select User Type <strong>External</strong>, enter App Name (e.g. <em>EdOrbit Meetings</em>), your email address, and save.
                        </p>
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3.5">
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-white text-[10px]">5</span>
                            Create OAuth Client ID
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            Go to <strong>APIs & Services &gt; Credentials</strong>. Click <strong>+ Create Credentials &gt; OAuth client ID</strong>. Under Application type, select <strong>Web application</strong>.
                        </p>
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3.5">
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-white text-[10px]">6</span>
                            Add Authorized Redirect URI
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            In Authorized redirect URIs, click <strong>+ Add URI</strong> and paste: <br/>
                            <code class="text-blue-700 bg-blue-50 px-1 py-0.5 rounded font-mono">{{ url('/google-meet/oauth/callback') }}</code><br/>
                            <em>Ensure exact match (no trailing slash changes).</em>
                        </p>
                    </div>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                    <strong class="font-bold flex items-center gap-1.5 mb-1">
                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Staff Live Class Credentials:
                    </strong>
                    Once enabled here, go to <a href="{{ route('sm-administrator.live-classes.index') }}" class="font-bold underline text-amber-900">Administrator &gt; Live Class Settings</a>. You will see all teaching staff assigned to classes in this session. Click <strong>Add Credential</strong> or <strong>Connect with Google</strong> on each staff member to link their Google Client ID, Client Secret, and auto-generate OAuth tokens!
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="px-6 py-2.5 bg-navy text-white text-sm font-bold rounded-lg hover:bg-navy/90 shadow-sm transition-colors">
                    Save Live Classes Settings
                </button>
            </div>
        </div>
    </form>

    <script>
        function toggleGoogleMeetSwitch(autoSave = true) {
            const checkbox = document.getElementById('google_meet_enable');
            checkbox.checked = !checkbox.checked;
            updateGoogleMeetToggleUI(checkbox.checked);

            if (autoSave) {
                saveGoogleMeetStatus(checkbox.checked);
            }
        }

        function updateGoogleMeetToggleUI(isActive) {
            const btn = document.getElementById('google_meet_switch_btn');
            const thumb = document.getElementById('google_meet_switch_thumb');
            const thumbText = document.getElementById('google_meet_switch_thumb_text');
            const badge = document.getElementById('google_meet_status_badge');
            const dot = document.getElementById('google_meet_status_dot');
            const text = document.getElementById('google_meet_status_text');
            const headerBadge = document.getElementById('google_meet_header_badge');

            if (btn) btn.setAttribute('aria-checked', isActive ? 'true' : 'false');

            if (isActive) {
                if (btn) {
                    btn.style.backgroundColor = '#10b981';
                    btn.style.borderColor = '#059669';
                }
                if (thumb) {
                    thumb.style.transform = 'translateX(34px)';
                    thumb.style.color = '#059669';
                }
                if (thumbText) thumbText.textContent = 'ON';

                if (badge) badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-extrabold transition-all cursor-pointer select-none bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm';
                if (dot) dot.className = 'h-2 w-2 rounded-full bg-emerald-500 animate-pulse';
                if (text) text.textContent = 'ACTIVE (ON)';

                if (headerBadge) {
                    headerBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300';
                    headerBadge.textContent = '● Google Meet Active';
                }
            } else {
                if (btn) {
                    btn.style.backgroundColor = '#9ca3af';
                    btn.style.borderColor = '#6b7280';
                }
                if (thumb) {
                    thumb.style.transform = 'translateX(0px)';
                    thumb.style.color = '#6b7280';
                }
                if (thumbText) thumbText.textContent = 'OFF';

                if (badge) badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-extrabold transition-all cursor-pointer select-none bg-gray-100 text-gray-600 border border-gray-300';
                if (dot) dot.className = 'h-2 w-2 rounded-full bg-gray-400';
                if (text) text.textContent = 'INACTIVE (OFF)';

                if (headerBadge) {
                    headerBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200';
                    headerBadge.textContent = '○ Inactive / Disabled';
                }
            }
        }

        function saveGoogleMeetStatus(isActive) {
            const statusIndicator = document.getElementById('google_meet_save_status');
            if (statusIndicator) {
                statusIndicator.innerHTML = '<span class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-semibold animate-pulse"><svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Saving...</span>';
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('form_section', 'live_classes');
            formData.append('academic_session_id', '{{ $session->id }}');
            formData.append('google_meet_enable', isActive ? '1' : '0');

            fetch('{{ route("sm-school.settings.update") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (statusIndicator) {
                    statusIndicator.innerHTML = `<span class="inline-flex items-center gap-1 text-[11px] font-bold ${isActive ? 'text-emerald-700' : 'text-gray-600'}">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Saved: ${isActive ? 'Active (ON)' : 'Inactive (OFF)'}
                    </span>`;
                    setTimeout(() => {
                        statusIndicator.innerHTML = '';
                    }, 3500);
                }
            })
            .catch(err => {
                if (statusIndicator) {
                    statusIndicator.innerHTML = '<span class="text-[11px] text-amber-600 font-medium">Auto-save skipped (click Save button below)</span>';
                }
            });
        }
    </script>
</section>

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
        <button type="button" onclick="document.getElementById('template_{{ $key }}').classList.toggle('hidden')" class="w-full flex items-center justify-between bg-navy px-5 py-3 text-white hover:opacity-90 transition-opacity" style="background-color: var(--color-primary);">
            <h3 class="text-sm font-bold text-center w-full text-white">{{ $title }}</h3>
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
                        <button type="submit" formaction="{{ route('sm-school.settings.test-email', ['template' => $key]) }}" class="px-4 py-2 border border-navy text-navy bg-white text-sm font-bold rounded-md hover:bg-primary-light whitespace-nowrap" style="border-color: var(--color-primary); color: var(--color-primary);">Send Test Email</button>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:opacity-90" style="background-color: var(--color-primary);">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</section>

@elseif($activeTab === 'sms-provider')
@php
    $currentProvider = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'sms_provider', 'twilio');
    $isSmsActive = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'sms_enable', '0') == '1';
@endphp
<section class="col-span-full overflow-hidden rounded-xl border border-border bg-white shadow-sm">
    <!-- Tab Header Banner -->
    <div class="border-b border-border bg-[#F5F1EB]/40 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-navy text-white shadow-xs" style="background-color: var(--color-primary);">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-heading">SMS Provider & Gateway Settings</h3>
                <p class="text-xs text-body">Configure Indian and Global SMS gateways for session <strong class="text-heading">{{ $session->name }}</strong></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span id="master_sms_status_badge" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isSmsActive ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                {{ $isSmsActive ? '● SMS Service Active' : '○ SMS Service Inactive' }}
            </span>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="p-6 space-y-6">
        <!-- Master ON/OFF Switch Card -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 rounded-xl border border-border bg-[#F5F1EB]/30">
            <div>
                <label class="text-sm font-bold text-heading select-none cursor-pointer" onclick="toggleMasterSmsSwitch()">Enable SMS Service for this Session</label>
                <p class="text-xs text-body mt-0.5">Activate automated SMS sending for admissions, fees, attendance, and exams in session <strong>{{ $session->name }}</strong>.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <input type="hidden" id="sms_enable" name="sms_enable" form="sms_provider_form" value="{{ $isSmsActive ? '1' : '0' }}">
                <button type="button" role="switch" aria-checked="{{ $isSmsActive ? 'true' : 'false' }}" id="master_sms_switch_btn" onclick="toggleMasterSmsSwitch()" class="relative inline-flex items-center cursor-pointer transition-all duration-200 ease-in-out focus:outline-none shadow-inner select-none" style="width: 68px; height: 34px; border-radius: 9999px; background-color: {{ $isSmsActive ? '#10b981' : '#9ca3af' }}; padding: 3px; border: 2px solid {{ $isSmsActive ? '#059669' : '#6b7280' }};" title="Click to Toggle ON / OFF">
                    <span id="master_sms_switch_thumb" class="bg-white shadow-md transition-transform duration-200 ease-in-out inline-flex items-center justify-center font-black text-[10px] select-none uppercase tracking-wide" style="width: 26px; height: 26px; border-radius: 9999px; transform: translateX({{ $isSmsActive ? '34px' : '0px' }}); color: {{ $isSmsActive ? '#059669' : '#6b7280' }};">
                        <span id="master_sms_switch_thumb_text">{{ $isSmsActive ? 'ON' : 'OFF' }}</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Carrier Configuration Form (Styled strictly matching Image 3 & 4) -->
        <form id="sms_provider_form" action="{{ route('sm-school.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
            <input type="hidden" name="form_section" value="sms_provider">

            <!-- Row 1: SMS Carrier Dropdown -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                <label for="sms_carrier_select" class="text-sm font-bold text-heading md:col-span-1">SMS Carrier:</label>
                <div class="md:col-span-3">
                    <select name="sms_provider" id="sms_carrier_select" onchange="onSmsCarrierChange(this.value)" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm font-semibold text-heading bg-white shadow-xs focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <option value="twilio" {{ $currentProvider === 'twilio' ? 'selected' : '' }}>Twilio (International)</option>
                        <option value="msg91" {{ $currentProvider === 'msg91' ? 'selected' : '' }}>MSG91 (India)</option>
                        <option value="fast2sms" {{ $currentProvider === 'fast2sms' ? 'selected' : '' }}>Fast2SMS (India)</option>
                        <option value="textlocal" {{ $currentProvider === 'textlocal' ? 'selected' : '' }}>Textlocal (India)</option>
                        <option value="exotel" {{ $currentProvider === 'exotel' ? 'selected' : '' }}>Exotel (India)</option>
                        <option value="gupshup" {{ $currentProvider === 'gupshup' ? 'selected' : '' }}>Gupshup (India)</option>
                        <option value="kaleyra" {{ $currentProvider === 'kaleyra' ? 'selected' : '' }}>Kaleyra (CPaaS)</option>
                        <option value="custom" {{ $currentProvider === 'custom' ? 'selected' : '' }}>Custom HTTP API Gateway</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: SMS Package Link (Matches Image 3 & 4) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                <label class="text-sm font-bold text-heading md:col-span-1">SMS Package:</label>
                <div class="md:col-span-3">
                    <a id="sms_package_link" href="https://console.twilio.com/" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-navy hover:underline inline-flex items-center gap-1" style="color: var(--color-primary);">
                        <span id="sms_package_text">Click for SMS Package Features and Pricing</span> &nearr;
                    </a>
                </div>
            </div>

            <hr class="border-border my-2">

            <!-- ================= CARRIER SPECIFIC FIELDS (ONLY SELECTED OPENS) ================= -->

            <!-- 1. Twilio Fields (Matches Image 3) -->
            <div id="fields_twilio" class="carrier-fields space-y-4 {{ $currentProvider === 'twilio' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">SID: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="text" name="twilio_account_sid" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'twilio_account_sid') }}" placeholder="Twilio Account SID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Auth Token: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="password" name="twilio_auth_token" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'twilio_auth_token') }}" placeholder="Twilio Auth Token" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">From:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="twilio_from_number" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'twilio_from_number') }}" placeholder="A Twilio phone number you purchased at twilio.com/console" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 2. MSG91 Fields (Matches Image 4) -->
            <div id="fields_msg91" class="carrier-fields space-y-4 {{ $currentProvider === 'msg91' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Auth Key: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="password" name="msg91_auth_key" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'msg91_auth_key') }}" placeholder="Msg91 Auth Key" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Route:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="msg91_route" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'msg91_route', '4') }}" placeholder="4" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Sender:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="msg91_sender_id" maxlength="6" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'msg91_sender_id', 'EDUVRS') }}" placeholder="Msg91 Sender" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono uppercase focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Country Code:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="msg91_country_code" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'msg91_country_code', '91') }}" placeholder="Msg91 Country Code" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">DLT Entity ID:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="msg91_entity_id" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'msg91_entity_id') }}" placeholder="19-digit DLT Principal Entity ID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 3. Fast2SMS Fields -->
            <div id="fields_fast2sms" class="carrier-fields space-y-4 {{ $currentProvider === 'fast2sms' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">API Key: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="password" name="fast2sms_api_key" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fast2sms_api_key') }}" placeholder="Fast2SMS Authorization API Key" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Route:</label>
                    <div class="md:col-span-3">
                        @php $fRoute = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fast2sms_route', 'v3'); @endphp
                        <select name="fast2sms_route" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-semibold focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <option value="v3" {{ $fRoute === 'v3' ? 'selected' : '' }}>Quick SMS (v3)</option>
                            <option value="dlt" {{ $fRoute === 'dlt' ? 'selected' : '' }}>DLT SMS (100% Delivery to DND)</option>
                            <option value="otp" {{ $fRoute === 'otp' ? 'selected' : '' }}>OTP Route (High Priority)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Sender ID:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="fast2sms_sender_id" maxlength="6" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fast2sms_sender_id', 'TXTIND') }}" placeholder="Fast2SMS Sender (e.g. TXTIND)" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono uppercase focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Entity ID:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="fast2sms_entity_id" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'fast2sms_entity_id') }}" placeholder="TRAI DLT Entity ID (Optional)" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 4. Textlocal Fields -->
            <div id="fields_textlocal" class="carrier-fields space-y-4 {{ $currentProvider === 'textlocal' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">API Key: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="password" name="textlocal_api_key" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'textlocal_api_key') }}" placeholder="Textlocal API Key" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Sender:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="textlocal_sender_id" maxlength="6" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'textlocal_sender_id', 'TXTLCL') }}" placeholder="Textlocal Sender" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono uppercase focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Country Code:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="textlocal_country_code" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'textlocal_country_code', '91') }}" placeholder="91" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 5. Exotel Fields -->
            <div id="fields_exotel" class="carrier-fields space-y-4 {{ $currentProvider === 'exotel' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">SID: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="text" name="exotel_sid" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exotel_sid') }}" placeholder="Exotel Account SID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">API Key & Token: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3 grid grid-cols-2 gap-3 max-w-xl">
                        <input type="text" name="exotel_api_key" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exotel_api_key') }}" placeholder="API Key" class="px-3.5 py-2.5 border border-border rounded-lg text-sm font-mono">
                        <input type="password" name="exotel_api_token" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exotel_api_token') }}" placeholder="API Token" class="px-3.5 py-2.5 border border-border rounded-lg text-sm font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Caller ID / From:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="exotel_sender_id" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'exotel_sender_id') }}" placeholder="Caller ID or Sender ID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 6. Gupshup Fields -->
            <div id="fields_gupshup" class="carrier-fields space-y-4 {{ $currentProvider === 'gupshup' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">User ID: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="text" name="gupshup_userid" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'gupshup_userid') }}" placeholder="Enterprise User ID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Password: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="password" name="gupshup_password" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'gupshup_password') }}" placeholder="Enterprise Password" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Mask / Sender:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="gupshup_mask" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'gupshup_mask') }}" placeholder="Registered Mask Name" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 7. Kaleyra Fields -->
            <div id="fields_kaleyra" class="carrier-fields space-y-4 {{ $currentProvider === 'kaleyra' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">API Key: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="password" name="kaleyra_api_key" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'kaleyra_api_key') }}" placeholder="Kaleyra API Key" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">SID: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="text" name="kaleyra_sid" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'kaleyra_sid') }}" placeholder="Kaleyra SID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Sender ID:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="kaleyra_sender_id" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'kaleyra_sender_id') }}" placeholder="Sender ID" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- 8. Custom HTTP Gateway Fields -->
            <div id="fields_custom" class="carrier-fields space-y-4 {{ $currentProvider === 'custom' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Gateway API URL: <span class="text-red-500">*</span></label>
                    <div class="md:col-span-3">
                        <input type="text" name="custom_sms_url" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'custom_sms_url') }}" placeholder="https://api.smsvendor.in/api/v1/send" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">HTTP Method:</label>
                    <div class="md:col-span-3">
                        @php $cMethod = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'custom_sms_method', 'GET'); @endphp
                        <select name="custom_sms_method" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-semibold focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <option value="GET" {{ $cMethod === 'GET' ? 'selected' : '' }}>GET Request</option>
                            <option value="POST" {{ $cMethod === 'POST' ? 'selected' : '' }}>POST Request (Form URL-Encoded)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Param Mapping:</label>
                    <div class="md:col-span-3 grid grid-cols-2 gap-3 max-w-xl">
                        <div>
                            <span class="text-[11px] text-body block mb-1">Mobile Param:</span>
                            <input type="text" name="custom_sms_mobile_param" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'custom_sms_mobile_param', 'mobile') }}" placeholder="mobile" class="w-full px-3 py-2 text-xs border border-border rounded-lg font-mono">
                        </div>
                        <div>
                            <span class="text-[11px] text-body block mb-1">Message Param:</span>
                            <input type="text" name="custom_sms_message_param" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'custom_sms_message_param', 'message') }}" placeholder="message" class="w-full px-3 py-2 text-xs border border-border rounded-lg font-mono">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <label class="text-sm font-bold text-heading md:col-span-1">Extra URL Parameters:</label>
                    <div class="md:col-span-3">
                        <input type="text" name="custom_sms_extra_params" value="{{ \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'custom_sms_extra_params') }}" placeholder="apikey=xyz&sender=MYSCHL&route=4" class="block w-full max-w-xl px-3.5 py-2.5 border border-border rounded-lg text-sm bg-white font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>
            </div>

            <!-- Bottom Actions: [Save] button and Send Test SMS Tool (Matches Image 3 & 4) -->
            <div class="pt-6 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="submit" class="px-8 py-2.5 bg-navy text-white text-sm font-bold rounded-md hover:opacity-90 shadow-sm inline-flex items-center gap-2 transition-opacity" style="background-color: var(--color-primary);">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save
                </button>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="text" name="test_phone" placeholder="Enter mobile for test" class="block w-full sm:w-52 px-3 py-2 border border-border rounded-md text-xs font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <button type="submit" formaction="{{ route('sm-school.settings.test-sms') }}" class="px-4 py-2 border border-navy text-navy bg-white text-xs font-bold rounded-md hover:bg-primary-light whitespace-nowrap shadow-xs transition-colors" style="border-color: var(--color-primary); color: var(--color-primary);">
                        Send Test SMS
                    </button>
                </div>
            </div>
        </form>

        <!-- India TRAI DLT Guidelines Box -->
        <div class="rounded-xl border border-navy/30 bg-[#F5F1EB]/50 p-4 space-y-2" style="border-color: rgba(180, 122, 66, 0.25); background-color: rgba(180, 122, 66, 0.05);">
            <div class="flex items-center gap-2 font-bold text-heading text-xs">
                <svg class="h-4 w-4 text-navy shrink-0" style="color: var(--color-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                TRAI DLT Guidelines in India:
            </div>
            <p class="text-body leading-relaxed text-[11px]">
                Under TRAI mandate in India, all commercial & educational SMS require registration on a DLT platform (such as <em>Vilpower / Jio / Airtel / Smartping / BSNL</em>). Register your School Entity ID, approve your 6-character Header (Sender ID), and set approved 19-digit Template IDs in the <strong>SMS Templates</strong> tab.
            </p>
        </div>
    </div>

    <!-- JavaScript for SMS Provider & Carrier Switching (Matches Image 3 & 4) -->
    <script>
        const smsPackageUrls = {
            twilio: {
                url: 'https://console.twilio.com/',
                text: 'Click for SMS Package Features and Pricing'
            },
            msg91: {
                url: 'https://msg91.com/sms',
                text: 'Click for SMS Package Features and Pricing'
            },
            fast2sms: {
                url: 'https://www.fast2sms.com/',
                text: 'Click for SMS Package Features and Pricing'
            },
            textlocal: {
                url: 'https://www.textlocal.in/pricing/',
                text: 'Click for SMS Package Features and Pricing'
            },
            exotel: {
                url: 'https://exotel.com/pricing/',
                text: 'Click for SMS Package Features and Pricing'
            },
            gupshup: {
                url: 'https://www.gupshup.io/pricing',
                text: 'Click for SMS Package Features and Pricing'
            },
            kaleyra: {
                url: 'https://www.kaleyra.com/pricing/',
                text: 'Click for SMS Package Features and Pricing'
            },
            custom: {
                url: '#',
                text: 'Click for Custom HTTP API Gateway Documentation'
            }
        };

        function onSmsCarrierChange(carrier) {
            if (!carrier) return;
            // Hide all carrier sections
            document.querySelectorAll('.carrier-fields').forEach(function(el) {
                el.classList.add('hidden');
            });

            // Show ONLY the selected carrier fields (Image 3 & 4 exact requirement)
            const activeFields = document.getElementById('fields_' + carrier);
            if (activeFields) {
                activeFields.classList.remove('hidden');
            }

            // Update SMS Package link text & URL
            const pkgLink = document.getElementById('sms_package_link');
            const pkgText = document.getElementById('sms_package_text');
            if (pkgLink && pkgText && smsPackageUrls[carrier]) {
                pkgLink.href = smsPackageUrls[carrier].url;
                pkgText.textContent = smsPackageUrls[carrier].text;
            }
        }

        function toggleMasterSmsSwitch() {
            const input = document.getElementById('sms_enable');
            const switchBtn = document.getElementById('master_sms_switch_btn');
            const switchThumb = document.getElementById('master_sms_switch_thumb');
            const thumbText = document.getElementById('master_sms_switch_thumb_text');
            const statusBadge = document.getElementById('master_sms_status_badge');

            if (!input || !switchBtn || !switchThumb) return;

            const isCurrentlyOn = input.value === '1';
            const newState = !isCurrentlyOn;
            input.value = newState ? '1' : '0';

            if (newState) {
                switchBtn.style.backgroundColor = '#10b981';
                switchBtn.style.borderColor = '#059669';
                switchThumb.style.transform = 'translateX(34px)';
                switchThumb.style.color = '#059669';
                if (thumbText) thumbText.textContent = 'ON';
                if (statusBadge) {
                    statusBadge.textContent = '● SMS Service Active';
                    statusBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300';
                }
            } else {
                switchBtn.style.backgroundColor = '#9ca3af';
                switchBtn.style.borderColor = '#6b7280';
                switchThumb.style.transform = 'translateX(0px)';
                switchThumb.style.color = '#6b7280';
                if (thumbText) thumbText.textContent = 'OFF';
                if (statusBadge) {
                    statusBadge.textContent = '○ SMS Service Inactive';
                    statusBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200';
                }
            }

            // Auto-save via AJAX so master toggle persists immediately
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('academic_session_id', '{{ $session->id }}');
                formData.append('form_section', 'sms_provider');
                formData.append('sms_enable', newState ? '1' : '0');

                fetch('{{ route("sm-school.settings.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).catch(function(err) { console.warn('Master SMS save:', err); });
            } catch(e) { console.warn(e); }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('sms_carrier_select');
            if (select) {
                onSmsCarrierChange(select.value);
            }
        });

        // Also run immediately in case DOM is already ready
        (function() {
            const select = document.getElementById('sms_carrier_select');
            if (select) {
                onSmsCarrierChange(select.value);
            }
        })();
    </script>
</section>

<!-- ================= SMS TEMPLATES TAB ================= -->
@elseif($activeTab === 'sms-templates')
<section class="col-span-full space-y-4">
    @php
        $smsTemplates = [
            'student_admission' => [
                'title' => 'Student Admission SMS Template',
                'desc' => 'Triggered when a new student is admitted or registered.',
                'default' => 'Dear [STUDENT_NAME], welcome to [SCHOOL_NAME]! Your admission in [CLASS] ([SECTION]) is confirmed. Roll No: [ROLL_NUMBER].',
            ],
            'invoice_generated' => [
                'title' => 'Invoice Generated SMS Template',
                'desc' => 'Sent to parents when a monthly or term fee invoice is issued.',
                'default' => 'Dear Parent, fee invoice of Rs. [AMOUNT] for [STUDENT_NAME] has been generated by [SCHOOL_NAME]. Due Date: [DUE_DATE].',
            ],
            'fee_received' => [
                'title' => 'Fee Payment Received SMS Template',
                'desc' => 'Sent to parents immediately upon collecting fee payments.',
                'default' => 'Dear Parent, payment of Rs. [AMOUNT] for [STUDENT_NAME] received successfully by [SCHOOL_NAME]. Txn ID: [TRANSACTION_ID]. Thank you.',
            ],
            'leave_application' => [
                'title' => 'Leave Application Status SMS Template',
                'desc' => 'Notification when student or staff leave request is updated.',
                'default' => 'Dear Parent, leave application for [STUDENT_NAME] has been updated by [SCHOOL_NAME].',
            ],
            'attendance_taken' => [
                'title' => 'Attendance Taken Notification SMS Template',
                'desc' => 'Sent to inform parents that daily class attendance has been logged.',
                'default' => 'Attendance Update: [STUDENT_NAME] attendance has been marked for today at [SCHOOL_NAME].',
            ],
            'absent_student' => [
                'title' => 'Absent Student Alert SMS Template',
                'desc' => 'High-priority SMS alert sent to parents if student is absent.',
                'default' => 'Alert: [STUDENT_NAME] is marked ABSENT today at [SCHOOL_NAME]. If not informed, please contact school office immediately.',
            ],
            'due_date' => [
                'title' => 'Fee Due Date Reminder SMS Template',
                'desc' => 'Sent 2-3 days before pending fee invoice deadline.',
                'default' => 'Fee Reminder: Pending fee of Rs. [AMOUNT] for [STUDENT_NAME] is due on [DUE_DATE]. Please pay on time. [SCHOOL_NAME].',
            ],
            'exam_admit_card' => [
                'title' => 'Exam Admit Card Release SMS Template',
                'desc' => 'Notification when admit cards are published for students.',
                'default' => 'Admit Card for [STUDENT_NAME] ([CLASS]) has been released for upcoming examinations by [SCHOOL_NAME]. Download from student portal.',
            ],
            'exam_result' => [
                'title' => 'Exam Result Announcement SMS Template',
                'desc' => 'Sent to parents when exam marks and report cards are published.',
                'default' => 'Exam Results Announced! Result for [STUDENT_NAME] ([CLASS]) is published. View report card on student portal. [SCHOOL_NAME].',
            ],
        ];
    @endphp

    <!-- Templates Header Banner -->
    <div class="rounded-xl border border-border bg-[#F5F1EB]/40 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-navy text-white shadow-xs" style="background-color: var(--color-primary);">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-heading">SMS Notification Templates</h3>
                    <p class="text-xs text-body">Configure event-driven automated SMS alerts for session <strong class="text-heading">{{ $session->name }}</strong></p>
                </div>
            </div>
            <a href="{{ route('sm-school.settings.index', ['tab' => 'sms-provider', 'session_id' => $session->id]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-navy hover:underline" style="color: var(--color-primary);">
                Configure SMS Provider &rarr;
            </a>
        </div>
    </div>

    <!-- Accordion Templates List -->
    @foreach($smsTemplates as $key => $tpl)
    @php
        $isTplEnabled = \App\Models\SchoolSetting::getSetting($school->id, $session->id, "{$key}_sms_enable", '0') == '1';
        $savedBody = \App\Models\SchoolSetting::getSetting($school->id, $session->id, "{$key}_sms_body");
        $bodyVal = !empty($savedBody) ? $savedBody : $tpl['default'];
        $dltId = \App\Models\SchoolSetting::getSetting($school->id, $session->id, "{$key}_sms_dlt_id", '');
    @endphp
    <div class="overflow-hidden rounded-xl border border-border bg-white shadow-xs transition-all">
        <!-- Accordion Header Button with Brand Color Theme -->
        <button type="button" onclick="document.getElementById('sms_tpl_{{ $key }}').classList.toggle('hidden')" class="w-full flex items-center justify-between px-5 py-3.5 bg-navy text-white hover:opacity-90 transition-opacity text-left shadow-xs" style="background-color: var(--color-primary); color: #ffffff;">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-white text-xs font-bold shrink-0">
                    {{ $loop->iteration }}
                </span>
                <div>
                    <h4 class="text-sm font-bold text-white">{{ $tpl['title'] }}</h4>
                    <span class="text-[11px] text-white/80 font-normal hidden sm:inline ml-1">{{ $tpl['desc'] }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span id="{{ $key }}_header_badge" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $isTplEnabled ? 'bg-white/25 text-white border border-white/40' : 'bg-black/20 text-white/80' }}">
                    {{ $isTplEnabled ? '● Enabled' : '○ Disabled' }}
                </span>
                <svg class="h-4 w-4 text-white transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </button>

        <!-- Accordion Body -->
        <div id="sms_tpl_{{ $key }}" class="{{ $loop->first ? '' : 'hidden' }} p-6 border-t border-border bg-white space-y-5">
            <form action="{{ route('sm-school.settings.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="academic_session_id" value="{{ $session->id }}">
                <input type="hidden" name="form_section" value="sms_templates">
                <input type="hidden" name="template_key" value="{{ $key }}">

                <!-- Enable SMS ON/OFF Toggle Switch & DLT Template ID (User requested ON/OFF TOGGLE) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded-xl bg-[#F5F1EB]/30 p-4.5 border border-border items-center">
                    <!-- ON/OFF Switch Container -->
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-heading select-none cursor-pointer" onclick="toggleSmsTemplateSwitch('{{ $key }}')">Enable SMS for this Event:</label>
                        <div class="flex items-center gap-2.5">
                            <input type="hidden" id="{{ $key }}_sms_enable" name="{{ $key }}_sms_enable" value="{{ $isTplEnabled ? '1' : '0' }}">
                            
                            <!-- Interactive ON/OFF Switch Button -->
                            <button type="button" role="switch" aria-checked="{{ $isTplEnabled ? 'true' : 'false' }}" id="{{ $key }}_switch_btn" onclick="toggleSmsTemplateSwitch('{{ $key }}')" class="relative inline-flex items-center cursor-pointer transition-all duration-200 ease-in-out focus:outline-none shadow-inner select-none" style="width: 66px; height: 32px; border-radius: 9999px; background-color: {{ $isTplEnabled ? '#10b981' : '#9ca3af' }}; padding: 3px; border: 2px solid {{ $isTplEnabled ? '#059669' : '#6b7280' }};" title="Click to toggle ON / OFF">
                                <span id="{{ $key }}_switch_thumb" class="bg-white shadow-md transition-transform duration-200 ease-in-out inline-flex items-center justify-center font-black text-[10px] select-none uppercase tracking-wide" style="width: 24px; height: 24px; border-radius: 9999px; transform: translateX({{ $isTplEnabled ? '34px' : '0px' }}); color: {{ $isTplEnabled ? '#059669' : '#6b7280' }};">
                                    <span id="{{ $key }}_switch_thumb_text">{{ $isTplEnabled ? 'ON' : 'OFF' }}</span>
                                </span>
                            </button>

                            <span id="{{ $key }}_status_badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold select-none {{ $isTplEnabled ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-600 border border-gray-300' }}">
                                {{ $isTplEnabled ? 'Active (ON)' : 'Inactive (OFF)' }}
                            </span>
                        </div>
                    </div>

                    <!-- DLT Template ID (India) -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-heading whitespace-nowrap">DLT Template ID (India):</label>
                        <input type="text" name="{{ $key }}_sms_dlt_id" value="{{ $dltId }}" placeholder="e.g. 1707161829384729102" class="w-full px-3 py-1.5 text-xs border border-border rounded-md font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>
                </div>

                <!-- Clickable Variables Selector -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-heading">Available Dynamic Tags (Click to insert):</span>
                        <span class="text-[11px] text-body">Click any variable below to add at cursor</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                        @php
                            $tags = ['[STUDENT_NAME]', '[CLASS]', '[SECTION]', '[ROLL_NUMBER]', '[ENROLLMENT_NUMBER]', '[ADMISSION_NUMBER]', '[SCHOOL_NAME]'];
                            if (in_array($key, ['invoice_generated', 'fee_received', 'due_date'])) {
                                $tags = array_merge($tags, ['[AMOUNT]', '[TOTAL_AMOUNT]', '[PAYABLE_AMOUNT]', '[ISSUE_DATE]', '[DUE_DATE]', '[TRANSACTION_ID]']);
                            }
                        @endphp
                        @foreach($tags as $tag)
                            <button type="button" onclick="insertSmsTag('sms_body_{{ $key }}', '{{ $tag }}')" class="rounded px-2.5 py-1 text-xs font-mono font-bold transition-all shadow-2xs border border-navy/30 bg-navy/10 text-navy hover:bg-navy hover:text-white" style="color: var(--color-primary); background-color: rgba(180, 122, 66, 0.08); border-color: rgba(180, 122, 66, 0.3);">
                                + {{ $tag }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- SMS Body Textarea with Live Character Count -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="sms_body_{{ $key }}" class="text-xs font-bold text-heading">SMS Message Content:</label>
                        <span id="counter_{{ $key }}" class="text-[11px] font-mono text-body font-semibold">
                            {{ strlen($bodyVal) }} characters ({{ ceil(max(strlen($bodyVal), 1) / 160) }} SMS credit)
                        </span>
                    </div>
                    <textarea id="sms_body_{{ $key }}" name="{{ $key }}_sms_body" rows="4" oninput="updateSmsCounter('{{ $key }}', this.value)" class="block w-full px-3.5 py-2.5 border border-border rounded-lg text-xs leading-relaxed text-heading focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" placeholder="Enter SMS text...">{{ $bodyVal }}</textarea>
                </div>

                <!-- Footer: Test SMS & Save Button -->
                <div class="flex flex-col sm:flex-row justify-between items-center border-t border-border pt-4 gap-4">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="text" name="test_phone" placeholder="Enter mobile for test" class="block w-full sm:w-52 px-3 py-2 border border-border rounded-lg text-xs font-mono focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                        <button type="submit" formaction="{{ route('sm-school.settings.test-sms', ['template' => $key]) }}" class="px-4 py-2 border border-navy text-navy bg-white text-xs font-bold rounded-lg hover:bg-primary-light whitespace-nowrap shadow-xs transition-colors" style="border-color: var(--color-primary); color: var(--color-primary);">
                            Send Test SMS
                        </button>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-navy text-white text-xs font-bold rounded-lg hover:opacity-90 shadow-xs transition-opacity" style="background-color: var(--color-primary);">
                        Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</section>

<!-- JavaScript for Dynamic SMS Provider Switching & Interactive ON/OFF Toggles -->
<script>
    const smsPackageUrls = {
        twilio: {
            url: 'https://console.twilio.com/',
            text: 'Click for SMS Package Features and Pricing (Twilio)'
        },
        msg91: {
            url: 'https://msg91.com/sms',
            text: 'Click for SMS Package Features and Pricing (MSG91)'
        },
        fast2sms: {
            url: 'https://www.fast2sms.com/',
            text: 'Click for SMS Package Features and Pricing (Fast2SMS)'
        },
        textlocal: {
            url: 'https://www.textlocal.in/pricing/',
            text: 'Click for SMS Package Features and Pricing (Textlocal)'
        },
        exotel: {
            url: 'https://exotel.com/pricing/',
            text: 'Click for SMS Package Features and Pricing (Exotel)'
        },
        gupshup: {
            url: 'https://www.gupshup.io/pricing',
            text: 'Click for SMS Package Features and Pricing (Gupshup)'
        },
        kaleyra: {
            url: 'https://www.kaleyra.com/pricing/',
            text: 'Click for SMS Package Features and Pricing (Kaleyra)'
        },
        custom: {
            url: '#',
            text: 'Click for Custom HTTP API Gateway Documentation'
        }
    };

    function onSmsCarrierChange(carrier) {
        // Hide all carrier sections
        document.querySelectorAll('.carrier-fields').forEach(el => el.classList.add('hidden'));

        // Show ONLY the selected carrier fields (Matches Image 3 & 4)
        const activeFields = document.getElementById('fields_' + carrier);
        if (activeFields) {
            activeFields.classList.remove('hidden');
        }

        // Update the SMS Package Link
        const pkgLink = document.getElementById('sms_package_link');
        const pkgText = document.getElementById('sms_package_text');
        if (pkgLink && pkgText && smsPackageUrls[carrier]) {
            pkgLink.href = smsPackageUrls[carrier].url;
            pkgText.textContent = smsPackageUrls[carrier].text;
        }
    }

    function toggleMasterSmsSwitch() {
        const input = document.getElementById('sms_enable');
        const switchBtn = document.getElementById('master_sms_switch_btn');
        const switchThumb = document.getElementById('master_sms_switch_thumb');
        const thumbText = document.getElementById('master_sms_switch_thumb_text');
        const statusBadge = document.getElementById('master_sms_status_badge');

        if (!input || !switchBtn || !switchThumb) return;

        const isCurrentlyOn = input.value === '1';
        const newState = !isCurrentlyOn;
        input.value = newState ? '1' : '0';

        if (newState) {
            switchBtn.style.backgroundColor = '#10b981';
            switchBtn.style.borderColor = '#059669';
            switchThumb.style.transform = 'translateX(34px)';
            switchThumb.style.color = '#059669';
            thumbText.textContent = 'ON';
            if (statusBadge) {
                statusBadge.textContent = '● SMS Service Active';
                statusBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300';
            }
        } else {
            switchBtn.style.backgroundColor = '#9ca3af';
            switchBtn.style.borderColor = '#6b7280';
            switchThumb.style.transform = 'translateX(0px)';
            switchThumb.style.color = '#6b7280';
            thumbText.textContent = 'OFF';
            if (statusBadge) {
                statusBadge.textContent = '○ SMS Service Inactive';
                statusBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200';
            }
        }
    }

    function toggleSmsTemplateSwitch(key) {
        const input = document.getElementById(key + '_sms_enable');
        const switchBtn = document.getElementById(key + '_switch_btn');
        const switchThumb = document.getElementById(key + '_switch_thumb');
        const thumbText = document.getElementById(key + '_switch_thumb_text');
        const statusBadge = document.getElementById(key + '_status_badge');
        const headerBadge = document.getElementById(key + '_header_badge');

        if (!input || !switchBtn || !switchThumb) return;

        const isCurrentlyOn = input.value === '1';
        const newState = !isCurrentlyOn;
        input.value = newState ? '1' : '0';

        if (newState) {
            switchBtn.style.backgroundColor = '#10b981';
            switchBtn.style.borderColor = '#059669';
            switchThumb.style.transform = 'translateX(34px)';
            switchThumb.style.color = '#059669';
            if (thumbText) thumbText.textContent = 'ON';
            if (statusBadge) {
                statusBadge.textContent = 'Active (ON)';
                statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold select-none bg-emerald-100 text-emerald-800 border border-emerald-300';
            }
            if (headerBadge) {
                headerBadge.textContent = '● Enabled';
                headerBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-white/25 text-white border border-white/40';
            }
        } else {
            switchBtn.style.backgroundColor = '#9ca3af';
            switchBtn.style.borderColor = '#6b7280';
            switchThumb.style.transform = 'translateX(0px)';
            switchThumb.style.color = '#6b7280';
            if (thumbText) thumbText.textContent = 'OFF';
            if (statusBadge) {
                statusBadge.textContent = 'Inactive (OFF)';
                statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold select-none bg-gray-100 text-gray-600 border border-gray-300';
            }
            if (headerBadge) {
                headerBadge.textContent = '○ Disabled';
                headerBadge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-black/20 text-white/80';
            }
        }

        // Auto-save toggle state via AJAX to persist immediately
        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('academic_session_id', '{{ $session->id }}');
            formData.append('form_section', 'sms_templates');
            formData.append('template_key', key);
            formData.append(key + '_sms_enable', newState ? '1' : '0');

            fetch('{{ route("sm-school.settings.update") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function(err) { console.warn('Template SMS toggle save:', err); });
        } catch(e) { console.warn(e); }
    }

    function insertSmsTag(textareaId, tag) {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
        textarea.dispatchEvent(new Event('input'));
    }

    function updateSmsCounter(key, text) {
        const len = text.length;
        const credits = Math.ceil(Math.max(len, 1) / 160);
        const counter = document.getElementById('counter_' + key);
        if (counter) {
            counter.textContent = `${len} characters (${credits} SMS credit${credits > 1 ? 's' : ''})`;
            if (len > 160) {
                counter.className = 'text-[11px] font-mono text-amber-700 font-bold';
            } else {
                counter.className = 'text-[11px] font-mono text-body font-semibold';
            }
        }
    }
</script>
@endif

@if($activeTab === 'trash')
<section class="col-span-full space-y-6">
    <!-- Header Card -->
    <div class="rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        Student Trash Bin
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 border border-amber-300">
                            30-Day Auto Retention
                        </span>
                    </h3>
                    <p class="mt-1 text-xs text-gray-600 max-w-2xl leading-relaxed">
                        Whenever a student is deleted from the Student Registry, their entire data footprint (including <strong>Accounting Invoices & Payments</strong>, <strong>Transport Routes & Invoices</strong>, <strong>Library Cards & Issued Books</strong>, <strong>Student Portal Logins</strong>, <strong>Examination Admit Cards & Results</strong>) is safely archived here for <strong>30 days</strong>. You can restore them anytime with all their records intact. Records older than 30 days are automatically and permanently purged from the database.
                    </p>
                </div>
            </div>
            @if(isset($trashedStudents) && $trashedStudents->count() > 0)
                <div class="shrink-0 self-start sm:self-center">
                    <form action="{{ route('sm-school.settings.trash.empty') }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete ALL {{ $trashedStudents->count() }} students from trash? This action CANNOT be undone.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3.5 py-2 text-xs font-semibold text-red-600 shadow-sm hover:bg-red-50 hover:border-red-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Empty Trash ({{ $trashedStudents->count() }})
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Trashed Students Table Card -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50/75 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h4 class="text-sm font-bold text-gray-800">Archived Students</h4>
                <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ isset($trashedStudents) ? $trashedStudents->count() : 0 }}</span>
            </div>
            <span class="text-xs text-gray-500">Auto-purges after 30 days</span>
        </div>

        @if(!isset($trashedStudents) || $trashedStudents->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-4">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Trash is Empty</h3>
                <p class="mt-1 text-sm text-gray-500 max-w-md">
                    There are no deleted students currently in the trash bin. When a student is deleted, they will appear here with all their associated data for 30 days.
                </p>
                <a href="{{ route('sm-student.students.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-navy px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-navy/90">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Go to Students Registry
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50/50 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3.5">Student Details</th>
                            <th class="px-5 py-3.5">Class / Section</th>
                            <th class="px-5 py-3.5">Archived Records</th>
                            <th class="px-5 py-3.5">Deleted Date</th>
                            <th class="px-5 py-3.5">Time Remaining</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($trashedStudents as $trash)
                            @php
                                $counts = $trash->related_counts ?? [];
                                $days = $trash->days_remaining;
                                $deletedDate = $trash->deleted_at ? \Carbon\Carbon::parse($trash->deleted_at) : null;
                                $expiresDate = $trash->expires_at;
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <!-- Student Details -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100">
                                            @if($trash->photo)
                                                <img src="{{ asset('storage/' . $trash->photo) }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center font-bold text-gray-600">
                                                    {{ strtoupper(substr($trash->first_name ?: 'S', 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $trash->full_name ?: ($trash->first_name . ' ' . $trash->last_name) }}</div>
                                            <div class="text-[11px] text-gray-500 flex items-center gap-2 mt-0.5">
                                                <span>Adm: <strong class="font-semibold text-gray-700">{{ $trash->admission_no }}</strong></span>
                                                @if($trash->gender)
                                                    <span>&bull;</span>
                                                    <span>{{ $trash->gender }}</span>
                                                @endif
                                                @if($trash->phone)
                                                    <span>&bull;</span>
                                                    <span>{{ $trash->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Class & Section -->
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-800">{{ $trash->school_class_name ?: 'N/A' }}</div>
                                    <div class="text-[11px] text-gray-500">Sec: {{ $trash->section_name ?: 'N/A' }}</div>
                                </td>

                                <!-- Archived Records Summary Badges -->
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5 max-w-xs">
                                        @if(($counts['invoices'] ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 border border-blue-200" title="{{ $counts['invoices'] }} Fee Invoices">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                {{ $counts['invoices'] }} Invoices
                                            </span>
                                        @endif
                                        @if(($counts['transport'] ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200" title="{{ $counts['transport'] }} Transport Invoices">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                {{ $counts['transport'] }} Transport
                                            </span>
                                        @endif
                                        @if(($counts['library_cards'] ?? 0) > 0 || ($counts['book_issues'] ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700 border border-purple-200" title="{{ $counts['library_cards'] ?? 0 }} Cards, {{ $counts['book_issues'] ?? 0 }} Book Issues">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                {{ ($counts['library_cards'] ?? 0) + ($counts['book_issues'] ?? 0) }} Library
                                            </span>
                                        @endif
                                        @if(($counts['exam_results'] ?? 0) > 0 || ($counts['admit_cards'] ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200" title="{{ $counts['admit_cards'] ?? 0 }} Admit Cards, {{ $counts['exam_results'] ?? 0 }} Results">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                {{ ($counts['admit_cards'] ?? 0) + ($counts['exam_results'] ?? 0) }} Exam/Results
                                            </span>
                                        @endif
                                        @if(($counts['has_login'] ?? 0) == 1)
                                            <span class="inline-flex items-center gap-1 rounded bg-cyan-50 px-2 py-0.5 text-[10px] font-semibold text-cyan-700 border border-cyan-200" title="Student Portal Login Account">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                Login Account
                                            </span>
                                        @endif
                                        @if(($counts['leaves'] ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded bg-gray-50 px-2 py-0.5 text-[10px] font-semibold text-gray-700 border border-gray-200">
                                                {{ $counts['leaves'] }} Leaves
                                            </span>
                                        @endif
                                        @if(empty($counts) || (array_sum($counts) == 0))
                                            <span class="text-[11px] text-gray-400 italic">No additional records</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Deleted Date -->
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-800">{{ $deletedDate ? $deletedDate->format('M d, Y') : 'N/A' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $deletedDate ? $deletedDate->format('h:i A') : '' }}</div>
                                </td>

                                <!-- Days Remaining Badge -->
                                <td class="px-5 py-4">
                                    @if($days > 15)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            {{ $days }} days left
                                        </span>
                                    @elseif($days > 5)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            {{ $days }} days left
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 border border-red-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                            {{ $days }} days left (Purging Soon)
                                        </span>
                                    @endif
                                    <div class="text-[10px] text-gray-400 mt-0.5">Expires {{ $expiresDate->format('M d, Y') }}</div>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Restore / Recover Button -->
                                        <form action="{{ route('sm-school.settings.trash.restore', $trash->id) }}" method="POST" onsubmit="return confirm('Restore student &quot;{{ $trash->full_name }}&quot; and all associated accounting, transport, library, and examination data back into active records?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors" title="Recover and restore student with all records">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.038 8.038 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Recover
                                            </button>
                                        </form>

                                        <!-- Delete Permanently Button -->
                                        <form action="{{ route('sm-school.settings.trash.destroy', $trash->id) }}" method="POST" onsubmit="return confirm('PERMANENT DELETION: Are you sure you want to permanently erase student &quot;{{ $trash->full_name }}&quot; and all data from database? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-red-600 shadow-sm hover:bg-red-50 hover:border-red-300 transition-colors" title="Delete permanently right now">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endif
@endsection