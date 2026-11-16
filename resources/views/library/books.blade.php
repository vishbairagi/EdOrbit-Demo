@extends('layouts.app')

@section('header', 'All Books')

@section('content')

{{-- Page Header --}}
<div class="rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6" style="background:#B47A42;">
    <div class="flex items-center gap-2">
        <i data-lucide="book" class="w-5 h-5 text-white"></i>
        <span class="text-white font-bold text-base tracking-wide">Books</span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('library.dashboard') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
           style="color:#B47A42;">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard
        </a>
        <a href="{{ route('library.books.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
           style="color:#B47A42;">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add New Book
        </a>
    </div>
</div>

{{-- Search Box --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <div class="flex items-center gap-3">
        <i data-lucide="search" class="w-4 h-4 text-body"></i>
        <input type="text" id="bookSearch" placeholder="Search books by title, author, subject..."
               class="flex-1 text-sm outline-none bg-transparent text-heading placeholder-body" />
    </div>
</div>

{{-- ══ Bulk Delete Toolbar ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('library.books.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>

{{-- Books Table --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="booksTable">
            <thead>
                <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">#</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Title</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Author</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Subject</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Rack No.</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Book No.</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">ISBN</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Price</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Qty</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Issued</th>
                    <th class="text-left px-4 py-3 text-white font-semibold text-xs uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($books as $i => $book)
                <tr class="hover:bg-orange-50/30 transition book-row" data-id="{{ $book->id }}">
                    <td class="px-4 py-2 text-center w-10">
                        <input type="checkbox" value="{{ $book->id }}" onchange="updateBulkToolbar()"
                            class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                    </td>
                    <td class="px-4 py-3 text-body text-xs">{{ $books->firstItem() ? ($books->firstItem() + $i) : ($i + 1) }}</td>
                    <td class="px-4 py-3 font-medium text-heading">{{ $book->title }}</td>
                    <td class="px-4 py-3 text-body">{{ $book->author ?? '—' }}</td>
                    <td class="px-4 py-3 text-body">{{ $book->subject ?? '—' }}</td>
                    <td class="px-4 py-3 text-body">{{ $book->rack_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-body">{{ $book->book_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-body text-xs">{{ $book->isbn_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-body">{{ $book->price ? '₹'.number_format($book->price, 2) : '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="font-semibold text-heading">Total: {{ $book->quantity }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              style="background:#fff4e5; color:#c26c10;">
                            Issued: {{ $book->active_issues_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1.5">
                            <a href="{{ route('library.books.edit', $book) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-white hover:opacity-90 transition"
                               style="background:#B47A42;">
                                <i data-lucide="pencil" class="w-3 h-3"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('library.books.destroy', $book) }}"
                                  onsubmit="return confirm('Delete this book?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-white hover:opacity-90 transition"
                                        style="background:#b44242;">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i> Delete
                                </button>
                            </form>
                            <a href="{{ route('library.book-issue') }}?book_id={{ $book->id }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-white hover:opacity-90 transition"
                               style="background:#2e7d52;">
                                Issue Book
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 py-10 text-center text-body text-sm">
                        No books found. <a href="{{ route('library.books.create') }}" style="color:#B47A42;" class="font-semibold">Add the first book →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($books, 'links'))
        <div class="p-2 border-t border-gray-100">
            {{ $books->links() }}
        </div>
    @endif
</div>

<script>
document.getElementById('bookSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.book-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

@endsection
