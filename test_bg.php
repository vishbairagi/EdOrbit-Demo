<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
echo App\Models\SchoolSetting::getSetting(1, 4, "invoice_background");
echo "\n";
