<?php
$files = [
    'c:/Users/intel/Documents/edu_verse/resources/views/accounting/invoices/print-single.blade.php',
    'c:/Users/intel/Documents/edu_verse/resources/views/accounting/invoices/print.blade.php',
    'c:/Users/intel/Documents/edu_verse/resources/views/accounting/transport-invoices/print-single.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add page-break-after: always;
    $content = str_replace(
        'break-after: page;',
        'page-break-after: always; break-after: page; margin-bottom: 30px;',
        $content
    );
    
    // Also explicitly add to @media print
    $content = str_replace(
        '.invoice-container { max-width: 100%; margin: 0; padding: 0; box-shadow: none; border: 0; }',
        ".invoice-container { max-width: 100%; margin: 0; padding: 0; box-shadow: none; border: 0; page-break-after: always; break-after: page; }\n            .invoice-container:last-of-type { page-break-after: auto; break-after: auto; }",
        $content
    );

    file_put_contents($file, $content);
}
echo "Done\n";
