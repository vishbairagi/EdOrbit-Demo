<?php $__env->startSection('header', 'Edit Student'); ?>

<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-heading">Edit: <?php echo e($student->full_name); ?></h2>
    <a href="<?php echo e(route('sm-student.students.index')); ?>"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        Back
    </a>
</div>

<form method="POST" action="<?php echo e(route('sm-student.students.update', $student)); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Personal Detail</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">* First Name</label>
                <input type="text" name="first_name" value="<?php echo e(old('first_name', $student->first_name)); ?>" required
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_middle_name', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Middle Name</label>
                <input type="text" name="middle_name" value="<?php echo e(old('middle_name', $student->middle_name)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php endif; ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Last Name</label>
                <input type="text" name="last_name" value="<?php echo e(old('last_name', $student->last_name)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Gender</label>
                <div class="flex gap-4 mt-1">
                    <?php $__currentLoopData = ['Male','Female','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="radio" name="gender" value="<?php echo e($g); ?>" class="accent-navy"
                                   <?php echo e(old('gender', $student->gender) === $g ? 'checked' : ''); ?>> <?php echo e($g); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Date of Birth</label>
                <input type="date" name="date_of_birth" value="<?php echo e(old('date_of_birth', $student->date_of_birth?->format('Y-m-d'))); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_blood_group', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Blood Group</label>
                <select name="blood_group" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Blood Group</option>
                    <?php $__currentLoopData = ['A+','A-','B+','B-','AB+','AB-','O+','O-']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($bg); ?>" <?php echo e(old('blood_group', $student->blood_group) === $bg ? 'selected' : ''); ?>><?php echo e($bg); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_religion', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Religion</label>
                <input type="text" name="religion" value="<?php echo e(old('religion', $student->religion)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php endif; ?>
            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_caste', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Caste</label>
                <input type="text" name="caste" value="<?php echo e(old('caste', $student->caste)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <?php endif; ?>
            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_student_category', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Category</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>" <?php echo e(old('category', $student->category) === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>
            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_student_type', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Student Type</label>
                <select name="student_type_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Student Type</option>
                    <?php $__currentLoopData = $studentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $studentType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($studentType->id); ?>" <?php echo e(old('student_type_id', $student->student_type_id) == $studentType->id ? 'selected' : ''); ?>><?php echo e($studentType->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SchoolSetting::getSetting($student->school_id, $student->academic_session_id, 'show_medium', '1') == '1'): ?>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Medium</label>
                <select name="medium_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Medium</option>
                    <?php $__currentLoopData = $mediums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medium): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($medium->id); ?>" <?php echo e(old('medium_id', $student->medium_id) == $medium->id ? 'selected' : ''); ?>><?php echo e($medium->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-body mb-1">Address</label>
                <textarea name="address" rows="2"
                          class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy"><?php echo e(old('address', $student->address)); ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Phone</label>
                <input type="text" name="phone" value="<?php echo e(old('phone', $student->phone)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email', $student->email)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">City</label>
                <input type="text" name="city" value="<?php echo e(old('city', $student->city)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">State</label>
                <input type="text" name="state" value="<?php echo e(old('state', $student->state)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div>
                <label class="block text-xs font-medium text-body mb-1">Country</label>
                <input type="text" name="country" value="<?php echo e(old('country', $student->country)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Photo</label>
                <?php if($student->photo_url): ?>
                    <img src="<?php echo e($student->photo_url); ?>" class="w-16 h-16 rounded-lg object-cover mb-2 border" alt="Photo">
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-sm text-body file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-navy file:text-white hover:file:opacity-90">
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <?php $__currentLoopData = ['Active','Inactive','Transferred','Promoted']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(old('status', $student->status) === $s ? 'selected' : ''); ?>><?php echo e($s); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

        </div>
    </div>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Class & Enrollment</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-body mb-1">* Class</label>
                <select name="school_class_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e($student->school_class_id == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Section</label>
                <select name="section_id" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Section</option>
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($section->id); ?>" <?php echo e($student->section_id == $section->id ? 'selected' : ''); ?>>
                            <?php echo e($section->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-body mb-1">Roll Number</label>
                <input type="number" name="roll_number" value="<?php echo e(old('roll_number', $student->roll_number)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

        </div>
    </div>

    
    <?php if($subjects->count()): ?>
        <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
                <h3 class="font-bold text-heading text-sm">Assigned Subjects</h3>
                <p class="text-xs text-body mt-0.5">Uncheck to remove a subject from this student.</p>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer hover:border-navy hover:bg-blue-50 transition
                            <?php echo e(in_array($subject->id, $assignedSubjectIds) ? 'border-navy bg-blue-50' : 'border-gray-200'); ?>">
                            <input type="checkbox" name="subject_ids[]" value="<?php echo e($subject->id); ?>"
                                   class="accent-navy w-4 h-4"
                                   <?php echo e(in_array($subject->id, $assignedSubjectIds) ? 'checked' : ''); ?>>
                            <div>
                                <p class="text-sm font-medium text-heading"><?php echo e($subject->name); ?></p>
                                <?php if($subject->code): ?>
                                    <p class="text-xs text-body"><?php echo e($subject->code); ?></p>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-3 border-b border-gray-100 rounded-t-xl" style="background:#f5f1eb;">
            <h3 class="font-bold text-heading text-sm">Parent / Guardian Details</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="block text-xs font-medium text-body mb-1">Father's Name</label>
                <input type="text" name="father_name" value="<?php echo e(old('father_name', $student->father_name)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Father's Phone</label>
                <input type="text" name="father_phone" value="<?php echo e(old('father_phone', $student->father_phone)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Father's Occupation</label>
                <input type="text" name="father_occupation" value="<?php echo e(old('father_occupation', $student->father_occupation)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Mother's Name</label>
                <input type="text" name="mother_name" value="<?php echo e(old('mother_name', $student->mother_name)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Mother's Phone</label>
                <input type="text" name="mother_phone" value="<?php echo e(old('mother_phone', $student->mother_phone)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Mother's Occupation</label>
                <input type="text" name="mother_occupation" value="<?php echo e(old('mother_occupation', $student->mother_occupation)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Guardian Name</label>
                <input type="text" name="guardian_name" value="<?php echo e(old('guardian_name', $student->guardian_name)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Guardian Phone</label>
                <input type="text" name="guardian_phone" value="<?php echo e(old('guardian_phone', $student->guardian_phone)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div><label class="block text-xs font-medium text-body mb-1">Guardian Relation</label>
                <input type="text" name="guardian_relation" value="<?php echo e(old('guardian_relation', $student->guardian_relation)); ?>"
                       class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
        </div>
    </div>

    
    <div class="flex gap-3 justify-end">
        <a href="<?php echo e(route('sm-student.students.index')); ?>"
           class="px-5 py-2 rounded-lg border border-gray-200 text-sm text-body hover:bg-gray-50 transition">Cancel</a>
        <button type="submit"
                class="px-6 py-2 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                style="background:#B47A42;">
            <i data-lucide="save" class="w-4 h-4 inline-block mr-1"></i>
            Update Student
        </button>
    </div>

</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/student/students/edit.blade.php ENDPATH**/ ?>