<?php $__env->startSection('header', 'Generated Admit Cards'); ?>

<?php $__env->startSection('content'); ?>
<style>
    @media print {
        @page { size: A4 portrait; margin: 0; }
        body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; padding: 0; }
        
        /* Hide everything except print container */
        body > aside, body > main > header, #ui-container { display: none !important; }
        
        /* Show print container */
        #print-container { display: block !important; }

        /* Print single or all logic */
        body.print-single .admit-card { display: none !important; }
        body.print-single .admit-card.print-selected { display: block !important; page-break-after: always; }
        
        body.print-all .admit-card { display: block !important; page-break-after: always; }
        body.print-all .admit-card:last-child { page-break-after: auto; }
        
        .admit-card { box-shadow: none !important; margin: 0 !important; border: none !important; min-height: 296mm !important; }
    }
</style>


<div id="ui-container">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background:var(--color-primary);">
        <div><h1 class="text-base font-bold">Generated Admit Cards</h1><p class="mt-1 text-xs"><?php echo e($exam->title); ?> · <?php echo e($exam->schoolClass->name); ?></p></div>
        <div class="flex flex-wrap gap-2">
            <?php if($cards->isNotEmpty()): ?>
                <button type="button" data-print-all-cards class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition"><i data-lucide="printer" class="h-3.5 w-3.5"></i> Print All Cards</button>
            <?php endif; ?>
            <a href="<?php echo e(route('examination.admit-cards.generate-form', $exam)); ?>" class="inline-flex items-center gap-1 rounded border border-white/70 px-3 py-2 text-xs font-semibold hover:bg-white/10 transition"><i data-lucide="contact-round" class="h-3.5 w-3.5"></i> Generate Admit Cards</a>
        </div>
    </div>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <?php if($cards->isNotEmpty()): ?>
            <div class="border-b border-gray-200 px-5 py-4 flex items-center justify-between">
                <p class="text-sm font-bold text-heading"><?php echo e($cards->count()); ?> Cards Generated</p>
            </div>
            
            
            <div id="bulkToolbar" class="hidden mx-5 mt-3 mb-1 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 flex-shrink-0"></i>
                <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
                <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('examination.admit-cards.bulk-delete', $exam)); ?>" onsubmit="return confirmBulkDelete()">
                    <?php echo csrf_field(); ?>
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
                        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors row-item">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="<?php echo e($card->id); ?>" onchange="updateBulkToolbar()"
                                           class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-navy"><?php echo e($card->exam_roll_number); ?></td>
                                <td class="px-4 py-3 font-semibold text-gray-900"><?php echo e($card->student->full_name); ?></td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo e($card->student->schoolClass->name); ?></td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo e($card->student->section?->name ?? '—'); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        
                                        <button type="button" data-print-card="<?php echo e($card->id); ?>" title="Print Admit Card"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        </button>
                                        
                                        
                                        <button type="button" onclick="openEditModal(<?php echo e($card->id); ?>, '<?php echo e(addslashes($card->exam_roll_number)); ?>')" title="Edit Roll Number"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </button>

                                        
                                        <form method="POST" action="<?php echo e(route('examination.admit-cards.delete', ['exam' => $exam->id, 'admitCard' => $card->id])); ?>"
                                              onsubmit="return confirm('Delete this admit card? The student will be available for generation again.')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" title="Delete Admit Card"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="p-8 text-center text-xs text-body border-t border-gray-100">No admit cards have been generated for this exam yet.</p>
        <?php endif; ?>
    </section>
</div>


<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h3 class="font-bold text-heading text-sm">Edit Exam Roll Number</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
        <form id="editForm" method="POST" action="">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
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


<div id="print-container" style="display: none;">
    <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $school = $card->student->school ?? $exam->school;
            $session = $exam->academicSession ?? \App\Models\AcademicSession::find(session('selected_academic_session_id'));
            $bg = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'examination_background');
            $bgClass = $bg ? 'has-bg' : '';
            $showPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'student_admit_card_photo', '0') == '1';
            // Get papers that map to the student's active subjects
            $activeSubjectIds = collect($card->student->activeSubjects)->pluck('id')->toArray();
            $studentPapers = collect($papers)->filter(function($paper) use ($activeSubjectIds) {
                return in_array($paper->subject_id, $activeSubjectIds);
            });
        ?>
        
        <div id="card-<?php echo e($card->id); ?>" class="admit-card p-8 mb-6 <?php echo e($bgClass); ?>" style="background-color: transparent !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; position: relative; min-height: 296mm; display: flex; flex-direction: column; overflow: hidden; z-index: 0;">
            
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;" alt="">
            <?php if($bg): ?>
                <img src="<?php echo e(asset($bg)); ?>" class="bg-print-img" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; opacity: 0.85;" alt="Background">
            <?php endif; ?>

            <div style="position: relative; z-index: 1;">
                
                <div class="text-center pb-3 border-b border-gray-400 mb-4">
                    <h1 class="text-xl font-bold text-black"><?php echo e($school->name ?? 'School Name'); ?></h1>
                    <p class="text-black mt-1" style="font-size: 11px;"><span class="font-bold">Phone:</span> <?php echo e($school->phone ?? '-'); ?> &nbsp;|&nbsp; <span class="font-bold">Email:</span> <?php echo e($school->email ?? '-'); ?></p>
                    <p class="text-black" style="font-size: 11px;"><span class="font-bold">Address:</span> <?php echo e($school->address ?? '-'); ?></p>
                </div>
                
                <div class="text-center mb-6">
                    <span class="font-bold uppercase underline text-black text-base">Student Admit Card</span>
                </div>

                
                <div class="flex justify-between items-start mb-6 text-black">
                    <div class="flex-1 pr-12" style="font-size: 13px; line-height: 2;">
                        <div class="flex justify-between">
                            <div><span class="font-bold">Student Name:</span> <?php echo e($card->student->full_name); ?></div>
                        </div>
                        <div class="flex justify-between">
                            <div><span class="font-bold">Session:</span> <?php echo e($session->name ?? '—'); ?></div>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <span class="font-bold">Class:</span> <?php echo e($card->student->schoolClass->name); ?> 
                                <span class="font-bold ml-6">Section:</span> <?php echo e($card->student->section?->name ?? '—'); ?>

                            </div>
                            <div><span class="font-bold">Exam Roll Number:</span> <?php echo e($card->exam_roll_number); ?></div>
                        </div>
                        <div class="flex justify-between">
                            <div><span class="font-bold">Phone:</span> <?php echo e($card->student->phone ?? '-'); ?></div>
                            <div><span class="font-bold">Email:</span> <?php echo e($card->student->email ?? '-'); ?></div>
                        </div>
                    </div>
                    <?php if($showPhoto && $card->student->photo): ?>
                        <div class="border border-black flex-shrink-0" style="width: 70px; height: 90px;">
                            <img src="<?php echo e($card->student->photo_url); ?>" class="w-full h-full object-cover" alt="Student Photo">
                        </div>
                    <?php elseif($showPhoto): ?>
                        <div class="border border-black flex-shrink-0 flex items-center justify-center text-[10px] text-gray-500" style="width: 70px; height: 90px;">
                            No Photo
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="font-bold text-sm text-black mb-2">
                    Exam: <?php echo e($exam->title); ?> (<?php echo e($exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'd-m-Y'))); ?> - <?php echo e($exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'd-m-Y'))); ?>) &nbsp; Class: <?php echo e($card->student->schoolClass->name); ?>

                </div>

                
                <div class="mb-10">
                    <?php if($studentPapers->isNotEmpty()): ?>
                        <table class="w-full text-xs text-left border-collapse border border-black text-black">
                            <thead>
                                <tr>
                                    <th class="p-2 border border-black font-bold">Subject</th>
                                    <th class="p-2 border border-black font-bold">Type</th>
                                    <th class="p-2 border border-black font-bold">Paper Code</th>
                                    <th class="p-2 border border-black font-bold">Date</th>
                                    <th class="p-2 border border-black font-bold">Timing</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $studentPapers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paper): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="p-2 border border-black"><?php echo e($paper->subject_name); ?></td>
                                        <td class="p-2 border border-black"><?php echo e($paper->subject_type ?? 'Theory'); ?></td>
                                        <td class="p-2 border border-black"><?php echo e($paper->paper_code ?? '—'); ?></td>
                                        <td class="p-2 border border-black"><?php echo e($paper->paper_date ? $paper->paper_date->format(\App\Models\SystemSetting::getSetting('date_format', 'd-m-Y')) : '—'); ?></td>
                                        <td class="p-2 border border-black">
                                            <?php echo e($paper->start_time ? \Carbon\Carbon::parse($paper->start_time)->format('h:i a') : '—'); ?> - <?php echo e($paper->end_time ? \Carbon\Carbon::parse($paper->end_time)->format('h:i a') : '—'); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($exam->exam_center): ?>
                                    <tr>
                                        <td class="p-2 border border-black font-bold">Exam Center</td>
                                        <td class="p-2 border border-black" colspan="4"><?php echo e($exam->exam_center); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-4 border border-black text-center text-sm text-black">No subjects scheduled for this student.</div>
                    <?php endif; ?>
                </div>

                
                <div class="mt-8 flex justify-end">
                    <div class="text-center w-40">
                        <?php if(($exam->settings['teacher_signature_enabled'] ?? false) && isset($exam->settings['teacher_signature_path'])): ?>
                            <img src="<?php echo e(asset('storage/'.$exam->settings['teacher_signature_path'])); ?>" class="h-8 mx-auto mb-1 object-contain">
                        <?php else: ?>
                            <div class="h-8"></div>
                        <?php endif; ?>
                        <div class="border-t border-black pt-1 text-sm font-bold text-black">Authorized By</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<script>
    // Print Logic
    document.querySelectorAll('[data-print-card]').forEach((button) => {
        button.addEventListener('click', () => printCard(button.dataset.printCard));
    });
    
    document.querySelector('[data-print-all-cards]')?.addEventListener('click', printAllCards);

    function printCard(cardId) {
        document.body.classList.remove('print-all');
        document.body.classList.add('print-single');
        document.querySelectorAll('.admit-card').forEach((card) => {
            card.classList.toggle('print-selected', card.id === `card-${cardId}`);
        });
        window.print();
    }

    function printAllCards() {
        document.body.classList.remove('print-single');
        document.body.classList.add('print-all');
        document.querySelectorAll('.admit-card').forEach((card) => card.classList.remove('print-selected'));
        window.print();
    }

    window.addEventListener('afterprint', () => {
        document.body.classList.remove('print-single', 'print-all');
        document.querySelectorAll('.admit-card').forEach((card) => card.classList.remove('print-selected'));
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
        let baseUrl = "<?php echo e(route('examination.admit-cards.update', ['exam' => $exam->id, 'admitCard' => ':id'])); ?>";
        form.action = baseUrl.replace(':id', cardId);
        
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/examination/admit-cards/view.blade.php ENDPATH**/ ?>