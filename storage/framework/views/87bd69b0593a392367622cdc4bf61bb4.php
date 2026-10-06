<?php $__env->startSection('header', 'Edit Route'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Edit Transport Route</h2>
        <p class="text-xs text-body"><?php echo e($school->name ?? 'School'); ?> · <?php echo e($session->name ?? 'Session'); ?></p>
    </div>
    <a href="<?php echo e(route('transport.routes.index')); ?>" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to routes</a>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white">
    <form action="<?php echo e(route('transport.routes.update', $route)); ?>" method="POST" class="space-y-5 p-5">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="route_name" class="mb-1 block text-sm font-medium text-body">Route Name <span class="text-red-500">*</span></label>
                <input type="text" name="route_name" id="route_name" value="<?php echo e(old('route_name', $route->route_name)); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['route_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div>
                <label for="route_fare" class="mb-1 block text-sm font-medium text-body">Route Fare <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="route_fare" id="route_fare" value="<?php echo e(old('route_fare', $route->route_fare)); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['route_fare'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div>
                <label for="period_id" class="mb-1 block text-sm font-medium text-body">Period <span class="text-red-500">*</span></label>
                <select name="period_id" id="period_id" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select Period</option>
                    <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($period->id); ?>" <?php echo e(old('period_id', $route->period_id) == $period->id ? 'selected' : ''); ?>>
                            <?php echo e($period->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['period_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div>
                <label for="vehicles" class="mb-1 block text-sm font-medium text-body">Transport Vehicles</label>
                <select name="vehicles[]" id="vehicles" multiple class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm h-32 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <?php $routeVehicleIds = $route->vehicles->pluck('id')->toArray(); ?>
                    <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vehicle->id); ?>" <?php echo e(in_array($vehicle->id, old('vehicles', $routeVehicleIds)) ? 'selected' : ''); ?>>
                            <?php echo e($vehicle->vehicle_number); ?> - <?php echo e($vehicle->driver_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="text-[11px] text-gray-500 mt-1">Hold Ctrl/Command to select multiple.</p>
                <?php $__errorArgs = ['vehicles'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
        </div>
        
        <div class="flex justify-end gap-2 border-t border-gray-200 pt-5">
            <a href="<?php echo e(route('transport.routes.index')); ?>" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="save" class="h-4 w-4"></i>Update Route</button>
        </div>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/transport/routes/edit.blade.php ENDPATH**/ ?>