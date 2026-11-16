@extends('layouts.app')

@section('header', 'Create Page')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-[#DED5CB] overflow-hidden mb-10">
    <div class="p-6 border-b border-[#DED5CB] bg-[#F5F1EB]">
        <h3 class="text-lg font-semibold text-[#211B17]">Create New Page</h3>
    </div>
    
    <form action="{{ route('website-settings.pages.store') }}" method="POST" class="p-6" id="pageForm" onsubmit="prepareSubmit()">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-[#211B17] mb-1">Page Title *</label>
                <input type="text" name="title" required class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#211B17] mb-1">URL Slug (Optional)</label>
                <input type="text" name="slug" placeholder="e.g. about-us" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate from title.</p>
            </div>
        </div>

        <div class="mb-6 flex items-center gap-6">
            <div class="flex items-center gap-2">
                <input type="checkbox" name="status" id="status" checked class="accent-[#B47A42]">
                <label for="status" class="text-sm font-medium text-[#211B17]">Published</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_homepage" id="is_homepage" class="accent-[#B47A42]">
                <label for="is_homepage" class="text-sm font-medium text-[#211B17]">Set as Home Page</label>
            </div>
        </div>

        <div id="builderSection">
            <!-- Page Builder Section -->
        <h4 class="font-bold text-[#B47A42] text-md mb-4 border-b border-[#DED5CB] pb-2 mt-8">Page Builder</h4>
        
        <input type="hidden" name="blocks" id="blocksInput">
        
        <div id="blocksContainer" class="space-y-4 mb-6">
            <!-- Blocks will be rendered here -->
        </div>

        <div class="flex items-center gap-2 mb-8 bg-gray-50 p-3 rounded border border-gray-200">
            <select id="blockTypeSelector" class="border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                <option value="paragraph">Paragraph</option>
                <option value="heading1">Heading 1</option>
                <option value="heading2">Heading 2</option>
                <option value="heading3">Heading 3</option>
                <option value="heading4">Heading 4</option>
                <option value="heading5">Heading 5</option>
                <option value="heading6">Heading 6</option>
                <option value="image">Image Upload</option>
                <option value="quote">Quote</option>
                <option value="code">Code Block</option>
                <option value="list">List (Bullets)</option>
                <option value="columns">Columns (Nested Blocks)</option>
            </select>
            <button type="button" onclick="addBlock()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-medium hover:bg-gray-300 transition-colors">
                + Add Block
            </button>
        </div>

        </div>
        
        <div class="flex justify-end pt-4 border-t border-[#DED5CB]">
            <button type="submit" class="px-6 py-2 bg-[#B47A42] text-white rounded-md text-sm font-medium hover:bg-[#9a6633] transition-colors">
                Save Page
            </button>
        </div>
    </form>
</div>

<script>
    let blocks = {!! isset($page) ? json_encode($page->blocks ?? []) : '[]' !!};
    if (!Array.isArray(blocks)) blocks = [];

    function getBlockRef(path) {
        let curr = blocks;
        for (let i = 0; i < path.length; i++) {
            curr = curr[path[i]];
        }
        return curr;
    }

    function updateBlock(pathStr, key, val) {
        let path = JSON.parse(pathStr);
        let block = getBlockRef(path);
        block[key] = val;
    }

    function deleteBlock(pathStr) {
        let path = JSON.parse(pathStr);
        let index = path.pop();
        let parentArr = path.length === 0 ? blocks : getBlockRef(path);
        parentArr.splice(index, 1);
        renderBlocks();
    }

    function moveBlock(pathStr, dir) {
        let path = JSON.parse(pathStr);
        let index = path.pop();
        let parentArr = path.length === 0 ? blocks : getBlockRef(path);
        if (index + dir < 0 || index + dir >= parentArr.length) return;
        const temp = parentArr[index];
        parentArr[index] = parentArr[index+dir];
        parentArr[index+dir] = temp;
        renderBlocks();
    }

    function addBlock(pathStr) {
        let path = pathStr ? JSON.parse(pathStr) : [];
        let typeSelectId = path.length === 0 ? 'blockTypeSelector' : 'blockTypeSelector-' + path.join('-');
        let type = document.getElementById(typeSelectId).value;
        
        let newBlock = { type: type, content: '' };
        if (type === 'columns') {
            newBlock.columns = [[], []]; // 2 columns by default
        }
        
        let targetArr = path.length === 0 ? blocks : getBlockRef(path);
        targetArr.push(newBlock);
        renderBlocks();
    }

    function addColumn(pathStr) {
        let path = JSON.parse(pathStr);
        let block = getBlockRef(path);
        block.columns.push([]);
        renderBlocks();
    }
    
    function removeColumn(pathStr, colIndex) {
        let path = JSON.parse(pathStr);
        let block = getBlockRef(path);
        block.columns.splice(colIndex, 1);
        renderBlocks();
    }

    function uploadImageBlock(pathStr, input) {
        if (!input.files || input.files.length === 0) return;
        
        const file = input.files[0];
        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        let pathId = JSON.parse(pathStr).join('-');
        document.getElementById('uploading-' + pathId).classList.remove('hidden');
        
        fetch('{{ route("website-settings.pages.upload-image") }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('uploading-' + pathId).classList.add('hidden');
            if (data.url) {
                updateBlock(pathStr, 'content', data.url);
                renderBlocks();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(err => {
            document.getElementById('uploading-' + pathId).classList.add('hidden');
            alert('Upload failed.');
        });
    }

    function buildBlockHtml(block, path) {
        const pathStr = JSON.stringify(path);
        const escapedPathStr = pathStr.replace(/"/g, '&quot;');
        const pathId = path.join('-');
        
        let contentHtml = '';
        const val = block.content ? (typeof block.content === 'string' ? block.content.replace(/"/g, '&quot;') : block.content) : '';
        
        if (block.type === 'paragraph') {
            contentHtml = `<textarea oninput="updateBlock('${escapedPathStr}', 'content', this.value)" rows="3" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm mt-2" placeholder="Enter paragraph text...">${block.content || ''}</textarea>`;
        } else if (block.type.startsWith('heading')) {
            contentHtml = `<input type="text" oninput="updateBlock('${escapedPathStr}', 'content', this.value)" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm mt-2 font-bold" placeholder="Enter heading text..." value="${val}">`;
        } else if (block.type === 'image') {
            const imgPreview = val ? (val.startsWith('http') ? val : '/' + val) : '';
            const previewHtml = val ? `<img src="${imgPreview}" class="w-auto h-32 object-contain rounded mt-2 border border-gray-200">` : '';
            contentHtml = `
                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <input type="file" accept="image/*" onchange="uploadImageBlock('${escapedPathStr}', this)" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-[#DED5CB] rounded-md">
                        <span id="uploading-${pathId}" class="text-xs text-[#B47A42] font-bold hidden shrink-0">Uploading...</span>
                    </div>
                    <div class="mt-3 flex gap-4 items-start">
                        <div id="preview-${pathId}">${previewHtml}</div>
                        <div class="${val ? '' : 'hidden'}">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Image Size</label>
                            <select onchange="updateBlock('${escapedPathStr}', 'size', this.value)" class="text-sm border border-[#DED5CB] rounded-md px-2 py-1.5 outline-none focus:border-[#B47A42]">
                                <option value="full" ${block.size === 'full' || !block.size ? 'selected' : ''}>Full Width</option>
                                <option value="large" ${block.size === 'large' ? 'selected' : ''}>Large</option>
                                <option value="medium" ${block.size === 'medium' ? 'selected' : ''}>Medium</option>
                                <option value="small" ${block.size === 'small' ? 'selected' : ''}>Small</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        } else if (block.type === 'quote') {
            contentHtml = `<textarea oninput="updateBlock('${escapedPathStr}', 'content', this.value)" rows="2" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm mt-2 italic" placeholder="Enter quote..." >${block.content || ''}</textarea>`;
        } else if (block.type === 'code') {
            contentHtml = `<textarea oninput="updateBlock('${escapedPathStr}', 'content', this.value)" rows="4" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm mt-2 font-mono bg-gray-50" placeholder="Enter code snippet..." >${block.content || ''}</textarea>`;
        } else if (block.type === 'list') {
            contentHtml = `<textarea oninput="updateBlock('${escapedPathStr}', 'content', this.value)" rows="4" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm mt-2" placeholder="Enter list items (one per line)..." >${block.content || ''}</textarea>`;
        } else if (block.type === 'columns') {
            let colsHtml = `
                <div class="mb-4 bg-gray-50 p-2 rounded border border-gray-200 inline-flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500">Column Layout:</label>
                    <select onchange="updateBlock('${escapedPathStr}', 'layout', this.value)" class="text-xs border border-[#DED5CB] rounded px-2 py-1 outline-none">
                        <option value="equal" ${block.layout === 'equal' || !block.layout ? 'selected' : ''}>Equal Width</option>
                        <option value="auto" ${block.layout === 'auto' ? 'selected' : ''}>Fit to Content</option>
                        <option value="left-heavy" ${block.layout === 'left-heavy' ? 'selected' : ''}>Left Heavy (66/33)</option>
                        <option value="right-heavy" ${block.layout === 'right-heavy' ? 'selected' : ''}>Right Heavy (33/66)</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-${block.columns ? block.columns.length : 2} gap-4 mt-2">
            `;
            if (!block.columns) block.columns = [[], []];
            block.columns.forEach((colBlocks, colIndex) => {
                let colPathStr = JSON.stringify([...path, 'columns', colIndex]);
                let escapedColPathStr = colPathStr.replace(/"/g, '&quot;');
                let innerBlocksHtml = '';
                colBlocks.forEach((childBlock, childIndex) => {
                    innerBlocksHtml += buildBlockHtml(childBlock, [...path, 'columns', colIndex, childIndex]);
                });
                colsHtml += `
                    <div class="border border-dashed border-gray-300 p-3 rounded-lg bg-gray-50 relative">
                        <div class="absolute top-2 right-2 text-xs">
                            <button type="button" onclick="removeColumn('${escapedPathStr}', ${colIndex})" class="text-red-400 hover:text-red-600">Del Col</button>
                        </div>
                        <h5 class="text-xs font-bold text-gray-400 mb-3 uppercase">Column ${colIndex + 1}</h5>
                        <div class="space-y-3 mb-3">
                            ${innerBlocksHtml}
                        </div>
                        <div class="flex items-center gap-2 mt-4 bg-white p-2 rounded border border-gray-200">
                            <select id="blockTypeSelector-${[...path, 'columns', colIndex].join('-')}" class="w-full border border-[#DED5CB] rounded-md px-2 py-1 text-xs outline-none">
                                <option value="paragraph">Paragraph</option>
                                <option value="heading1">Heading 1</option>
                                <option value="heading2">Heading 2</option>
                                <option value="heading3">Heading 3</option>
                                <option value="heading4">Heading 4</option>
                                <option value="heading5">Heading 5</option>
                                <option value="heading6">Heading 6</option>
                                <option value="image">Image Upload</option>
                                <option value="quote">Quote</option>
                                <option value="code">Code Block</option>
                                <option value="list">List (Bullets)</option>
                            </select>
                            <button type="button" onclick="addBlock('${escapedColPathStr}')" class="px-3 py-1 bg-gray-200 text-gray-800 rounded-md text-xs font-medium hover:bg-gray-300 whitespace-nowrap">
                                Add
                            </button>
                        </div>
                    </div>
                `;
            });
            colsHtml += '</div>';
            colsHtml += `<button type="button" onclick="addColumn('${escapedPathStr}')" class="mt-4 px-3 py-1 border border-dashed border-[#B47A42] text-[#B47A42] rounded text-xs font-medium hover:bg-[#F5F1EB]">+ Add Column</button>`;
            contentHtml = colsHtml;
        }

        return `
            <div class="border border-[#DED5CB] rounded p-4 bg-white relative mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase">${block.type}</span>
                    <div class="flex gap-2">
                        <button type="button" onclick="moveBlock('${escapedPathStr}', -1)" class="text-gray-400 hover:text-gray-700" title="Move Up">↑</button>
                        <button type="button" onclick="moveBlock('${escapedPathStr}', 1)" class="text-gray-400 hover:text-gray-700" title="Move Down">↓</button>
                        <button type="button" onclick="deleteBlock('${escapedPathStr}')" class="text-red-400 hover:text-red-600 text-xs ml-2">Remove</button>
                    </div>
                </div>
                ${contentHtml}
            </div>
        `;
    }

    function renderBlocks() {
        const container = document.getElementById('blocksContainer');
        if (blocks.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500 italic">No blocks added yet. Use the button below to add content blocks.</p>';
            return;
        }
        let html = '';
        blocks.forEach((block, index) => {
            html += buildBlockHtml(block, [index]);
        });
        container.innerHTML = html;
    }

    function prepareSubmit() {
        document.getElementById('blocksInput').value = JSON.stringify(blocks);
    }

    renderBlocks();
</script>
@endsection
