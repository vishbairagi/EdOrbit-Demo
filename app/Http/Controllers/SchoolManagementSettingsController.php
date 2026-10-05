<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SchoolManagementSettingsController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::all();
        $currentSessionId = SystemSetting::getSetting('current_session_id');
        $dateFormat = SystemSetting::getSetting('date_format', 'd-m-Y');
        $showLibrary = SystemSetting::getSetting('show_library_menu', '1');
        $showExamination = SystemSetting::getSetting('show_examination_menu', '1');
        $showTransport = SystemSetting::getSetting('show_transport_menu', '1');
        $showAccounting = SystemSetting::getSetting('show_accounting_menu', '1');
        $themeColor = SystemSetting::getSetting('theme_color', '#B47A42');

        return view('school-management.settings.index', compact(
            'sessions', 
            'currentSessionId', 
            'dateFormat',
            'showLibrary',
            'showExamination',
            'showTransport',
            'showAccounting',
            'themeColor'
        ));
    }

    public function update(Request $request)
    {
        // Handle file uploads
        $fileInputs = ['school_logo', 'school_signature', 'id_card_background', 'invoice_background', 'upi_qr_code'];
        foreach ($fileInputs as $fileInput) {
            if ($request->hasFile($fileInput)) {
                $file = $request->file($fileInput);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/settings'), $filename);
                SystemSetting::setSetting($fileInput, 'uploads/settings/' . $filename);
            }
        }

        // Handle regular text/select inputs
        $exclude = array_merge(['_token'], $fileInputs);
        foreach ($request->except($exclude) as $key => $value) {
            SystemSetting::setSetting($key, $value);
        }

        // Handle checkboxes (if they are unchecked, they won't be in the request, so we explicitly set them to 0 if not present)
        $checkboxes = [
            'show_library_menu', 'show_examination_menu', 'show_transport_menu', 'show_accounting_menu',
            'enable_bank_transfer', 'enable_upi_transfer', 'enable_cash_payment', 'enable_check_payment',
        ];
        foreach ($checkboxes as $chk) {
            SystemSetting::setSetting($chk, $request->has($chk) ? '1' : '0');
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function resetSystem(Request $request)
    {
        // Recreate all database tables
        Artisan::call('migrate:fresh', ['--force' => true]);
        
        // Logout since all users are deleted
        auth()->logout();
        
        return redirect()->route('login')->with('success', 'System has been reset successfully. Please login again.');
    }

    public function generateDemoData(Request $request)
    {
        Artisan::call('db:seed', ['--class' => 'DemoDataSeeder', '--force' => true]);
        return redirect()->back()->with('success', 'Demo Data generated successfully!');
    }

    public function cleanDemoData(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $demoSchools = \App\Models\School::where('name', 'Like', '%Artemyra Academy Demo%')->get();
        foreach($demoSchools as $ds) {
            $ds->delete(); // This cascades and deletes most things
        }
        
        // Also cleanup orphaned sessions
        AcademicSession::where('name', 'Like', '202%')->delete();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return redirect()->back()->with('success', 'Demo Data cleaned successfully!');
    }
}
