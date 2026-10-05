<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::table('student_types', function (Blueprint $table) {
        $table->dropForeign(['academic_session_id']);
    });
} catch (\Exception $e) {}

try {
    Schema::dropColumns('student_types', 'academic_session_id');
} catch (\Exception $e) {}

try {
    Schema::table('student_categories', function (Blueprint $table) {
        $table->dropForeign(['academic_session_id']);
    });
} catch (\Exception $e) {}

try {
    Schema::dropColumns('student_categories', 'academic_session_id');
} catch (\Exception $e) {}

try {
    Schema::table('payment_methods', function (Blueprint $table) {
        $table->dropForeign(['academic_session_id']);
    });
} catch (\Exception $e) {}

try {
    Schema::dropColumns('payment_methods', 'academic_session_id');
} catch (\Exception $e) {}

echo "Done\n";
