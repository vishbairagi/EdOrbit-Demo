<?php $__env->startSection('header', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <!-- Settings Header & Tabs -->
    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-4" id="settings-tabs">
        <button type="button" class="tab-btn px-5 py-2 rounded-lg font-bold text-sm transition-colors bg-navy text-white shadow-sm" data-target="#tab-general">General</button>
        <button type="button" class="tab-btn px-5 py-2 rounded-lg font-bold text-sm transition-colors text-gray-500 hover:bg-gray-100 hover:text-gray-900" data-target="#tab-appearances">Appearances</button>
        <button type="button" class="tab-btn px-5 py-2 rounded-lg font-bold text-sm transition-colors text-gray-500 hover:bg-gray-100 hover:text-gray-900" data-target="#tab-reset">Reset Plugin</button>
        <button type="button" class="tab-btn px-5 py-2 rounded-lg font-bold text-sm transition-colors text-gray-500 hover:bg-gray-100 hover:text-gray-900" data-target="#tab-demo">Demo Data</button>
    </div>

    <!-- Main Settings Form -->
    <form action="<?php echo e(route('school-management.settings.update')); ?>" method="POST" enctype="multipart/form-data" id="main-settings-form">
        <?php echo csrf_field(); ?>
        
        <!-- Tab: General -->
        <div id="tab-general" class="tab-content block">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-sm font-bold text-heading">General Configuration</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-heading mb-2">Set Active Session</label>
                        <?php
                            $sessions = \App\Models\AcademicSession::all();
                            $currentSessionId = \App\Models\SystemSetting::getSetting('current_session_id');
                        ?>
                        <select name="current_session_id" class="block w-full max-w-md px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-navy focus:border-navy text-sm">
                            <option value="">-- Select Current Session --</option>
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($session->id); ?>" <?php echo e($currentSessionId == $session->id ? 'selected' : ''); ?>>
                                    <?php echo e($session->name); ?> (<?php echo e(ucfirst($session->status)); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-heading mb-2">Set Date Format</label>
                        <?php $dateFormat = \App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'); ?>
                        <select name="date_format" class="block w-full max-w-md px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-navy focus:border-navy text-sm">
                            <option value="d-m-Y" <?php echo e($dateFormat === 'd-m-Y' ? 'selected' : ''); ?>>dd-mm-yyyy</option>
                            <option value="m-d-Y" <?php echo e($dateFormat === 'm-d-Y' ? 'selected' : ''); ?>>mm-dd-yyyy</option>
                            <option value="Y-m-d" <?php echo e($dateFormat === 'Y-m-d' ? 'selected' : ''); ?>>yyyy-mm-dd</option>
                            <option value="d/m/Y" <?php echo e($dateFormat === 'd/m/Y' ? 'selected' : ''); ?>>dd/mm/yyyy</option>
                            <option value="m/d/Y" <?php echo e($dateFormat === 'm/d/Y' ? 'selected' : ''); ?>>mm/dd/yyyy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-heading mb-3">Menu Visibility</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <?php 
                                $showLib = \App\Models\SystemSetting::getSetting('show_library_menu', '1') == '1'; 
                                $showExam = \App\Models\SystemSetting::getSetting('show_examination_menu', '1') == '1';
                                $showTrans = \App\Models\SystemSetting::getSetting('show_transport_menu', '1') == '1';
                                $showAcc = \App\Models\SystemSetting::getSetting('show_accounting_menu', '1') == '1';
                            ?>
                            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="show_library_menu" value="1" <?php echo e($showLib ? 'checked' : ''); ?> class="rounded text-navy focus:ring-navy h-4 w-4">
                                <span class="text-sm font-semibold text-gray-700">Library Menu</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="show_examination_menu" value="1" <?php echo e($showExam ? 'checked' : ''); ?> class="rounded text-navy focus:ring-navy h-4 w-4">
                                <span class="text-sm font-semibold text-gray-700">Examination Menu</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="show_transport_menu" value="1" <?php echo e($showTrans ? 'checked' : ''); ?> class="rounded text-navy focus:ring-navy h-4 w-4">
                                <span class="text-sm font-semibold text-gray-700">Transport Menu</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="show_accounting_menu" value="1" <?php echo e($showAcc ? 'checked' : ''); ?> class="rounded text-navy focus:ring-navy h-4 w-4">
                                <span class="text-sm font-semibold text-gray-700">Accounting Menu</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-navy hover:bg-primary-dark text-white text-sm font-bold py-2 px-6 rounded-lg transition-colors shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab: Appearances -->
        <div id="tab-appearances" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-sm font-bold text-heading">Appearance Settings</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-heading mb-2">Project Theme Color</label>
                        <div class="flex items-center gap-4">
                            <input type="color" name="theme_color" value="<?php echo e(\App\Models\SystemSetting::getSetting('theme_color', '#B47A42')); ?>" class="h-12 w-24 p-1 border border-gray-300 rounded-md cursor-pointer shadow-sm">
                            <span class="text-sm text-gray-500 max-w-sm">Select the main primary color for the system. This affects buttons, links, and highlights.</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-navy hover:bg-primary-dark text-white text-sm font-bold py-2 px-6 rounded-lg transition-colors shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i> Save Settings
                    </button>
                </div>
            </div>
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
                    t.classList.remove('bg-navy', 'text-white', 'shadow-sm');
                    t.classList.add('text-gray-500', 'hover:bg-gray-100', 'hover:text-gray-900');
                });
                
                contents.forEach(c => {
                    c.classList.add('hidden');
                    c.classList.remove('block');
                });
                
                tab.classList.add('bg-navy', 'text-white', 'shadow-sm');
                tab.classList.remove('text-gray-500', 'hover:bg-gray-100', 'hover:text-gray-900');
                
                const target = document.querySelector(tab.dataset.target);
                if(target) {
                    target.classList.remove('hidden');
                    target.classList.add('block');
                }
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/school-management/settings/index.blade.php ENDPATH**/ ?>