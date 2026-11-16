@extends('layouts.app')

@section('header', 'Generated Admit Cards')

@section('content')
@include('examination.admit-cards._card-styles')

{{-- UI Container (Hidden on Print) --}}
<div id="ui-container">
    {{-- Header Navigation Bar --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background:var(--color-primary);">
        <div>
            <h1 class="text-base font-bold">Generated Admit Cards</h1>
            <p class="mt-1 text-xs opacity-90">{{ $exam->title }} · {{ $exam->schoolClass->name }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($cards->isNotEmpty())
                <button type="button" data-print-all-cards class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-1.5 text-xs font-semibold hover:bg-white/10 transition">
                    <i data-lucide="printer" class="h-3.5 w-3.5"></i> Print All Cards
                </button>
            @endif
            <a href="{{ route('examination.admit-cards.generate-form', $exam) }}" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-1.5 text-xs font-semibold hover:bg-white/10 transition">
                <i data-lucide="contact-round" class="h-3.5 w-3.5"></i> Generate Admit Cards
            </a>
        </div>
    </div>

    {{-- Cards Table View --}}
    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        @if($cards->isNotEmpty())
            <div class="border-b border-gray-200 px-5 py-4 flex items-center justify-between">
                <p class="text-sm font-bold text-heading">{{ $cards->count() }} Cards Generated</p>
            </div>
            
            {{-- Bulk Delete Toolbar --}}
            <div id="bulkToolbar" class="hidden mx-5 mt-3 mb-1 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 flex-shrink-0"></i>
                <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
                <form id="bulkDeleteForm" method="POST" action="{{ route('examination.admit-cards.bulk-delete', $exam) }}" onsubmit="return confirmBulkDelete()">
                    @csrf
                    <div id="bulkIdsContainer"></div>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
                    </button>
                </form>
                <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline ml-2">Clear Selection</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left" style="background:#F5F1EB;">
                            <th class="px-4 py-3 text-center text-xs font-semibold text-body w-10">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                       class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                                       title="Select / Deselect All">
                            </th>
                            <th class="px-4 py-3 text-xs font-semibold text-body">Exam Roll Number</th>
                            <th class="px-4 py-3 text-xs font-semibold text-body">Student Name</th>
                            <th class="px-4 py-3 text-xs font-semibold text-body">Class</th>
                            <th class="px-4 py-3 text-xs font-semibold text-body">Section</th>
                            <th class="px-4 py-3 text-xs font-semibold text-body">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cards as $card)
                            <tr class="hover:bg-gray-50 transition-colors row-item">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $card->id }}" onchange="updateBulkToolbar()"
                                           class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-navy">{{ $card->exam_roll_number }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-900">{{ $card->student->full_name }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $card->student->schoolClass->name }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $card->student->section?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        {{-- Print Button --}}
                                        <button type="button" data-print-card="{{ $card->id }}" title="Print Admit Card"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        </button>
                                        
                                        {{-- Edit Button --}}
                                        <button type="button" onclick="openEditModal({{ $card->id }}, '{{ addslashes($card->exam_roll_number) }}')" title="Edit Roll Number"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('examination.admit-cards.delete', ['exam' => $exam->id, 'admitCard' => $card->id]) }}"
                                              onsubmit="return confirm('Delete this admit card? The student will be available for generation again.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Delete Admit Card"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="p-8 text-center text-xs text-body border-t border-gray-100">No admit cards have been generated for this exam yet.</p>
        @endif
    </section>
</div>

{{-- Edit Roll Number Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h3 class="font-bold text-heading text-sm">Edit Exam Roll Number</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
        <form id="editForm" method="POST" action="">
            @csrf @method('PUT')
            <div class="p-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">New Roll Number (Prefix/Suffix included)</label>
                <input type="text" id="editRollInput" name="exam_roll_number" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <p class="text-[11px] text-gray-500 mt-2">Modify the roll number manually to update prefix, suffix, or the number itself.</p>
            </div>
            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 text-right rounded-b-xl">
                <button type="button" onclick="closeEditModal()" class="mr-2 rounded-md px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-200">Cancel</button>
                <button type="submit" class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Print Container (Used exclusively for Browser Printing) --}}
<div id="print-container" style="display: none;">
    @foreach($cards as $card)
        @include('examination.admit-cards._card', ['card' => $card, 'exam' => $exam, 'papers' => $papers])
    @endforeach
</div>

<script>
    // Print Logic
    document.querySelectorAll('[data-print-card]').forEach((button) => {
        button.addEventListener('click', () => printCard(button.dataset.printCard));
    });
    
    document.querySelectorAll('[data-print-all-cards]').forEach((button) => {
        button.addEventListener('click', printAllCards);
    });

    function printCard(cardId) {
        document.body.classList.remove('print-all');
        document.body.classList.add('print-single');
        document.querySelectorAll('#print-container .admit-card').forEach((card) => {
            card.classList.toggle('print-selected', card.id === `card-${cardId}`);
        });
        window.print();
    }

    function printAllCards() {
        document.body.classList.remove('print-single');
        document.body.classList.add('print-all');
        document.querySelectorAll('#print-container .admit-card').forEach((card) => card.classList.remove('print-selected'));
        window.print();
    }

    window.addEventListener('afterprint', () => {
        document.body.classList.remove('print-single', 'print-all');
        document.querySelectorAll('#print-container .admit-card').forEach((card) => card.classList.remove('print-selected'));
    });

    // Bulk Delete Logic
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
            toolbar.classList.remove('hidden');
            countEl.textContent = checked.length + ' selected';
        } else {
            toolbar.classList.add('hidden');
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
        document.getElementById('bulkToolbar').classList.add('hidden');
    }
    
    function confirmBulkDelete() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm(checked.length + ' admit cards will be permanently deleted. Are you sure?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    // Edit Modal Logic
    function openEditModal(cardId, currentRoll) {
        document.getElementById('editRollInput').value = currentRoll;
        
        let form = document.getElementById('editForm');
        let baseUrl = "{{ route('examination.admit-cards.update', ['exam' => $exam->id, 'admitCard' => ':id']) }}";
        form.action = baseUrl.replace(':id', cardId);
        
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>
@endsection