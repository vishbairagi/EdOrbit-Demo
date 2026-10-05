<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt <?php echo e($feePayment->id); ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            color: #111;
            font-family: 'Times New Roman', serif;
            background: #f3f4f6;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin: 0 auto 14px;
            max-width: 800px;
        }

        .print-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 0;
            border-radius: 4px;
            background: #000;
            color: #fff;
            padding: 7px 14px;
            font-size: 14px;
            font-family: sans-serif;
            cursor: pointer;
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 15px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        h1 {
            margin: 0 0 5px;
            font-size: 20px;
            font-weight: bold;
        }

        .muted {
            font-size: 11px;
            margin: 2px 0;
        }

        .font-bold {
            font-weight: bold;
        }

        .title-row {
            text-align: center;
            margin-bottom: 20px;
        }

        .title-row h2 {
            font-size: 18px;
            margin: 0;
            font-weight: normal;
        }

        .title-row strong {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
            font-family: sans-serif;
        }

        th,
        td {
            border: 1px solid #d2d5db;
            padding: 12px;
            text-align: left;
        }

        th {
            width: 35%;
            font-weight: bold;
            color: #111;
        }

        td {
            color: #333;
        }

        .signature {
            text-align: right;
            margin-top: 40px;
            font-weight: bold;
            font-size: 14px;
            font-family: sans-serif;
            padding-right: 20px;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .receipt {
                max-width: none;
                padding: 0;
                box-shadow: none;
                border: 0;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button class="print-button" type="button" onclick="window.history.back()"
            style="background: #e5e7eb; color: #374151; margin-right: 10px;">Back</button>
        <button class="print-button" type="button" onclick="window.print()">Print Receipt</button>
    </div>

    <div class="receipt">
        <header>
            <h1><?php echo e($school->name); ?></h1>
            <p class="muted"><span class="font-bold">Phone:</span> <?php echo e($school->phone ?? '555-4399'); ?> &nbsp;|&nbsp;
                <span class="font-bold">Email:</span> <?php echo e($school->email ?? 'school@example.com'); ?></p>
            <p class="muted"><span class="font-bold">Address:</span> <?php echo e($school->address ?? '123 School Road, City'); ?>

            </p>
        </header>

        <div class="title-row">
            <h2><strong>Payment Receipt</strong> Receipt No.
                <?php echo e(str_pad((string) $feePayment->id, 6, '0', STR_PAD_LEFT)); ?></h2>
        </div>

        <table>
            <tbody>
                <tr>
                    <th>Invoice Title</th>
                    <td><?php echo e($feePayment->invoice->invoice_title); ?></td>
                </tr>
                <tr>
                    <th>Receipt Number</th>
                    <td><?php echo e(str_pad((string) $feePayment->id, 6, '0', STR_PAD_LEFT)); ?></td>
                </tr>
                <tr>
                    <th>Invoice Amount</th>
                    <td><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format((float) $feePayment->invoice->total_amount, 2)); ?>

                    </td>
                </tr>
                <tr>
                    <th>Amount Received</th>
                    <td><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format((float) $feePayment->amount, 2)); ?>

                    </td>
                </tr>
                <tr>
                    <th>Due Amount</th>
                    <td><?php echo e(\App\Models\SchoolSetting::getCurrencySymbol()); ?><?php echo e(number_format(max(0, (float) $feePayment->invoice->total_amount - (float) $feePayment->invoice->amount_paid), 2)); ?>

                    </td>
                </tr>
                <tr>
                    <th>Payment Method</th>
                    <td><?php echo e($feePayment->paymentMethod?->name ?? '—'); ?></td>
                </tr>
                <tr>
                    <th>Authorized By</th>
                    <td><?php echo e($feePayment->authorized_by); ?></td>
                </tr>
                <tr>
                    <th>Transaction ID</th>
                    <td><?php echo e($feePayment->transaction_id ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><?php echo e($feePayment->paid_on->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d'))); ?>

                    </td>
                </tr>
                <tr>
                    <th>Student Name</th>
                    <td><?php echo e($feePayment->invoice->student->full_name); ?></td>
                </tr>
                <tr>
                    <th>Enrollment Number</th>
                    <td><?php echo e($feePayment->invoice->student->admission_no); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo e($feePayment->invoice->student->phone ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo e($feePayment->invoice->student->email ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Class</th>
                    <td><?php echo e($feePayment->invoice->student->schoolClass->name); ?></td>
                </tr>
                <tr>
                    <th>Section</th>
                    <td><?php echo e($feePayment->invoice->student->section?->name ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Roll Number</th>
                    <td><?php echo e($feePayment->invoice->student->roll_number ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Father's Name</th>
                    <td><?php echo e($feePayment->invoice->student->father_name ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Father's Phone</th>
                    <td><?php echo e($feePayment->invoice->student->father_phone ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Receiver Name</th>
                    <td><?php echo e($feePayment->authorized_by); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="signature">
            Receiver's Signature
        </div>
    </div>
    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>

</html><?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/accounting/payments/receipt.blade.php ENDPATH**/ ?>