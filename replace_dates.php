<?php
$directory = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

$count = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        $original = $content;

        // We want to replace format strings that are meant for display:
        // 'd M Y', 'd F Y', 'd/m/Y', 'd-m-Y', 'M d, Y', 'F Y'
        // But what about 'Y-m-d'? Only replace 'Y-m-d' if it's NOT inside a value="{{ ... }}" of an input.
        // It's safer to just replace all ->format('...') except ->format('Y-m-d') and ->format('Y-m') and ->format('H:i') etc.
        // Let's replace ONLY specific display formats:

        $formatsToReplace = [
            'd M Y',
            'd F Y',
            'd/m/Y',
            'd-m-Y',
            'M d, Y',
            'd M, Y',
            'jS M, Y',
        ];

        foreach ($formatsToReplace as $f) {
            $pattern = "/->format\('" . preg_quote($f, '/') . "'\)/";
            $content = preg_replace($pattern, "->format(\\App\\Models\\SystemSetting::getSetting('date_format', 'Y-m-d'))", $content);
        }

        // Now for ->format('Y-m-d') that is NOT inside an <input>
        // We'll skip replacing 'Y-m-d' globally to avoid breaking inputs. Most inputs use 'Y-m-d'.
        
        // Now handle Y-m-d safely per line
        $lines = explode("\n", $content);
        foreach ($lines as &$line) {
            if (strpos($line, '<input') === false && strpos($line, 'input type="date"') === false) {
                $line = preg_replace("/->format\('Y-m-d'\)/", "->format(\\App\\Models\\SystemSetting::getSetting('date_format', 'Y-m-d'))", $line);
            }
        }
        $content = implode("\n", $lines);

        if ($content !== $original) {
            file_put_contents($file->getRealPath(), $content);
            $count++;
            echo "Updated: " . $file->getFilename() . "\n";
        }
    }
}
echo "Total files updated: $count\n";
