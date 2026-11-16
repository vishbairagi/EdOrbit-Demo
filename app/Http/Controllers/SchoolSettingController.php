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

        $exclude = ['_token', '_method', 'form_section'];
        $fileInputs = ['school_logo', 'school_signature', 'id_card_background', 'invoice_background', 'examination_background', 'upi_qr_code'];

        $formSection = $request->input('form_section');

        // 1. Payment Method checkboxes (only process if submitting payment methods)
        $paymentCheckboxes = ['enable_bank_transfer', 'enable_upi_transfer', 'enable_cash_payment', 'enable_check_payment'];
        $isPaymentForm = $formSection === 'payment_methods' || $request->hasAny(['bank_account_no', 'bank_name', 'bank_branch_code', 'upi_id', 'enable_bank_transfer', 'enable_upi_transfer', 'enable_cash_payment', 'enable_check_payment']);
        if ($isPaymentForm) {
            $methodNameMap = [
                'enable_bank_transfer' => 'Bank Transfer',
                'enable_upi_transfer' => 'UPI Transfer',
                'enable_cash_payment' => 'Cash',
                'enable_check_payment' => 'Check',
            ];
            foreach ($paymentCheckboxes as $checkbox) {
                $isActive = $request->has($checkbox);
                SchoolSetting::setSetting($schoolId, $sessionId, $checkbox, $isActive ? '1' : '0');
                $exclude[] = $checkbox;

                if (isset($methodNameMap[$checkbox])) {
                    $pmName = $methodNameMap[$checkbox];
                    \App\Models\PaymentMethod::updateOrCreate(
                        ['school_id' => $schoolId, 'academic_session_id' => $sessionId, 'name' => $pmName],
                        ['is_active' => $isActive]
                    );
                }
            }
        }

        // 2. Registration checkboxes (only process if submitting registration settings)
        $registrationCheckboxes = ['show_student_category', 'show_student_type', 'show_blood_group', 'show_religion', 'show_caste', 'show_middle_name', 'show_medium'];
        $isRegistrationForm = $formSection === 'registration' || $request->hasAny($registrationCheckboxes);
        if ($isRegistrationForm) {
            foreach ($registrationCheckboxes as $checkbox) {
                $isActive = $request->has($checkbox);
                SchoolSetting::setSetting($schoolId, $sessionId, $checkbox, $isActive ? '1' : '0');
                $exclude[] = $checkbox;
            }
        }

        // 3. Live Classes checkbox (only process if submitting live classes settings)
        $isLiveClassesForm = $formSection === 'live_classes' || $request->has('live_classes_form') || $request->has('google_meet_enable');
        if ($isLiveClassesForm) {
            $val = $request->input('google_meet_enable');
            $isMeetActive = ($val === '1' || $val === 1 || $val === 'true' || $val === true || $val === 'on');
            SchoolSetting::setSetting($schoolId, $sessionId, 'google_meet_enable', $isMeetActive ? '1' : '0');
            $exclude[] = 'google_meet_enable';
            $exclude[] = 'live_classes_form';
        }

        // 4. SMS Provider checkbox (only process if submitting SMS provider settings)
        $isSmsProviderForm = $formSection === 'sms_provider' || $request->has('sms_provider_form') || $request->has('sms_enable');
        if ($isSmsProviderForm) {
            $val = $request->input('sms_enable');
            $isSmsActive = ($val === '1' || $val === 1 || $val === 'true' || $val === true || $val === 'on');
            SchoolSetting::setSetting($schoolId, $sessionId, 'sms_enable', $isSmsActive ? '1' : '0');
            $exclude[] = 'sms_enable';
            $exclude[] = 'sms_provider_form';
        }

        // 5. SMS Templates checkboxes
        $smsTemplateKeys = [
            'student_admission', 'invoice_generated', 'fee_received', 'leave_application',
            'attendance_taken', 'absent_student', 'due_date', 'exam_admit_card', 'exam_result'
        ];
        $isSmsTemplatesForm = $formSection === 'sms_templates' || $request->has('sms_templates_form');
        if ($isSmsTemplatesForm) {
            $tplKey = $request->input('template_key');
            if ($tplKey) {
                $enableField = "{$tplKey}_sms_enable";
                $val = $request->input($enableField);
                $isTplActive = ($val === '1' || $val === 1 || $val === 'true' || $val === true || $val === 'on');
                SchoolSetting::setSetting($schoolId, $sessionId, $enableField, $isTplActive ? '1' : '0');
                $exclude[] = $enableField;
            } else {
                foreach ($smsTemplateKeys as $k) {
                    $enableField = "{$k}_sms_enable";
                    if ($request->has($enableField)) {
                        $val = $request->input($enableField);
                        $isTplActive = ($val === '1' || $val === 1 || $val === 'true' || $val === true || $val === 'on');
                        SchoolSetting::setSetting($schoolId, $sessionId, $enableField, $isTplActive ? '1' : '0');
                    }
                    $exclude[] = $enableField;
                }
            }
            $exclude[] = 'sms_templates_form';
            $exclude[] = 'template_key';
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.',
                'google_meet_enable' => SchoolSetting::getSetting($schoolId, $sessionId, 'google_meet_enable', '0'),
            ]);
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

    /**
     * Send a test SMS to verify provider credentials and templates.
     */
    public function testSms(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string',
        ]);

        $schoolId = session('selected_school_id');
        $sessionId = $request->input('academic_session_id') ?: session('selected_academic_session_id');

        $phone = preg_replace('/[^0-9+]/', '', (string) $request->input('test_phone'));
        $provider = $request->input('sms_provider') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'sms_provider', 'fast2sms');

        // Check if called from SMS template or carrier test
        $template = $request->query('template') ?: $request->input('template');
        if ($template) {
            $msg = $request->input("{$template}_sms_body") ?: SchoolSetting::getSetting($schoolId, $sessionId, "{$template}_sms_body");
            if (!$msg) {
                $msg = "EdOrbit: This is a test alert notification for {$template}.";
            }
        } else {
            $msg = $request->input('test_message') ?: "EdOrbit Test SMS: SMS gateway integration is working successfully for session!";
        }

        // Replace sample variables
        $school = \App\Models\School::find($schoolId);
        $schoolName = $school ? $school->name : 'EdOrbit School';
        $msg = str_replace(
            ['[STUDENT_NAME]', '[CLASS]', '[SECTION]', '[ROLL_NUMBER]', '[SCHOOL_NAME]', '[AMOUNT]', '[DUE_DATE]', '[TRANSACTION_ID]'],
            ['Aarav Sharma', 'Class 10', 'A', '101', $schoolName, '5000', date('d-m-Y'), 'TXN' . rand(100000, 999999)],
            $msg
        );

        // Always save current form state first
        $this->update($request);

        try {
            $responseMessage = $this->dispatchSms($schoolId, $sessionId, $provider, $phone, $msg, $request);
            return back()->with('success', "Test SMS sent via " . strtoupper($provider) . ": " . $responseMessage);
        } catch (\Exception $e) {
            return back()->with('error', "Test SMS error ({$provider}): " . $e->getMessage());
        }
    }

    /**
     * Dispatch SMS through the selected Indian or global gateway.
     */
    protected function dispatchSms($schoolId, $sessionId, string $provider, string $phone, string $message, Request $request): string
    {
        $cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);
        if (strlen($cleanPhone) < 10) {
            throw new \Exception('Please enter a valid 10-digit mobile number.');
        }

        switch (strtolower($provider)) {
            case 'fast2sms':
                $apiKey = $request->input('fast2sms_api_key') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'fast2sms_api_key');
                if (empty($apiKey)) {
                    throw new \Exception('Fast2SMS Authorization API Key is empty. Please enter and save your API Key.');
                }
                $route = $request->input('fast2sms_route') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'fast2sms_route', 'v3');
                $senderId = $request->input('fast2sms_sender_id') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'fast2sms_sender_id', 'TXTIND');

                $payload = [
                    'route' => $route,
                    'message' => $message,
                    'language' => 'english',
                    'flash' => 0,
                    'numbers' => $cleanPhone,
                ];
                if ($route === 'dlt') {
                    $payload['sender_id'] = $senderId;
                }

                $res = \Illuminate\Support\Facades\Http::withHeaders([
                    'authorization' => $apiKey,
                ])->post('https://www.fast2sms.com/dev/bulkV2', $payload);

                if ($res->successful() && ($res->json('return') === true || $res->json('status_code') == 200)) {
                    $resMsg = $res->json('message')[0] ?? 'Request accepted by Fast2SMS';
                    return "Delivered to {$cleanPhone} ({$resMsg})";
                }
                $errMsg = $res->json('message')[0] ?? $res->body();
                throw new \Exception($errMsg ?: 'Fast2SMS returned response code ' . $res->status());

            case 'msg91':
                $authKey = $request->input('msg91_auth_key') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'msg91_auth_key');
                if (empty($authKey)) {
                    throw new \Exception('MSG91 Auth Key is empty. Please enter and save your Auth Key.');
                }
                $senderId = $request->input('msg91_sender_id') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'msg91_sender_id', 'EDUVRS');
                $route = $request->input('msg91_route') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'msg91_route', '4');

                $res = \Illuminate\Support\Facades\Http::withHeaders([
                    'authkey' => $authKey,
                ])->post('https://api.msg91.com/api/v2/sendsms', [
                    'sender' => $senderId,
                    'route' => $route,
                    'country' => $request->input('msg91_country_code') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'msg91_country_code', '91'),
                    'sms' => [
                        ['message' => $message, 'to' => [$cleanPhone]]
                    ]
                ]);

                if ($res->successful()) {
                    return "Request sent to MSG91 for {$cleanPhone}. Status: " . ($res->json('type') ?: 'Success');
                }
                throw new \Exception($res->json('message') ?: $res->body());

            case 'textlocal':
                $apiKey = $request->input('textlocal_api_key') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'textlocal_api_key');
                if (empty($apiKey)) {
                    throw new \Exception('Textlocal API Key is empty. Please enter and save your API Key.');
                }
                $sender = $request->input('textlocal_sender_id') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'textlocal_sender_id', 'TXTLCL');

                $res = \Illuminate\Support\Facades\Http::asForm()->post('https://api.textlocal.in/send/', [
                    'apikey' => $apiKey,
                    'numbers' => '91' . $cleanPhone,
                    'message' => rawurlencode($message),
                    'sender' => $sender,
                ]);

                if ($res->successful() && $res->json('status') === 'success') {
                    return "Sent via Textlocal to 91{$cleanPhone}. Balance: " . ($res->json('balance') ?? 'N/A');
                }
                $errors = $res->json('errors');
                $errMsg = is_array($errors) ? ($errors[0]['message'] ?? 'Error') : $res->body();
                throw new \Exception($errMsg);

            case 'twilio':
                $sid = $request->input('twilio_account_sid') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'twilio_account_sid');
                $token = $request->input('twilio_auth_token') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'twilio_auth_token');
                $from = $request->input('twilio_from_number') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'twilio_from_number');

                if (empty($sid) || empty($token)) {
                    throw new \Exception('Twilio Account SID or Auth Token is empty.');
                }

                $res = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $token)
                    ->asForm()
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                        'From' => $from,
                        'To' => '+91' . $cleanPhone,
                        'Body' => $message,
                    ]);

                if ($res->successful()) {
                    return "Twilio SMS dispatched. SID: " . ($res->json('sid') ?? 'OK');
                }
                throw new \Exception($res->json('message') ?: $res->body());

            case 'exotel':
                $sid = $request->input('exotel_sid') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'exotel_sid');
                $apiKey = $request->input('exotel_api_key') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'exotel_api_key');
                $apiToken = $request->input('exotel_api_token') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'exotel_api_token');
                $subdomain = $request->input('exotel_subdomain') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'exotel_subdomain', 'api.exotel.com');
                $sender = $request->input('exotel_sender_id') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'exotel_sender_id');

                if (empty($sid) || empty($apiKey) || empty($apiToken)) {
                    throw new \Exception('Exotel SID, API Key, or API Token is empty.');
                }

                $res = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, $apiToken)
                    ->asForm()
                    ->post("https://{$subdomain}/v1/Accounts/{$sid}/Sms/send.json", [
                        'From' => $sender,
                        'To' => '0' . $cleanPhone,
                        'Body' => $message,
                    ]);

                if ($res->successful()) {
                    return "Exotel SMS dispatched successfully.";
                }
                throw new \Exception($res->body());

            case 'gupshup':
                $user = $request->input('gupshup_userid') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'gupshup_userid');
                $pass = $request->input('gupshup_password') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'gupshup_password');
                $mask = $request->input('gupshup_mask') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'gupshup_mask');

                if (empty($user) || empty($pass)) {
                    throw new \Exception('Gupshup User ID or Password is empty.');
                }

                $res = \Illuminate\Support\Facades\Http::asForm()->post('https://enterprise.smsgupshup.com/GatewayAPI/rest', [
                    'method' => 'SendMessage',
                    'send_to' => $cleanPhone,
                    'msg' => $message,
                    'userid' => $user,
                    'password' => $pass,
                    'v' => '1.1',
                    'msg_type' => 'TEXT',
                    'auth_scheme' => 'PLAIN',
                    'mask' => $mask,
                ]);

                if ($res->successful() && str_contains($res->body(), 'success')) {
                    return "Gupshup SMS queued: " . $res->body();
                }
                throw new \Exception($res->body());

            case 'kaleyra':
                $apiKey = $request->input('kaleyra_api_key') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'kaleyra_api_key');
                $sid = $request->input('kaleyra_sid') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'kaleyra_sid');
                $sender = $request->input('kaleyra_sender_id') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'kaleyra_sender_id');

                if (empty($apiKey) || empty($sid)) {
                    throw new \Exception('Kaleyra API Key or SID is empty.');
                }

                $res = \Illuminate\Support\Facades\Http::withHeaders([
                    'api-key' => $apiKey,
                ])->asForm()->post("https://api.kaleyra.io/v1/{$sid}/messages", [
                    'to' => '+91' . $cleanPhone,
                    'type' => 'TXN',
                    'sender' => $sender,
                    'body' => $message,
                ]);

                if ($res->successful()) {
                    return "Kaleyra SMS sent successfully.";
                }
                throw new \Exception($res->body());

            case 'custom':
                $url = $request->input('custom_sms_url') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'custom_sms_url');
                if (empty($url)) {
                    throw new \Exception('Custom SMS Gateway URL is empty.');
                }
                $method = strtoupper($request->input('custom_sms_method') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'custom_sms_method', 'GET'));
                $mobileParam = $request->input('custom_sms_mobile_param') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'custom_sms_mobile_param', 'mobile');
                $msgParam = $request->input('custom_sms_message_param') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'custom_sms_message_param', 'message');
                $extra = $request->input('custom_sms_extra_params') ?: SchoolSetting::getSetting($schoolId, $sessionId, 'custom_sms_extra_params');

                $params = [
                    $mobileParam => $cleanPhone,
                    $msgParam => $message,
                ];

                if (!empty($extra)) {
                    parse_str($extra, $parsedExtra);
                    if (is_array($parsedExtra)) {
                        $params = array_merge($params, $parsedExtra);
                    }
                }

                if ($method === 'POST') {
                    $res = \Illuminate\Support\Facades\Http::asForm()->post($url, $params);
                } else {
                    $res = \Illuminate\Support\Facades\Http::get($url, $params);
                }

                if ($res->successful()) {
                    return "Custom Gateway Request sent. Response: " . substr($res->body(), 0, 100);
                }
                throw new \Exception("Custom gateway returned HTTP {$res->status()}: " . substr($res->body(), 0, 150));

            default:
                return "SMS Simulation: Gateway '{$provider}' configured. Sent to +91{$cleanPhone}.";
        }
    }
}
