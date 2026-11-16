<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->title }} - EdOrbit School</title>
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
                footer_columns: {!! json_encode(\App\Http\Controllers\HomepageSettingController::getFooterColumns()) !!}
            };
            window.PAGE_DATA = {!! json_encode($page) !!};
        </script>
    </head>
    <body class="antialiased bg-cream">
        <div id="app"></div>
    </body>
</html>
