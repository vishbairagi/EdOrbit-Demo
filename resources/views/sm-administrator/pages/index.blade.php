@extends('layouts.app')

@section('header', 'Pages')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-[#DED5CB] overflow-hidden">
    <div class="p-6 border-b border-[#DED5CB] flex justify-between items-center bg-[#F5F1EB]">
        <div>
            <h3 class="text-lg font-semibold text-[#211B17]">All Pages</h3>
            <p class="text-sm text-[#766C63] mt-1">Manage your custom website pages here.</p>
        </div>
        <a href="{{ route('website-settings.pages.create') }}" class="px-4 py-2 bg-[#B47A42] text-white rounded-md text-sm font-medium hover:bg-[#9a6633] transition-colors">
            + Create New Page
        </a>
    </div>
    
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#F9F7F5] border-b border-[#DED5CB]">
                    <th class="px-6 py-3 text-xs font-semibold text-[#766C63] uppercase whitespace-nowrap">Title</th>
                    <th class="px-6 py-3 text-xs font-semibold text-[#766C63] uppercase whitespace-nowrap">URL Slug</th>
                    <th class="px-6 py-3 text-xs font-semibold text-[#766C63] uppercase whitespace-nowrap">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-[#766C63] uppercase whitespace-nowrap text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    <tr class="border-b border-[#DED5CB] hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-[#211B17]">{{ $page->title }}</td>
                        <td class="px-6 py-4 text-sm text-[#766C63]">/{{ $page->slug }}</td>
                        <td class="px-6 py-4">
                            @if($page->status)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Published</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <form action="{{ route('website-settings.pages.add-to-menu', $page) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="text-xs px-2 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition-colors" title="Add to Header Menu">
                                    Add to Menu
                                </button>
                            </form>
                            <a href="{{ route('website-settings.pages.edit', $page) }}" class="text-[#B47A42] hover:underline text-sm font-medium inline-block ml-2">Edit</a>
                            <form action="{{ route('website-settings.pages.destroy', $page) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline text-sm font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-[#766C63]">
                            No pages found. <a href="{{ route('website-settings.pages.create') }}" class="text-[#B47A42] hover:underline">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
