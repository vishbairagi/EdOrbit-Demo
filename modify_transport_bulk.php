<?php

$path = 'c:/Users/intel/Documents/edu_verse/resources/views/accounting/transport-invoices/print-bulk.blade.php';

if (!file_exists($path)) {
    echo "File not found: " . basename($path) . "\n";
    exit;
}

$content = file_get_contents($path);

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

$sessionVar = '$transportInvoice->academic_session_id';

$search = '@foreach($invoices as $transportInvoice)
    <div class="invoice-container">';
$replace = <<<HTML
    @foreach(\$invoices as \$transportInvoice)
        @php
            \$copies = \App\Models\SchoolSetting::getSetting(\$school->id, $sessionVar ?? session('selected_academic_session_id') ?? \App\Models\AcademicSession::latest('id')->value('id'), 'invoice_copy', 'No') === 'Yes' ? 2 : 1;
        @endphp
        @for(\$i = 0; \$i < \$copies; \$i++)
            @php
                \$copyLabel = '';
                if (\$copies == 2) {
                    \$copyLabel = \$i == 0 ? ' (Student Copy)' : ' (School Copy)';
                }
            @endphp
    <div class="invoice-container">
HTML;

$content = str_replace($search, $replace, $content);

// Add the label to the title bar
$content = str_replace(
    '<span class="font-bold">Transport Invoice:</span> {{ $transportInvoice->invoice_title }} <span class="font-bold">Invoice No.</span>',
    '<span class="font-bold">Transport Invoice:</span> {{ $transportInvoice->invoice_title }}{{ $copyLabel ?? \'\' }} <span class="font-bold">Invoice No.</span>',
    $content
);

// Add closing tag for the for loop
$content = str_replace('@endforeach', "        @endfor\n    @endforeach", $content);

file_put_contents($path, $content);
echo "Updated: " . basename($path) . "\n";
