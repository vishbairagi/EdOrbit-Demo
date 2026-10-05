<?php
$bladePath = 'c:\Users\intel\Documents\edu_verse\resources\views\sm-school\settings\index.blade.php';
$content = file_get_contents($bladePath);

$newTabs = <<<EOT
@elseif(\$activeTab === 'general')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">General Settings</h3>
    </div>
    <form action="{{ route('school-management.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Set Currency:</label>
            <div class="col-span-2">
                @php 
                    \$currency = \App\Models\SystemSetting::getSetting('currency', 'USD');
                    \$currencies = ['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'INR' => 'INR (₹)', 'AUD' => 'AUD ($)', 'CAD' => 'CAD ($)', 'SGD' => 'SGD ($)', 'JPY' => 'JPY (¥)', 'NGN' => 'NGN (₦)', 'ZAR' => 'ZAR (R)'];
                @endphp
                <select name="currency" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary sm:text-sm">
                    @foreach(\$currencies as \$k => \$v)
                    <option value="{{ \$k }}" {{ \$currency === \$k ? 'selected' : '' }}>{{ \$v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Invoice Copy:</label>
            <div class="col-span-2">
                @php \$invoiceCopy = \App\Models\SystemSetting::getSetting('invoice_copy', 'No'); @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="invoice_copy" value="Yes" {{ \$invoiceCopy === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="invoice_copy" value="No" {{ \$invoiceCopy === 'No' ? 'checked' : '' }}> No</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Admin will be able to print school and student copies of invoices.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Registration Invoice Type:</label>
            <div class="col-span-2">
                @php \$regInvType = \App\Models\SystemSetting::getSetting('registration_invoice_type', 'Combined Invoice'); @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="registration_invoice_type" value="Combined Invoice" {{ \$regInvType === 'Combined Invoice' ? 'checked' : '' }}> Combined Invoice</label>
                    <label><input type="radio" name="registration_invoice_type" value="Separate Invoices" {{ \$regInvType === 'Separate Invoices' ? 'checked' : '' }}> Separate Invoices</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Choose whether to create a single combined invoice with all fee types or separate invoices for each fee type when a new student is registered.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Auto Generate Invoice:</label>
            <div class="col-span-2">
                @php \$autoInv = \App\Models\SystemSetting::getSetting('auto_generate_invoice', 'No'); @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="auto_generate_invoice" value="Yes" {{ \$autoInv === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="auto_generate_invoice" value="No" {{ \$autoInv === 'No' ? 'checked' : '' }}> No</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">New - Monthly invoices will be auto generated.</p>
                @if(\$autoInv === 'Yes')
                <p class="text-xs font-semibold text-green-600 mt-1">Next cron job will run on: {{ now()->addMonth()->startOfMonth()->format('d M Y') }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload School Logo:</label>
            <div class="col-span-2">
                <input type="file" name="school_logo" class="block w-full text-sm">
                @if(\App\Models\SystemSetting::getSetting('school_logo'))
                <p class="text-xs text-green-600 mt-1">Logo already uploaded.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload School Signature:</label>
            <div class="col-span-2">
                <input type="file" name="school_signature" class="block w-full text-sm">
                @if(\App\Models\SystemSetting::getSetting('school_signature'))
                <p class="text-xs text-green-600 mt-1">Signature already uploaded.</p>
                @endif
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>

@elseif(\$activeTab === 'registration' || \$activeTab === 'dashboard' || \$activeTab === 'live-classes')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="p-12 text-center text-gray-500 text-sm font-semibold">
        {{ ucfirst(str_replace('-', ' ', \$activeTab)) }} settings are upcoming/blank.
    </div>
</section>

@elseif(\$activeTab === 'card-background')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Card Background Settings</h3>
    </div>
    <form action="{{ route('school-management.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Id Card Background:</label>
            <div class="col-span-2">
                <input type="file" name="id_card_background" class="block w-full text-sm">
                @if(\App\Models\SystemSetting::getSetting('id_card_background'))
                <p class="text-xs text-green-600 mt-1">Background already uploaded.</p>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Upload Invoice Background:</label>
            <div class="col-span-2">
                <input type="file" name="invoice_background" class="block w-full text-sm">
                @if(\App\Models\SystemSetting::getSetting('invoice_background'))
                <p class="text-xs text-green-600 mt-1">Background already uploaded.</p>
                @endif
            </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>

@elseif(\$activeTab === 'examination')
<section class="col-span-full overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="text-sm font-bold text-heading">Examination Settings</h3>
    </div>
    <form action="{{ route('school-management.settings.update') }}" method="POST" class="p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Student Admit Card Photo:</label>
            <div class="col-span-2">
                @php \$admitPhoto = \App\Models\SystemSetting::getSetting('student_admit_card_photo', '0') == '1'; @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="student_admit_card_photo" value="1" {{ \$admitPhoto ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="student_admit_card_photo" value="0" {{ !\$admitPhoto ? 'checked' : '' }}> No</label>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-sm font-bold text-heading">Exam Result Photo:</label>
            <div class="col-span-2">
                @php \$resultPhoto = \App\Models\SystemSetting::getSetting('exam_result_photo', '0') == '1'; @endphp
                <div class="flex items-center gap-4">
                    <label><input type="radio" name="exam_result_photo" value="1" {{ \$resultPhoto ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="exam_result_photo" value="0" {{ !\$resultPhoto ? 'checked' : '' }}> No</label>
                </div>
            </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-navy text-white text-sm font-bold rounded-md hover:bg-opacity-90">Save Settings</button>
        </div>
    </form>
</section>
EOT;

$content = str_replace('@endif', \$newTabs . "\n@endif", \$content);
file_put_contents($bladePath, \$content);
echo "Tabs injected!\n";
