<?php $__env->startSection('header', 'Library Dashboard'); ?>

<?php $__env->startSection('content'); ?>


<div class="mb-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#B47A42;">
            <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight"><?php echo e($school?->name ?? 'Library'); ?></h2>
            <p class="text-xs text-body">Session: <span class="font-semibold" style="color:#B47A42;"><?php echo e($session?->name ?? '—'); ?></span></p>
        </div>
    </div>
    <a href="<?php echo e(route('library.books.create')); ?>"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-semibold shadow-sm hover:opacity-90 transition"
       style="background:#B47A42;">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add New Book
    </a>
</div>


<div class="rounded-xl shadow-md px-5 py-3 flex items-center mb-6" style="background:#B47A42;">
    <i data-lucide="library" class="w-5 h-5 text-white mr-2.5"></i>
    <span class="text-white font-bold text-base tracking-wide">Library</span>
</div>


<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

    
    <div class="bg-card rounded-xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#e9f0fb;">
                <i data-lucide="book" class="w-5 h-5" style="color:#3b6dd4;"></i>
            </div>
            <span class="text-3xl font-bold text-heading"><?php echo e($totalBooks); ?></span>
        </div>
        <p class="text-sm font-semibold text-heading">Books</p>
        <p class="text-xs text-body mb-3">Library Inventory</p>
        <div class="flex gap-2 flex-wrap">
            <a href="<?php echo e(route('library.books')); ?>"
               class="text-xs px-3 py-1.5 rounded font-medium text-white hover:opacity-90 transition"
               style="background:#B47A42;">View</a>
            <a href="<?php echo e(route('library.books.create')); ?>"
               class="text-xs px-3 py-1.5 rounded font-medium text-white hover:opacity-90 transition"
               style="background:#B47A42;">Add</a>
        </div>
    </div>

    
    <div class="bg-card rounded-xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#eaf7ee;">
                <i data-lucide="book-check" class="w-5 h-5" style="color:#2e7d52;"></i>
            </div>
            <span class="text-3xl font-bold text-heading"><?php echo e($totalIssued); ?></span>
        </div>
        <p class="text-sm font-semibold text-heading">Books Issued</p>
        <p class="text-xs text-body mb-3">Pending <?php echo e($totalReturned); ?> returned</p>
        <div class="flex gap-2 flex-wrap">
            <a href="<?php echo e(route('library.book-issue')); ?>"
               class="text-xs px-3 py-1.5 rounded font-medium text-white hover:opacity-90 transition"
               style="background:#B47A42;">Issued List</a>
            <a href="<?php echo e(route('library.book-issue')); ?>#issue-form"
               class="text-xs px-3 py-1.5 rounded font-medium text-white hover:opacity-90 transition"
               style="background:#B47A42;">Issue Book</a>
        </div>
    </div>

    
    <div class="bg-card rounded-xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#fff4e5;">
                <i data-lucide="credit-card" class="w-5 h-5" style="color:#c26c10;"></i>
            </div>
            <span class="text-3xl font-bold text-heading"><?php echo e($totalCards); ?></span>
        </div>
        <p class="text-sm font-semibold text-heading">Library Cards</p>
        <p class="text-xs text-body mb-3">Issued to students</p>
        <div class="flex gap-2 flex-wrap">
            <a href="<?php echo e(route('library.library-cards')); ?>"
               class="text-xs px-3 py-1.5 rounded font-medium text-white hover:opacity-90 transition"
               style="background:#B47A42;">View Cards</a>
            <a href="<?php echo e(route('library.library-cards')); ?>#generate-form"
               class="text-xs px-3 py-1.5 rounded font-medium text-white hover:opacity-90 transition"
               style="background:#B47A42;">Issue Cards</a>
        </div>
    </div>

</div>


<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-2 flex items-center gap-2">
            <i data-lucide="book-plus" class="w-4 h-4" style="color:#B47A42;"></i>
            All Books
        </h4>
        <p class="text-xs text-body mb-3">View, add, edit and manage all library books.</p>
        <a href="<?php echo e(route('library.books')); ?>"
           class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md hover:opacity-90 transition"
           style="background:#B47A42;">
            Manage Books
        </a>
    </div>

    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-2 flex items-center gap-2">
            <i data-lucide="arrow-right-left" class="w-4 h-4" style="color:#B47A42;"></i>
            Book Issue
        </h4>
        <p class="text-xs text-body mb-3">Issue books to students and track returns.</p>
        <a href="<?php echo e(route('library.book-issue')); ?>"
           class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md hover:opacity-90 transition"
           style="background:#B47A42;">
            Issue / Return
        </a>
    </div>

    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <h4 class="text-base font-bold text-heading mb-2 flex items-center gap-2">
            <i data-lucide="credit-card" class="w-4 h-4" style="color:#B47A42;"></i>
            Library Cards
        </h4>
        <p class="text-xs text-body mb-3">Generate bulk library cards for students by class.</p>
        <a href="<?php echo e(route('library.library-cards')); ?>"
           class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md hover:opacity-90 transition"
           style="background:#B47A42;">
            Manage Cards
        </a>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/library/dashboard.blade.php ENDPATH**/ ?>