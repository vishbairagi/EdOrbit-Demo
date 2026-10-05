<?php
$bladePath = 'c:\Users\intel\Documents\edu_verse\resources\views\sm-school\settings\index.blade.php';
$content = file_get_contents($bladePath);

$newPaymentMethods = <<<EOT
@elseif(\$activeTab === 'payment-methods')
<section class="col-span-full space-y-6">
    <form action="{{ route('school-management.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Bank Transfer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-orange-500 text-white font-bold p-3 text-center">Bank Transfer Payment Method</div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Bank Transfer Payment:</label>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="enable_bank_transfer" value="1" {{ \App\Models\SystemSetting::getSetting('enable_bank_transfer', '0') == '1' ? 'checked' : '' }}> <span class="text-sm">Enable</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Branch Code:</label>
                    <div class="col-span-2">
                        <input type="text" name="bank_branch_code" value="{{ \App\Models\SystemSetting::getSetting('bank_branch_code') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Account No.:</label>
                    <div class="col-span-2">
                        <input type="text" name="bank_account_no" value="{{ \App\Models\SystemSetting::getSetting('bank_account_no') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Name:</label>
                    <div class="col-span-2">
                        <input type="text" name="bank_name" value="{{ \App\Models\SystemSetting::getSetting('bank_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                    <div class="col-span-2">
                        <textarea name="bank_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">{{ \App\Models\SystemSetting::getSetting('bank_instructions') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Instructions to be shown to the students for bank transfer.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- UPI Transfer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-orange-500 text-white font-bold p-3 text-center">UPI Transfer Payment Method</div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">UPI Transfer Payment:</label>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="enable_upi_transfer" value="1" {{ \App\Models\SystemSetting::getSetting('enable_upi_transfer', '0') == '1' ? 'checked' : '' }}> <span class="text-sm">Enable</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">QR Code:</label>
                    <div class="col-span-2">
                        <input type="file" name="upi_qr_code" class="block w-full text-sm">
                        @if(\App\Models\SystemSetting::getSetting('upi_qr_code'))
                        <p class="text-xs text-green-600 mt-1">QR Code already uploaded.</p>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">UPI ID:</label>
                    <div class="col-span-2">
                        <input type="text" name="upi_id" value="{{ \App\Models\SystemSetting::getSetting('upi_id') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Name:</label>
                    <div class="col-span-2">
                        <input type="text" name="upi_name" value="{{ \App\Models\SystemSetting::getSetting('upi_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                    <div class="col-span-2">
                        <textarea name="upi_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">{{ \App\Models\SystemSetting::getSetting('upi_instructions') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Instructions to be shown to the students for UPI transfer.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Payment -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-orange-500 text-white font-bold p-3 text-center">Cash Payment Method</div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Cash Payment:</label>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="enable_cash_payment" value="1" {{ \App\Models\SystemSetting::getSetting('enable_cash_payment', '0') == '1' ? 'checked' : '' }}> <span class="text-sm">Enable</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                    <div class="col-span-2">
                        <textarea name="cash_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">{{ \App\Models\SystemSetting::getSetting('cash_instructions') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Instructions to be shown to students for cash payments.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Check Payment -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-orange-500 text-white font-bold p-3 text-center">Check/Cheque Payment Method</div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <label class="text-sm font-bold text-heading">Check Payment:</label>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="enable_check_payment" value="1" {{ \App\Models\SystemSetting::getSetting('enable_check_payment', '0') == '1' ? 'checked' : '' }}> <span class="text-sm">Enable</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <label class="text-sm font-bold text-heading mt-2">Instructions:</label>
                    <div class="col-span-2">
                        <textarea name="check_instructions" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">{{ \App\Models\SystemSetting::getSetting('check_instructions') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Instructions to be shown to students for check payments.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 mb-6">
            <button type="submit" class="px-6 py-2 bg-orange-500 text-white text-sm font-bold rounded-md hover:bg-orange-600">Save Payment Methods</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white mt-8">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold text-heading">Custom Payment Gateways</h3>
            <p class="mt-1 text-xs text-body">Available methods for recording school fee collections dynamically.</p>
        </div>
EOT;

$content = str_replace(
    "@else\n<section class=\"col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white\">\n    <div class=\"border-b border-gray-200 px-5 py-4\">\n        <h3 class=\"text-sm font-bold text-heading\">Payment Methods</h3>\n        <p class=\"mt-1 text-xs text-body\">Available methods for recording school fee collections.</p>\n    </div>", 
    \$newPaymentMethods, 
    \$content
);

file_put_contents($bladePath, \$content);
echo "Payment methods replaced!\n";
