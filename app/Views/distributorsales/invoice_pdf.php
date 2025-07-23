<!DOCTYPE html>
<html>

<head>
    <title>Invoice - <?= esc($sales_order['invoice_number']) ?></title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            margin: 15mm;
        }

        /* Add new styles for images */
        .company-logo {
            max-width: 150px;
            /* Adjust as needed */
            height: auto;
            position: absolute;
            /* Position the logo */
            top: 15mm;
            left: 15mm;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            position: relative;
            /* Needed for absolute positioning of logo if placed inside */
        }

        /* Adjust header title to make space for logo */
        .invoice-header h1 {
            margin: 0;
            font-size: 24pt;
            color: #333;
            /* text-align: right; If logo is left, move title right */
        }

        .company-info,
        .customer-info {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .company-info {
            float: left;
            text-align: left;
        }

        .customer-info {
            float: right;
            text-align: right;
        }

        .company-info p,
        .customer-info p {
            margin: 0;
            line-height: 1.5;
        }

        .invoice-details {
            clear: both;
            margin-bottom: 20px;
        }

        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-details table td {
            padding: 5px 0;
        }

        .invoice-details .label {
            font-weight: bold;
            width: 150px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .item-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .totals-table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
        }

        .totals-table .label {
            font-weight: bold;
            text-align: right;
        }

        .totals-table .amount {
            text-align: right;
        }

        .final-total {
            font-size: 14pt;
            background-color: #e0e0e0;
        }

        .payments-section {
            clear: both;
            margin-top: 30px;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }

        .payments-section h3 {
            margin-bottom: 10px;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payments-table th,
        .payments-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .payments-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .notes-section {
            margin-top: 30px;
            clear: both;
            /* Clear floats before this section */
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
            position: relative;
            /* For stamp/signature positioning */
        }

        .signature-section p {
            margin: 0;
            padding-top: 20px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 200px;
            text-align: center;
        }

        .company-signature {
            max-width: 150px;
            /* Adjust as needed */
            height: auto;
            position: absolute;
            bottom: 30px;
            /* Position above the signature line */
            right: 50px;
            /* Adjust right position */
        }

        .company-stamp {
            max-width: 120px;
            /* Adjust as needed */
            height: auto;
            position: absolute;
            bottom: 10px;
            /* Adjust relative to signature */
            right: 250px;
            /* Adjust position */
            opacity: 0.7;
            /* Make stamp slightly transparent */
        }


        .page-break {
            page-break-after: always;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount-words {
            margin-top: 15px;
            font-weight: bold;
            font-style: italic;
        }
    </style>
</head>

<body>

    <div class="invoice-header">
        <?php if ($company_logo_data): ?>
            <div style="float: left; margin-right: 45px;">
                <img src="<?= esc($company_logo_data) ?>" alt="Company Logo" style="max-width: 100px; height: auto;">
            </div>
        <?php endif; ?>

        <div style="float: left;">
            <h3>INVOICE</h3>
            <h2><strong>SAMHITA SOIL SOLUTIONS</strong></h2>
        </div>

        <div style="clear: both;"></div>

        <p style="text-align: center; margin-top: 10px;">2-46-26/21, Venkat Nager, Kakinada-533003</p>
        <p style="text-align: center; margin: 0;">GSTIN: 37AQFPB2946M1ZN</p>
        <p style="text-align: center; margin: 0;">Phone: 9848549349, 9491822559 | Email: samhithasoilsolutions@gmail.com</p>

    </div>
    <div style="clear: both;"></div>
    <div class="company-info">
        <h3>Invoice From:</h3>
        <p><strong>SAMHITA SOIL SOLUTIONS</strong></p>
        <p>2-46-26/21, Venkat Nager, </p>
        <p>Kakinada-533003</p>
        <p>GSTIN: 37AQFPB2946M1ZN</p>
        <p>Phone: 9848549349 , 9491822559</p>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p><strong><?= esc($distributor['agency_name'] ?? 'N/A') ?></strong></p>
        <p>Coustmer: <?= esc($distributor['owner_name'] ?? 'N/A') ?></p>
        <p><?= esc($distributor['agency_address'] ?? 'N/A') ?></p>
        <p>GSTIN: <?= esc($distributor['agency_gst_number'] ?? 'N/A') ?></p>
        <p>Phone: <?= esc($distributor['owner_phone'] ?? 'N/A') ?></p>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td class="label">Invoice Number:</td>
                <td><?= esc($sales_order['invoice_number']) ?></td>
                <td class="label">Invoice Date:</td>
                <td><?= esc(date('d-M-Y', strtotime($sales_order['invoice_date']))) ?></td>
            </tr>
            <tr>
                <td class="label">Order Date:</td>
                <td><?= esc(date('d-M-Y', strtotime($sales_order['invoice_date']))) ?></td>
                <td class="label">Status:</td>
                <td><?= esc($sales_order['status']) ?></td>

            </tr>

        </table>
    </div>

    <h3>Invoice Items:</h3>
    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="30%">Product</th>
                <th width="10%" class="text-right">Quantity</th>
                <th width="15%" class="text-right">Unit Price</th>
                <th width="10%" class="text-right">GST %</th>
                <th width="15%" class="text-right">Amount (Excl. GST)</th>
                <th width="15%" class="text-right">GST Amount</th>
                <th width="15%" class="text-right">Total (Incl. GST)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sales_order_items)): ?>
                <?php $i = 1; ?>
                <?php foreach ($sales_order_items as $item): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td><?= esc($item['product_name']) ?></td>
                        <td class="text-right"><?= esc($item['quantity']) ?></td>
                        <td class="text-right">₹<?= number_format($item['unit_price_at_sale'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['gst_rate_at_sale'], 2) ?>%</td>
                        <td class="text-right">₹<?= number_format($item['item_total_before_gst'], 2) ?></td>
                        <td class="text-right">₹<?= number_format($item['item_gst_amount'], 2) ?></td>
                        <td class="text-right">₹<?= number_format($item['item_final_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No items found for this invoice.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal (Excl. GST):</td>
            <td class="amount">₹<?= number_format($sales_order['total_amount_before_gst'], 2) ?></td>
        </tr>
        <tr>
            <td class="label">Total GST:</td>
            <td class="amount">₹<?= number_format($sales_order['total_gst_amount'], 2) ?></td>
        </tr>
        <tr>
            <td class="label">Gross Total:</td>
            <td class="amount">₹<?= number_format($sales_order['total_amount_before_gst'] + $sales_order['total_gst_amount'], 2) ?></td>
        </tr>
        <tr>
            <td class="label">Discount:</td>
            <td class="amount">₹<?= number_format($sales_order['discount_amount'], 2) ?></td>
        </tr>
        <tr class="final-total">
            <td class="label">Grand Total:</td>
            <td class="amount"><strong>₹<?= number_format($sales_order['final_total_amount'], 2) ?></strong></td>
        </tr>
        <tr>
            <td class="label">Amount Paid:</td>
            <td class="amount">₹<?= number_format($sales_order['amount_paid'], 2) ?></td>
        </tr>
        <tr>
            <td class="label">Amount Due:</td>
            <td class="amount"><strong>₹<?= number_format($sales_order['due_amount'], 2) ?></strong></td>
        </tr>
    </table>

    <div class="amount-words">
        Amount in words: <?= convertNumberToWords($sales_order['final_total_amount']) ?> Rupees Only.
    </div>

    <div class="payments-section">
        <h3>Payment History:</h3>
        <?php if (empty($payments)): ?>
            <p>No payments recorded for this invoice yet.</p>
        <?php else: ?>
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= esc(date('d-M-Y', strtotime($payment['payment_date']))) ?></td>
                            <td>₹<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= esc($payment['payment_method'] ?? 'N/A') ?></td>
                            <td><?= esc($payment['transaction_id'] ?? 'N/A') ?></td>
                            <td><?= esc($payment['notes'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="notes-section">
        <h3>Notes:</h3>
        <p><?= esc($sales_order['notes'] ?? 'N/A') ?></p>
    </div>



    <div class="signature-section">
        <div style="float: right; text-align: center; position: relative; width: 350px; height: 150px;">

            <?php if ($company_stamp_data): ?>
                <img src="<?= esc($company_stamp_data) ?>" alt="Company Stamp"
                    style="max-width: 100px; height: auto; position: absolute; bottom: 80px; left: 50px; opacity: 0.7;">
            <?php endif; ?>

            <?php if ($company_signature_data): ?>
                <img src="<?= esc($company_signature_data) ?>" alt="Company Signature"
                    style="max-width: 150px; height: auto; position: absolute; bottom: 80px; right: 50px;">
            <?php endif; ?>

            <p style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); 
                  border-top: 1px solid #000; width: 200px; text-align: center; margin: 0;">
                Authorized Signature
            </p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer" style="position: fixed; bottom: 15mm; left: 15mm; right: 15mm; text-align: center; font-size: 8pt; color: #555;">
        Thank you for your business!
    </div>
</body>

</html>

<?php
// Helper function to convert number to words (for Indian Rupees)
// You might want to put this in a helper file, but for now, include it here.
if (!function_exists('convertNumberToWords')) {
    function convertNumberToWords($number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore'); // For Indian system

        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? " and " . ($words[$decimal - $decimal % 10] ?? $words[floor($decimal / 10) * 10]) . " " . ($words[$decimal % 10] ?? '') . " Paise" : '';
        return ucfirst($Rupees) . $paise;
    }
}
?>