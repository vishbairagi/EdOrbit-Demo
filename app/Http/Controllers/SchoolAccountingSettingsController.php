<?php

namespace App\Http\Controllers;

use App\Models\FeePeriod;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolAccountingSettingsController extends AccountingController
{
    private function currentSession(): \App\Models\AcademicSession
    {
        $sessionId = request('session_id') ?: session('selected_academic_session_id');
        if ($sessionId) {
            $session = \App\Models\AcademicSession::find($sessionId);
            if ($session) return $session;
        }
        return \App\Models\AcademicSession::latest('id')->firstOrFail();
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $school = $this->selectedSchool();
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => ['required', 'string', 'max:100'],
            'duration_months' => ['required', 'integer', 'between:1,12'],
        ]);
        $exists = FeePeriod::where('school_id', $school->id)
            ->where('academic_session_id', $validated['academic_session_id'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($validated['name']))])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'This period name already exists for the selected session.'])->withInput();
        }
        $validated['name'] = trim($validated['name']);
        $school->feePeriods()->create($validated);

        return redirect()->route('sm-school.settings.index', ['tab' => 'registration', 'session_id' => $validated['academic_session_id']])->with('success', 'Fee period created successfully.');
    }

    public function updatePeriod(Request $request, FeePeriod $feePeriod): RedirectResponse
    {
        $school = $this->selectedSchool();
        abort_unless((int) $feePeriod->school_id === $school->id, 404);
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => ['required', 'string', 'max:100'],
            'duration_months' => ['required', 'integer', 'between:1,12'],
        ]);
        $exists = FeePeriod::where('school_id', $school->id)
            ->where('academic_session_id', $validated['academic_session_id'])
            ->where('id', '!=', $feePeriod->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($validated['name']))])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'This period name already exists for the selected session.'])->withInput();
        }
        if ((int) $validated['academic_session_id'] !== (int) $feePeriod->academic_session_id && $feePeriod->feeTypes()->exists()) {
            return back()->withErrors(['academic_session_id' => 'A period used by fee types cannot be moved to another session.'])->withInput();
        }

        $validated['name'] = trim($validated['name']);
        $feePeriod->update($validated);

        return redirect()->route('sm-school.settings.index', ['tab' => 'registration', 'session_id' => $validated['academic_session_id']])->with('success', 'Fee period updated successfully.');
    }

    public function destroyPeriod(FeePeriod $feePeriod): RedirectResponse
    {
        $school = $this->selectedSchool();
        $sessionId = $feePeriod->academic_session_id;
        abort_unless((int) $feePeriod->school_id === $school->id, 404);

        if ($feePeriod->feeTypes()->exists()) {
            return back()->with('error', 'This period is used by fee types and cannot be deleted.');
        }

        $feePeriod->delete();

        return redirect()->route('sm-school.settings.index', ['tab' => 'registration', 'session_id' => $sessionId])->with('success', 'Fee period deleted successfully.');
    }

    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $school = $this->selectedSchool();
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_methods', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
            ],
            'logo' => ['nullable', 'image', 'max:2048'],
            'settings_keys' => ['nullable', 'array'],
            'settings_values' => ['nullable', 'array']
        ]);

        $settings = [];
        if ($request->has('settings_keys') && $request->has('settings_values')) {
            $keys = $request->input('settings_keys');
            $values = $request->input('settings_values');
            foreach ($keys as $index => $key) {
                if (!empty($key)) {
                    $settings[$key] = $values[$index] ?? null;
                }
            }
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('payment_gateways', 'public');
        }

        $school->paymentMethods()->create([
            'name' => trim($validated['name']), 
            'is_active' => true,
            'academic_session_id' => $validated['academic_session_id'],
            'logo' => $logoPath,
            'settings' => $settings
        ]);

        return redirect()->route('sm-school.settings.index', ['tab' => 'payment-methods', 'session_id' => $validated['academic_session_id']])->with('success', 'Payment method created successfully.');
    }

    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $school = $this->selectedSchool();
        $session = $paymentMethod->academic_session_id ? \App\Models\AcademicSession::find($paymentMethod->academic_session_id) : $this->currentSession();
        abort_unless((int) $paymentMethod->school_id === $school->id, 404);
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_methods', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
                    ->ignore($paymentMethod->id)
            ],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'settings_keys' => ['nullable', 'array'],
            'settings_values' => ['nullable', 'array']
        ]);

        $settings = [];
        if ($request->has('settings_keys') && $request->has('settings_values')) {
            $keys = $request->input('settings_keys');
            $values = $request->input('settings_values');
            foreach ($keys as $index => $key) {
                if (!empty($key)) {
                    $settings[$key] = $values[$index] ?? null;
                }
            }
        }

        $updateData = [
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active'),
            'academic_session_id' => $validated['academic_session_id'],
            'settings' => $settings,
        ];

        if ($request->hasFile('logo')) {
            $updateData['logo'] = $request->file('logo')->store('payment_gateways', 'public');
        }

        $paymentMethod->update($updateData);

        return redirect()->route('sm-school.settings.index', ['tab' => 'payment-methods', 'session_id' => $validated['academic_session_id']])->with('success', 'Payment method updated successfully.');
    }

    public function destroyPaymentMethod(PaymentMethod $paymentMethod): RedirectResponse
    {
        $school = $this->selectedSchool();
        $sessionId = $paymentMethod->academic_session_id;
        abort_unless((int) $paymentMethod->school_id === $school->id, 404);

        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', 'This payment method is used by payment history and cannot be deleted.');
        }

        $paymentMethod->delete();

        return redirect()->route('sm-school.settings.index', ['tab' => 'payment-methods', 'session_id' => $sessionId])->with('success', 'Payment method deleted successfully.');
    }
}
