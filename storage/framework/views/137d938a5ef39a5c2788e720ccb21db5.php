<?php $__env->startSection('header', 'All Books'); ?>

<?php $__env->startSection('content'); ?>


<div class="rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6" style="background:#B47A42;">
    <div class="flex items-center gap-2">
        <i data-lucide="book" class="w-5 h-5 text-white"></i>
        <span class="text-white font-bold text-base tracking-wide">Books</span>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('library.dashboard')); ?>"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
           style="color:#B47A42;">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard
        </a>
        <a href="<?php echo e(route('library.books.create')); ?>"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
           style="color:#B47A42;">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add New Book
        </a>
    </div>
</div>


<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <div class="flex items-center gap-3">
        <i data-lucide="search" class="w-4 h-4 text-body"></i>
        <input type="text" id="bookSearch" placeholder="Search books by title, author, subject..."
               class="flex-1 text-sm outline-none bg-transparent text-heading placeholder-body" />
    </div>
</div>


<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="booksTable">
            <thead>
                <tr style="background:#B47A42;">
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">#</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Title</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Author</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Subject</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Rack No.</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Book No.</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">ISBN</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Price</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Qty</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Issued</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-orange-50/30 transition book-row">
                    <td class="px-4 py-3 text-body text-xs"><?php echo e($i + 1); ?></td>
                    <td class="px-4 py-3 font-medium text-heading"><?php echo e($book->title); ?></td>
                    <td class="px-4 py-3 text-body"><?php echo e($book->author ?? '—'); ?></td>
                    <td class="px-4 py-3 text-body"><?php echo e($book->subject ?? '—'); ?></td>
                    <td class="px-4 py-3 text-body"><?php echo e($book->rack_number ?? '—'); ?></td>
                    <td class="px-4 py-3 text-body"><?php echo e($book->book_number ?? '—'); ?></td>
                    <td class="px-4 py-3 text-body text-xs"><?php echo e($book->isbn_number ?? '—'); ?></td>
                    <td class="px-4 py-3 text-body"><?php echo e($book->price ? '₹'.number_format($book->price, 2) : '—'); ?></td>
                    <td class="px-4 py-3">
                        <span class="font-semibold text-heading">Total: <?php echo e($book->quantity); ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              style="background:#fff4e5; color:#c26c10;">
                            Issued: <?php echo e($book->active_issues_count); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1.5">
                            <a href="<?php echo e(route('library.books.edit', $book)); ?>"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-white hover:opacity-90 transition"
                               style="background:#B47A42;">
                                <i data-lucide="pencil" class="w-3 h-3"></i> Edit
                            </a>
                            <form method="POST" action="<?php echo e(route('library.books.destroy', $book)); ?>"
                                  onsubmit="return confirm('Delete this book?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-white hover:opacity-90 transition"
                                        style="background:#b44242;">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i> Delete
                                </button>
                            </form>
                            <a href="<?php echo e(route('library.book-issue')); ?>?book_id=<?php echo e($book->id); ?>"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-white hover:opacity-90 transition"
                               style="background:#2e7d52;">
                                Issue Book
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="11" class="px-4 py-10 text-center text-body text-sm">
                        No books found. <a href="<?php echo e(route('library.books.create')); ?>" style="color:#B47A42;" class="font-semibold">Add the first book →</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('bookSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.book-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/library/books.blade.php ENDPATH**/ ?>