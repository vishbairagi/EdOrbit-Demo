<?php $__env->startSection('header', 'Student Promotion'); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center mb-4">
    <i data-lucide="trending-up" class="w-5 h-5 text-white mr-2.5"></i>
    <span class="text-white font-bold text-base tracking-wide">Student Promotion</span>
</div>


<div class="p-4 rounded-xl mb-5 flex items-start gap-3" style="background:#e8f4fd; border: 1px solid #b3d9f7;">
    <i data-lucide="info" class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:#2563eb;"></i>
    <p class="text-sm" style="color:#1e40af;">
        <strong>Note:</strong> Promoting a student from the present class to the next class will create a new enrollment for that student in the next session. The student's personal details (name, all personal info, previous school details etc.) will be carried forward. Present class data will not be transferred.
    </p>
</div>


<div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
    <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl text-center" style="background:#f5f1eb;">
        <h3 class="font-bold text-heading text-sm">Promotion</h3>
        <p class="text-xs text-body">Select class to promote, next session and new class.</p>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-xs font-semibold text-body block mb-1">Current Session:</label>
                <p class="text-sm font-semibold text-heading"><?php echo e($currentSession?->name ?? '—'); ?></p>
            </div>
            <div>
                <label class="text-xs font-semibold text-body block mb-1">* Promote to Session:</label>
                <select id="toSession" onchange="loadTargetClasses(this.value)"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Next Session</option>
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($session->id !== $currentSession?->id): ?>
                            <option value="<?php echo e($session->id); ?>"><?php echo e($session->name); ?></option>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-body block mb-1">* Promotion From Class:</label>
                <select id="fromClass"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Class</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-body block mb-1">* Promotion To Class:</label>
                <select id="toClass"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Next Session First</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button id="loadStudentsBtn" onclick="loadStudents()"
                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition"
                    style="background:#B47A42;">
                <i data-lucide="users" class="w-4 h-4"></i>
                Manage Promotion
            </button>
        </div>
    </div>
</div>


<div id="studentTable" class="hidden">
    <form method="POST" action="<?php echo e(route('sm-student.promote.store')); ?>" id="promoteForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="to_session_id" id="toSessionHidden">
        <input type="hidden" name="to_class_id" id="toClassHidden">

        <div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100" style="background:#f5f1eb;">
                <h3 class="font-semibold text-heading text-sm">Students to Promote</h3>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition"
                        style="background:#B47A42;">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    Promote Selected
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100" style="background:#fafafa;">
                            <th class="px-4 py-3 text-left w-8">
                                <input type="checkbox" id="selectAllStudents" class="rounded accent-navy">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-body">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-body">Admission No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-body">Roll No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-body">Section</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-body">Status</th>
                        </tr>
                    </thead>
                    <tbody id="studentRows" class="divide-y divide-gray-50">
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<div id="loadingSpinner" class="hidden text-center py-8">
    <div class="inline-flex items-center gap-2 text-body text-sm">
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
        </svg>
        Loading students…
    </div>
</div>

<script>
function loadTargetClasses(sessionId) {
    const toClassSelect = document.getElementById('toClass');
    toClassSelect.innerHTML = '<option value="">Loading classes…</option>';

    if (!sessionId) {
        toClassSelect.innerHTML = '<option value="">Select Next Session First</option>';
        return;
    }

    fetch(`/sm-student/promote/classes/${sessionId}`)
        .then(r => r.json())
        .then(classes => {
            toClassSelect.innerHTML = '';
            if (!classes.length) {
                toClassSelect.innerHTML = '<option value="">No classes found in selected session</option>';
            } else {
                toClassSelect.innerHTML = '<option value="">Select Class</option>';
                classes.forEach(c => {
                    toClassSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
            }
        })
        .catch(() => {
            toClassSelect.innerHTML = '<option value="">Failed to load classes</option>';
        });
}

function loadStudents() {
    const sessionId = document.getElementById('toSession').value;
    const classId   = document.getElementById('fromClass').value;
    const toClassId = document.getElementById('toClass').value;

    if (!sessionId || !classId || !toClassId) {
        alert('Please select all fields: current session/class and promotion target.');
        return;
    }

    document.getElementById('toSessionHidden').value = sessionId;
    document.getElementById('toClassHidden').value   = toClassId;

    document.getElementById('loadingSpinner').classList.remove('hidden');
    document.getElementById('studentTable').classList.add('hidden');

    fetch(`<?php echo e(route('sm-student.promote.students')); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            class_id:   classId,
            session_id: '<?php echo e($currentSession?->id); ?>'
        })
    })
    .then(r => r.json())
    .then(students => {
        document.getElementById('loadingSpinner').classList.add('hidden');
        const tbody = document.getElementById('studentRows');
        tbody.innerHTML = '';

        if (!students.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-body text-sm">No active students found in the selected class.</td></tr>';
        } else {
            students.forEach(s => {
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="student_ids[]" value="${s.id}" class="promote-cb rounded accent-navy" checked>
                        </td>
                        <td class="px-4 py-3 font-medium text-heading">${s.full_name}</td>
                        <td class="px-4 py-3 text-body">${s.admission_no}</td>
                        <td class="px-4 py-3 text-body">${s.roll_number ?? '—'}</td>
                        <td class="px-4 py-3 text-body">${s.section ?? '—'}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">${s.status}</span>
                        </td>
                    </tr>`;
            });
        }

        document.getElementById('studentTable').classList.remove('hidden');
        lucide.createIcons();
    })
    .catch(() => {
        document.getElementById('loadingSpinner').classList.add('hidden');
        alert('Failed to load students. Please try again.');
    });
}

document.getElementById('selectAllStudents')?.addEventListener('change', function () {
    document.querySelectorAll('.promote-cb').forEach(cb => cb.checked = this.checked);
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/student/promote/index.blade.php ENDPATH**/ ?>