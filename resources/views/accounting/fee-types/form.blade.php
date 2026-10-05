@extends('layouts.app')

@section('header', $editing ? 'Edit Fee Type' : 'Add Fee Type')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">{{ $editing ? 'Edit Fee Type' : 'Add Fee Type' }}</h2>
        <p class="text-xs text-body">{{ $school->name }} · {{ $session->name }}</p>
    </div>
    <a href="{{ route('accounting.fee-types.index', ['session_id' => $session->id]) }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>Back to Fee Types
    </a>
</div>
@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>
@endif

<section class="rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Fee Type Details</h3>
        <p class="mt-1 text-xs text-body">Assign this fee to one or more classes in a session.</p>
    </div>
    <form action="{{ $editing ? route('accounting.fee-types.update', $feeType) : route('accounting.fee-types.store') }}" method="POST" class="p-5">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="feeName" class="mb-1 block text-xs font-medium text-body">Fee Name <span class="text-red-600">*</span></label>
                <input id="feeName" name="name" value="{{ old('name', $feeType?->name) }}" required maxlength="150" placeholder="e.g. Tuition Fee" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label for="feeSession" class="mb-1 block text-xs font-medium text-body">Session <span class="text-red-600">*</span></label>
                <select id="feeSession" name="academic_session_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    @foreach($sessions as $academicSession)
                        <option value="{{ $academicSession->id }}" {{ old('academic_session_id', $session->id) == $academicSession->id ? 'selected' : '' }}>{{ $academicSession->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="feeStudentType" class="mb-1 block text-xs font-medium text-body">Student Type <span class="text-red-600">*</span></label>
                @php($selectedStudentType = old('student_type', $feeType?->student_type_id ?? $feeType?->student_type ?? 'all'))
                <select id="feeStudentType" name="student_type" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="all" {{ (string) $selectedStudentType === 'all' ? 'selected' : '' }}>All Students</option>
                    <option value="new_admission" {{ (string) $selectedStudentType === 'new_admission' ? 'selected' : '' }}>New Admissions</option>
                    <option value="existing" {{ (string) $selectedStudentType === 'existing' ? 'selected' : '' }}>Existing Students</option>
                    @foreach($studentTypes as $studentType)
                        <option value="{{ $studentType->id }}" {{ (string) $selectedStudentType === (string) $studentType->id ? 'selected' : '' }}>{{ $studentType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="feePeriod" class="mb-1 block text-xs font-medium text-body">Period <span class="text-red-600">*</span></label>
                <select id="feePeriod" name="fee_period_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">{{ $periods->isEmpty() ? 'No periods configured for this session' : 'Select period' }}</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ old('fee_period_id', $feeType?->fee_period_id) == $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="feeAmount" class="mb-1 block text-xs font-medium text-body">Amount <span class="text-red-600">*</span></label>
                <input id="feeAmount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $feeType?->amount) }}" required placeholder="0.00" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <span class="mb-1 block text-xs font-medium text-body">Classes <span class="text-red-600">*</span></span>
                <div id="feeClassOptions" class="grid grid-cols-2 gap-2 rounded-md border border-gray-200 p-3 sm:grid-cols-3 xl:grid-cols-4">
                    @forelse($classes as $class)
                        <label class="flex items-center gap-2 text-sm text-heading"><input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, array_map('intval', old('class_ids', $feeType?->classes->pluck('id')->all() ?? [])), true) ? 'checked' : '' }} class="rounded accent-navy">{{ $class->name }}</label>
                    @empty
                        <p class="col-span-full text-xs text-body">No classes are configured for this session.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <label class="flex items-start gap-2 rounded-md bg-gray-50 p-3 text-xs text-body"><input type="checkbox" name="invoice_on_admission" value="1" {{ old('invoice_on_admission', $feeType?->invoice_on_admission) ? 'checked' : '' }} class="mt-0.5 rounded accent-navy"><span><strong class="block text-heading">Invoice on Admission</strong>Offer this fee when admitting a student.</span></label>
            <label class="flex items-start gap-2 rounded-md bg-gray-50 p-3 text-xs text-body"><input type="checkbox" name="include_on_promotion" value="1" {{ old('include_on_promotion', $feeType?->include_on_promotion) ? 'checked' : '' }} class="mt-0.5 rounded accent-navy"><span><strong class="block text-heading">Include on Promotion</strong>Carry this fee type into the next session.</span></label>
            <label class="flex items-start gap-2 rounded-md bg-gray-50 p-3 text-xs text-body"><input type="checkbox" name="dashboard_disabled" value="1" {{ old('dashboard_disabled', $feeType?->dashboard_disabled) ? 'checked' : '' }} class="mt-0.5 rounded accent-navy"><span><strong class="block text-heading">Hide from Dashboard</strong>Exclude this fee from projected totals.</span></label>
        </div>
        <div class="mt-5 flex flex-wrap justify-end gap-2">
            <a href="{{ route('accounting.fee-types.index', ['session_id' => $session->id]) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-gray-50">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="save" class="h-4 w-4"></i>{{ $editing ? 'Save Changes' : 'Add Fee Type' }}</button>
        </div>
    </form>
</section>

<script>
(() => {
    const sessionSelect = document.getElementById('feeSession');
    const periodSelect = document.getElementById('feePeriod');
    const studentTypeSelect = document.getElementById('feeStudentType');
    const classOptions = document.getElementById('feeClassOptions');
    const classesUrl = @json(route('accounting.fee-types.classes', ['academicSession' => '__SESSION__']));
    let latestRequest = 0;

    async function loadSession(sessionId, selectedClasses = [], selectedPeriod = '', selectedType = '') {
        if (!sessionId) return;
        const requestNumber = ++latestRequest;
        classOptions.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'col-span-full text-xs text-body';
        loading.textContent = 'Loading session options...';
        classOptions.append(loading);
        periodSelect.replaceChildren(new Option('Loading periods...', ''));

        try {
            const response = await fetch(classesUrl.replace('__SESSION__', sessionId), { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Unable to load session options.');
            const data = await response.json();
            if (requestNumber !== latestRequest) return;

            classOptions.replaceChildren();
            if (!data.classes.length) {
                const empty = document.createElement('p');
                empty.className = 'col-span-full text-xs text-body';
                empty.textContent = 'No classes are configured for this session.';
                classOptions.append(empty);
            }
            data.classes.forEach((schoolClass) => {
                const label = document.createElement('label');
                label.className = 'flex items-center gap-2 text-sm text-heading';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'class_ids[]';
                checkbox.value = schoolClass.id;
                checkbox.className = 'rounded accent-navy';
                checkbox.checked = selectedClasses.map(Number).includes(Number(schoolClass.id));
                label.append(checkbox, document.createTextNode(schoolClass.name));
                classOptions.append(label);
            });

            periodSelect.replaceChildren(new Option(data.periods.length ? 'Select period' : 'No periods configured for this session', ''));
            data.periods.forEach((period) => periodSelect.add(new Option(period.name, period.id, false, String(period.id) === String(selectedPeriod))));
            const currentType = selectedType || studentTypeSelect.value;
            studentTypeSelect.replaceChildren(new Option('All Students', 'all'), new Option('New Admissions', 'new_admission'), new Option('Existing Students', 'existing'));
            data.student_types.forEach((studentType) => studentTypeSelect.add(new Option(studentType.name, studentType.id)));
            studentTypeSelect.value = String(currentType || 'all');
        } catch (error) {
            if (requestNumber !== latestRequest) return;
            classOptions.replaceChildren();
            const failure = document.createElement('p');
            failure.className = 'col-span-full text-xs text-red-700';
            failure.textContent = 'Unable to load session options. Change the session and try again.';
            classOptions.append(failure);
            periodSelect.replaceChildren(new Option('Unable to load periods', ''));
        }
    }

    sessionSelect.addEventListener('change', () => loadSession(sessionSelect.value, [], '', studentTypeSelect.value));
    loadSession(sessionSelect.value, @json(old('class_ids', $feeType?->classes->pluck('id')->all() ?? [])), @json(old('fee_period_id', $feeType?->fee_period_id ?? '')), @json(old('student_type', $feeType?->student_type_id ?? $feeType?->student_type ?? 'all')));
})();
</script>
@endsection
