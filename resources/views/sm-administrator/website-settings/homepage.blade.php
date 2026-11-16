@extends('layouts.app')

@section('header', 'Homepage Settings')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-[#DED5CB] overflow-hidden">
    <div class="p-6 border-b border-[#DED5CB] bg-[#F5F1EB]">
        <h3 class="text-lg font-semibold text-[#211B17]">Edit Homepage Content</h3>
        <p class="text-sm text-[#766C63] mt-1">Manage the content that appears on the school's React homepage.</p>
    </div>
    
    <form action="{{ route('website-settings.homepage.update') }}" method="POST" enctype="multipart/form-data" class="p-6" onsubmit="prepareHeroButtons()">
        @csrf
        
        <!-- General Settings -->
        <h4 class="font-bold text-[#B47A42] text-md mb-4 border-b border-[#DED5CB] pb-2">General Settings</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Active Home Page</label>
                <select name="homepage_type" id="homepageTypeSelect" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                    <option value="default" {{ $settings['homepage_type'] == 'default' ? 'selected' : '' }}>Default Home Page (Hardcoded UI)</option>
                    <option value="page" {{ $settings['homepage_type'] == 'page' ? 'selected' : '' }}>Dynamic Page Only</option>
                    <option value="both" {{ $settings['homepage_type'] == 'both' ? 'selected' : '' }}>Both (Hardcoded UI + Dynamic Page)</option>
                </select>
                <p class="text-xs text-[#766C63] mt-1">Choose whether to use the default homepage UI or a custom page you created.</p>
            </div>
            
            <div class="col-span-2 md:col-span-1" id="dynamicPageSelectContainer" style="display: {{ in_array($settings['homepage_type'], ['page', 'both']) ? 'block' : 'none' }};">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Select Custom Page</label>
                <select name="homepage_page_id" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                    <option value="">-- Select a Page --</option>
                    @foreach($pages as $p)
                        <option value="{{ $p->id }}" {{ $settings['homepage_page_id'] == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <script>
            document.getElementById('homepageTypeSelect').addEventListener('change', function() {
                document.getElementById('dynamicPageSelectContainer').style.display = (this.value === 'page' || this.value === 'both') ? 'block' : 'none';
            });
        </script>

        <!-- Hero Section -->
        <h4 class="font-bold text-[#B47A42] text-md mb-4 border-b border-[#DED5CB] pb-2">Hero Section</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Hero Title</label>
                <input type="text" name="homepage_hero_title" id="heroTitleInput" value="{{ $settings['homepage_hero_title'] }}" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
            </div>
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Highlighted Word (optional)</label>
                <input type="text" name="homepage_hero_title_highlight" id="heroHighlightInput" value="{{ $settings['homepage_hero_title_highlight'] }}" placeholder="e.g. potential" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                <p class="text-xs text-[#766C63] mt-1">Title ka yeh word colorful gradient me dikhega. Word title me likha hona chahiye.</p>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Hero Image</label>
                <input type="file" name="homepage_hero_image" accept="image/*" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                <p class="text-xs text-[#766C63] mt-1">Homepage ke hero section me dikhne wali photo (max 5 MB). Khali chhodne par purani/default image rahegi.</p>
                @if($settings['homepage_hero_image'])
                    <div class="mt-3">
                        <p class="text-xs text-[#766C63] mb-1">Current Image:</p>
                        <img src="{{ asset($settings['homepage_hero_image']) }}" alt="Hero image" class="h-32 border border-[#DED5CB] rounded bg-gray-50 p-1">
                        <label class="flex items-center gap-2 mt-2 text-xs text-[#766C63] cursor-pointer">
                            <input type="checkbox" name="remove_hero_image" value="1" class="accent-[#B47A42]"> Default image wapas lagao
                        </label>
                    </div>
                @endif
                @error('homepage_hero_image')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-[#211B17] mb-1">Hero Subtitle / Badge</label>
                <input type="text" name="homepage_hero_subtitle" value="{{ $settings['homepage_hero_subtitle'] }}" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Hero Description</label>
                <textarea name="homepage_hero_description" rows="3" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">{{ $settings['homepage_hero_description'] }}</textarea>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-[#211B17] mb-1">Hero Buttons</label>
                <p class="text-xs text-[#766C63] mb-3">Buttons ko on/off kar sakte hain, text aur link badal sakte hain, style chun sakte hain aur naye buttons add kar sakte hain.</p>
                <input type="hidden" name="homepage_hero_buttons" id="heroButtonsInput">
                <div id="heroButtonBuilder" class="space-y-3 mb-3"></div>
                <button type="button" onclick="addHeroButton()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                    + Add Button
                </button>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="flex items-center justify-between mb-4 border-b border-[#DED5CB] pb-2 mt-8">
            <h4 class="font-bold text-[#B47A42] text-md">Statistics Section</h4>
            <label class="flex items-center gap-2 text-sm text-[#766C63] cursor-pointer">
                <input type="checkbox" id="statsEnabledToggle" onchange="statsConfig.enabled = this.checked; renderStats();" class="accent-[#B47A42] w-4 h-4">
                <span id="statsEnabledLabel">Show on homepage</span>
            </label>
        </div>
        <input type="hidden" name="homepage_stats" id="statsInput">
        <div id="statsWrapper" class="mb-6">
            <div id="statsBuilder" class="space-y-3 mb-3"></div>
            <button type="button" onclick="addStat()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                + Add Statistic
            </button>
        </div>

        <!-- Features Section -->
        <div class="flex items-center justify-between mb-4 border-b border-[#DED5CB] pb-2 mt-8">
            <h4 class="font-bold text-[#B47A42] text-md">Features Section</h4>
            <label class="flex items-center gap-2 text-sm text-[#766C63] cursor-pointer">
                <input type="checkbox" id="featuresEnabledToggle" onchange="featuresConfig.enabled = this.checked; renderFeatures();" class="accent-[#B47A42] w-4 h-4">
                <span id="featuresEnabledLabel">Show on homepage</span>
            </label>
        </div>
        <input type="hidden" name="homepage_features" id="featuresInput">
        <div id="featuresWrapper" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-[#766C63] mb-1">Small Heading (e.g. Why Choose Us)</label>
                    <input type="text" id="featuresTag" oninput="featuresConfig.tag = this.value" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                </div>
                <div>
                    <label class="block text-xs text-[#766C63] mb-1">Main Heading</label>
                    <input type="text" id="featuresTitle" oninput="featuresConfig.title = this.value" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                </div>
            </div>
            <div id="featuresBuilder" class="space-y-3 mb-3"></div>
            <button type="button" onclick="addFeature()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                + Add Feature
            </button>
        </div>

        <!-- Testimonials Section -->
        <div class="flex items-center justify-between mb-4 border-b border-[#DED5CB] pb-2 mt-8">
            <h4 class="font-bold text-[#B47A42] text-md">Testimonials Section</h4>
            <label class="flex items-center gap-2 text-sm text-[#766C63] cursor-pointer">
                <input type="checkbox" id="testimonialsEnabledToggle" onchange="testimonialsConfig.enabled = this.checked; renderTestimonials();" class="accent-[#B47A42] w-4 h-4">
                <span id="testimonialsEnabledLabel">Show on homepage</span>
            </label>
        </div>
        <input type="hidden" name="homepage_testimonials" id="testimonialsInput">
        <div id="testimonialsWrapper" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-[#766C63] mb-1">Small Heading (e.g. Testimonials)</label>
                    <input type="text" id="testimonialsTag" oninput="testimonialsConfig.tag = this.value" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                </div>
                <div>
                    <label class="block text-xs text-[#766C63] mb-1">Main Heading</label>
                    <input type="text" id="testimonialsTitle" oninput="testimonialsConfig.title = this.value" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                </div>
            </div>
            <div id="testimonialsBuilder" class="space-y-3 mb-3"></div>
            <button type="button" onclick="addTestimonial()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                + Add Testimonial
            </button>
        </div>

        <!-- CTA Section -->
        <div class="flex items-center justify-between mb-4 border-b border-[#DED5CB] pb-2 mt-8">
            <h4 class="font-bold text-[#B47A42] text-md">Call to Action Section</h4>
            <label class="flex items-center gap-2 text-sm text-[#766C63] cursor-pointer">
                <input type="checkbox" id="ctaEnabledToggle" onchange="ctaConfig.enabled = this.checked; renderCta();" class="accent-[#B47A42] w-4 h-4">
                <span id="ctaEnabledLabel">Show on homepage</span>
            </label>
        </div>
        <input type="hidden" name="homepage_cta" id="ctaInput">
        <div id="ctaWrapper" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block text-sm font-medium text-[#211B17] mb-1">CTA Title</label>
                    <input type="text" id="ctaTitle" oninput="ctaConfig.title = this.value" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#211B17] mb-1">CTA Subtitle</label>
                    <input type="text" id="ctaSubtitle" oninput="ctaConfig.subtitle = this.value" class="w-full border border-[#DED5CB] rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#B47A42]">
                </div>
            </div>
            <div class="mb-2">
                <label class="block text-sm font-medium text-[#211B17] mb-1">CTA Buttons</label>
            </div>
            <div id="ctaButtonBuilder" class="space-y-3 mb-3"></div>
            <button type="button" onclick="addCtaButton()" class="text-sm px-4 py-2 border border-[#B47A42] text-[#B47A42] rounded-md hover:bg-[#B47A42] hover:text-white transition-colors">
                + Add Button
            </button>
        </div>



        <div class="flex justify-end pt-4 border-t border-[#DED5CB]">
            <button type="submit" class="px-6 py-2 bg-[#B47A42] text-white rounded-md text-sm font-medium hover:bg-[#9a6633] transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>
<script>
    let heroButtons = @json(json_decode($settings['homepage_hero_buttons'], true) ?: []);

    function esc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function renderHeroButtons() {
        const container = document.getElementById('heroButtonBuilder');
        container.innerHTML = '';
        heroButtons.forEach((btn, index) => {
            const enabled = btn.enabled !== false;
            const styles = [['primary', 'Solid (Primary)'], ['dark', 'Solid (Dark)'], ['soft', 'Soft (White)'], ['outline', 'Outlined'], ['text', 'Text Only']];
            container.insertAdjacentHTML('beforeend', `
                <div class="border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5] ${enabled ? '' : 'opacity-60'}">
                    <div class="flex gap-4 items-center">
                        <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-4">
                            <input type="checkbox" ${enabled ? 'checked' : ''} onchange="updateHeroButton(${index}, 'enabled', this.checked); renderHeroButtons();" class="accent-[#B47A42] w-4 h-4">
                            ${enabled ? 'On' : 'Off'}
                        </label>
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div>
                                <label class="text-xs text-[#766C63]">Button Text</label>
                                <input type="text" value="${esc(btn.label)}" oninput="updateHeroButton(${index}, 'label', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">URL / Link</label>
                                <input type="text" value="${esc(btn.url)}" oninput="updateHeroButton(${index}, 'url', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">Shape / Style</label>
                                <select onchange="updateHeroButton(${index}, 'style', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                                    ${styles.map(s => `<option value="${s[0]}" ${btn.style === s[0] ? 'selected' : ''}>${s[1]}</option>`).join('')}
                                </select>
                            </div>
                        </div>
                        <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-4">
                            <input type="checkbox" ${btn.icon ? 'checked' : ''} onchange="updateHeroButton(${index}, 'icon', this.checked)" class="accent-[#B47A42] w-4 h-4">
                            Play icon
                        </label>
                        <div class="flex flex-col gap-1 pt-4">
                            <button type="button" onclick="moveHeroButton(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled' : ''}>▲</button>
                            <button type="button" onclick="moveHeroButton(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === heroButtons.length - 1 ? 'disabled' : ''}>▼</button>
                        </div>
                        <button type="button" onclick="deleteHeroButton(${index})" class="text-red-500 hover:text-red-700 pt-4">✕</button>
                    </div>
                </div>
            `);
        });
    }

    function addHeroButton() { heroButtons.push({ label: 'New Button', url: '#', style: 'primary', enabled: true, icon: false }); renderHeroButtons(); }
    function updateHeroButton(i, key, val) { heroButtons[i][key] = val; }
    function deleteHeroButton(i) { heroButtons.splice(i, 1); renderHeroButtons(); }
    function moveHeroButton(i, dir) {
        if (i + dir < 0 || i + dir >= heroButtons.length) return;
        const t = heroButtons[i]; heroButtons[i] = heroButtons[i + dir]; heroButtons[i + dir] = t; renderHeroButtons();
    }
    function prepareHeroButtons() {
        document.getElementById('heroButtonsInput').value = JSON.stringify(heroButtons);
        document.getElementById('statsInput').value = JSON.stringify(statsConfig);
        document.getElementById('featuresInput').value = JSON.stringify(featuresConfig);
        document.getElementById('testimonialsInput').value = JSON.stringify(testimonialsConfig);
        document.getElementById('ctaInput').value = JSON.stringify(ctaConfig);
    }

    renderHeroButtons();

    // Statistics builder
    let statsConfig = @json(json_decode($settings['homepage_stats'], true));
    if (!statsConfig || !Array.isArray(statsConfig.items)) statsConfig = { enabled: true, items: [] };

    function renderStats() {
        document.getElementById('statsEnabledToggle').checked = !!statsConfig.enabled;
        document.getElementById('statsEnabledLabel').textContent = statsConfig.enabled ? 'Showing on homepage' : 'Hidden on homepage';
        document.getElementById('statsWrapper').classList.toggle('opacity-50', !statsConfig.enabled);

        const container = document.getElementById('statsBuilder');
        container.innerHTML = '';
        statsConfig.items.forEach((s, index) => {
            const on = s.enabled !== false;
            container.insertAdjacentHTML('beforeend', `
                <div class="flex gap-4 items-center border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5] ${on ? '' : 'opacity-60'}">
                    <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-4">
                        <input type="checkbox" ${on ? 'checked' : ''} onchange="updateStat(${index}, 'enabled', this.checked); renderStats();" class="accent-[#B47A42] w-4 h-4">
                        ${on ? 'On' : 'Off'}
                    </label>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-[#766C63]">Value (e.g. 98%)</label>
                            <input type="text" value="${esc(s.value)}" oninput="updateStat(${index}, 'value', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                        </div>
                        <div>
                            <label class="text-xs text-[#766C63]">Label</label>
                            <input type="text" value="${esc(s.label)}" oninput="updateStat(${index}, 'label', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 pt-4">
                        <button type="button" onclick="moveStat(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled' : ''}>▲</button>
                        <button type="button" onclick="moveStat(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === statsConfig.items.length - 1 ? 'disabled' : ''}>▼</button>
                    </div>
                    <button type="button" onclick="deleteStat(${index})" class="text-red-500 hover:text-red-700 pt-4">✕</button>
                </div>
            `);
        });
    }

    function addStat() { statsConfig.items.push({ value: '100+', label: 'New Statistic', enabled: true }); renderStats(); }
    function updateStat(i, key, val) { statsConfig.items[i][key] = val; }
    function deleteStat(i) { statsConfig.items.splice(i, 1); renderStats(); }
    function moveStat(i, dir) {
        const a = statsConfig.items;
        if (i + dir < 0 || i + dir >= a.length) return;
        const t = a[i]; a[i] = a[i + dir]; a[i + dir] = t; renderStats();
    }

    renderStats();

    // Features builder
    let featuresConfig = @json(json_decode($settings['homepage_features'], true));
    if (!featuresConfig || !Array.isArray(featuresConfig.items)) featuresConfig = { enabled: true, tag: '', title: '', items: [] };
    const featureIcons = [['book', 'Book'], ['flask', 'Science / Lab'], ['smile', 'Smile'], ['cap', 'Graduation Cap'], ['users', 'People'], ['star', 'Star'], ['globe', 'Globe'], ['heart', 'Heart'], ['bulb', 'Idea / Bulb']];

    function renderFeatures() {
        document.getElementById('featuresEnabledToggle').checked = !!featuresConfig.enabled;
        document.getElementById('featuresEnabledLabel').textContent = featuresConfig.enabled ? 'Showing on homepage' : 'Hidden on homepage';
        document.getElementById('featuresWrapper').classList.toggle('opacity-50', !featuresConfig.enabled);

        const container = document.getElementById('featuresBuilder');
        container.innerHTML = '';
        featuresConfig.items.forEach((f, index) => {
            const on = f.enabled !== false;
            container.insertAdjacentHTML('beforeend', `
                <div class="flex gap-4 items-start border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5] ${on ? '' : 'opacity-60'}">
                    <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-5">
                        <input type="checkbox" ${on ? 'checked' : ''} onchange="updateFeature(${index}, 'enabled', this.checked); renderFeatures();" class="accent-[#B47A42] w-4 h-4">
                        ${on ? 'On' : 'Off'}
                    </label>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs text-[#766C63]">Icon</label>
                            <select onchange="updateFeature(${index}, 'icon', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                                ${featureIcons.map(i => `<option value="${i[0]}" ${f.icon === i[0] ? 'selected' : ''}>${i[1]}</option>`).join('')}
                            </select>
                            <label class="text-xs text-[#766C63] block mt-2">Title</label>
                            <input type="text" value="${esc(f.title)}" oninput="updateFeature(${index}, 'title', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-[#766C63]">Image (Optional)</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="file" name="feature_image_${index}" accept="image/*" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm bg-white">
                                ${f.image ? `
                                    <img src="/${f.image}" class="h-8 w-8 object-cover rounded border">
                                    <label class="text-xs text-red-500 flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                        <input type="checkbox" name="remove_feature_image_${index}" value="1"> Remove
                                    </label>
                                ` : ''}
                            </div>
                            <p class="text-[10px] text-[#766C63] mt-1 mb-2">Agar image upload karenge to icon ki jagah image dikhegi card ke top par.</p>

                            <label class="text-xs text-[#766C63]">Description</label>
                            <textarea rows="3" oninput="updateFeature(${index}, 'desc', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">${esc(f.desc)}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 pt-5">
                        <button type="button" onclick="moveFeature(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled' : ''}>▲</button>
                        <button type="button" onclick="moveFeature(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === featuresConfig.items.length - 1 ? 'disabled' : ''}>▼</button>
                    </div>
                    <button type="button" onclick="deleteFeature(${index})" class="text-red-500 hover:text-red-700 pt-5">✕</button>
                </div>
            `);
        });
    }

    function addFeature() { featuresConfig.items.push({ icon: 'star', title: 'New Feature', desc: 'Describe this feature here.', enabled: true }); renderFeatures(); }
    function updateFeature(i, key, val) { featuresConfig.items[i][key] = val; }
    function deleteFeature(i) { featuresConfig.items.splice(i, 1); renderFeatures(); }
    function moveFeature(i, dir) {
        const a = featuresConfig.items;
        if (i + dir < 0 || i + dir >= a.length) return;
        const t = a[i]; a[i] = a[i + dir]; a[i + dir] = t; renderFeatures();
    }

    document.getElementById('featuresTag').value = featuresConfig.tag || '';
    document.getElementById('featuresTitle').value = featuresConfig.title || '';
    renderFeatures();

    // CTA builder
    let ctaConfig = @json(json_decode($settings['homepage_cta'] ?? '{}', true));
    if (!ctaConfig || !Array.isArray(ctaConfig.buttons)) ctaConfig = { enabled: true, title: '', subtitle: '', buttons: [] };

    function renderCta() {
        document.getElementById('ctaEnabledToggle').checked = !!ctaConfig.enabled;
        document.getElementById('ctaEnabledLabel').textContent = ctaConfig.enabled ? 'Showing on homepage' : 'Hidden on homepage';
        document.getElementById('ctaWrapper').classList.toggle('opacity-50', !ctaConfig.enabled);

        const container = document.getElementById('ctaButtonBuilder');
        container.innerHTML = '';
        ctaConfig.buttons.forEach((btn, index) => {
            const enabled = btn.enabled !== false;
            const styles = [['primary', 'Solid (Primary)'], ['dark', 'Solid (Dark)'], ['soft', 'Soft (White)'], ['outline', 'Outlined'], ['text', 'Text Only']];
            container.insertAdjacentHTML('beforeend', `
                <div class="border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5] ${enabled ? '' : 'opacity-60'}">
                    <div class="flex gap-4 items-center">
                        <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-4">
                            <input type="checkbox" ${enabled ? 'checked' : ''} onchange="updateCtaButton(${index}, 'enabled', this.checked); renderCta();" class="accent-[#B47A42] w-4 h-4">
                            ${enabled ? 'On' : 'Off'}
                        </label>
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div>
                                <label class="text-xs text-[#766C63]">Button Text</label>
                                <input type="text" value="${esc(btn.label)}" oninput="updateCtaButton(${index}, 'label', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">URL / Link</label>
                                <input type="text" value="${esc(btn.url)}" oninput="updateCtaButton(${index}, 'url', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-xs text-[#766C63]">Shape / Style</label>
                                <select onchange="updateCtaButton(${index}, 'style', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">
                                    ${styles.map(s => `<option value="${s[0]}" ${btn.style === s[0] ? 'selected' : ''}>${s[1]}</option>`).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 pt-4">
                            <button type="button" onclick="moveCtaButton(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled' : ''}>▲</button>
                            <button type="button" onclick="moveCtaButton(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === ctaConfig.buttons.length - 1 ? 'disabled' : ''}>▼</button>
                        </div>
                        <button type="button" onclick="deleteCtaButton(${index})" class="text-red-500 hover:text-red-700 pt-4">✕</button>
                    </div>
                </div>
            `);
        });
    }

    function addCtaButton() { ctaConfig.buttons.push({ label: 'New Button', url: '#', style: 'primary', enabled: true }); renderCta(); }
    function updateCtaButton(i, key, val) { ctaConfig.buttons[i][key] = val; }
    function deleteCtaButton(i) { ctaConfig.buttons.splice(i, 1); renderCta(); }
    function moveCtaButton(i, dir) {
        if (i + dir < 0 || i + dir >= ctaConfig.buttons.length) return;
        const t = ctaConfig.buttons[i]; ctaConfig.buttons[i] = ctaConfig.buttons[i + dir]; ctaConfig.buttons[i + dir] = t; renderCta();
    }

    document.getElementById('ctaTitle').value = ctaConfig.title || '';
    document.getElementById('ctaSubtitle').value = ctaConfig.subtitle || '';
    renderCta();

    // Testimonials builder
    let testimonialsConfig = @json(json_decode($settings['homepage_testimonials'] ?? '{}', true));
    if (!testimonialsConfig || !Array.isArray(testimonialsConfig.items)) testimonialsConfig = { enabled: true, tag: '', title: '', items: [] };

    function renderTestimonials() {
        document.getElementById('testimonialsEnabledToggle').checked = !!testimonialsConfig.enabled;
        document.getElementById('testimonialsEnabledLabel').textContent = testimonialsConfig.enabled ? 'Showing on homepage' : 'Hidden on homepage';
        document.getElementById('testimonialsWrapper').classList.toggle('opacity-50', !testimonialsConfig.enabled);

        const container = document.getElementById('testimonialsBuilder');
        container.innerHTML = '';
        testimonialsConfig.items.forEach((t, index) => {
            const on = t.enabled !== false;
            container.insertAdjacentHTML('beforeend', `
                <div class="flex gap-4 items-start border border-[#DED5CB] rounded-md p-3 bg-[#F9F7F5] ${on ? '' : 'opacity-60'}">
                    <label class="flex flex-col items-center gap-1 text-xs text-[#766C63] cursor-pointer pt-5">
                        <input type="checkbox" ${on ? 'checked' : ''} onchange="updateTestimonial(${index}, 'enabled', this.checked); renderTestimonials();" class="accent-[#B47A42] w-4 h-4">
                        ${on ? 'On' : 'Off'}
                    </label>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-[#766C63]">Author Name</label>
                            <input type="text" value="${esc(t.author)}" oninput="updateTestimonial(${index}, 'author', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1 mb-2">
                            
                            <label class="text-xs text-[#766C63]">Role / Relation (e.g. Student)</label>
                            <input type="text" value="${esc(t.role)}" oninput="updateTestimonial(${index}, 'role', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1 mb-2">

                            <label class="text-xs text-[#766C63]">Author Image (Optional)</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="file" name="testimonial_image_${index}" accept="image/*" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm bg-white">
                                ${t.image ? `
                                    <img src="/${t.image}" class="h-8 w-8 object-cover rounded-full border">
                                    <label class="text-xs text-red-500 flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                        <input type="checkbox" name="remove_testimonial_image_${index}" value="1"> Remove
                                    </label>
                                ` : ''}
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-[#766C63]">Testimonial Text / Quote</label>
                            <textarea rows="5" oninput="updateTestimonial(${index}, 'text', this.value)" class="w-full border border-[#DED5CB] rounded px-2 py-1 text-sm mt-1">${esc(t.text)}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 pt-5">
                        <button type="button" onclick="moveTestimonial(${index}, -1)" class="text-gray-500 hover:text-black text-xs" ${index === 0 ? 'disabled' : ''}>▲</button>
                        <button type="button" onclick="moveTestimonial(${index}, 1)" class="text-gray-500 hover:text-black text-xs" ${index === testimonialsConfig.items.length - 1 ? 'disabled' : ''}>▼</button>
                    </div>
                    <button type="button" onclick="deleteTestimonial(${index})" class="text-red-500 hover:text-red-700 pt-5">✕</button>
                </div>
            `);
        });
    }

    function addTestimonial() { testimonialsConfig.items.push({ text: 'This is a great school.', author: 'John Doe', role: 'Parent', enabled: true }); renderTestimonials(); }
    function updateTestimonial(i, key, val) { testimonialsConfig.items[i][key] = val; }
    function deleteTestimonial(i) { testimonialsConfig.items.splice(i, 1); renderTestimonials(); }
    function moveTestimonial(i, dir) {
        const a = testimonialsConfig.items;
        if (i + dir < 0 || i + dir >= a.length) return;
        const t = a[i]; a[i] = a[i + dir]; a[i + dir] = t; renderTestimonials();
    }

    document.getElementById('testimonialsTag').value = testimonialsConfig.tag || '';
    document.getElementById('testimonialsTitle').value = testimonialsConfig.title || '';
    renderTestimonials();
</script>
@endsection
