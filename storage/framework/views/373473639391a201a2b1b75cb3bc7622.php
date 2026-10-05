<?php $__env->startSection('header', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <!-- Settings Header & Tabs -->
    <div class="mb-6 flex gap-2 border-b border-gray-200 overflow-x-auto" id="settings-tabs">
        <button type="button" class="tab-btn whitespace-nowrap px-4 py-2 border-b-2 border-primary text-primary font-semibold text-sm transition-colors" data-target="#tab-general">General</button>
        <button type="button" class="tab-btn whitespace-nowrap px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold text-sm transition-colors" data-target="#tab-appearances">Appearances</button>
        <button type="button" class="tab-btn whitespace-nowrap px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold text-sm transition-colors" data-target="#tab-reset">Reset Plugin</button>
        <button type="button" class="tab-btn whitespace-nowrap px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold text-sm transition-colors" data-target="#tab-demo">Demo Data</button>
    </div>

    <!-- Main Settings Form -->
    <form action="<?php echo e(route('school-management.settings.update')); ?>" method="POST" enctype="multipart/form-data" id="main-settings-form">
        <?php echo csrf_field(); ?>
        
        <!-- Tab: General -->
        <div id="tab-general" class="tab-content block">
            <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Set Active Session:</label>
                        <div class="col-span-2">
                            <?php
                                $sessions = \App\Models\AcademicSession::all();
                                $currentSessionId = \App\Models\SystemSetting::getSetting('current_session_id');
                            ?>
                            <select name="current_session_id" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">-- Select Current Session --</option>
                                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($session->id); ?>" <?php echo e($currentSessionId == $session->id ? 'selected' : ''); ?>>
                                        <?php echo e($session->name); ?> (<?php echo e(ucfirst($session->status)); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Set Date Format:</label>
                        <div class="col-span-2">
                            <?php $dateFormat = \App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'); ?>
                            <select name="date_format" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="d-m-Y" <?php echo e($dateFormat === 'd-m-Y' ? 'selected' : ''); ?>>dd-mm-yyyy</option>
                                <option value="m-d-Y" <?php echo e($dateFormat === 'm-d-Y' ? 'selected' : ''); ?>>mm-dd-yyyy</option>
                                <option value="Y-m-d" <?php echo e($dateFormat === 'Y-m-d' ? 'selected' : ''); ?>>yyyy-mm-dd</option>
                                <option value="d/m/Y" <?php echo e($dateFormat === 'd/m/Y' ? 'selected' : ''); ?>>dd/mm/yyyy</option>
                                <option value="m/d/Y" <?php echo e($dateFormat === 'm/d/Y' ? 'selected' : ''); ?>>mm/dd/yyyy</option>
                            </select>
                        </div>
                    </div>



                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-1">Menu Visibility:</label>
                        <div class="col-span-2 space-y-4">
                            <?php 
                                $showLib = \App\Models\SystemSetting::getSetting('show_library_menu', '1') == '1'; 
                                $showExam = \App\Models\SystemSetting::getSetting('show_examination_menu', '1') == '1';
                                $showTrans = \App\Models\SystemSetting::getSetting('show_transport_menu', '1') == '1';
                                $showAcc = \App\Models\SystemSetting::getSetting('show_accounting_menu', '1') == '1';
                            ?>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="show_library_menu" value="1" <?php echo e($showLib ? 'checked' : ''); ?> class="rounded text-primary focus:ring-primary h-4 w-4">
                                <span class="text-sm text-gray-700">Library Menu show</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="show_examination_menu" value="1" <?php echo e($showExam ? 'checked' : ''); ?> class="rounded text-primary focus:ring-primary h-4 w-4">
                                <span class="text-sm text-gray-700">Examination Menu show</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="show_transport_menu" value="1" <?php echo e($showTrans ? 'checked' : ''); ?> class="rounded text-primary focus:ring-primary h-4 w-4">
                                <span class="text-sm text-gray-700">Transport Menu show</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="show_accounting_menu" value="1" <?php echo e($showAcc ? 'checked' : ''); ?> class="rounded text-primary focus:ring-primary h-4 w-4">
                                <span class="text-sm text-gray-700">Accounting Menu show</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Appearances -->
        <div id="tab-appearances" class="tab-content hidden">
            <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Project Theme Color:</label>
                        <div class="col-span-2 flex items-center gap-3">
                            <input type="color" name="theme_color" value="<?php echo e(\App\Models\SystemSetting::getSetting('theme_color', '#B47A42')); ?>" class="h-10 w-20 p-1 border border-gray-300 rounded cursor-pointer">
                            <span class="text-sm text-gray-500">Select the main primary color for the system.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Form Save Button (Fixed at bottom or floating) -->
        <div class="flex justify-end pt-4 mb-8">
            <button type="submit" class="px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white transition-colors" style="background:var(--color-primary);">
                Save Settings
            </button>
        </div>
    </form>

    <!-- Tab: Reset System -->
    <div id="tab-reset" class="tab-content hidden">
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-4 bg-blue-50 border-b border-blue-100">
                <p class="text-sm text-blue-800 font-medium">Here, you can reset the plugin to its initial state.</p>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm font-bold text-heading">This will:</p>
                <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
                    <li>Recreate all database tables.</li>
                    <li>Reset all settings.</li>
                </ul>
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <form action="<?php echo e(route('school-management.settings.reset')); ?>" method="POST" onsubmit="return confirm('Are you sure you want to RESET the entire system? All data will be LOST permanently.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" style="background-color: #dc2626; color: white;" class="inline-flex items-center gap-2 px-6 py-2 shadow-sm text-sm font-bold rounded-md transition-colors hover:opacity-90">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Reset System
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Demo Data -->
    <div id="tab-demo" class="tab-content hidden">
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-heading mb-3">Generate Demo Data</h3>
                    <div class="p-4 bg-blue-50 border border-blue-100 rounded mb-4">
                        <p class="text-sm text-blue-800">This will generate demo data for testing purposes.</p>
                    </div>
                    <div class="flex justify-end">
                        <form action="<?php echo e(route('school-management.settings.demo-data')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" style="background-color: #f97316; color: white;" class="inline-flex items-center gap-2 px-6 py-2 shadow-sm text-sm font-bold rounded-md transition-colors hover:opacity-90">
                                <i data-lucide="database" class="w-4 h-4"></i> Generate Demo Data
                            </button>
                        </form>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 mt-6">
                    <h3 class="text-lg font-bold text-heading mb-3">Clean Demo Data</h3>
                    <div class="p-4 rounded mb-4" style="background-color: #fef08a; border: 1px solid #fde047;">
                        <p class="text-sm" style="color: #854d0e;">This will clean up all demo data. This action cannot be undone.</p>
                    </div>
                    <div class="flex justify-end">
                        <form action="<?php echo e(route('school-management.settings.clean-demo')); ?>" method="POST" onsubmit="return confirm('Are you sure you want to clean demo data?');">
                            <?php echo csrf_field(); ?>
                            <button type="submit" style="background-color: #dc2626; color: white;" class="inline-flex items-center gap-2 px-6 py-2 shadow-sm text-sm font-bold rounded-md transition-colors hover:opacity-90">
                                <i data-lucide="trash-2" class="w-4 h-4"></i> Clean Up
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.tab-btn');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('border-primary', 'text-primary');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                
                contents.forEach(c => c.classList.add('hidden'));
                contents.forEach(c => c.classList.remove('block'));
                
                tab.classList.add('border-primary', 'text-primary');
                tab.classList.remove('border-transparent', 'text-gray-500');
                
                const target = document.querySelector(tab.dataset.target);
                target.classList.remove('hidden');
                target.classList.add('block');
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/school-management/settings/index.blade.php ENDPATH**/ ?>