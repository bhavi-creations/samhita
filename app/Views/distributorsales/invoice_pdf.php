<!DOCTYPE html>
<html>

<head>
    <title>Invoice - <?= esc($sales_order['invoice_number']) ?></title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            /* Ensure Rupee symbol support */
            font-size: 10pt;
            margin: 15mm;
            /* Adjust margins as needed */
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .invoice-header h1 {
            margin: 0;
            font-size: 24pt;
            color: #333;
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
            /* Adjust as needed */
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
            /* Align to right */
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
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
        }

        .signature-section p {
            margin: 0;
            padding-top: 20px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 200px;
            text-align: center;
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
        <h1>INVOICE</h1>
        <p><strong>[Your Company Name]</strong></p>
        <p>[Your Company Address Line 1]</p>
        <p>[Your Company Address Line 2]</p>
        <p>GSTIN: [Your Company GSTIN]</p>
        <p>Phone: [Your Company Phone Number] | Email: [Your Company Email]</p>
    </div>

    <div class="company-info">
        <h3>Invoice From:</h3>
        <p><strong>[Your Company Name]</strong></p>
        <p>[Your Company Address Line 1]</p>
        <p>[Your Company Address Line 2]</p>
        <p>GSTIN: [Your Company GSTIN]</p>
        <p>Phone: [Your Company Phone Number]</p>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p><strong><?= esc($distributor['agency_name'] ?? 'N/A') ?></strong></p>
        <p>Attn: <?= esc($distributor['owner_name'] ?? 'N/A') ?></p>
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
                <td class="label">Payment Type:</td>
                <td><?= esc($sales_order['payment_type'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td class="label">Status:</td>
                <td><?= esc($sales_order['status']) ?></td>
                <td class="label">Due Date:</td>
                <td><?php // You might want to calculate a due date here, e.g., 30 days from invoice date
                    echo esc(date('d-M-Y', strtotime($sales_order['invoice_date'] . ' + 30 days')));
                    ?></td>
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
        <p>Authorized Signature</p>
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