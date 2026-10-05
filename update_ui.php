<?php

$path = 'c:/Users/intel/Documents/edu_verse/resources/views/sm-school/settings/index.blade.php';
$content = file_get_contents($path);

// We need to replace the section starting at `@elseif($activeTab === 'payment-methods')`
// up to `@elseif($activeTab === 'registration')`

$startPos = strpos($content, "@elseif(\$activeTab === 'payment-methods')");
$endPos = strpos($content, "@elseif(\$activeTab === 'registration')", $startPos);

if ($startPos === false || $endPos === false) {
    echo "Could not find sections.\n";
    exit;
}

$replacement = <<<HTML
@elseif(\$activeTab === 'payment-methods')
<section class="col-span-full space-y-8" x-data="{ activeMethod: null }">
    <!-- Static Payment Methods (Bank, UPI, Cash, Check) -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Standard Payment Methods</h3>
            <p class="mt-1 text-xs text-body">Configure built-in payment methods.</p>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Method Boxes -->
                <button type="button" @click="activeMethod = activeMethod === 'bank' ? null : 'bank'" class="flex flex-col items-center justify-center p-4 border rounded-xl shadow-sm transition-all hover:border-orange-500 hover:shadow-md bg-white" :class="activeMethod === 'bank' ? 'border-orange-500 ring-1 ring-orange-500' : 'border-gray-200'">
                    <i data-lucide="building-2" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Bank Transfer</span>
                </button>
                <button type="button" @click="activeMethod = activeMethod === 'upi' ? null : 'upi'" class="flex flex-col items-center justify-center p-4 border rounded-xl shadow-sm transition-all hover:border-orange-500 hover:shadow-md bg-white" :class="activeMethod === 'upi' ? 'border-orange-500 ring-1 ring-orange-500' : 'border-gray-200'">
                    <i data-lucide="smartphone" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">UPI Transfer</span>
                </button>
                <button type="button" @click="activeMethod = activeMethod === 'cash' ? null : 'cash'" class="flex flex-col items-center justify-center p-4 border rounded-xl shadow-sm transition-all hover:border-orange-500 hover:shadow-md bg-white" :class="activeMethod === 'cash' ? 'border-orange-500 ring-1 ring-orange-500' : 'border-gray-200'">
                    <i data-lucide="banknote" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Cash</span>
                </button>
                <button type="button" @click="activeMethod = activeMethod === 'check' ? null : 'check'" class="flex flex-col items-center justify-center p-4 border rounded-xl shadow-sm transition-all hover:border-orange-500 hover:shadow-md bg-white" :class="activeMethod === 'check' ? 'border-orange-500 ring-1 ring-orange-500' : 'border-gray-200'">
                    <i data-lucide="file-signature" class="h-8 w-8 text-gray-700 mb-2"></i>
                    <span class="text-sm font-semibold text-heading">Check</span>
                </button>
            </div>

            <form action="{{ route('sm-school.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 border border-gray-200 rounded-xl p-5" x-show="activeMethod !== null" x-transition x-cloak>
                @csrf
                <input type="hidden" name="academic_session_id" value="{{ \$session->id }}">
                
                <!-- Bank Form -->
                <div x-show="activeMethod === 'bank'" class="space-y-4">
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Bank Transfer Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Bank Transfer:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_bank_transfer" value="1" {{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'enable_bank_transfer', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Branch Code:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_branch_code" value="{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'bank_branch_code') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Account No.:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_account_no" value="{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'bank_account_no') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Name:</label>
                        <div class="col-span-2">
                            <input type="text" name="bank_name" value="{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'bank_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="bank_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'bank_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- UPI Form -->
                <div x-show="activeMethod === 'upi'" class="space-y-4" x-cloak>
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">UPI Transfer Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable UPI Transfer:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_upi_transfer" value="1" {{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'enable_upi_transfer', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">QR Code:</label>
                        <div class="col-span-2">
                            <input type="file" name="upi_qr_code" class="block w-full text-sm">
                            @if(\App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'upi_qr_code'))
                            <p class="text-xs text-green-600 mt-1">QR Code already uploaded.</p>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">UPI ID:</label>
                        <div class="col-span-2">
                            <input type="text" name="upi_id" value="{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'upi_id') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Name:</label>
                        <div class="col-span-2">
                            <input type="text" name="upi_name" value="{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'upi_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="upi_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'upi_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Cash Form -->
                <div x-show="activeMethod === 'cash'" class="space-y-4" x-cloak>
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Cash Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Cash:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_cash_payment" value="1" {{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'enable_cash_payment', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="cash_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'cash_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Check Form -->
                <div x-show="activeMethod === 'check'" class="space-y-4" x-cloak>
                    <h4 class="text-sm font-bold text-orange-600 border-b pb-2 mb-4">Check Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="text-sm font-bold text-heading">Enable Check:</label>
                        <div class="col-span-2">
                            <input type="checkbox" name="enable_check_payment" value="1" {{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'enable_check_payment', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                        <div class="col-span-2">
                            <textarea name="check_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none">{{ \App\Models\SchoolSetting::getSetting(\$school->id, \$session->id, 'check_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white text-sm font-bold rounded-md hover:bg-orange-600">Save Standard Methods</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Payment Gateways -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Custom Payment Gateways</h3>
            <p class="mt-1 text-xs text-body">Add dynamically configured custom gateways (e.g. Razorpay, Stripe).</p>
        </div>
        <form action="{{ route('sm-school.settings.payment-methods.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label for="paymentMethodName" class="mb-1 block text-xs font-medium text-body">Method Name</label>
                <input id="paymentMethodName" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Razorpay" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
            </div>
            <div class="w-40">
                <label for="new-method-session" class="mb-1 block text-xs font-medium text-body">Session</label>
                <select id="new-method-session" name="academic_session_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy" required>
                    @foreach(\$sessions as \$s)
                        <option value="{{ \$s->id }}" @selected(\$s->id === \$session->id)>{{ \$s->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark"><i data-lucide="plus" class="h-4 w-4"></i>Add Gateway</button>
        </form>
        
        <div class="divide-y divide-gray-100" x-data="{ editingGateway: null }">
            @forelse(\$paymentMethods as \$method)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between gap-3 p-3 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer" @click="editingGateway = editingGateway === {{ \$method->id }} ? null : {{ \$method->id }}">
                        <div class="flex items-center gap-4">
                            @if(\$method->logo)
                                <img src="{{ asset('storage/' . \$method->logo) }}" alt="Logo" class="h-8 w-8 object-contain rounded">
                            @else
                                <div class="h-8 w-8 bg-gray-100 flex items-center justify-center rounded text-gray-500"><i data-lucide="credit-card" class="h-5 w-5"></i></div>
                            @endif
                            <div>
                                <span class="text-sm font-bold text-heading block">{{ \$method->name }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ \$method->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ \$method->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="chevron-down" class="h-4 w-4 text-gray-400 transition-transform" :class="editingGateway === {{ \$method->id }} ? 'rotate-180' : ''"></i>
                            <form action="{{ route('sm-school.settings.payment-methods.destroy', \$method) }}" method="POST" onsubmit="event.stopPropagation(); return confirm('Delete this custom gateway?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Delete payment method"><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                        </div>
                    </div>
                    
                    <div x-show="editingGateway === {{ \$method->id }}" x-transition x-cloak class="mt-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <form action="{{ route('sm-school.settings.payment-methods.update', \$method) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf @method('PUT')
                            <input type="hidden" name="academic_session_id" value="{{ \$session->id }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-body">Gateway Name</label>
                                    <input type="text" name="name" value="{{ \$method->name }}" required maxlength="100" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-body">Gateway Logo</label>
                                    <input type="file" name="logo" class="w-full text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="flex items-center gap-2 text-sm font-medium text-body">
                                    <input type="checkbox" name="is_active" value="1" {{ \$method->is_active ? 'checked' : '' }}> Gateway is Active
                                </label>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 mt-4" x-data="{ fields: {{ json_encode(\$method->settings && count(\$method->settings) > 0 ? collect(\$method->settings)->map(function(\$v, \$k) { return ['key' => \$k, 'value' => \$v]; })->values()->all() : [['key' => '', 'value' => '']]) }} }">
                                <h4 class="text-sm font-bold text-heading mb-3">Custom Fields (e.g. Secret Key, Client ID)</h4>
                                <template x-for="(field, index) in fields" :key="index">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="text" x-model="field.key" name="settings_keys[]" placeholder="Field Name (e.g. secret_key)" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm font-mono">
                                        <input type="text" x-model="field.value" name="settings_values[]" placeholder="Field Value" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <button type="button" @click="fields.splice(index, 1)" class="text-red-500 hover:text-red-700 p-2"><i data-lucide="x" class="h-4 w-4"></i></button>
                                    </div>
                                </template>
                                <button type="button" @click="fields.push({key: '', value: ''})" class="text-xs font-semibold text-navy hover:text-primary-dark flex items-center gap-1 mt-2"><i data-lucide="plus" class="h-3 w-3"></i> Add Field</button>
                            </div>
                            
                            <div class="flex justify-end border-t border-gray-200 pt-3">
                                <button type="submit" class="rounded-md bg-navy px-4 py-2 text-xs font-semibold text-white hover:bg-primary-dark">Save Gateway Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-body">No custom gateways configured.</p>
            @endforelse
        </div>
    </div>
</section>
HTML;

$newContent = substr($content, 0, $startPos) . $replacement . substr($content, $endPos);

file_put_contents($path, $newContent);
echo "UI Updated successfully!";
