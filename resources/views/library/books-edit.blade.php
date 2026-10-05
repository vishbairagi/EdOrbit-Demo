@extends('layouts.app')

@section('header', 'Edit Book')

@section('content')

<div class="rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6" style="background:#B47A42;">
    <div class="flex items-center gap-2">
        <i data-lucide="pencil" class="w-5 h-5 text-white"></i>
        <span class="text-white font-bold text-base">Edit Book</span>
    </div>
    <a href="{{ route('library.books') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-white hover:bg-gray-50 transition"
       style="color:#B47A42;">
        <i data-lucide="list" class="w-3.5 h-3.5"></i> View All
    </a>
</div>

@if($errors->any())
<div class="mb-5 p-4 rounded-lg" style="background:#fef0f0; border-left: 4px solid #B4232A;">
    <ul class="text-sm list-disc pl-5" style="color:#7a181c;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-6">
    <form method="POST" action="{{ route('library.books.update', $book) }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-heading mb-1.5">
                    <span class="text-red-500">*</span> Title
                </label>
                <input type="text" name="title" value="{{ old('title', $book->title) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" required />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Author</label>
                <input type="text" name="author" value="{{ old('author', $book->author) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $book->subject) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Price (₹)</label>
                <input type="number" name="price" value="{{ old('price', $book->price) }}" step="0.01" min="0"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">
                    <span class="text-red-500">*</span> Quantity
                </label>
                <input type="number" name="quantity" value="{{ old('quantity', $book->quantity) }}" min="1"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" required />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Rack Number</label>
                <input type="text" name="rack_number" value="{{ old('rack_number', $book->rack_number) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">Book Number</label>
                <input type="text" name="book_number" value="{{ old('book_number', $book->book_number) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-heading mb-1.5">ISBN Number</label>
                <input type="text" name="isbn_number" value="{{ old('isbn_number', $book->isbn_number) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none"
                       style="border-color: #DED5CB;" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-heading mb-1.5">Description</label>
                <textarea name="description" rows="4"
                          class="w-full border rounded-lg px-3 py-2 text-sm text-heading outline-none resize-none"
                          style="border-color: #DED5CB;">{{ old('description', $book->description) }}</textarea>
            </div>

        </div>

        <div class="flex justify-center mt-6">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-8 py-2.5 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition"
                    style="background:#B47A42;">
                <i data-lucide="save" class="w-4 h-4"></i>
                Update Book
            </button>
        </div>
    </form>
</div>

@endsection
