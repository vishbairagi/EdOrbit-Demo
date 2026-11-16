@extends('layouts.app')

@section('header', 'Add Study Material')

@section('content')

{{-- Top Header Section --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Add Study Material</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name ?? 'Current Session' }}</p>
    </div>
    <a href="{{ route('academic.study-materials.index') }}"
       class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
        Back to List
    </a>
</div>

<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-6 max-w-2xl">
    <form method="POST" action="{{ route('academic.study-materials.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-5">

            {{-- Book Name --}}
            <div>
                <label class="block text-sm font-medium text-heading mb-1">Book Name <span class="text-red-500">*</span></label>
                <input type="text" name="book_name" required placeholder="e.g. Mathematics Part-1"
                       value="{{ old('book_name') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
            </div>

            {{-- Title / Topic --}}
            <div>
                <label class="block text-sm font-medium text-heading mb-1">Title / Topic (optional)</label>
                <input type="text" name="title" placeholder="e.g. Chapter 1 - Number System"
                       value="{{ old('title') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
            </div>

            {{-- Class --}}
            <div>
                <label class="block text-sm font-medium text-heading mb-1">Class <span class="text-red-500">*</span></label>
                <select name="school_class_id" id="classSelect" required onchange="loadSections(this.value)"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    <option value="">— Select Class —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Section --}}
            <div>
                <label class="block text-sm font-medium text-heading mb-1">Section (optional)</label>
                <select name="section_id" id="sectionSelect"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                    <option value="">— All Sections —</option>
                </select>
                <p class="text-xs text-body mt-1">Leave blank to share with all sections of selected class.</p>
            </div>

            {{-- Material Type --}}
            <div>
                <label class="block text-sm font-medium text-heading mb-1">Material Type <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach(['pdf' => 'PDF', 'image' => 'Image', 'video' => 'Video', 'description' => 'Notes/Text'] as $value => $label)
                        <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-navy has-[:checked]:bg-navy/5">
                            <input type="radio" name="type" value="{{ $value }}" onchange="handleTypeChange('{{ $value }}')"
                                   {{ old('type', 'pdf') === $value ? 'checked' : '' }}
                                   class="text-navy">
                            <span class="text-sm font-medium text-heading">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- File Upload --}}
            <div id="fileSection">
                <label class="block text-sm font-medium text-heading mb-1">Upload File <span class="text-red-500">*</span></label>
                <input type="file" name="file" id="fileInput"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                <p class="text-xs text-body mt-1" id="fileHint">Max 50MB. PDF, images, or video files.</p>
            </div>

            {{-- Description / Notes --}}
            <div id="descriptionSection" class="hidden">
                <label class="block text-sm font-medium text-heading mb-1">Notes / Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="6" placeholder="Write your notes or description here..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y">{{ old('description') }}</textarea>
            </div>

        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit"
                    class="flex-1 bg-navy text-white text-sm font-semibold py-2.5 rounded-lg hover:opacity-90 transition-opacity">
                <i data-lucide="upload" class="w-4 h-4 inline mr-1.5"></i>
                Upload Material
            </button>
            <a href="{{ route('academic.study-materials.index') }}"
               class="flex-1 text-center bg-gray-100 text-heading text-sm font-medium py-2.5 rounded-lg hover:bg-gray-200 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    function handleTypeChange(type) {
        const fileSection = document.getElementById('fileSection');
        const descSection = document.getElementById('descriptionSection');
        const fileHint    = document.getElementById('fileHint');

        if (type === 'description') {
            fileSection.classList.add('hidden');
            descSection.classList.remove('hidden');
        } else {
            fileSection.classList.remove('hidden');
            descSection.classList.add('hidden');
            if (type === 'pdf') fileHint.textContent = 'Max 50MB. PDF files only.';
            else if (type === 'image') fileHint.textContent = 'Max 50MB. JPG, PNG, GIF etc.';
            else if (type === 'video') fileHint.textContent = 'Max 50MB. MP4, AVI etc.';
        }
    }

    function loadSections(classId) {
        const select = document.getElementById('sectionSelect');
        select.innerHTML = '<option value="">— All Sections —</option>';
        if (!classId) return;

        fetch('/academic/study-materials/sections/' + classId)
            .then(r => r.json())
            .then(sections => {
                sections.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    select.appendChild(opt);
                });
            });
    }

    // Init on load
    handleTypeChange('{{ old("type", "pdf") }}');
    @if(old('school_class_id'))
        loadSections('{{ old("school_class_id") }}');
    @endif
</script>

@endsection
