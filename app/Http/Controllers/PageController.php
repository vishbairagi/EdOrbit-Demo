<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->get();
        return view('sm-administrator.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('sm-administrator.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
        ]);

        $page = new Page();
        $page->title = $request->title;
        $page->slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);
        $page->blocks = json_decode($request->blocks, true) ?? [];
        $page->status = $request->has('status');
        $page->save();

        if ($request->has('is_homepage')) {
            SystemSetting::setSetting('homepage_type', 'page');
            SystemSetting::setSetting('homepage_page_id', $page->id);
        }

        return redirect()->route('website-settings.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('sm-administrator.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
        ]);

        $page->title = $request->title;
        $page->slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);
        $page->blocks = json_decode($request->blocks, true) ?? [];
        $page->status = $request->has('status');
        $page->save();

        $currentHomepageId = SystemSetting::getSetting('homepage_page_id');
        $currentHomepageType = SystemSetting::getSetting('homepage_type');

        if ($request->has('is_homepage')) {
            SystemSetting::setSetting('homepage_type', 'page');
            SystemSetting::setSetting('homepage_page_id', $page->id);
        } elseif ($currentHomepageType === 'page' && $currentHomepageId == $page->id) {
            // Unchecked, so revert to default
            SystemSetting::setSetting('homepage_type', 'default');
            SystemSetting::setSetting('homepage_page_id', '');
        }

        return redirect()->route('website-settings.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('website-settings.pages.index')->with('success', 'Page deleted successfully.');
    }

    public function addToMenu(Page $page)
    {
        $menus = json_decode((string)SystemSetting::getSetting('header_menus', '[]'), true);
        if (!is_array($menus)) $menus = [];
        
        $menus[] = [
            'label' => $page->title,
            'url' => '/' . $page->slug,
            'children' => []
        ];
        
        SystemSetting::setSetting('header_menus', json_encode($menus));
        return redirect()->back()->with('success', 'Page added to Header Menu successfully.');
    }

    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('status', true)->firstOrFail();
        
        $homepageType = \App\Models\SystemSetting::getSetting('homepage_type');
        $homepagePageId = \App\Models\SystemSetting::getSetting('homepage_page_id');

        if (($homepageType === 'page' || $homepageType === 'both') && $homepagePageId == $page->id) {
            if ($homepageType === 'both') {
                return view('welcome', compact('page'));
            }
            return view('page', compact('page'));
        }

        return view('page', compact('page'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/pages'), $filename);
            return response()->json(['url' => 'uploads/pages/' . $filename]);
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }
}
