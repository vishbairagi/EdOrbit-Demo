<?php $__env->startSection('header', 'Edit School: ' . $school->name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?php echo e(route('school-management.schools.index')); ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-navy transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Schools
    </a>
    <a href="<?php echo e(route('school-management.schools.show', $school)); ?>" class="inline-flex items-center text-sm font-medium text-navy hover:text-primary-dark transition-colors">
        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View School
    </a>
</div>

<form action="<?php echo e(route('school-management.schools.update', $school)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        
        <div class="lg:col-span-2 space-y-5">

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-navy/5 to-transparent">
                    <i data-lucide="building-2" class="w-4 h-4 mr-2 text-navy"></i>
                    <h3 class="text-sm font-semibold text-heading">School Information</h3>
                </div>
                <div class="p-5 space-y-4">
                    
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-600 mb-1">School Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="<?php echo e(old('name', $school->name)); ?>" required
                            placeholder="e.g. Sunrise Public School"
                            class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-gray-600 mb-1">Phone</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
                                </div>
                                <input type="text" name="phone" id="phone" value="<?php echo e(old('phone', $school->phone)); ?>"
                                    placeholder="+91 9876543210"
                                    class="block w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                            </div>
                            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i>
                                </div>
                                <input type="email" name="email" id="email" value="<?php echo e(old('email', $school->email)); ?>"
                                    placeholder="school@example.com"
                                    class="block w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                            </div>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    
                    <div>
                        <label for="registration_number" class="block text-xs font-semibold text-gray-600 mb-1">Registration Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="hash" class="w-3.5 h-3.5 text-gray-400"></i>
                            </div>
                            <input type="text" name="registration_number" id="registration_number" value="<?php echo e(old('registration_number', $school->registration_number)); ?>"
                                placeholder="e.g. SCH-2024-001"
                                class="block w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                        </div>
                        <?php $__errorArgs = ['registration_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label for="address" class="block text-xs font-semibold text-gray-600 mb-1">Address</label>
                        <div class="relative">
                            <div class="absolute top-2.5 left-3 pointer-events-none">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-gray-400"></i>
                            </div>
                            <textarea name="address" id="address" rows="2"
                                placeholder="School address..."
                                class="block w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all resize-none"><?php echo e(old('address', $school->address)); ?></textarea>
                        </div>
                        <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label for="description" class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                        <textarea name="description" id="description" rows="2"
                            placeholder="Brief description about the school..."
                            class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all resize-none"><?php echo e(old('description', $school->description)); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-center px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-transparent">
                        <i data-lucide="users" class="w-4 h-4 mr-2 text-blue-500"></i>
                        <h3 class="text-sm font-semibold text-heading">Enrollment Number</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <label for="enrollment_prefix" class="block text-xs font-semibold text-gray-600 mb-1">Prefix</label>
                            <input type="text" name="enrollment_prefix" id="enrollment_prefix"
                                value="<?php echo e(old('enrollment_prefix', $school->enrollment_prefix)); ?>"
                                placeholder="e.g. ENR-"
                                onkeyup="updateEnrollmentPreview()"
                                class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="enrollment_base_number" class="block text-xs font-semibold text-gray-600 mb-1">Base Number</label>
                                <input type="number" name="enrollment_base_number" id="enrollment_base_number"
                                    value="<?php echo e(old('enrollment_base_number', $school->enrollment_base_number)); ?>" min="0"
                                    onkeyup="updateEnrollmentPreview()" onchange="updateEnrollmentPreview()"
                                    class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                            </div>
                            <div>
                                <label for="enrollment_base_padding" class="block text-xs font-semibold text-gray-600 mb-1">Padding</label>
                                <input type="number" name="enrollment_base_padding" id="enrollment_base_padding"
                                    value="<?php echo e(old('enrollment_base_padding', $school->enrollment_base_padding)); ?>" min="1" max="20"
                                    onkeyup="updateEnrollmentPreview()" onchange="updateEnrollmentPreview()"
                                    class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                            </div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <p class="text-xs text-blue-500 font-medium mb-0.5">Preview</p>
                            <span id="enrollment_preview" class="text-base font-bold text-navy tracking-wider"></span>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-center px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-green-50 to-transparent">
                        <i data-lucide="user-check" class="w-4 h-4 mr-2 text-green-500"></i>
                        <h3 class="text-sm font-semibold text-heading">Admission Number</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <label for="admission_prefix" class="block text-xs font-semibold text-gray-600 mb-1">Prefix</label>
                            <input type="text" name="admission_prefix" id="admission_prefix"
                                value="<?php echo e(old('admission_prefix', $school->admission_prefix)); ?>"
                                placeholder="e.g. ADM-"
                                onkeyup="updateAdmissionPreview()"
                                class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="admission_base_number" class="block text-xs font-semibold text-gray-600 mb-1">Base Number</label>
                                <input type="number" name="admission_base_number" id="admission_base_number"
                                    value="<?php echo e(old('admission_base_number', $school->admission_base_number)); ?>" min="0"
                                    onkeyup="updateAdmissionPreview()" onchange="updateAdmissionPreview()"
                                    class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                            </div>
                            <div>
                                <label for="admission_base_padding" class="block text-xs font-semibold text-gray-600 mb-1">Padding</label>
                                <input type="number" name="admission_base_padding" id="admission_base_padding"
                                    value="<?php echo e(old('admission_base_padding', $school->admission_base_padding)); ?>" min="1" max="20"
                                    onkeyup="updateAdmissionPreview()" onchange="updateAdmissionPreview()"
                                    class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                            </div>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                            <p class="text-xs text-green-500 font-medium mb-0.5">Preview</p>
                            <span id="admission_preview" class="text-base font-bold text-navy tracking-wider"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="space-y-5">

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-navy/5 to-transparent">
                    <i data-lucide="toggle-right" class="w-4 h-4 mr-2 text-navy"></i>
                    <h3 class="text-sm font-semibold text-heading">Status</h3>
                </div>
                <div class="p-5">
                    <label for="status" class="block text-xs font-semibold text-gray-600 mb-1">School Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" class="block w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy transition-all">
                        <option value="active" <?php echo e(old('status', $school->status) === 'active' ? 'selected' : ''); ?>>✓ Active</option>
                        <option value="inactive" <?php echo e(old('status', $school->status) === 'inactive' ? 'selected' : ''); ?>>✗ Inactive</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                <div class="flex items-start">
                    <i data-lucide="info" class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0"></i>
                    <div class="space-y-1.5 w-full">
                        <p class="text-xs font-semibold text-gray-600">School Info</p>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Created</span>
                            <span class="text-gray-700 font-medium"><?php echo e($school->created_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Last Updated</span>
                            <span class="text-gray-700 font-medium"><?php echo e($school->updated_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="flex flex-col gap-2">
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 bg-navy hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update School
                </button>
                <a href="<?php echo e(route('school-management.schools.show', $school)); ?>" class="w-full flex items-center justify-center px-4 py-2.5 border border-gray-200 text-sm font-medium rounded-lg text-gray-600 bg-white hover:bg-gray-50 transition-colors">
                    <i data-lucide="eye" class="w-4 h-4 mr-2"></i> View School
                </a>
                <a href="<?php echo e(route('school-management.schools.index')); ?>" class="w-full flex items-center justify-center px-4 py-2.5 border border-gray-200 text-sm font-medium rounded-lg text-gray-500 bg-white hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
    function generatePreview(prefix, baseNumber, padding) {
        let numStr = String(baseNumber);
        if (numStr.length < padding) numStr = '0'.repeat(padding - numStr.length) + numStr;
        return prefix + numStr;
    }
    function updateEnrollmentPreview() {
        let prefix = document.getElementById('enrollment_prefix').value || '';
        let baseNumber = parseInt(document.getElementById('enrollment_base_number').value) || 0;
        let padding = parseInt(document.getElementById('enrollment_base_padding').value) || 1;
        document.getElementById('enrollment_preview').innerText = generatePreview(prefix, baseNumber + 1, padding);
    }
    function updateAdmissionPreview() {
        let prefix = document.getElementById('admission_prefix').value || '';
        let baseNumber = parseInt(document.getElementById('admission_base_number').value) || 0;
        let padding = parseInt(document.getElementById('admission_base_padding').value) || 1;
        document.getElementById('admission_preview').innerText = generatePreview(prefix, baseNumber, padding);
    }
    document.addEventListener('DOMContentLoaded', function() {
        updateEnrollmentPreview();
        updateAdmissionPreview();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/school-management/schools/edit.blade.php ENDPATH**/ ?>