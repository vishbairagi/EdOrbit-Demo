<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$methods = [
    'enable_bank_transfer' => 'Bank Transfer',
    'enable_upi_transfer' => 'UPI Transfer',
    'enable_cash_payment' => 'Cash',
    'enable_check_payment' => 'Check',
];

foreach (\App\Models\School::all() as $s) {
    $session = \App\Models\AcademicSession::latest('id')->first();
    $sessionId = $session ? $session->id : 1;
    
    foreach ($methods as $k => $v) {
        $isActive = \App\Models\SchoolSetting::getSetting($s->id, $sessionId, $k, '0') == '1';
        \App\Models\PaymentMethod::updateOrCreate(
            ['school_id' => $s->id, 'name' => $v],
            ['is_active' => $isActive, 'academic_session_id' => $sessionId]
        );
    }
}
echo "Payment methods synced!\n";
