<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class HomepageSettingController extends Controller
{
    /**
     * Statistics section config: ['enabled' => bool, 'items' => [['value','label','enabled'], ...]].
     * Falls back to the older fixed stat1-3 settings until the new list is saved.
     */
    public static function getStats(): array
    {
        $stored = json_decode((string) SystemSetting::getSetting('homepage_stats', ''), true);
        if (is_array($stored) && isset($stored['items'])) {
            return [
                'enabled' => !empty($stored['enabled']),
                'items' => array_values($stored['items']),
            ];
        }

        return [
            'enabled' => true,
            'items' => [
                ['value' => SystemSetting::getSetting('homepage_stat1_value', '98%'), 'label' => SystemSetting::getSetting('homepage_stat1_label', 'Graduation Rate'), 'enabled' => true],
                ['value' => SystemSetting::getSetting('homepage_stat2_value', '50+'), 'label' => SystemSetting::getSetting('homepage_stat2_label', 'Student Clubs'), 'enabled' => true],
                ['value' => SystemSetting::getSetting('homepage_stat3_value', '1:12'), 'label' => SystemSetting::getSetting('homepage_stat3_label', 'Student-Faculty'), 'enabled' => true],
            ],
        ];
    }

    public const FEATURE_ICONS = ['book', 'flask', 'smile', 'star', 'users', 'globe', 'heart', 'cap', 'bulb'];

    /**
     * Features section config: enabled flag, heading texts and items (icon, title, desc, enabled).
     * Falls back to the older fixed feature1-3 settings until the new list is saved.
     */
    public static function getFeatures(): array
    {
        $stored = json_decode((string) SystemSetting::getSetting('homepage_features', ''), true);
        if (is_array($stored) && isset($stored['items'])) {
            return [
                'enabled' => !empty($stored['enabled']),
                'tag' => $stored['tag'] ?? 'Why Choose Us',
                'title' => $stored['title'] ?? 'An education that goes beyond the classroom',
                'items' => array_values($stored['items']),
            ];
        }

        return [
            'enabled' => true,
            'tag' => 'Why Choose Us',
            'title' => 'An education that goes beyond the classroom',
            'items' => [
                ['icon' => 'book', 'enabled' => true, 'title' => SystemSetting::getSetting('homepage_feature1_title', 'Academic Excellence'), 'desc' => SystemSetting::getSetting('homepage_feature1_desc', 'Our rigorous curriculum is designed to challenge students and foster critical thinking, preparing them for success in college and beyond.')],
                ['icon' => 'flask', 'enabled' => true, 'title' => SystemSetting::getSetting('homepage_feature2_title', 'State-of-the-Art Labs'), 'desc' => SystemSetting::getSetting('homepage_feature2_desc', 'Experience hands-on learning in our modern laboratories equipped with the latest technology for science and engineering.')],
                ['icon' => 'smile', 'enabled' => true, 'title' => SystemSetting::getSetting('homepage_feature3_title', 'Holistic Development'), 'desc' => SystemSetting::getSetting('homepage_feature3_desc', 'We nurture not just academic intelligence, but emotional and social well-being through sports, arts, and community service.')],
            ],
        ];
    }

    public static function getCta(): array
    {
        $stored = json_decode((string) SystemSetting::getSetting('homepage_cta', ''), true);
        if (is_array($stored) && isset($stored['buttons'])) {
            return [
                'enabled' => !empty($stored['enabled']),
                'title' => $stored['title'] ?? '',
                'subtitle' => $stored['subtitle'] ?? '',
                'buttons' => array_values($stored['buttons']),
            ];
        }

        return [
            'enabled' => true,
            'title' => SystemSetting::getSetting('homepage_cta_title', 'Ready to shape your future?'),
            'subtitle' => SystemSetting::getSetting('homepage_cta_subtitle', 'Join a community of forward-thinkers. Applications for the upcoming academic year are now open.'),
            'buttons' => [
                ['label' => 'Start Application', 'url' => '#', 'style' => 'primary', 'enabled' => true],
                ['label' => 'Request Info', 'url' => '#', 'style' => 'soft', 'enabled' => true],
            ],
        ];
    }

    public static function getTestimonials(): array
    {
        $stored = json_decode((string) SystemSetting::getSetting('homepage_testimonials', ''), true);
        if (is_array($stored) && isset($stored['items'])) {
            return [
                'enabled' => !empty($stored['enabled']),
                'tag' => $stored['tag'] ?? '',
                'title' => $stored['title'] ?? '',
                'items' => array_values($stored['items']),
            ];
        }

        return [
            'enabled' => true,
            'tag' => 'Testimonials',
            'title' => 'What our students and parents say',
            'items' => [
                [
                    'text' => 'EdOrbit has completely transformed my learning experience. The professors are top-notch and the community is incredibly supportive.',
                    'author' => 'Sarah Jenkins',
                    'role' => 'Computer Science, Class of 2024',
                    'image' => '',
                    'enabled' => true,
                ],
                [
                    'text' => 'As a parent, I couldn\'t be happier with the growth I\'ve seen in my child. The holistic approach to education here is truly remarkable.',
                    'author' => 'Michael Chen',
                    'role' => 'Parent',
                    'image' => '',
                    'enabled' => true,
                ],
            ],
        ];
    }

    public function index()
    {
        // Older saved titles contain HTML. Convert them to plain text + highlighted word for the editor.
        $rawTitle = SystemSetting::getSetting('homepage_hero_title', 'Ignite your potential today.');
        $highlight = SystemSetting::getSetting('homepage_hero_title_highlight', null);
        if (str_contains($rawTitle, '<')) {
            if ($highlight === null && preg_match('/<span[^>]*>(.*?)<\/span>/is', $rawTitle, $m)) {
                $highlight = trim(strip_tags($m[1]));
            }
            $rawTitle = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('/<br\s*\/?>/i', ' ', $rawTitle))));
        }

        $defaultHeroButtons = [
            ['label' => 'Discover More', 'url' => '#', 'style' => 'primary', 'enabled' => true, 'icon' => false],
            ['label' => 'Campus Tour', 'url' => '#', 'style' => 'soft', 'enabled' => true, 'icon' => true],
        ];

        $settings = [
            'homepage_type' => SystemSetting::getSetting('homepage_type', 'default'),
            'homepage_page_id' => SystemSetting::getSetting('homepage_page_id', ''),
            'homepage_hero_buttons' => SystemSetting::getSetting('homepage_hero_buttons', json_encode($defaultHeroButtons)),
            'homepage_hero_title' => $rawTitle,
            'homepage_hero_title_highlight' => $highlight ?? '',
            'homepage_hero_image' => SystemSetting::getSetting('homepage_hero_image', ''),
            'homepage_hero_subtitle' => SystemSetting::getSetting('homepage_hero_subtitle', 'Admissions Open for 2026-27'),
            'homepage_hero_description' => SystemSetting::getSetting('homepage_hero_description', 'Empowering the next generation of thinkers, creators, and leaders through innovative education and a vibrant campus community.'),
            
            'homepage_stats' => json_encode(self::getStats()),
            
            'homepage_features' => json_encode(self::getFeatures()),
            'homepage_testimonials' => json_encode(self::getTestimonials()),
            
            'homepage_cta' => json_encode(self::getCta()),
        ];

        $pages = \App\Models\Page::where('status', true)->get(['id', 'title']);

        return view('sm-administrator.website-settings.homepage', compact('settings', 'pages'));
    }

    public function update(Request $request)
    {
        $keys = [
            'homepage_type',
            'homepage_page_id',
            'homepage_hero_title',
            'homepage_hero_title_highlight',
            'homepage_hero_subtitle',
            'homepage_hero_description',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $value = $request->input($key);
                // Titles are plain text now; never store markup.
                if (in_array($key, ['homepage_hero_title', 'homepage_hero_title_highlight'])) {
                    $value = trim(strip_tags((string) $value));
                }
                SystemSetting::setSetting($key, $value);
            }
        }

        $request->validate([
            'homepage_hero_image' => 'nullable|image|max:5120',
        ]);

        if ($request->has('homepage_hero_buttons')) {
            $buttons = json_decode((string) $request->input('homepage_hero_buttons'), true);
            if (is_array($buttons)) {
                $allowedStyles = ['primary', 'dark', 'soft', 'outline', 'text'];
                $clean = [];
                foreach ($buttons as $b) {
                    $clean[] = [
                        'label' => trim(strip_tags((string) ($b['label'] ?? ''))),
                        'url' => trim((string) ($b['url'] ?? '#')) ?: '#',
                        'style' => in_array($b['style'] ?? '', $allowedStyles) ? $b['style'] : 'primary',
                        'enabled' => !empty($b['enabled']),
                        'icon' => !empty($b['icon']),
                    ];
                }
                SystemSetting::setSetting('homepage_hero_buttons', json_encode($clean));
            }
        }

        if ($request->has('homepage_stats')) {
            $stats = json_decode((string) $request->input('homepage_stats'), true);
            if (is_array($stats)) {
                $items = [];
                foreach (($stats['items'] ?? []) as $s) {
                    $items[] = [
                        'value' => trim(strip_tags((string) ($s['value'] ?? ''))),
                        'label' => trim(strip_tags((string) ($s['label'] ?? ''))),
                        'enabled' => !empty($s['enabled']),
                    ];
                }
                SystemSetting::setSetting('homepage_stats', json_encode([
                    'enabled' => !empty($stats['enabled']),
                    'items' => $items,
                ]));
            }
        }

        if ($request->has('homepage_features')) {
            $features = json_decode((string) $request->input('homepage_features'), true);
            if (is_array($features)) {
                $items = [];
                foreach (($features['items'] ?? []) as $i => $f) {
                    $icon = $f['icon'] ?? 'book';
                    
                    $imagePath = $f['image'] ?? '';
                    if ($request->hasFile("feature_image_{$i}")) {
                        $file = $request->file("feature_image_{$i}");
                        $filename = time() . "_feature_{$i}_" . $file->getClientOriginalName();
                        $file->move(public_path('uploads/settings'), $filename);
                        $imagePath = 'uploads/settings/' . $filename;
                    } elseif ($request->boolean("remove_feature_image_{$i}")) {
                        $imagePath = '';
                    }

                    $items[] = [
                        'icon' => in_array($icon, self::FEATURE_ICONS) ? $icon : 'book',
                        'image' => $imagePath,
                        'title' => trim(strip_tags((string) ($f['title'] ?? ''))),
                        'desc' => trim(strip_tags((string) ($f['desc'] ?? ''))),
                        'enabled' => !empty($f['enabled']),
                    ];
                }
                SystemSetting::setSetting('homepage_features', json_encode([
                    'enabled' => !empty($features['enabled']),
                    'tag' => trim(strip_tags((string) ($features['tag'] ?? ''))),
                    'title' => trim(strip_tags((string) ($features['title'] ?? ''))),
                    'items' => $items,
                ]));
            }
        }

        if ($request->has('homepage_cta')) {
            $cta = json_decode((string) $request->input('homepage_cta'), true);
            if (is_array($cta)) {
                $buttons = [];
                $allowedStyles = ['primary', 'dark', 'soft', 'outline', 'text'];
                foreach (($cta['buttons'] ?? []) as $b) {
                    $buttons[] = [
                        'label' => trim(strip_tags((string) ($b['label'] ?? ''))),
                        'url' => trim((string) ($b['url'] ?? '#')) ?: '#',
                        'style' => in_array($b['style'] ?? '', $allowedStyles) ? $b['style'] : 'primary',
                        'enabled' => !empty($b['enabled']),
                    ];
                }
                SystemSetting::setSetting('homepage_cta', json_encode([
                    'enabled' => !empty($cta['enabled']),
                    'title' => trim(strip_tags((string) ($cta['title'] ?? ''))),
                    'subtitle' => trim(strip_tags((string) ($cta['subtitle'] ?? ''))),
                    'buttons' => $buttons,
                ]));
            }
        }

        if ($request->has('homepage_testimonials')) {
            $testimonials = json_decode((string) $request->input('homepage_testimonials'), true);
            if (is_array($testimonials)) {
                $items = [];
                foreach (($testimonials['items'] ?? []) as $i => $t) {
                    $imagePath = $t['image'] ?? '';
                    if ($request->hasFile("testimonial_image_{$i}")) {
                        $file = $request->file("testimonial_image_{$i}");
                        $filename = time() . "_testimonial_{$i}_" . $file->getClientOriginalName();
                        $file->move(public_path('uploads/settings'), $filename);
                        $imagePath = 'uploads/settings/' . $filename;
                    } elseif ($request->boolean("remove_testimonial_image_{$i}")) {
                        $imagePath = '';
                    }

                    $items[] = [
                        'text' => trim(strip_tags((string) ($t['text'] ?? ''))),
                        'author' => trim(strip_tags((string) ($t['author'] ?? ''))),
                        'role' => trim(strip_tags((string) ($t['role'] ?? ''))),
                        'image' => $imagePath,
                        'enabled' => !empty($t['enabled']),
                    ];
                }
                SystemSetting::setSetting('homepage_testimonials', json_encode([
                    'enabled' => !empty($testimonials['enabled']),
                    'tag' => trim(strip_tags((string) ($testimonials['tag'] ?? ''))),
                    'title' => trim(strip_tags((string) ($testimonials['title'] ?? ''))),
                    'items' => $items,
                ]));
            }
        }

        if ($request->boolean('remove_hero_image')) {
            SystemSetting::setSetting('homepage_hero_image', '');
        }

        if ($request->hasFile('homepage_hero_image')) {
            $file = $request->file('homepage_hero_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/settings'), $filename);
            SystemSetting::setSetting('homepage_hero_image', 'uploads/settings/' . $filename);
        }

        return redirect()->back()->with('success', 'Homepage settings updated successfully.');
    }

    public function header()
    {
        $defaultMenus = [
            ['label' => 'About Us', 'url' => '#about', 'children' => []],
            ['label' => 'Academics', 'url' => '#academics', 'children' => []],
            ['label' => 'Campus Life', 'url' => '#campus', 'children' => []],
            ['label' => 'Admissions', 'url' => '#admissions', 'children' => []]
        ];

        $defaultButtons = [
            ['label' => 'Login', 'url' => '/login', 'style' => 'text'],
            ['label' => 'Apply Now', 'url' => '#apply', 'style' => 'primary']
        ];

        $settings = [
            'header_logo_type' => SystemSetting::getSetting('header_logo_type', 'text'),
            'header_logo_text' => SystemSetting::getSetting('header_logo_text', 'EdOrbit'),
            'header_logo_image' => SystemSetting::getSetting('header_logo_image', ''),
            'header_menus' => SystemSetting::getSetting('header_menus', json_encode($defaultMenus)),
            'header_buttons' => SystemSetting::getSetting('header_buttons', json_encode($defaultButtons)),
            'theme_color_primary' => SystemSetting::getSetting('theme_color_primary', '#B47A42'),
            'theme_color_secondary' => SystemSetting::getSetting('theme_color_secondary', '#211B17'),
            'theme_color_background' => SystemSetting::getSetting('theme_color_background', '#F5F1EB'),
        ];
        return view('sm-administrator.website-settings.header', compact('settings'));
    }

    public function updateHeader(Request $request)
    {
        $keys = [
            'header_logo_type', 'header_logo_text', 'header_menus', 'header_buttons',
            'theme_color_primary', 'theme_color_secondary', 'theme_color_background'
        ];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                SystemSetting::setSetting($key, $request->input($key));
            }
        }

        if ($request->hasFile('header_logo_image')) {
            $file = $request->file('header_logo_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/settings'), $filename);
            SystemSetting::setSetting('header_logo_image', 'uploads/settings/' . $filename);
        }

        return redirect()->back()->with('success', 'Header settings updated successfully.');
    }

    public static function getFooterColumns(): array
    {
        $stored = json_decode((string) SystemSetting::getSetting('homepage_footer_columns', ''), true);
        if (is_array($stored) && count($stored) > 0) {
            return $stored;
        }

        return [
            [
                'id' => 'col_about',
                'type' => 'about',
                'enabled' => true,
                'title' => 'About Us',
                'text' => SystemSetting::getSetting('footer_about_text', 'Empowering minds, shaping the future. A place where excellence meets opportunity.'),
            ],
            [
                'id' => 'col_links_1',
                'type' => 'links',
                'enabled' => true,
                'title' => 'Academics',
                'links' => [
                    ['label' => 'Curriculum', 'url' => '#', 'enabled' => true],
                    ['label' => 'Departments', 'url' => '#', 'enabled' => true],
                    ['label' => 'Calendar', 'url' => '#', 'enabled' => true],
                ]
            ],
            [
                'id' => 'col_links_2',
                'type' => 'links',
                'enabled' => true,
                'title' => 'Admissions',
                'links' => [
                    ['label' => 'How to Apply', 'url' => '#', 'enabled' => true],
                    ['label' => 'Tuition & Aid', 'url' => '#', 'enabled' => true],
                    ['label' => 'Visit Campus', 'url' => '#', 'enabled' => true],
                ]
            ],
            [
                'id' => 'col_contact',
                'type' => 'contact',
                'enabled' => true,
                'title' => 'Contact',
                'email' => SystemSetting::getSetting('footer_contact_email', 'hello@edorbit.edu'),
                'phone' => SystemSetting::getSetting('footer_contact_phone', '+1 (555) 123-4567'),
                'address' => SystemSetting::getSetting('footer_contact_address', "123 Education Lane\nKnowledge City, ST 12345"),
            ]
        ];
    }

    public function footer()
    {
        $settings = [
            'homepage_footer_columns' => json_encode(self::getFooterColumns()),
        ];
        return view('sm-administrator.website-settings.footer', compact('settings'));
    }

    public function updateFooter(Request $request)
    {
        if ($request->has('homepage_footer_columns')) {
            $cols = json_decode((string) $request->input('homepage_footer_columns'), true);
            if (is_array($cols)) {
                $clean = [];
                foreach ($cols as $c) {
                    $type = $c['type'] ?? 'about';
                    $col = [
                        'id' => $c['id'] ?? uniqid('col_'),
                        'type' => $type,
                        'enabled' => !empty($c['enabled']),
                        'title' => trim(strip_tags((string) ($c['title'] ?? ''))),
                    ];
                    if ($type === 'about') {
                        $col['text'] = trim(strip_tags((string) ($c['text'] ?? '')));
                        $col['image'] = $c['image'] ?? '';
                        if ($request->hasFile("footer_col_image_{$col['id']}")) {
                            $file = $request->file("footer_col_image_{$col['id']}");
                            $filename = time() . "_footer_{$col['id']}_" . $file->getClientOriginalName();
                            $file->move(public_path('uploads/settings'), $filename);
                            $col['image'] = 'uploads/settings/' . $filename;
                        } elseif ($request->boolean("remove_footer_col_image_{$col['id']}")) {
                            $col['image'] = '';
                        }
                    } elseif ($type === 'links') {
                        $links = [];
                        foreach (($c['links'] ?? []) as $l) {
                            $links[] = [
                                'label' => trim(strip_tags((string) ($l['label'] ?? ''))),
                                'url' => trim(strip_tags((string) ($l['url'] ?? '#'))),
                                'enabled' => !empty($l['enabled']),
                            ];
                        }
                        $col['links'] = $links;
                    } elseif ($type === 'contact') {
                        $col['email'] = trim(strip_tags((string) ($c['email'] ?? '')));
                        $col['phone'] = trim(strip_tags((string) ($c['phone'] ?? '')));
                        $col['address'] = trim(strip_tags((string) ($c['address'] ?? '')));
                    }
                    $clean[] = $col;
                }
                SystemSetting::setSetting('homepage_footer_columns', json_encode($clean));
            }
        }
        return redirect()->back()->with('success', 'Footer settings updated successfully.');
    }
}
