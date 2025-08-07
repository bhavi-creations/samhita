<!DOCTYPE html>
<html>
<head>
    <title>Stock In Details - ID <?= esc($stock_entry['id']) ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; margin: 20mm; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; padding: 0; font-size: 18pt; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .details-table th { background-color: #f2f2f2; font-weight: bold; width: 30%; }
        .section-title { font-size: 14pt; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .financial-summary { border: 1px solid #000; padding: 15px; background-color: #f9f9f9; }
        .financial-summary p { margin: 5px 0; }
        .financial-summary strong { display: inline-block; width: 150px; }
        .text-danger { color: #dc3545; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock In Details</h1>
        <p>Entry ID: <?= esc($stock_entry['id']) ?></p>
        <p>Date Generated: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <div class="section-title">General Information</div>
    <table class="details-table">
        <tr>
            <th>Date Received</th>
            <td><?= esc($stock_entry['date_received']) ?></td>
        </tr>
        <tr>
            <th>Vendor</th>
            <td><?= esc($stock_entry['vendor_agency_name']) ?> (<?= esc($stock_entry['vendor_name']) ?>)</td>
        </tr>
        <tr>
            <th>Notes</th>
            <td><?= esc($stock_entry['notes'] ?? 'N/A') ?></td>
        </tr>
    </table>

    <div class="section-title">Product Details</div>
    <table class="details-table">
        <tr>
            <th>Product</th>
            <!-- --- CHANGE START --- -->
            <!-- Display product name from purchased_products -->
            <td><?= esc($stock_entry['product_name']) ?></td>
            <!-- --- CHANGE END --- -->
        </tr>
        <tr>
            <th>Quantity</th>
            <!-- --- CHANGE START --- -->
            <!-- Display unit name from purchased_products' unit -->
            <td><?= esc($stock_entry['quantity']) ?> <?= esc($stock_entry['unit_name']) ?></td>
            <!-- --- CHANGE END --- -->
        </tr>
        <tr>
            <th>Purchase Price (per unit)</th>
            <td>₹<?= number_format($stock_entry['purchase_price'], 2) ?></td>
        </tr>
    </table>

    <div class="section-title">Financial Summary</div>
    <div class="financial-summary">
        <p><strong>GST Rate:</strong> <?= esc($stock_entry['gst_rate_name'] ?? 'N/A') ?> (<?= esc($stock_entry['gst_rate_percentage'] ?? '0') ?>%)</p>
        <p><strong>Sub Total (before GST):</strong> ₹<?= number_format($stock_entry['total_amount_before_gst'] ?? 0, 2) ?></p>
        <p><strong>GST Amount:</strong> ₹<?= number_format($stock_entry['gst_amount'] ?? 0, 2) ?></p>
        <p><strong>Grand Total (incl. GST):</strong> <span class="fw-bold">₹<?= number_format($stock_entry['grand_total'] ?? 0, 2) ?></span></p>
        <p><strong>Amount Paid:</strong> ₹<?= number_format($stock_entry['amount_paid'] ?? 0, 2) ?></p>
        <p><strong>Amount Pending:</strong> <span class="fw-bold <?= (($stock_entry['amount_pending'] ?? 0) > 0) ? 'text-danger' : '' ?>">₹<?= number_format($stock_entry['amount_pending'] ?? 0, 2) ?></span></p>
    </div>

    <div class="section-title" style="page-break-before: always;">Payment Transactions</div>
<?php if (empty($stock_payments)): ?>
    <p style="text-align: center;">No payments recorded for this entry yet.</p>
<?php else: ?>
    <table class="details-table">
        <thead style="background-color: #f2f2f2;">
            <tr>
                <th>S.No.</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Notes</th>
                <th>Recorded At</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($stock_payments as $payment): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= esc($payment['payment_date']) ?></td>
                    <td>₹<?= number_format($payment['payment_amount'], 2) ?></td>
                    <td><?= esc($payment['notes']) ?></td>
                    <td><?= esc($payment['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

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
