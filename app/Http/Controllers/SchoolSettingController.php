<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Session;

class SchoolSettingController extends Controller
{
    public function update(Request $request)
    {
        $schoolId = session('selected_school_id');
        $sessionId = $request->input('academic_session_id') ?: session('selected_academic_session_id');

        if (!$sessionId) {
            $sessionId = \App\Models\AcademicSession::latest('id')->value('id');
        }

        if (!$schoolId || !$sessionId) {
            return back()->with('error', 'Active school or session not set.');
        }

        $exclude = ['_token', '_method'];
        $fileInputs = ['school_logo', 'school_signature', 'id_card_background', 'invoice_background', 'examination_background', 'upi_qr_code'];

        $checkboxes = [
            'enable_bank_transfer', 'enable_upi_transfer', 'enable_cash_payment', 'enable_check_payment',
            'show_student_category', 'show_student_type',
            'show_blood_group', 'show_religion', 'show_caste', 'show_middle_name', 'show_medium'
        ];

        // Process checkboxes
        foreach ($checkboxes as $checkbox) {
            $isActive = $request->has($checkbox);
            SchoolSetting::setSetting($schoolId, $sessionId, $checkbox, $isActive ? '1' : '0');
            $exclude[] = $checkbox;
            
            // Sync with PaymentMethod table
            $methodNameMap = [
                'enable_bank_transfer' => 'Bank Transfer',
                'enable_upi_transfer' => 'UPI Transfer',
                'enable_cash_payment' => 'Cash',
                'enable_check_payment' => 'Check',
            ];
            
            if (isset($methodNameMap[$checkbox])) {
                $pmName = $methodNameMap[$checkbox];
                \App\Models\PaymentMethod::updateOrCreate(
                    ['school_id' => $schoolId, 'name' => $pmName],
                    ['is_active' => $isActive, 'academic_session_id' => $sessionId]
                );
            }
        }

        // Process file uploads
        foreach ($fileInputs as $fileInput) {
            $removeKey = 'remove_' . $fileInput;
            if ($request->has($removeKey) && $request->input($removeKey) == '1') {
                $existingPath = SchoolSetting::getSetting($schoolId, $sessionId, $fileInput);
                if ($existingPath && file_exists(public_path($existingPath))) {
                    @unlink(public_path($existingPath));
                }
                SchoolSetting::where('school_id', $schoolId)
                    ->where(function($q) use ($sessionId) {
                        $q->where('academic_session_id', $sessionId)
                          ->orWhereNull('academic_session_id');
                    })
                    ->where('key', $fileInput)
                    ->delete();
            } elseif ($request->hasFile($fileInput)) {
                $file = $request->file($fileInput);
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->move(public_path('uploads/settings'), $filename);
                SchoolSetting::setSetting($schoolId, $sessionId, $fileInput, 'uploads/settings/' . $filename);
            }
            $exclude[] = $fileInput;
            $exclude[] = $removeKey;
        }

        // Process remaining fields
        foreach ($request->except($exclude) as $key => $value) {
            if ($value !== null) {
                SchoolSetting::setSetting($schoolId, $sessionId, $key, $value);
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $template = $request->query('template');
        
        $request->validate([
            'test_email' => 'required|email',
            "{$template}_email_subject" => 'required|string',
            "{$template}_email_body" => 'required|string',
        ]);

        $subject = $request->input("{$template}_email_subject");
        $body = $request->input("{$template}_email_body");

        $schoolId = session('selected_school_id');
        $sessionId = $request->input('academic_session_id') ?: session('selected_academic_session_id');

        $carrier = SchoolSetting::getSetting($schoolId, $sessionId, 'email_carrier');
        if ($carrier === 'smtp') {
            config(['mail.default' => 'smtp']);
            config(['mail.mailers.smtp.host' => SchoolSetting::getSetting($schoolId, $sessionId, 'smtp_host')]);
            config(['mail.mailers.smtp.port' => SchoolSetting::getSetting($schoolId, $sessionId, 'smtp_port')]);
            config(['mail.mailers.smtp.encryption' => SchoolSetting::getSetting($schoolId, $sessionId, 'smtp_encryption')]);
            config(['mail.mailers.smtp.username' => SchoolSetting::getSetting($schoolId, $sessionId, 'smtp_username')]);
            config(['mail.mailers.smtp.password' => SchoolSetting::getSetting($schoolId, $sessionId, 'smtp_password')]);
            config(['mail.from.address' => SchoolSetting::getSetting($schoolId, $sessionId, 'smtp_username')]);
            config(['mail.from.name' => SchoolSetting::getSetting($schoolId, $sessionId, 'mail_from_name')]);
            
            app('mail.manager')->purge('smtp');
        }

        try {
            \Illuminate\Support\Facades\Mail::mailer('smtp')->html(nl2br(e($body)), function ($message) use ($request, $subject) {
                $message->to($request->test_email)
                        ->subject($subject);
            });
            
            // Call update to save the current form state
            $this->update($request);
            
            return back()->with('success', 'Test email sent successfully!');
        } catch (\Exception $e) {
            // Still update the current form state even if email fails
            $this->update($request);
            return back()->with('error', 'Test email failed: ' . $e->getMessage());
        }
    }
}
