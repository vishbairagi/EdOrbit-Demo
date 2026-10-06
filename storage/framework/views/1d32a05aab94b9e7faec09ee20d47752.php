<?php $__env->startSection('header', 'Website Settings'); ?>

<?php $__env->startSection('content'); ?>
<style>
    :root {
        --ws-primary: #094cb2;
        --ws-primary-container: #d6e3ff;
        --ws-surface: #ffffff;
        --ws-surface-dim: #f4f3fa;
        --ws-ghost-border: rgba(26, 27, 31, 0.15);
        --ws-radius-md: 12px;
    }

    .ws-dashboard {
        display: flex;
        background: var(--ws-surface-dim);
        border-radius: var(--ws-radius-md);
        overflow: hidden;
        border: 1px solid var(--ws-ghost-border);
        min-height: calc(100vh - 120px);
    }

    .ws-sidebar {
        width: 250px;
        background-color: var(--ws-surface);
        border-right: 1px solid var(--ws-ghost-border);
        display: flex;
        flex-direction: column;
        padding: 24px 0;
    }

    .ws-nav-item {
        padding: 12px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #444;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        border-radius: 0 24px 24px 0;
        margin-right: 16px;
        margin-bottom: 4px;
    }

    .ws-nav-item:hover {
        background-color: var(--ws-surface-dim);
    }

    .ws-nav-item.active {
        background-color: var(--ws-primary-container);
        color: var(--ws-primary);
        font-weight: 600;
    }

    .ws-main {
        flex: 1;
        padding: 40px;
        overflow-y: auto;
    }

    .ws-panel {
        display: none;
        animation: fadeIn 0.4s ease;
        max-width: 900px;
    }

    .ws-panel.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .ws-card {
        background-color: var(--ws-surface);
        border-radius: var(--ws-radius-md);
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        border: 1px solid var(--ws-ghost-border);
    }

    .ws-card h3 {
        font-size: 1.2rem;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--ws-surface-dim);
        font-weight: 600;
        color: #1a1b1f;
    }

    .ws-form-group {
        margin-bottom: 20px;
    }

    .ws-form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .ws-form-row .ws-form-group {
        flex: 1;
        margin-bottom: 0;
    }

    .ws-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .ws-input {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.95rem;
        background-color: var(--ws-surface);
        border: 1px solid var(--ws-ghost-border);
        border-radius: 8px;
        transition: all 0.2s;
    }

    .ws-input:focus {
        outline: none;
        border-color: var(--ws-primary);
        box-shadow: 0 0 0 3px var(--ws-primary-container);
    }

    .ws-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background-color: var(--ws-surface-dim);
        border-radius: 8px;
        margin-bottom: 12px;
    }

    .ws-toggle span {
        font-weight: 500;
        font-size: 0.95rem;
    }

    /* Simple toggle switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc; border-radius: 24px; transition: .4s;
    }
    .slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px;
        background-color: white; border-radius: 50%; transition: .4s;
    }
    input:checked + .slider { background-color: var(--ws-primary); }
    input:checked + .slider:before { transform: translateX(20px); }

    .ws-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--ws-primary);
        color: white;
    }

    .ws-btn:hover { background: #073a8c; }

    .ws-dynamic-item {
        background: var(--ws-surface-dim);
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 12px;
        position: relative;
    }

    .ws-remove-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        color: #d32f2f;
        background: none;
        border: none;
        cursor: pointer;
    }
    
    .ws-save-bar {
        position: sticky;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 16px 24px;
        border-top: 1px solid var(--ws-ghost-border);
        display: flex;
        justify-content: flex-end;
        z-index: 50;
    }
</style>

<div class="ws-dashboard">
    <div class="ws-sidebar">
        <a href="#" class="ws-nav-item <?php echo e($tab == 'general' ? 'active' : ''); ?>" data-target="ws-general"><i data-lucide="sliders"></i> General Settings</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'home' ? 'active' : ''); ?>" data-target="ws-home"><i data-lucide="home"></i> Home Page Settings</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'header' ? 'active' : ''); ?>" data-target="ws-header"><i data-lucide="layout-template"></i> Header Settings</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'color' ? 'active' : ''); ?>" data-target="ws-color"><i data-lucide="palette"></i> Color Appearance</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'social' ? 'active' : ''); ?>" data-target="ws-social"><i data-lucide="share-2"></i> Social Media</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'footer' ? 'active' : ''); ?>" data-target="ws-footer"><i data-lucide="layout-panel-bottom"></i> Footer Settings</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'contact' ? 'active' : ''); ?>" data-target="ws-contact"><i data-lucide="contact"></i> Contact Settings</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'pages' ? 'active' : ''); ?>" data-target="ws-pages"><i data-lucide="file-text"></i> Pages Settings</a>
        <a href="#" class="ws-nav-item <?php echo e($tab == 'menu' ? 'active' : ''); ?>" data-target="ws-menu"><i data-lucide="menu"></i> Menus Settings</a>
    </div>
    
    <div class="ws-main">
        <form action="<?php echo e(route('website-settings.update')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="current_tab" id="current_tab" value="<?php echo e($tab); ?>">
            
            <?php if(session('success')): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-xl font-medium">
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>

            <!-- GENERAL SETTINGS -->
            <div id="ws-general" class="ws-panel <?php echo e($tab == 'general' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">General Settings</h2>
                
                <div class="ws-card">
                    <h3>Site Identity</h3>
                    <div class="ws-form-group">
                        <label class="ws-label">Site Title</label>
                        <input type="text" name="site_title" class="ws-input" value="<?php echo e($settings['site_title'] ?? 'Vidya Niketan Academy'); ?>">
                    </div>
                    <div class="ws-form-group">
                        <label class="ws-label">Site Meta Description</label>
                        <textarea name="site_description" class="ws-input" rows="3"><?php echo e($settings['site_description'] ?? 'A progressive pedagogical framework...'); ?></textarea>
                    </div>
                </div>

                <div class="ws-card">
                    <h3>Global Features</h3>
                    <div class="ws-toggle">
                        <span>Enable Sticky Header</span>
                        <label class="switch"><input type="checkbox" name="enable_sticky_header" value="1" <?php echo e(isset($settings['enable_sticky_header']) && $settings['enable_sticky_header'] == '1' ? 'checked' : ''); ?>><span class="slider"></span></label>
                    </div>
                </div>
            </div>

            <!-- HOME PAGE SETTINGS -->
            <div id="ws-home" class="ws-panel <?php echo e($tab == 'home' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Home Page Settings</h2>
                
                <div class="ws-card">
                    <h3>Hero Section</h3>
                    <div class="ws-form-row">
                        <div class="ws-form-group">
                            <label class="ws-label">Hero Title</label>
                            <input type="text" name="hero_title" class="ws-input" value="<?php echo e($settings['hero_title'] ?? 'Nurturing Curious Minds, Inspiring Character.'); ?>">
                        </div>
                    </div>
                    <div class="ws-form-group">
                        <label class="ws-label">Hero Subtitle</label>
                        <textarea name="hero_subtitle" class="ws-input" rows="3"><?php echo e($settings['hero_subtitle'] ?? 'Empowering children from kindergarten through grade twelve to lead with intellectual integrity and empathy.'); ?></textarea>
                    </div>
                    <div class="ws-form-row">
                        <div class="ws-form-group">
                            <label class="ws-label">Button 1 Text</label>
                            <input type="text" name="hero_btn1_text" class="ws-input" value="<?php echo e($settings['hero_btn1_text'] ?? 'Enroll Today'); ?>">
                        </div>
                        <div class="ws-form-group">
                            <label class="ws-label">Button 2 Text</label>
                            <input type="text" name="hero_btn2_text" class="ws-input" value="<?php echo e($settings['hero_btn2_text'] ?? 'Virtual Tour'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- HEADER SETTINGS -->
            <div id="ws-header" class="ws-panel <?php echo e($tab == 'header' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Header Settings</h2>
                <div class="ws-card">
                    <div class="ws-form-group">
                        <label class="ws-label">Top Bar Text</label>
                        <input type="text" name="header_top_text" class="ws-input" value="<?php echo e($settings['header_top_text'] ?? 'Affiliated to CBSE (Affiliation No: 123456)'); ?>">
                    </div>
                    <div class="ws-form-group">
                        <label class="ws-label">Header Phone Number</label>
                        <input type="text" name="header_phone" class="ws-input" value="<?php echo e($settings['header_phone'] ?? '+91 (0) 98765 43210'); ?>">
                    </div>
                </div>
            </div>

            <!-- COLOR APPEARANCE -->
            <div id="ws-color" class="ws-panel <?php echo e($tab == 'color' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Color Appearance</h2>
                <div class="ws-card">
                    <div class="ws-form-row">
                        <div class="ws-form-group">
                            <label class="ws-label">Primary Theme Color</label>
                            <input type="color" name="color_primary" class="ws-input" style="height:50px" value="<?php echo e($settings['color_primary'] ?? '#094cb2'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SOCIAL MEDIA -->
            <div id="ws-social" class="ws-panel <?php echo e($tab == 'social' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Social Media Links</h2>
                <div class="ws-card">
                    <div class="ws-form-row">
                        <div class="ws-form-group"><label class="ws-label">Facebook</label><input type="url" name="social_facebook" class="ws-input" value="<?php echo e($settings['social_facebook'] ?? ''); ?>"></div>
                        <div class="ws-form-group"><label class="ws-label">Instagram</label><input type="url" name="social_instagram" class="ws-input" value="<?php echo e($settings['social_instagram'] ?? ''); ?>"></div>
                    </div>
                </div>
            </div>

            <!-- FOOTER SETTINGS -->
            <div id="ws-footer" class="ws-panel <?php echo e($tab == 'footer' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Footer Settings</h2>
                <div class="ws-card">
                    <div class="ws-form-group">
                        <label class="ws-label">Footer Description</label>
                        <textarea name="footer_desc" class="ws-input"><?php echo e($settings['footer_desc'] ?? 'Nurturing intellectual curiosity, moral character, and social courage through comprehensive education and modern ethos since 1999.'); ?></textarea>
                    </div>
                    <div class="ws-form-row">
                        <div class="ws-form-group"><label class="ws-label">Copyright Year</label><input type="text" name="footer_year" class="ws-input" value="<?php echo e($settings['footer_year'] ?? '2025'); ?>"></div>
                    </div>
                </div>
            </div>

            <!-- CONTACT SETTINGS -->
            <div id="ws-contact" class="ws-panel <?php echo e($tab == 'contact' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Contact Settings</h2>
                <div class="ws-card">
                    <div class="ws-form-group">
                        <label class="ws-label">Address</label>
                        <textarea name="contact_address" class="ws-input"><?php echo e($settings['contact_address'] ?? 'Knowledge Enclave, Institutional Area, Sector 14, New Delhi - 110041, India'); ?></textarea>
                    </div>
                    <div class="ws-form-group">
                        <label class="ws-label">Email</label>
                        <input type="text" name="contact_email" class="ws-input" value="<?php echo e($settings['contact_email'] ?? 'admissions@vidyaniketan.edu.in'); ?>">
                    </div>
                </div>
            </div>

            <!-- PAGES SETTINGS -->
            <div id="ws-pages" class="ws-panel <?php echo e($tab == 'pages' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Pages Settings</h2>
                <div class="ws-card">
                    <p class="text-gray-500 mb-4">Manage static pages like Privacy Policy and Terms.</p>
                </div>
            </div>

            <!-- MENUS SETTINGS -->
            <div id="ws-menu" class="ws-panel <?php echo e($tab == 'menu' ? 'active' : ''); ?>">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Menus Settings</h2>
                <div class="ws-card">
                    <p class="text-gray-500 mb-4">Manage header and footer menus.</p>
                </div>
            </div>
            
            <div class="ws-save-bar">
                <button type="submit" class="ws-btn"><i data-lucide="save"></i> Save All Settings</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const navItems = document.querySelectorAll('.ws-nav-item');
    const panels = document.querySelectorAll('.ws-panel');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            navItems.forEach(nav => nav.classList.remove('active'));
            panels.forEach(panel => panel.classList.remove('active'));
            item.classList.add('active');
            const targetId = item.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
            
            const tabName = targetId.replace('ws-', '');
            document.getElementById('current_tab').value = tabName;
            
            // update URL optionally
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/website-settings/index.blade.php ENDPATH**/ ?>