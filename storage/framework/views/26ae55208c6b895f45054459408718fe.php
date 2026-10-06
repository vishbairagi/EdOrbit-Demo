<?php $__env->startSection('header', 'Edit Vehicle'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-heading">Edit Vehicle</h2>
        <p class="text-xs text-body"><?php echo e($school->name ?? 'School'); ?> · <?php echo e($session->name ?? 'Session'); ?></p>
    </div>
    <a href="<?php echo e(route('transport.vehicles.index')); ?>" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-body hover:bg-white"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to vehicles</a>
</div>

<section class="mb-5 rounded-lg border border-gray-200 bg-white">
    <form action="<?php echo e(route('transport.vehicles.update', $vehicle)); ?>" method="POST" class="space-y-5 p-5">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="vehicle_number" class="mb-1 block text-sm font-medium text-body">Vehicle Number <span class="text-red-500">*</span></label>
                <input type="text" name="vehicle_number" id="vehicle_number" value="<?php echo e(old('vehicle_number', $vehicle->vehicle_number)); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['vehicle_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="vehicle_model" class="mb-1 block text-sm font-medium text-body">Vehicle Model</label>
                <input type="text" name="vehicle_model" id="vehicle_model" value="<?php echo e(old('vehicle_model', $vehicle->vehicle_model)); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['vehicle_model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="driver_name" class="mb-1 block text-sm font-medium text-body">Driver Name <span class="text-red-500">*</span></label>
                <input type="text" name="driver_name" id="driver_name" value="<?php echo e(old('driver_name', $vehicle->driver_name)); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['driver_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="driver_phone" class="mb-1 block text-sm font-medium text-body">Driver Phone <span class="text-red-500">*</span></label>
                <input type="text" name="driver_phone" id="driver_phone" value="<?php echo e(old('driver_phone', $vehicle->driver_phone)); ?>" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['driver_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="license_number" class="mb-1 block text-sm font-medium text-body">License Number</label>
                <input type="text" name="license_number" id="license_number" value="<?php echo e(old('license_number', $vehicle->license_number)); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                <?php $__errorArgs = ['license_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="md:col-span-2">
                <label for="note" class="mb-1 block text-sm font-medium text-body">Note</label>
                <textarea name="note" id="note" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"><?php echo e(old('note', $vehicle->note)); ?></textarea>
                <?php $__errorArgs = ['note'];
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
            <a href="<?php echo e(route('transport.vehicles.index')); ?>" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-body hover:bg-white">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="save" class="h-4 w-4"></i>Update Vehicle</button>
        </div>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/transport/vehicles/edit.blade.php ENDPATH**/ ?>