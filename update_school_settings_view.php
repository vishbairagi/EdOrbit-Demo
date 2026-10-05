<?php

$bladePath = 'c:/Users/intel/Documents/edu_verse/resources/views/sm-school/settings/index.blade.php';
$content = file_get_contents($bladePath);

// Replace form actions
$content = str_replace(
    "route('school-management.settings.update')",
    "route('sm-school.settings.update')",
    $content
);

// Replace \App\Models\SystemSetting::getSetting('...') with \App\Models\SchoolSetting::getSetting($school->id, $session->id, '...')
$content = preg_replace(
    "/\\\\App\\\\Models\\\\SystemSetting::getSetting\('([^']+)'(,\s*[^)]+)?\)/",
    "\\App\\Models\\SchoolSetting::getSetting(\$school->id, \$session->id, '$1'$2)",
    $content
);

file_put_contents($bladePath, $content);
echo "View updated!\n";
