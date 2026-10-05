<?php

$viewsPath = 'c:/Users/intel/Documents/edu_verse/resources/views/';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$count = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $original = file_get_contents($file->getRealPath());
        $content = $original;

        // Replace \App\Models\SystemSetting::getSetting('currency_symbol', '$')
        $content = str_replace(
            "\App\Models\SystemSetting::getSetting('currency_symbol', '$')",
            "\App\Models\SystemSetting::getCurrencySymbol()",
            $content
        );

        // Replace hardcoded '$' inside accounting/invoice/print views
        // This is a bit tricky, let's only replace it where it makes sense, e.g., print views
        // Let's use a regex to find $ followed by {{
        $content = preg_replace('/\$(\{\{ number_format)/', "{{\\App\\Models\\SystemSetting::getCurrencySymbol()}}$1", $content);
        $content = preg_replace('/\$(\{\{)/', "{{\\App\\Models\\SystemSetting::getCurrencySymbol()}}$1", $content);

        if ($content !== $original) {
            file_put_contents($file->getRealPath(), $content);
            echo "Updated: " . $file->getBasename() . "\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
