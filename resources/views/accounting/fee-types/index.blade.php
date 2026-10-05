@extends('layouts.app')

@section('header', 'Fee Types')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Fee Types</h2>
        <p class="text-xs text-body">{{ $school->name }} · {{ $session->name }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-gray-50"><i data-lucide="upload" class="h-4 w-4"></i>Import</button>
        <a href="{{ route('accounting.fee-types.export', ['session_id' => $session->id]) }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-gray-50"><i data-lucide="download" class="h-4 w-4"></i>Export</a>
        <button onclick="openAddModal()" class="inline-flex items-center gap-2 rounded-md bg-navy px-3 py-2 text-xs font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Add Fee Type</button>
        <form method="GET" action="{{ route('accounting.fee-types.index') }}" class="flex items-center gap-2">
            <label for="feeTypeSessionFilter" class="text-xs text-body">Session</label>
            <select id="feeTypeSessionFilter" name="session_id" onchange="this.form.submit()" class="rounded-md border border-gray-300 bg-white px-2 py-2 text-xs">
                @foreach($sessions as $academicSession)
                    <option value="{{ $academicSession->id }}" {{ $academicSession->id === $session->id ? 'selected' : '' }}>{{ $academicSession->name }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('sm-school.settings.index', ['tab' => 'fee-periods']) }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white">
            <i data-lucide="calendar-range" class="h-4 w-4"></i> Manage periods
        </a>
    </div>
</div>
@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>
@endif

<section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-sm font-bold text-heading">Configured Fee Types</h3></div>
    {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('accounting.fee-types.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
        {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('accounting.fee-types.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead><tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Fee Name</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Classes</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Student Type</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Period</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Amount</th><th class="px-4 py-2 font-semibold text-white uppercase tracking-wider">Admission Invoice</th><th class="px-4 py-2 text-right font-semibold text-white uppercase tracking-wider">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($feeTypes as $feeType)
                    <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $feeType->id }}">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="{{ $feeType->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="px-4 py-3 font-semibold text-heading">{{ $feeType->name }}</td>
                        <td class="px-4 py-3 text-body">{{ $feeType->classes->pluck('name')->join(', ') }}</td>
                        <td class="px-4 py-3 text-body">{{ $feeType->studentType?->name ?? (['all' => 'All Students', 'new_admission' => 'New Admissions', 'existing' => 'Existing Students'][$feeType->student_type] ?? $feeType->student_type) }}</td>
                        <td class="px-4 py-3 text-body">{{ $feeType->period->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-heading">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $feeType->amount, 2) }}</td>
                        <td class="px-4 py-3">{{ $feeType->invoice_on_admission ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-3">
                            <button onclick='openEditModal(@json($feeType))' class="text-blue-700 hover:text-blue-900" title="Edit fee type"><i data-lucide="pencil" class="h-4 w-4"></i></button>
                            <form method="POST" action="{{ route('accounting.fee-types.destroy', $feeType) }}" onsubmit="return confirm('Delete this fee type?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800" title="Delete fee type"><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50 transition-colors"><td colspan="100%" class="px-4 py-12 text-center text-sm text-body">No fee types configured for this session.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
</section>

{{-- Add/Edit Fee Type Modal --}}
<div id="feeTypeModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b pb-3">
            <h3 id="modalTitle" class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5 text-navy"></i> Add Fee Type
            </h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="feeTypeForm" action="{{ route('accounting.fee-types.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            {{-- STEP 1 --}}
            <div id="feeStep1">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Fee Name <span class="text-red-600">*</span></label>
                        <input id="feeName" name="name" required maxlength="150" placeholder="e.g. Tuition Fee" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Session <span class="text-red-600">*</span></label>
                        <select id="feeSession" name="academic_session_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                            @foreach($sessions as $academicSession)
                                <option value="{{ $academicSession->id }}" {{ $academicSession->id == $session->id ? 'selected' : '' }}>{{ $academicSession->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Student Type <span class="text-red-600">*</span></label>
                        <select id="feeStudentType" name="student_type" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                            <option value="all">All Students</option>
                            <option value="new_admission">New Admissions</option>
                            <option value="existing">Existing Students</option>
                            @foreach($studentTypes as $studentType)
                                <option value="{{ $studentType->id }}">{{ $studentType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Period <span class="text-red-600">*</span></label>
                        <select id="feePeriod" name="fee_period_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                            <option value="">{{ $periods->isEmpty() ? 'No periods configured for this session' : 'Select period' }}</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}">{{ $period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Amount <span class="text-red-600">*</span></label>
                        <input id="feeAmount" name="amount" type="number" min="0.01" step="0.01" required placeholder="0.00" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    </div>
                </div>
                
                <div class="mt-6 flex flex-wrap justify-end gap-3 border-t pt-4">
                    <button type="button" onclick="closeModal()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-heading hover:bg-gray-200 transition-colors">Cancel</button>
                    <button type="button" onclick="goToStep(2)" class="inline-flex items-center gap-2 rounded-lg bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark transition-colors">Next <i data-lucide="arrow-right" class="h-4 w-4"></i></button>
                </div>
            </div>

            {{-- STEP 2 --}}
            <div id="feeStep2" class="hidden">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Classes <span class="text-red-600">*</span></label>
                        <select id="feeClasses" name="class_ids[]" multiple class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white" placeholder="Search and select classes..."></select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-body">Class Sections <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <select id="feeSections" name="section_ids[]" multiple class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy bg-white" placeholder="Select sections (defaults to all if empty)..."></select>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label class="flex items-start gap-2 rounded-md bg-gray-50 p-3 text-xs text-body"><input type="checkbox" id="feeInvoice" name="invoice_on_admission" value="1" class="mt-0.5 rounded accent-navy"><span><strong class="block text-heading">Invoice on Admission</strong>Offer this fee when admitting a student.</span></label>
                    <label class="flex items-start gap-2 rounded-md bg-gray-50 p-3 text-xs text-body"><input type="checkbox" id="feeInclude" name="include_on_promotion" value="1" class="mt-0.5 rounded accent-navy"><span><strong class="block text-heading">Include on Promotion</strong>Carry this fee type into the next session.</span></label>
                    <label class="flex items-start gap-2 rounded-md bg-gray-50 p-3 text-xs text-body"><input type="checkbox" id="feeHide" name="dashboard_disabled" value="1" class="mt-0.5 rounded accent-navy"><span><strong class="block text-heading">Hide from Dashboard</strong>Exclude this fee from projected totals.</span></label>
                </div>
                
                <div class="mt-6 flex flex-wrap justify-between gap-3 border-t pt-4">
                    <button type="button" onclick="goToStep(1)" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-heading hover:bg-gray-200 transition-colors inline-flex items-center gap-2"><i data-lucide="arrow-left" class="h-4 w-4"></i> Back</button>
                    <div class="flex gap-3">
                        <button type="submit" name="action_type" value="save" id="saveBtn" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition-opacity" style="background:linear-gradient(135deg,#B47A42,#D4A055);"><i data-lucide="save" class="h-4 w-4"></i> Save</button>
                        <button type="submit" name="action_type" value="save_and_next" id="saveAndNextBtn" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"><i data-lucide="arrow-right-circle" class="h-4 w-4"></i> Save & Next</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-sm transition-all">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-heading">Import Fee Types</h3>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-5 w-5"></i></button>
            </div>
            <form action="{{ route('accounting.fee-types.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                
                <div class="mb-4">
                    <p class="mb-2 text-sm text-body">Download the sample file to see the required format.</p>
                    <a href="{{ route('accounting.fee-types.sample') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800"><i data-lucide="download" class="h-4 w-4"></i>Download Sample CSV</a>
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-heading">Select CSV File</label>
                    <input type="file" name="file" accept=".csv,.txt" required class="block w-full text-sm text-body file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-heading hover:file:bg-gray-200">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-body hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const classesUrl = @json(route('accounting.fee-types.classes', ['academicSession' => '__SESSION__']));
    const defaultSession = "{{ $session->id }}";
    
    let tsClasses = null;
    let tsSections = null;
    let allClassesData = [];

    function goToStep(step) {
        if (step === 2) {
            // Validate Step 1 before proceeding
            const name = document.getElementById('feeName').value;
            const amount = document.getElementById('feeAmount').value;
            const period = document.getElementById('feePeriod').value;
            
            if (!name || !amount || !period) {
                alert('Please fill out all required fields (Name, Period, Amount) before proceeding.');
                return;
            }
            
            document.getElementById('feeStep1').classList.add('hidden');
            document.getElementById('feeStep2').classList.remove('hidden');
        } else {
            document.getElementById('feeStep2').classList.add('hidden');
            document.getElementById('feeStep1').classList.remove('hidden');
        }
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerHTML = '<i data-lucide="plus" class="w-5 h-5 text-navy"></i> Add Fee Type';
        document.getElementById('feeTypeForm').action = "{{ route('accounting.fee-types.store') }}";
        document.getElementById('formMethod').value = "POST";
        document.getElementById('feeTypeForm').reset();
        if (tsClasses) tsClasses.clear();
        if (tsSections) tsSections.clear();
        document.getElementById('feeSession').value = defaultSession;
        document.getElementById('saveAndNextBtn').style.display = 'inline-flex';
        document.getElementById('saveBtn').innerHTML = '<i data-lucide="save" class="h-4 w-4"></i> Save';
        loadSession(defaultSession, []);
        goToStep(1);
        document.getElementById('feeTypeModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditModal(feeType) {
        document.getElementById('modalTitle').innerHTML = '<i data-lucide="edit" class="w-5 h-5 text-navy"></i> Edit Fee Type';
        document.getElementById('feeTypeForm').action = `/accounting/fee-types/${feeType.id}`;
        document.getElementById('formMethod').value = "PUT";
        
        document.getElementById('feeName').value = feeType.name;
        document.getElementById('feeSession').value = feeType.academic_session_id;
        document.getElementById('feeAmount').value = feeType.amount;
        
        document.getElementById('feeInvoice').checked = !!feeType.invoice_on_admission;
        document.getElementById('feeInclude').checked = !!feeType.include_on_promotion;
        document.getElementById('feeHide').checked = !!feeType.dashboard_disabled;
        
        document.getElementById('saveAndNextBtn').style.display = 'none';
        document.getElementById('saveBtn').innerHTML = '<i data-lucide="save" class="h-4 w-4"></i> Update';
        
        let classIds = [];
        if (feeType.classes) {
            classIds = feeType.classes.map(c => c.id);
        }
        
        loadSession(feeType.academic_session_id, classIds, feeType.fee_period_id, feeType.student_type_id || feeType.student_type, feeType.sections ? feeType.sections.map(s => s.id) : []);
        
        goToStep(1);
        document.getElementById('feeTypeModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeModal() {
        document.getElementById('feeTypeModal').classList.add('hidden');
    }

    async function loadSession(sessionId, selectedClasses = [], selectedPeriod = '', selectedType = '', selectedSections = []) {
        const periodSelect = document.getElementById('feePeriod');
        const studentTypeSelect = document.getElementById('feeStudentType');
        
        if (!sessionId) return;
        
        periodSelect.innerHTML = '<option value="">Loading...</option>';

        try {
            const response = await fetch(classesUrl.replace('__SESSION__', sessionId), { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Unable to load session options.');
            const data = await response.json();
            
            allClassesData = data.classes;
            
            if (tsClasses) {
                tsClasses.clearOptions();
                data.classes.forEach(c => {
                    tsClasses.addOption({value: c.id, text: c.name});
                });
                tsClasses.setValue(selectedClasses);
            }
            
            updateSectionsDropdown(selectedClasses, selectedSections);

            periodSelect.innerHTML = data.periods.length ? '<option value="">Select period</option>' : '<option value="">No periods configured</option>';
            data.periods.forEach((period) => {
                const selected = String(period.id) === String(selectedPeriod) ? 'selected' : '';
                periodSelect.innerHTML += `<option value="${period.id}" ${selected}>${period.name}</option>`;
            });
            
            const currentType = selectedType || studentTypeSelect.value;
            studentTypeSelect.innerHTML = `<option value="all">All Students</option><option value="new_admission">New Admissions</option><option value="existing">Existing Students</option>`;
            data.student_types.forEach((studentType) => {
                studentTypeSelect.innerHTML += `<option value="${studentType.id}">${studentType.name}</option>`;
            });
            if (currentType) {
                studentTypeSelect.value = String(currentType);
            }
            
        } catch (error) {
            console.error('Unable to load session options.');
        }
    }
    
    function updateSectionsDropdown(selectedClassIds, selectedSections = []) {
        if (!tsSections) return;
        tsSections.clearOptions();
        
        let availableSections = [];
        allClassesData.forEach(c => {
            if (selectedClassIds.includes(String(c.id)) || selectedClassIds.includes(Number(c.id))) {
                if (c.sections && c.sections.length > 0) {
                    c.sections.forEach(s => {
                        // Prevent duplicates if multiple classes share a section name/id (usually they don't, but just in case)
                        if (!availableSections.some(as => as.id === s.id)) {
                            availableSections.push(s);
                            tsSections.addOption({value: s.id, text: `${c.name} - ${s.name}`});
                        }
                    });
                }
            }
        });
        tsSections.setValue(selectedSections);
    }

    document.getElementById('feeSession').addEventListener('change', (e) => {
        loadSession(e.target.value, [], '', document.getElementById('feeStudentType').value, []);
    });

    document.addEventListener('DOMContentLoaded', () => {
        tsClasses = new TomSelect('#feeClasses', {
            plugins: ['remove_button'],
            placeholder: 'Search and select classes...',
            onChange: function(values) {
                updateSectionsDropdown(values, tsSections.getValue());
            }
        });
        
        tsSections = new TomSelect('#feeSections', {
            plugins: ['remove_button'],
            placeholder: 'Select sections (defaults to all if empty)...'
        });
    });

    @if(session('reopen_add_modal'))
        document.addEventListener('DOMContentLoaded', () => {
            openAddModal();
        });
    @endif
</script>

    {{-- ══ Bulk Select JS (auto-injected) ══ --}}
    <script>
    (function() {
        function toggleSelectAll(cb) {
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.checked = cb.checked;
                r.closest('tr').classList.toggle('bg-red-50', cb.checked);
            });
            updateBulkToolbar();
        }
        function updateBulkToolbar() {
            var checked = document.querySelectorAll('.row-checkbox:checked');
            var all     = document.querySelectorAll('.row-checkbox');
            var toolbar = document.getElementById('bulkToolbar');
            var countEl = document.getElementById('selectedCount');
            var sa      = document.getElementById('selectAll');
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.closest('tr').classList.toggle('bg-red-50', r.checked);
            });
            if (checked.length > 0) {
                if(toolbar) toolbar.classList.remove('hidden');
                if(countEl) countEl.textContent = checked.length + ' selected';
            } else {
                if(toolbar) toolbar.classList.add('hidden');
            }
            if (sa) {
                sa.indeterminate = checked.length > 0 && checked.length < all.length;
                sa.checked = all.length > 0 && checked.length === all.length;
            }
        }
        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(function(r) {
                r.checked = false;
                r.closest('tr').classList.remove('bg-red-50');
            });
            var sa = document.getElementById('selectAll');
            if (sa) { sa.checked = false; sa.indeterminate = false; }
            var toolbar = document.getElementById('bulkToolbar');
            if(toolbar) toolbar.classList.add('hidden');
        }
        function confirmBulkDelete() {
            var checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return false;
            if (!confirm(checked.length + ' item(s) permanently delete honge. Sure hain?')) return false;
            var c = document.getElementById('bulkIdsContainer');
            c.innerHTML = '';
            checked.forEach(function(cb) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
                c.appendChild(inp);
            });
            return true;
        }
        window.toggleSelectAll   = toggleSelectAll;
        window.updateBulkToolbar = updateBulkToolbar;
        window.clearSelection    = clearSelection;
        window.confirmBulkDelete = confirmBulkDelete;
    })();
    </script>
@endsection
