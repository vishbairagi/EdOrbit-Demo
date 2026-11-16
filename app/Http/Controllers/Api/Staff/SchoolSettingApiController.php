<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\SchoolSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolSettingApiController extends BaseApiController
{
    /**
     * Get All Settings for current school & session.
     */
    public function index(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $settings = SchoolSetting::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->pluck('value', 'key');

        $schoolInfo = [
            'name' => $school->name,
            'phone' => $school->phone,
            'email' => $school->email,
            'address' => $school->address,
            'code' => $school->code,
            'logo' => $school->logo ? (filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . ltrim($school->logo, '/'))) : '',
        ];

        return $this->success($request, [
            'school_info' => $schoolInfo,
            'settings' => $settings,
        ], 'School settings retrieved successfully');
    }

    /**
     * Save / Update Generic Settings.
     */
    public function update(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $inputs = $request->except(['_token', '_method', 'session_id']);

        // Check if school general info was included
        $schoolUpdated = false;
        $schoolFields = ['school_name' => 'name', 'school_phone' => 'phone', 'school_email' => 'email', 'school_address' => 'address'];
        foreach ($schoolFields as $inputKey => $modelField) {
            if ($request->has($inputKey)) {
                $school->$modelField = $request->input($inputKey);
                $schoolUpdated = true;
            }
        }
        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('schools/logos', 'public');
            $school->logo = $path;
            $schoolUpdated = true;
        }
        if ($schoolUpdated) {
            $school->save();
        }

        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            SchoolSetting::setSetting($school->id, $session->id, (string) $key, (string) $value);
        }

        $allSettings = SchoolSetting::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->pluck('value', 'key');

        return $this->success($request, $allSettings, 'Settings updated successfully');
    }

    // ==========================================
    // SPECIFIC SETTINGS ENDPOINTS (Postman Compatibility)
    // ==========================================

    public function viewGeneralSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $keys = ['currency', 'currency_symbol', 'date_format', 'time_zone', 'school_tagline'];
        $settings = [];
        foreach ($keys as $k) {
            $settings[$k] = SchoolSetting::getSetting($school->id, $session->id, $k, '');
        }

        $data = array_merge([
            'school_name' => $school->name,
            'school_phone' => $school->phone,
            'school_email' => $school->email,
            'school_address' => $school->address,
            'school_logo' => $school->logo ? (filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . ltrim($school->logo, '/'))) : '',
        ], $settings);

        return $this->success($request, $data, 'General settings retrieved');
    }

    public function saveGeneralSettings(Request $request): JsonResponse
    {
        return $this->update($request);
    }

    public function viewPaymentMethodsSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $keys = [
            'enable_bank_transfer', 'enable_upi_transfer', 'enable_cash_payment', 'enable_check_payment',
            'bank_name', 'bank_account_no', 'bank_branch_code', 'bank_ifsc_code', 'upi_id'
        ];

        $data = [];
        foreach ($keys as $k) {
            $data[$k] = SchoolSetting::getSetting($school->id, $session->id, $k, '');
        }

        $paymentMethods = PaymentMethod::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        $data['payment_methods'] = $paymentMethods;

        return $this->success($request, $data, 'Payment methods settings retrieved');
    }

    public function savePaymentMethodsSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $checkboxes = ['enable_bank_transfer', 'enable_upi_transfer', 'enable_cash_payment', 'enable_check_payment'];
        $methodNameMap = [
            'enable_bank_transfer' => 'Bank Transfer',
            'enable_upi_transfer' => 'UPI Transfer',
            'enable_cash_payment' => 'Cash',
            'enable_check_payment' => 'Check',
        ];

        foreach ($checkboxes as $cb) {
            if ($request->has($cb)) {
                $val = $request->input($cb);
                $isActive = ($val === true || $val === '1' || $val === 1 || $val === 'true' || $val === 'on');
                SchoolSetting::setSetting($school->id, $session->id, $cb, $isActive ? '1' : '0');

                if (isset($methodNameMap[$cb])) {
                    PaymentMethod::updateOrCreate(
                        ['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => $methodNameMap[$cb]],
                        ['is_active' => $isActive]
                    );
                }
            }
        }

        $textFields = ['bank_name', 'bank_account_no', 'bank_branch_code', 'bank_ifsc_code', 'upi_id'];
        foreach ($textFields as $tf) {
            if ($request->has($tf)) {
                SchoolSetting::setSetting($school->id, $session->id, $tf, (string) $request->input($tf));
            }
        }

        return $this->success($request, null, 'Payment methods settings saved successfully');
    }

    public function viewLiveClassesSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $data = [
            'google_meet_enable' => SchoolSetting::getSetting($school->id, $session->id, 'google_meet_enable', '0'),
            'zoom_enable' => SchoolSetting::getSetting($school->id, $session->id, 'zoom_enable', '0'),
            'zoom_api_key' => SchoolSetting::getSetting($school->id, $session->id, 'zoom_api_key', ''),
            'zoom_api_secret' => SchoolSetting::getSetting($school->id, $session->id, 'zoom_api_secret', ''),
        ];

        return $this->success($request, $data, 'Live classes settings retrieved');
    }

    public function saveLiveClassesSettings(Request $request): JsonResponse
    {
        return $this->update($request);
    }

    public function viewCardBackgroundsSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $keys = ['id_card_background', 'invoice_background', 'examination_background'];
        $data = [];
        foreach ($keys as $k) {
            $data[$k] = SchoolSetting::getSetting($school->id, $session->id, $k, '');
        }

        return $this->success($request, $data, 'Card backgrounds settings retrieved');
    }

    public function saveCardBackgroundsSettings(Request $request): JsonResponse
    {
        return $this->update($request);
    }

    public function viewExaminationSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $keys = ['exam_admit_card_rules', 'exam_result_signature', 'exam_passing_percentage', 'grade_criteria'];
        $data = [];
        foreach ($keys as $k) {
            $data[$k] = SchoolSetting::getSetting($school->id, $session->id, $k, '');
        }

        return $this->success($request, $data, 'Examination settings retrieved');
    }

    public function saveExaminationSettings(Request $request): JsonResponse
    {
        return $this->update($request);
    }
}
