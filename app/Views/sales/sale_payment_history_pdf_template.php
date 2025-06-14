<!DOCTYPE html>
<html>
<head>
    <title>Payment History for Sale #<?= esc($sale['id']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        h1, h2, h3 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { margin-bottom: 20px; }
        .summary { margin-bottom: 30px; }
        .text-info { color: #17a2b8; }
        .text-danger { color: #dc3545; }
        .text-success { color: #28a745; }
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .bg-success { background-color: #28a745; color: #fff; }
        .bg-warning { background-color: #ffc107; color: #212529; }
        .bg-danger { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <h1>Payment History for Sale #<?= esc($sale['id']) ?></h1>

    <div class="summary">
        <h2>Sale Information</h2>
        <table>
            <tr>
                <td><strong>Product:</strong></td>
                <td><?= esc($sale['product_name']) ?></td>
                <td><strong>Total Sale Price:</strong></td>
                <td class="text-success">₹<?= number_format($sale['total_price'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>Customer Name:</strong></td>
                <td><?= esc($sale['customer_name']) ?></td>
                <td><strong>Total Amount Remitted:</strong></td>
                <td class="text-info">₹<?= number_format($sale['amount_received_from_person'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>Sale Date:</strong></td>
                <td><?= esc($sale['date_sold']) ?></td>
                <td><strong>Balance Due:</strong></td>
                <td class="text-danger">₹<?= number_format($sale['balance_from_person'], 2) ?></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><strong>Payment Status:</strong></td>
                <td>
                    <span class="badge
                        <?php
                        if ($sale['payment_status_from_person'] == 'Paid') echo 'bg-success';
                        elseif ($sale['payment_status_from_person'] == 'Partial') echo 'bg-warning text-dark';
                        else echo 'bg-danger';
                        ?>">
                        <?= esc($sale['payment_status_from_person']) ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <h2>Individual Payment Transactions</h2>
    <?php if (!empty($payments)): ?>
        <table>
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Payment Date</th>
                    <th>Amount Paid</th>
                    <th>Method</th>
                    <th>Remarks</th>
                    <th>Recorded At</th>
                </tr>
            </thead>
            <tbody>
                <?php $s_no = 1; ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= $s_no++ ?></td>
                        <td><?= esc($payment['payment_date']) ?></td>
                        <td>₹<?= number_format($payment['amount_paid'], 2) ?></td>
                        <td><?= esc($payment['payment_method'] ?: 'N/A') ?></td>
                        <td><?= esc($payment['remarks'] ?: 'N/A') ?></td>
                        <td><?= esc($payment['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No payments recorded for this sale yet.</p>
    <?php endif; ?>

    <p style="font-size: 8pt; text-align: center; margin-top: 50px;">Generated on <?= date('Y-m-d H:i:s') ?> (Kakinada, Andhra Pradesh, India)</p>
</body>
</html>