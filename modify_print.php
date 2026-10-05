<?php

function processFile($path, $isSingle) {
    $content = file_get_contents($path);
    
    // Determine the variable name for session based on whether we have $invoices or just $feeInvoice
    $sessionVar = $isSingle ? '$feeInvoice->academic_session_id' : '$feeInvoice->academic_session_id';

    // Replacement logic
    if ($isSingle) {
        $search = '<div class="invoice-container">';
        $replace = <<<HTML
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
    } else {
        $search = '@foreach($invoices as $feeInvoice)
    <div class="invoice-container">';
        $replace = <<<HTML
    @foreach(\$invoices as \$feeInvoice)
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
    }
    
    $content = str_replace($search, $replace, $content);
    
    // Add the label to the title bar
    $content = str_replace(
        '<span class="font-bold">Fee Invoice:</span> {{ $feeInvoice->invoice_title }} <span class="font-bold">Invoice No.</span>',
        '<span class="font-bold">Fee Invoice:</span> {{ $feeInvoice->invoice_title }}{{ $copyLabel ?? \'\' }} <span class="font-bold">Invoice No.</span>',
        $content
    );

    // Add closing tag for the for loop
    if ($isSingle) {
        $content = preg_replace('/<\/body>/', "    @endfor\n</body>", $content);
    } else {
        $content = str_replace('@endforeach', "        @endfor\n    @endforeach", $content);
    }
    
    file_put_contents($path, $content);
    echo "Updated: " . basename($path) . "\n";
}

processFile('c:/Users/intel/Documents/edu_verse/resources/views/accounting/invoices/print-single.blade.php', true);
processFile('c:/Users/intel/Documents/edu_verse/resources/views/accounting/invoices/print.blade.php', false);

