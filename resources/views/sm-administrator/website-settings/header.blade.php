@extends('layouts.app')

@section('header', 'Header Settings')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-sm border border-[#DED5CB] overflow-hidden mb-10">
    <div class="p-6 border-b border-[#DED5CB] bg-[#F5F1EB]">
        <h3 class="text-lg font-semibold text-[#211B17]">Edit Header Content</h3>
        <p class="text-sm text-[#766C63] mt-1">Manage the logo, menus, and action buttons in the top navigation bar.</p>
    </div>
    
    <form action="{{ route('website-settings.header.update') }}" method="POST" enctype="multipart/form-data" class="p-6" id="headerForm">
        @csrf
        
        <!-- Logo Section -->
        <h4 class="font-bold text-[#B47A42] text-md mb-4 border-b border-[#DED5CB] pb-2">Logo Settings</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-[#211B17] mb-2">Logo Type</label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="header_logo_type" value="text" {{ $settings['header_logo_type'] == 'text' ? 'checked' : '' }} class="accent-[#B47A42]" onchange="toggleLogoType()">
                        <span class="text-sm">Text Logo</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="header_logo_type" value="image" {{ $settings['header_logo_type'] == 'image' ? 'checked' : '' }} class="accent-[#B47A42]" onchange="toggleLogoType()">
                        <span class="text-sm">Image Logo</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="header_logo_type" value="both" {{ $settings['header_logo_type'] == 'both' ? 'checked' : '' }} class="accent-[#B47A42]" onchange="toggleLogoType()">
                        <span class="text-sm">Image + Text (Both)</span>
                    </label>
                </div>
            </div>
            
            <div id="textLogoDiv" class="col-span-2 md:col-span-1" style="{{ in_array($settings['header_logo_type'], ['text', 'both']) ? '' : 'display: none;' }}">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Logo Text</label>
                <input type="text" name="header_logo_text" value="{{ $settings['header_logo_text'] }}" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
            </div>
            
            <div id="imageLogoDiv" class="col-span-2" style="{{ in_array($settings['header_logo_type'], ['image', 'both']) ? '' : 'display: none;' }}">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Upload Logo Image</label>
                <div class="flex items-center gap-2">
                    <input type="file" id="headerLogoImageInput" name="header_logo_image" accept="image/*" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                    <button type="button" onclick="extractColors()" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition-colors whitespace-nowrap">
                        Extract Colors
                    </button>
                </div>
                <div id="colorExtractLoading" style="display: none;" class="text-xs text-blue-600 mt-1">Extracting colors...</div>
                @if($settings['header_logo_image'])
                    <div class="mt-3">
                        <p class="text-xs text-[#766C63] mb-1">Current Image:</p>
                        <img src="{{ asset($settings['header_logo_image']) }}" alt="Logo" class="h-12 border border-[#DED5CB] rounded bg-gray-50 p-1">
                    </div>
                @endif
            </div>
        </div>
        
        <div class="flex items-center justify-between border-b border-[#DED5CB] mb-4 pb-2 mt-8">
            <h4 class="font-bold text-[#B47A42] text-md">Theme Colors</h4>
            <button type="button" onclick="resetThemeColors()" class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors">
                Reset to Default
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 bg-[#F9F7F5] p-4 rounded border border-[#DED5CB]">
            <div>
                <label class="block text-sm font-medium text-[#211B17] mb-1">Primary Color (Buttons, Highlights)</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="theme_color_primary_picker" name="theme_color_primary" value="{{ $settings['theme_color_primary'] ?? '#B47A42' }}" class="h-9 w-12 border border-[#DED5CB] rounded cursor-pointer p-0.5 bg-white" oninput="this.nextElementSibling.value = this.value.toUpperCase()">
                    <input type="text" id="theme_color_primary_text" value="{{ $settings['theme_color_primary'] ?? '#B47A42' }}" class="flex-1 border border-[#DED5CB] rounded-md px-3 py-1.5 text-sm uppercase" oninput="this.previousElementSibling.value = this.value" onchange="this.previousElementSibling.value = this.value">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#211B17] mb-1">Secondary / Heading Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="theme_color_secondary_picker" name="theme_color_secondary" value="{{ $settings['theme_color_secondary'] ?? '#211B17' }}" class="h-9 w-12 border border-[#DED5CB] rounded cursor-pointer p-0.5 bg-white" oninput="this.nextElementSibling.value = this.value.toUpperCase()">
                    <input type="text" id="theme_color_secondary_text" value="{{ $settings['theme_color_secondary'] ?? '#211B17' }}" class="flex-1 border border-[#DED5CB] rounded-md px-3 py-1.5 text-sm uppercase" oninput="this.previousElementSibling.value = this.value" onchange="this.previousElementSibling.value = this.value">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#211B17] mb-1">Background Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="theme_color_background_picker" name="theme_color_background" value="{{ $settings['theme_color_background'] ?? '#F5F1EB' }}" class="h-9 w-12 border border-[#DED5CB] rounded cursor-pointer p-0.5 bg-white" oninput="this.nextElementSibling.value = this.value.toUpperCase()">
                    <input type="text" id="theme_color_background_text" value="{{ $settings['theme_color_background'] ?? '#F5F1EB' }}" class="flex-1 border border-[#DED5CB] rounded-md px-3 py-1.5 text-sm uppercase" oninput="this.previousElementSibling.value = this.value" onchange="this.previousElementSibling.value = this.value">
                </div>
            </div>
        </div>

        <!-- Menus Section -->
        <h4 class="font-bold text-[#B47A42] text-md mb-4 border-b border-[#DED5CB] pb-2">Navigation Menus</h4>
        <input type="hidden" name="header_menus" id="headerMenusInput">
        <div class="mb-8">
            <div id="menuBuilder" class="space-y-3 mb-4">
                <!-- JS will render menu items here -->
            </div>
            <button type="button" onclick="addMenu()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                + Add Parent Menu
            </button>
        </div>

        <!-- Buttons Section -->
        <h4 class="font-bold text-[#B47A42] text-md mb-4 border-b border-[#DED5CB] pb-2">Action Buttons</h4>
        <input type="hidden" name="header_buttons" id="headerButtonsInput">
        <div class="mb-8">
            <div id="buttonBuilder" class="space-y-3 mb-4">
                <!-- JS will render buttons here -->
            </div>
            <button type="button" onclick="addButton()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                + Add Button
            </button>
        </div>

        <div class="flex justify-end pt-4 border-t border-[#DED5CB]">
            <button type="submit" onclick="prepareSubmit()" class="px-6 py-2 bg-[#B47A42] text-white rounded-md text-sm font-medium hover:bg-[#9a6633] transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
    // Logo toggle
    function toggleLogoType() {
        const type = document.querySelector('input[name="header_logo_type"]:checked').value;
        document.getElementById('textLogoDiv').style.display = (type === 'text' || type === 'both') ? 'block' : 'none';
        document.getElementById('imageLogoDiv').style.display = (type === 'image' || type === 'both') ? 'block' : 'none';
    }

    // Dynamic Menu Builder
    let menus = {!! $settings['header_menus'] !!} || [];
    
    function renderMenus() {
        const container = document.getElementById('menuBuilder');
        container.innerHTML = '';
        
        menus.forEach((menu, index) => {
            const html = `
                <div class="border border-[#DED5CB] rounded-md p-4 bg-[#F9F7F5]">
                    <div class="flex gap-4 items-start">
                        <div class="flex-1 grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-[#766C63]">Label</label>
                                <input type="text" value="${menu.label}" onchange="updateMenu(${index}, 'label', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">URL / Link</label>
                                <input type="text" value="${menu.url}" onchange="updateMenu(${index}, 'url', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 pt-5">
                            <button type="button" onclick="moveMenu(${index}, -1)" class="text-gray-500 hover:text-black" ${index === 0 ? 'disabled opacity-30' : ''}>▲</button>
                            <button type="button" onclick="moveMenu(${index}, 1)" class="text-gray-500 hover:text-black" ${index === menus.length - 1 ? 'disabled opacity-30' : ''}>▼</button>
                        </div>
                        <button type="button" onclick="deleteMenu(${index})" class="text-red-500 hover:text-red-700 pt-5">✕</button>
                    </div>
                    
                    <!-- Child Menus -->
                    <div class="mt-4 pl-4 border-l-2 border-[#DED5CB]">
                        <p class="text-xs font-semibold text-[#766C63] mb-2">Child Submenus</p>
                        <div class="space-y-2 mb-2">
                            ${(menu.children || []).map((child, cIndex) => `
                                <div class="flex gap-2 items-center">
                                    <input type="text" placeholder="Label" value="${child.label}" onchange="updateChild(${index}, ${cIndex}, 'label', this.value)" class="w-1/3 border border-[#DED5CB] rounded px-2 py-1 text-xs">
                                    <input type="text" placeholder="URL" value="${child.url}" onchange="updateChild(${index}, ${cIndex}, 'url', this.value)" class="flex-1 border border-[#DED5CB] rounded px-2 py-1 text-xs">
                                    <button type="button" onclick="moveChild(${index}, ${cIndex}, -1)" class="text-gray-400 hover:text-black text-xs" ${cIndex === 0 ? 'disabled opacity-30' : ''}>▲</button>
                                    <button type="button" onclick="moveChild(${index}, ${cIndex}, 1)" class="text-gray-400 hover:text-black text-xs" ${cIndex === (menu.children || []).length - 1 ? 'disabled opacity-30' : ''}>▼</button>
                                    <button type="button" onclick="deleteChild(${index}, ${cIndex})" class="text-red-400 hover:text-red-600 text-xs ml-1">✕</button>
                                </div>
                            `).join('')}
                        </div>
                        <button type="button" onclick="addChild(${index})" class="text-xs text-[#B47A42] hover:underline">+ Add Child Menu</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    function addMenu() { menus.push({ label: 'New Menu', url: '#', children: [] }); renderMenus(); }
    function updateMenu(i, key, val) { menus[i][key] = val; }
    function deleteMenu(i) { menus.splice(i, 1); renderMenus(); }
    function moveMenu(i, dir) { 
        if (i + dir < 0 || i + dir >= menus.length) return;
        const temp = menus[i]; menus[i] = menus[i+dir]; menus[i+dir] = temp; renderMenus(); 
    }

    function addChild(i) { if (!menus[i].children) menus[i].children = []; menus[i].children.push({ label: 'Submenu', url: '#' }); renderMenus(); }
    function updateChild(i, c, key, val) { menus[i].children[c][key] = val; }
    function deleteChild(i, c) { menus[i].children.splice(c, 1); renderMenus(); }
    function moveChild(i, c, dir) { 
        if (c + dir < 0 || c + dir >= menus[i].children.length) return;
        const temp = menus[i].children[c]; menus[i].children[c] = menus[i].children[c+dir]; menus[i].children[c+dir] = temp; renderMenus(); 
    }

    // Dynamic Button Builder
    let buttons = {!! $settings['header_buttons'] !!} || [];

    function renderButtons() {
        const container = document.getElementById('buttonBuilder');
        container.innerHTML = '';
        
        buttons.forEach((btn, index) => {
            const html = `
                <div class="border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5]">
                    <div class="flex gap-4 items-center">
                        <div class="flex-1 grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-xs text-[#766C63]">Label</label>
                                <input type="text" value="${btn.label}" onchange="updateButton(${index}, 'label', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">URL / Link</label>
                                <input type="text" value="${btn.url}" onchange="updateButton(${index}, 'url', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">Style</label>
                                <select onchange="updateButton(${index}, 'style', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                                    <option value="primary" ${btn.style === 'primary' ? 'selected' : ''}>Primary (Solid)</option>
                                    <option value="outline" ${btn.style === 'outline' ? 'selected' : ''}>Outline</option>
                                    <option value="text" ${btn.style === 'text' ? 'selected' : ''}>Text Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 pt-4">
                            <button type="button" onclick="moveButton(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled opacity-30' : ''}>▲</button>
                            <button type="button" onclick="moveButton(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === buttons.length - 1 ? 'disabled opacity-30' : ''}>▼</button>
                        </div>
                        <button type="button" onclick="deleteButton(${index})" class="text-red-500 hover:text-red-700 pt-4">✕</button>
                    </div>

                    <!-- Button Child Menus -->
                    <div class="mt-3 pl-4 border-l-2 border-[#DED5CB]">
                        <p class="text-xs font-semibold text-[#766C63] mb-2">Child Submenus</p>
                        <div class="space-y-2 mb-2">
                            ${(btn.children || []).map((child, cIndex) => `
                                <div class="flex gap-2 items-center">
                                    <input type="text" placeholder="Label" value="${child.label}" onchange="updateButtonChild(${index}, ${cIndex}, 'label', this.value)" class="w-1/3 border border-[#DED5CB] rounded px-2 py-1 text-xs">
                                    <input type="text" placeholder="URL" value="${child.url}" onchange="updateButtonChild(${index}, ${cIndex}, 'url', this.value)" class="flex-1 border border-[#DED5CB] rounded px-2 py-1 text-xs">
                                    <button type="button" onclick="moveButtonChild(${index}, ${cIndex}, -1)" class="text-gray-400 hover:text-black text-xs" ${cIndex === 0 ? 'disabled opacity-30' : ''}>▲</button>
                                    <button type="button" onclick="moveButtonChild(${index}, ${cIndex}, 1)" class="text-gray-400 hover:text-black text-xs" ${cIndex === (btn.children || []).length - 1 ? 'disabled opacity-30' : ''}>▼</button>
                                    <button type="button" onclick="deleteButtonChild(${index}, ${cIndex})" class="text-red-400 hover:text-red-600 text-xs ml-1">✕</button>
                                </div>
                            `).join('')}
                        </div>
                        <button type="button" onclick="addButtonChild(${index})" class="text-xs text-[#B47A42] hover:underline">+ Add Child Menu</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    function addButton() { buttons.push({ label: 'Action', url: '#', style: 'primary', children: [] }); renderButtons(); }
    function updateButton(i, key, val) { buttons[i][key] = val; }
    function deleteButton(i) { buttons.splice(i, 1); renderButtons(); }
    function moveButton(i, dir) { 
        if (i + dir < 0 || i + dir >= buttons.length) return;
        const temp = buttons[i]; buttons[i] = buttons[i+dir]; buttons[i+dir] = temp; renderButtons(); 
    }

    function addButtonChild(i) { if (!buttons[i].children) buttons[i].children = []; buttons[i].children.push({ label: 'Submenu', url: '#' }); renderButtons(); }
    function updateButtonChild(i, c, key, val) { buttons[i].children[c][key] = val; }
    function deleteButtonChild(i, c) { buttons[i].children.splice(c, 1); renderButtons(); }
    function moveButtonChild(i, c, dir) { 
        if (c + dir < 0 || c + dir >= buttons[i].children.length) return;
        const temp = buttons[i].children[c]; buttons[i].children[c] = buttons[i].children[c+dir]; buttons[i].children[c+dir] = temp; renderButtons(); 
    }

    // Initialization & Submit
    function prepareSubmit() {
        document.getElementById('headerMenusInput').value = JSON.stringify(menus);
        document.getElementById('headerButtonsInput').value = JSON.stringify(buttons);
    }

    renderMenus();
    renderButtons();

    // Color Extraction Logic
    function rgbToHex(r, g, b) {
        return "#" + (1 << 24 | r << 16 | g << 8 | b).toString(16).slice(1).toUpperCase();
    }

    function extractColors() {
        const fileInput = document.getElementById('headerLogoImageInput');
        
        let imgSrc = null;
        if (fileInput.files && fileInput.files[0]) {
            // Wait for file reader
        } else {
            const existingImg = document.querySelector('#imageLogoDiv img');
            if (existingImg) {
                imgSrc = existingImg.src;
            }
        }

        if (!fileInput.files[0] && !imgSrc) {
            alert('Please select an image file first or ensure an image is already uploaded.');
            return;
        }

        document.getElementById('colorExtractLoading').style.display = 'block';

        const processImage = (src) => {
            const img = new Image();
            img.crossOrigin = "Anonymous"; // Important for canvas extraction from existing URLs
            img.src = src;
            img.onload = function() {
                const colorThief = new ColorThief();
                try {
                    const palette = colorThief.getPalette(img, 3);
                    if (palette && palette.length >= 2) {
                        const primaryHex = rgbToHex(palette[0][0], palette[0][1], palette[0][2]);
                        document.getElementById('theme_color_primary_picker').value = primaryHex;
                        document.getElementById('theme_color_primary_text').value = primaryHex;
                        
                        const secondaryHex = rgbToHex(palette[1][0], palette[1][1], palette[1][2]);
                        document.getElementById('theme_color_secondary_picker').value = secondaryHex;
                        document.getElementById('theme_color_secondary_text').value = secondaryHex;
                        
                        // if (palette.length >= 3) {
                        //     const bgHex = rgbToHex(palette[2][0], palette[2][1], palette[2][2]);
                        // }
                    } else {
                        const color = colorThief.getColor(img);
                        const hex = rgbToHex(color[0], color[1], color[2]);
                        document.getElementById('theme_color_primary_picker').value = hex;
                        document.getElementById('theme_color_primary_text').value = hex;
                    }
                } catch(err) {
                    console.error('Color extraction failed', err);
                    alert('Could not extract colors from this image. It might be due to cross-origin restrictions on the existing image.');
                }
                document.getElementById('colorExtractLoading').style.display = 'none';
            }
        };

        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                processImage(e.target.result);
            };
            reader.readAsDataURL(fileInput.files[0]);
        } else if (imgSrc) {
            processImage(imgSrc);
        }
    }

    function resetThemeColors() {
        const defaults = {
            primary: '#B47A42',
            secondary: '#211B17',
            background: '#F5F1EB'
        };

        document.getElementById('theme_color_primary_picker').value = defaults.primary;
        document.getElementById('theme_color_primary_text').value = defaults.primary;

        document.getElementById('theme_color_secondary_picker').value = defaults.secondary;
        document.getElementById('theme_color_secondary_text').value = defaults.secondary;

        document.getElementById('theme_color_background_picker').value = defaults.background;
        document.getElementById('theme_color_background_text').value = defaults.background;
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.umd.js"></script>
@endsection
