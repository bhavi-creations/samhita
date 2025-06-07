<!DOCTYPE html>
<html>
<head>
    <title>Vendor Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #666; padding: 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Vendor Report</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vendor</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Purchase Price</th>
                <th>Selling Price</th>
                <th>Date</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; $totalQty = 0; $totalPrice = 0; ?>
            <?php foreach ($stock_entries as $entry): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= esc($entry['vendor_name']) ?> (<?= esc($entry['vendor_agency_name']) ?>)</td>
                    <td><?= esc($entry['product_name']) ?></td>
                    <td><?= esc($entry['quantity']) ?></td>
                    <td><?= esc($entry['unit_name']) ?></td>
                    <td><?= esc($entry['purchase_price']) ?></td>
                    <td><?= esc($entry['selling_price']) ?></td>
                    <td><?= esc($entry['date_received']) ?></td>
                    <td><?= esc($entry['notes']) ?></td>
                </tr>
                <?php
                    $totalQty += $entry['quantity'];
                    $totalPrice += $entry['quantity'] * $entry['purchase_price'];
                ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th><?= $totalQty ?></th>
                <th></th>
                <th colspan="4">Total Purchase Price: ₹<?= number_format($totalPrice, 2) ?></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
