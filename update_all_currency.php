<?php

$viewsPath = 'c:/Users/intel/Documents/edu_verse/resources/views/';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$count = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $original = file_get_contents($file->getRealPath());
        $content = $original;

        // Replace any hardcoded ₹ with the dynamic currency symbol
        $content = str_replace(
            '₹{{ number_format',
            '{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format',
            $content
        );

        // Prepend {{ \App\Models\SchoolSetting::getCurrencySymbol() }} if not already there and not preceded by hardcoded ₹
        $content = preg_replace(
            '/(?<!\{\{\s\\\\App\\\\Models\\\\SchoolSetting::getCurrencySymbol\(\)\s\}\})(?<!\{\{\\\\App\\\\Models\\\\SchoolSetting::getCurrencySymbol\(\)\}\})(?<!₹)(\{\{\s*number_format\()/',
            '{{\\App\\Models\\SchoolSetting::getCurrencySymbol()}}$1',
            $content
        );

        if ($content !== $original) {
            file_put_contents($file->getRealPath(), $content);
            echo "Updated: " . $file->getRealPath() . "\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
