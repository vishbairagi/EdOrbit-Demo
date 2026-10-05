<?php
$bladePath = 'c:/Users/intel/Documents/edu_verse/resources/views/sm-school/settings/index.blade.php';
$content = file_get_contents($bladePath);
$content = preg_replace(
    "/(<form action=\"\{\{ route\('sm-school\.settings\.update'\) \}\}\"[^>]*>\s*@csrf)/s",
    "$1\n    <input type=\"hidden\" name=\"academic_session_id\" value=\"{{ \$session->id }}\">",
    $content
);
file_put_contents($bladePath, $content);
echo "Updated forms!\n";
