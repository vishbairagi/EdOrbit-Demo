<?php

$viewsPath = 'c:/Users/intel/Documents/edu_verse/resources/views/accounting/';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$count = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $original = file_get_contents($file->getRealPath());
        $content = $original;

        // Pattern to find {{ number_format(...) }} and prepend {{ \App\Models\SchoolSetting::getCurrencySymbol() }} if not already there
        $content = preg_replace(
            '/(?<!\{\{\s\\\\App\\\\Models\\\\SchoolSetting::getCurrencySymbol\(\)\s\}\})(?<!\{\{\\\\App\\\\Models\\\\SchoolSetting::getCurrencySymbol\(\)\}\})(\{\{\s*number_format\()/',
            '{{\\App\\Models\\SchoolSetting::getCurrencySymbol()}}$1',
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
