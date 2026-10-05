<?php

$viewsPath = 'c:/Users/intel/Documents/edu_verse/resources/views/';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$count = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $original = file_get_contents($file->getRealPath());
        $content = $original;

        // Replace \App\Models\SystemSetting::getCurrencySymbol()
        $content = str_replace(
            "\\App\\Models\\SystemSetting::getCurrencySymbol()",
            "\\App\\Models\\SchoolSetting::getCurrencySymbol()",
            $content
        );

        if ($content !== $original) {
            file_put_contents($file->getRealPath(), $content);
            echo "Updated: " . $file->getBasename() . "\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
