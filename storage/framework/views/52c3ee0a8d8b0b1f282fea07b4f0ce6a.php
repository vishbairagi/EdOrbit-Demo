<?php $__env->startSection('header', 'Administrator – Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 1400px; margin: 0 auto;">

    
    <?php
        $school = session('selected_school_id') ? \App\Models\School::find(session('selected_school_id')) : null;
        $currentSessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
        $currentSession   = $currentSessionId ? \App\Models\AcademicSession::find($currentSessionId) : null;
    ?>

    <?php if($school): ?>
    <div class="flex items-center justify-center mb-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold" style="color:#211B17;">
                <span style="color:#B47A42;">🏫</span> <?php echo e($school->name); ?>

                <?php if($currentSession): ?>
                    <span class="ml-2 text-lg font-medium" style="color:#766C63;"><?php echo e($currentSession->name); ?></span>
                <?php endif; ?>
            </h1>
            <?php if($currentSession): ?>
            <div class="mt-1 text-sm" style="color:#9e8f84;">
                Current Session: <span class="font-semibold" style="color:#B47A42;"><?php echo e($currentSession->name); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="flex items-center justify-center mb-6">
        <div class="px-8 py-3 rounded-lg font-semibold text-white text-lg tracking-wide flex items-center gap-2"
             style="background: linear-gradient(135deg, #B47A42, #D4A055); box-shadow: 0 4px 12px rgba(180,122,66,0.35);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Administrator
        </div>
    </div>

    <?php
    $dashboardCards = [
        [
            'title' => 'Admins',
            'subtitle' => 'Manage Admins',
            'count' => $adminsCount ?? 0,
            'icon' => 'shield-check',
            'color' => 'blue',
            'url' => route('sm-administrator.admins.index')
        ],
        [
            'title' => 'Roles',
            'subtitle' => 'Manage Roles',
            'count' => $rolesCount ?? 0,
            'icon' => 'shield',
            'color' => 'indigo',
            'url' => route('sm-administrator.roles.index')
        ],
        [
            'title' => 'Staff',
            'subtitle' => 'Active Staff',
            'count' => $staffCount ?? 0,
            'icon' => 'users',
            'color' => 'purple',
            'url' => route('sm-administrator.staff.index')
        ],
        [
            'title' => 'Staff ID Cards',
            'subtitle' => 'Print ID Cards',
            'count' => null,
            'icon' => 'id-card',
            'color' => 'cyan',
            'url' => route('sm-administrator.id-cards.index')
        ],
        [
            'title' => 'Staff Leaves',
            'subtitle' => 'Leaves Pending',
            'count' => $leavesCount ?? 0,
            'icon' => 'calendar-off',
            'color' => 'rose',
            'url' => route('sm-administrator.leaves.index')
        ],
        [
            'title' => 'Staff Payroll',
            'subtitle' => 'Salary Management',
            'count' => null,
            'icon' => 'circle-dollar-sign',
            'color' => 'emerald',
            'url' => route('sm-administrator.payroll.index')
        ],
        [
            'title' => 'Users',
            'subtitle' => 'All System Users',
            'count' => null,
            'icon' => 'user-check',
            'color' => 'amber',
            'url' => route('sm-administrator.users.index')
        ],
    ];
    ?>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
        <?php $__currentLoopData = $dashboardCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($card['url']); ?>" class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow group">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-<?php echo e($card['color']); ?>-50 flex items-center justify-center mr-2.5 flex-shrink-0 group-hover:bg-<?php echo e($card['color']); ?>-100 transition-colors">
                        <i data-lucide="<?php echo e($card['icon']); ?>" class="w-4 h-4 text-<?php echo e($card['color']); ?>-500"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-heading leading-tight"><?php echo e($card['title']); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($card['subtitle']); ?></p>
                    </div>
                </div>
                <?php if(!is_null($card['count'])): ?>
                    <div class="text-lg font-bold text-heading ml-1"><?php echo e($card['count']); ?></div>
                <?php else: ?>
                    <div class="text-gray-300 ml-1 group-hover:text-<?php echo e($card['color']); ?>-500 transition-colors">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/sm-administrator/dashboard.blade.php ENDPATH**/ ?>