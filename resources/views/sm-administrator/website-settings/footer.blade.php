@extends('layouts.app')

@section('header', 'Footer Settings')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-[#DED5CB] overflow-hidden">
    <div class="p-6 border-b border-[#DED5CB] bg-[#F5F1EB]">
        <h3 class="text-lg font-semibold text-[#211B17]">Edit Footer Content</h3>
        <p class="text-sm text-[#766C63] mt-1">Manage the content that appears in the footer area. You can add up to 4 columns.</p>
    </div>
    
    <form action="{{ route('website-settings.footer.update') }}" method="POST" enctype="multipart/form-data" class="p-6" onsubmit="prepareFooter()">
        @csrf
        <input type="hidden" name="homepage_footer_columns" id="footerColumnsInput">
        
        <div id="footerColumnsBuilder" class="space-y-4 mb-6"></div>
        
        <button type="button" onclick="addColumn()" id="addColumnBtn" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
            + Add Column
        </button>

        <div class="flex justify-end pt-4 border-t border-[#DED5CB] mt-6">
            <button type="submit" class="px-6 py-2 bg-[#B47A42] text-white rounded-md text-sm font-medium hover:bg-[#9a6633] transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
    let columns = @json(json_decode($settings['homepage_footer_columns'] ?? '[]', true));
    if (!Array.isArray(columns) || columns.length === 0) {
        columns = [];
    }

    function esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderColumns() {
        const container = document.getElementById('footerColumnsBuilder');
        container.innerHTML = '';
        columns.forEach((c, index) => {
            const on = c.enabled !== false;
            
            let contentHtml = '';
            if (c.type === 'about') {
                contentHtml = `
                    <label class="text-xs text-[#766C63]">Logo / Image (Optional)</label>
                    <div class="flex items-center gap-2 mt-1 mb-2">
                        <input type="file" name="footer_col_image_${c.id}" accept="image/*" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm bg-white">
                        ${c.image ? `
                            <img src="/${c.image}" class="h-8 w-8 object-contain rounded border bg-white">
                            <label class="text-xs text-red-500 flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                <input type="checkbox" name="remove_footer_col_image_${c.id}" value="1"> Remove
                            </label>
                        ` : ''}
                    </div>
                    <label class="text-xs text-[#766C63]">About Text</label>
                    <textarea rows="3" oninput="updateCol(${index}, 'text', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">${esc(c.text)}</textarea>
                `;
            } else if (c.type === 'contact') {
                contentHtml = `
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-[#766C63]">Email</label>
                            <input type="text" value="${esc(c.email)}" oninput="updateCol(${index}, 'email', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                        </div>
                        <div>
                            <label class="text-xs text-[#766C63]">Phone</label>
                            <input type="text" value="${esc(c.phone)}" oninput="updateCol(${index}, 'phone', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-[#766C63]">Address</label>
                            <textarea rows="2" oninput="updateCol(${index}, 'address', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">${esc(c.address)}</textarea>
                        </div>
                    </div>
                `;
            } else if (c.type === 'links') {
                const links = c.links || [];
                contentHtml = `
                    <label class="text-xs text-[#766C63] mb-1 block">Links</label>
                    <div class="space-y-2 mb-2">
                        ${links.map((l, li) => `
                            <div class="flex gap-2 items-center bg-white p-2 rounded border border-[#DED5CB]">
                                <input type="checkbox" ${l.enabled !== false ? 'checked' : ''} onchange="updateLink(${index}, ${li}, 'enabled', this.checked)" class="accent-[#B47A42] w-4 h-4" title="Enable/Disable Link">
                                <input type="text" placeholder="Label" value="${esc(l.label)}" oninput="updateLink(${index}, ${li}, 'label', this.value)" class="flex-1 border border-[#DED5CB] rounded px-2 py-1 text-xs">
                                <input type="text" placeholder="URL" value="${esc(l.url)}" oninput="updateLink(${index}, ${li}, 'url', this.value)" class="flex-1 border border-[#DED5CB] rounded px-2 py-1 text-xs">
                                <button type="button" onclick="deleteLink(${index}, ${li})" class="text-red-500 hover:text-red-700">✕</button>
                            </div>
                        `).join('')}
                    </div>
                    <button type="button" onclick="addLink(${index})" class="text-xs px-2 py-1 bg-[#DED5CB] rounded hover:bg-gray-300">+ Add Link</button>
                `;
            }

            container.insertAdjacentHTML('beforeend', `
                <div class="flex gap-4 items-start border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5] ${on ? '' : 'opacity-60'}">
                    <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-5">
                        <input type="checkbox" ${on ? 'checked' : ''} onchange="updateCol(${index}, 'enabled', this.checked); renderColumns();" class="accent-[#B47A42] w-4 h-4">
                        ${on ? 'On' : 'Off'}
                    </label>
                    
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs text-[#766C63]">Column Type</label>
                            <select onchange="updateColType(${index}, this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1 mb-2 bg-white">
                                <option value="about" ${c.type === 'about' ? 'selected' : ''}>About (Logo & Text)</option>
                                <option value="links" ${c.type === 'links' ? 'selected' : ''}>Links List</option>
                                <option value="contact" ${c.type === 'contact' ? 'selected' : ''}>Contact Info</option>
                            </select>
                            
                            <label class="text-xs text-[#766C63]">Column Title</label>
                            <input type="text" value="${esc(c.title)}" oninput="updateCol(${index}, 'title', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1" placeholder="Leave empty for none">
                        </div>
                        <div class="md:col-span-2">
                            ${contentHtml}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 pt-5">
                        <button type="button" onclick="moveCol(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled' : ''}>▲</button>
                        <button type="button" onclick="moveCol(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === columns.length - 1 ? 'disabled' : ''}>▼</button>
                    </div>
                    <button type="button" onclick="deleteCol(${index})" class="text-red-500 hover:text-red-700 pt-5">✕</button>
                </div>
            `);
        });

        document.getElementById('addColumnBtn').style.display = columns.length >= 4 ? 'none' : 'inline-block';
    }

    function addColumn() {
        if (columns.length >= 4) return;
        columns.push({
            id: 'col_' + Date.now(),
            type: 'links',
            enabled: true,
            title: 'New Column',
            links: [{label: 'Link 1', url: '#', enabled: true}]
        });
        renderColumns();
    }

    function updateCol(i, key, val) { columns[i][key] = val; }
    
    function updateColType(i, type) {
        columns[i].type = type;
        if (type === 'links' && !columns[i].links) columns[i].links = [];
        renderColumns();
    }
    
    function deleteCol(i) { columns.splice(i, 1); renderColumns(); }
    
    function moveCol(i, dir) {
        if (i + dir < 0 || i + dir >= columns.length) return;
        const t = columns[i]; columns[i] = columns[i + dir]; columns[i + dir] = t; renderColumns();
    }

    function addLink(colIdx) {
        if (!columns[colIdx].links) columns[colIdx].links = [];
        columns[colIdx].links.push({label: 'New Link', url: '#', enabled: true});
        renderColumns();
    }
    
    function updateLink(colIdx, linkIdx, key, val) {
        columns[colIdx].links[linkIdx][key] = val;
    }
    
    function deleteLink(colIdx, linkIdx) {
        columns[colIdx].links.splice(linkIdx, 1);
        renderColumns();
    }

    function prepareFooter() {
        document.getElementById('footerColumnsInput').value = JSON.stringify(columns);
    }

    renderColumns();
</script>
@endsection
