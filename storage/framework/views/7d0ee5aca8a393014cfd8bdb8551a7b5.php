<?php $__env->startSection('header', 'New Admission'); ?>

<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center">
            <i data-lucide="user-plus" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight">New Admission</h2>
            <p class="text-xs text-body">Session: <span class="font-semibold text-navy"><?php echo e($session?->name ?? '—'); ?></span></p>
        </div>
    </div>
    <a href="<?php echo e(route('sm-student.students.index')); ?>"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        View Students
    </a>
</div>


<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center">
        <i data-lucide="user-plus" class="w-5 h-5 text-white mr-2.5"></i>
        <span class="text-white font-bold text-base tracking-wide">New Admission For Session: <?php echo e($session?->name ?? '—'); ?></span>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('sm-student.students.index')); ?>"
           class="text-xs px-3 py-1.5 rounded bg-white font-medium hover:bg-gray-100 transition" style="color:#211B17;">
            View Students
        </a>
    </div>
</div>

<form method="POST" action="<?php echo e(route('sm-student.students.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Personal Detail</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">* First Name</label>
                <input type="text" name="first_name" value="<?php echo e(old('first_name')); ?>"
                       placeholder="Enter first name" required
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_middle_name', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Middle Name</label>
                <input type="text" name="middle_name" value="<?php echo e(old('middle_name')); ?>"
                       placeholder="Enter middle name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php endif; ?>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Last Name</label>
                <input type="text" name="last_name" value="<?php echo e(old('last_name')); ?>"
                       placeholder="Enter last name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Gender</label>
                <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                        <input type="radio" name="gender" value="Male" class="accent-navy"
                               <?php echo e(old('gender', 'Male') === 'Male' ? 'checked' : ''); ?>> Male
                    </label>
                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                        <input type="radio" name="gender" value="Female" class="accent-navy"
                               <?php echo e(old('gender') === 'Female' ? 'checked' : ''); ?>> Female
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Date of Birth</label>
                <input type="date" name="date_of_birth" value="<?php echo e(old('date_of_birth')); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_blood_group', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Blood Group</label>
                <select name="blood_group" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Blood Group</option>
                    <?php $__currentLoopData = ['A+','A-','B+','B-','AB+','AB-','O+','O-']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($bg); ?>" <?php echo e(old('blood_group') === $bg ? 'selected' : ''); ?>><?php echo e($bg); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_religion', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Religion</label>
                <input type="text" name="religion" value="<?php echo e(old('religion')); ?>"
                       placeholder="Enter religion"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_caste', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Caste / Sub Caste</label>
                <input type="text" name="caste" value="<?php echo e(old('caste')); ?>"
                       placeholder="Enter caste/sub caste"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_student_category', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Category</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>" <?php echo e(old('category') === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_student_type', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Student Type</label>
                <select id="studentTypeSelect" name="student_type_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Student Type</option>
                    <?php $__currentLoopData = $studentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $studentType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($studentType->id); ?>" <?php echo e(old('student_type_id') == $studentType->id ? 'selected' : ''); ?>><?php echo e($studentType->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'show_medium', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Medium</label>
                <select name="medium_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Medium</option>
                    <?php $__currentLoopData = $mediums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medium): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($medium->id); ?>" <?php echo e(old('medium_id') == $medium->id ? 'selected' : ''); ?>><?php echo e($medium->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Address</label>
                <textarea name="address" rows="2" placeholder="Enter student address"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy"><?php echo e(old('address')); ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Phone</label>
                <input type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                       placeholder="Enter student phone number"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                       placeholder="Enter student email address"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">City</label>
                <input type="text" name="city" value="<?php echo e(old('city')); ?>"
                       placeholder="Enter city"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">State</label>
                <input type="text" name="state" value="<?php echo e(old('state')); ?>"
                       placeholder="Enter state"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Country</label>
                <input type="text" name="country" value="<?php echo e(old('country')); ?>"
                       placeholder="Enter country"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Photo</label>
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-sm text-body file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-navy file:text-white hover:file:opacity-90">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Extra Note / Detail</label>
                <textarea name="extra_note" rows="2" placeholder="Enter any extra notes"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy"><?php echo e(old('extra_note')); ?></textarea>
            </div>

        </div>
    </div>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Class & Admission Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">Admission No</label>
                <input type="text" name="admission_no" value="<?php echo e(old('admission_no', $admissionNo)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Class</label>
                <select name="school_class_id" id="classSelect" required
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Class</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e(old('school_class_id') == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Section</label>
                <select name="section_id" id="sectionSelect"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Section</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Roll Number</label>
                <input type="number" name="roll_number" value="<?php echo e(old('roll_number')); ?>"
                       placeholder="Roll number"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

        </div>
    </div>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5" id="admissionFeesCard">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Admission Fee Types</h3>
            <p class="text-xs text-body mt-0.5">Choose the class fees to invoice now. You can add or remove fees before admission.</p>
        </div>
        <div class="p-5" id="admissionFeeTypesList">
            <p class="text-sm text-body italic">Select a class to load its admission fee types.</p>
        </div>
    </div>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5" id="subjectsCard">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Assign Subjects</h3>
            <p class="text-xs text-body mt-0.5">Subjects will load automatically based on selected class. You can uncheck to remove.</p>
        </div>
        <div class="p-5" id="subjectsList">
            <p class="text-sm text-body italic">Please select a class to load subjects.</p>
        </div>
    </div>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Parent / Guardian Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">Father's Name</label>
                <input type="text" name="father_name" value="<?php echo e(old('father_name')); ?>"
                       placeholder="Enter father's name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Father's Phone</label>
                <input type="text" name="father_phone" value="<?php echo e(old('father_phone')); ?>"
                       placeholder="Enter father's phone"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Father's Occupation</label>
                <input type="text" name="father_occupation" value="<?php echo e(old('father_occupation')); ?>"
                       placeholder="Enter occupation"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Mother's Name</label>
                <input type="text" name="mother_name" value="<?php echo e(old('mother_name')); ?>"
                       placeholder="Enter mother's name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Mother's Phone</label>
                <input type="text" name="mother_phone" value="<?php echo e(old('mother_phone')); ?>"
                       placeholder="Enter mother's phone"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Mother's Occupation</label>
                <input type="text" name="mother_occupation" value="<?php echo e(old('mother_occupation')); ?>"
                       placeholder="Enter occupation"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Guardian Name</label>
                <input type="text" name="guardian_name" value="<?php echo e(old('guardian_name')); ?>"
                       placeholder="Enter guardian name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Guardian Phone</label>
                <input type="text" name="guardian_phone" value="<?php echo e(old('guardian_phone')); ?>"
                       placeholder="Enter guardian phone"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Guardian Relation</label>
                <input type="text" name="guardian_relation" value="<?php echo e(old('guardian_relation')); ?>"
                       placeholder="e.g. Uncle, Aunt"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Guardian Address</label>
                <textarea name="guardian_address" rows="2" placeholder="Enter guardian address"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy"><?php echo e(old('guardian_address')); ?></textarea>
            </div>

        </div>
    </div>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Previous School Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">Previous School Name</label>
                <input type="text" name="previous_school_name" value="<?php echo e(old('previous_school_name')); ?>"
                       placeholder="Enter previous school name"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Previous Class</label>
                <input type="text" name="previous_class" value="<?php echo e(old('previous_class')); ?>"
                       placeholder="e.g. Grade 5"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Board</label>
                <input type="text" name="previous_school_board" value="<?php echo e(old('previous_school_board')); ?>"
                       placeholder="e.g. CBSE, ICSE"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">TC Number (Leaving Certificate)</label>
                <input type="text" name="leaving_certificate_no" value="<?php echo e(old('leaving_certificate_no')); ?>"
                       placeholder="Enter TC number"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-body mb-1">Previous School Address</label>
                <input type="text" name="previous_school_address" value="<?php echo e(old('previous_school_address')); ?>"
                       placeholder="Enter previous school address"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

        </div>
    </div>

    
    <div class="flex gap-3 justify-end">
        <a href="<?php echo e(route('sm-student.students.index')); ?>"
           class="px-5 py-2 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit"
                class="px-6 py-2 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                style="background:#B47A42;">
            <i data-lucide="save" class="w-4 h-4 inline-block mr-1"></i>
            Save Admission
        </button>
    </div>

</form>

<script>
const classSelect   = document.getElementById('classSelect');
const sectionSelect = document.getElementById('sectionSelect');
const subjectsList  = document.getElementById('subjectsList');
const admissionFeeTypesList = document.getElementById('admissionFeeTypesList');
const studentTypeSelect = document.getElementById('studentTypeSelect');
const previouslySelectedFeeTypes = <?php echo json_encode(old('fee_type_ids', []), 512) ?>;
let feeTypeRequestNumber = 0;

async function loadAdmissionFeeTypes() {
    const classId = classSelect.value;
    const requestNumber = ++feeTypeRequestNumber;
    if (!classId) {
        admissionFeeTypesList.innerHTML = '<p class="text-sm text-body italic">Select a class to load its admission fee types.</p>';
        return;
    }

    admissionFeeTypesList.innerHTML = '<p class="text-sm text-body italic">Loading fee types...</p>';
    const studentTypeQuery = studentTypeSelect && studentTypeSelect.value
        ? `?student_type_id=${encodeURIComponent(studentTypeSelect.value)}`
        : '';

    try {
        const response = await fetch(`<?php echo e(url('sm-student/classes')); ?>/${classId}/fee-types${studentTypeQuery}`, {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) throw new Error('Fee types could not be loaded.');
        const feeTypes = await response.json();
        if (requestNumber !== feeTypeRequestNumber) return;
        if (!feeTypes.length) {
            admissionFeeTypesList.innerHTML = '<p class="text-sm text-body italic">No admission fee types match this class and student type.</p>';
            return;
        }

        const selectedIds = previouslySelectedFeeTypes.map(Number);
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3';
        feeTypes.forEach(fee => {
            const label = document.createElement('label');
            label.className = 'flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:border-navy hover:bg-gray-50 transition';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'fee_type_ids[]';
            checkbox.value = fee.id;
            checkbox.checked = selectedIds.length ? selectedIds.includes(Number(fee.id)) : true;
            checkbox.className = 'mt-0.5 rounded accent-navy';
            const text = document.createElement('span');
            text.className = 'min-w-0 flex-1';
            const name = document.createElement('span');
            name.className = 'block text-sm font-semibold text-heading';
            name.textContent = fee.name;
            const details = document.createElement('span');
            details.className = 'block text-xs text-body';
            details.textContent = `${fee.period} · ${Number(fee.amount).toFixed(2)}`;
            text.append(name, details);
            label.append(checkbox, text);
            grid.append(label);
        });
        admissionFeeTypesList.replaceChildren(grid);
    } catch (error) {
        if (requestNumber !== feeTypeRequestNumber) return;
        admissionFeeTypesList.innerHTML = '<p class="text-sm text-red-700">Unable to load fee types. Change the class or student type and try again.</p>';
    }
}

classSelect.addEventListener('change', function () {
    const classId = this.value;
    if (!classId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        subjectsList.innerHTML  = '<p class="text-sm text-body italic">Please select a class to load subjects.</p>';
        loadAdmissionFeeTypes();
        return;
    }

    // Load sections
    fetch(`<?php echo e(url('sm-student/classes')); ?>/${classId}/sections`)
        .then(r => r.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            data.forEach(s => {
                sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });

    // Load subjects
    fetch(`<?php echo e(url('sm-student/classes')); ?>/${classId}/subjects`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                subjectsList.innerHTML = '<p class="text-sm text-body italic">No subjects found for this class.</p>';
                return;
            }
            let html = '<div class="grid grid-cols-2 md:grid-cols-4 gap-3">';
            data.forEach(s => {
                html += `
                    <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-navy hover:bg-blue-50 transition">
                        <input type="checkbox" name="subject_ids[]" value="${s.id}" checked
                               class="accent-navy w-4 h-4 rounded">
                        <div>
                            <p class="text-sm font-medium text-heading">${s.name}</p>
                            ${s.code ? `<p class="text-xs text-body">${s.code}</p>` : ''}
                        </div>
                    </label>`;
            });
            html += '</div>';
            subjectsList.innerHTML = html;
        });

    loadAdmissionFeeTypes();
});

if (studentTypeSelect) studentTypeSelect.addEventListener('change', loadAdmissionFeeTypes);
if (classSelect.value) classSelect.dispatchEvent(new Event('change'));
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/student/students/create.blade.php ENDPATH**/ ?>