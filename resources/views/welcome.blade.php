<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EdOrbit School</title>
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        <style>
            :root {
                --color-primary: {{ \App\Models\SystemSetting::getSetting('theme_color_primary', '#B47A42') }};
                --color-primary-dark: color-mix(in srgb, var(--color-primary) 85%, black);
                --color-primary-light: color-mix(in srgb, var(--color-primary) 15%, white);
                
                --color-heading: {{ \App\Models\SystemSetting::getSetting('theme_color_secondary', '#211B17') }};
                --color-cream: {{ \App\Models\SystemSetting::getSetting('theme_color_background', '#F5F1EB') }};
            }
        </style>
        <script>
            window.HOMEPAGE_SETTINGS = {
                header_logo_type: {!! json_encode(\App\Models\SystemSetting::getSetting('header_logo_type', 'text')) !!},
                header_logo_text: {!! json_encode(\App\Models\SystemSetting::getSetting('header_logo_text', 'EdOrbit')) !!},
                header_logo_image: {!! json_encode(\App\Models\SystemSetting::getSetting('header_logo_image', '')) !!},
                header_menus: {!! \App\Models\SystemSetting::getSetting('header_menus', '[]') !!},
                header_buttons: {!! \App\Models\SystemSetting::getSetting('header_buttons', '[]') !!},
                
                hero_title: {!! json_encode(\App\Models\SystemSetting::getSetting('homepage_hero_title', 'Ignite your potential today.')) !!},
                hero_title_highlight: {!! json_encode(\App\Models\SystemSetting::getSetting('homepage_hero_title_highlight', '')) !!},
                hero_image: {!! json_encode(\App\Models\SystemSetting::getSetting('homepage_hero_image', '')) !!},
                hero_buttons: {!! \App\Models\SystemSetting::getSetting('homepage_hero_buttons', json_encode([['label' => 'Discover More', 'url' => '#', 'style' => 'primary', 'enabled' => true, 'icon' => false], ['label' => 'Campus Tour', 'url' => '#', 'style' => 'soft', 'enabled' => true, 'icon' => true]])) !!},
                hero_subtitle: {!! json_encode(\App\Models\SystemSetting::getSetting('homepage_hero_subtitle', 'Admissions Open for 2026-27')) !!},
                hero_description: {!! json_encode(\App\Models\SystemSetting::getSetting('homepage_hero_description', 'Empowering the next generation of thinkers, creators, and leaders through innovative education and a vibrant campus community.')) !!},
                stats: {!! json_encode(\App\Http\Controllers\HomepageSettingController::getStats()) !!},
                features: {!! json_encode(\App\Http\Controllers\HomepageSettingController::getFeatures()) !!},
                testimonials: {!! json_encode(\App\Http\Controllers\HomepageSettingController::getTestimonials()) !!},
                cta: {!! json_encode(\App\Http\Controllers\HomepageSettingController::getCta()) !!},
                footer_columns: {!! json_encode(\App\Http\Controllers\HomepageSettingController::getFooterColumns()) !!},
                homepage_type: {!! json_encode(\App\Models\SystemSetting::getSetting('homepage_type', 'default')) !!}
            };
            @if(isset($page))
            window.PAGE_DATA = {!! json_encode($page) !!};
            @endif
        </script>
    </head>
    <body class="antialiased">
        <div id="app"></div>
    </body>
</html>
