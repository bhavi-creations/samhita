<?php // Path: app/Views/sales/pdf_template_person_sales.php ?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report - <?= esc($marketingPersonName) ?></title>
    <style>
        /* Embed DejaVu Sans font for better Unicode/Rupee symbol support */
        @font-face {
            font-family: 'DejaVu Sans';
            /* Make sure the path to your font file is correct */
            src: url('<?= FCPATH . 'public/fonts/DejaVuSans.ttf' ?>') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16px;
        }
        h2 { font-size: 14px; margin-bottom: 15px;}
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            word-wrap: break-word; /* Prevents long text from breaking layout */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary-table {
            width: 80%;
            margin: 20px auto; /* Center the summary table */
            font-size: 11px;
            border: 1px solid #ccc;
        }
        .summary-table th, .summary-table td {
            text-align: center;
            padding: 8px;
            border: 1px solid #eee;
        }
        .summary-table th {
            background-color: #e0f7fa; /* Light blue */
        }
        .summary-table tbody tr.total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <h2>Marketing Person: <?= esc($marketingPersonName) ?> (ID: <?= esc($marketingPersonCustomId) ?>)
        <?php if (!empty($filterProductName)): ?>
            <br>Product Filter: <?= esc($filterProductName) ?>
        <?php endif; ?>
    </h2>

    <?php if (isset($summary) && is_array($summary)): ?>
        <table class="summary-table">
            <thead>
                <tr class="table-info">
                    <th></th>
                    <th>Qty Issued</th>
                    <th>Qty Sold</th>
                    <th>Remaining</th>
                    <th>Total Value</th>
                </tr>
            </thead>
            <tbody>
                <tr class="total-row">
                    <td>Total</td>
                    <td><?= number_format($summary['total_qty_issued'] ?? 0) ?></td>
                    <td><?= number_format($summary['total_quantity_sold'] ?? 0) ?></td>
                    <td><?= number_format($summary['total_remaining'] ?? 0) ?></td>
                    <td>₹<?= number_format($summary['overall_total_price'] ?? 0, 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Sale Date</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price/Unit</th>
                <th>Discount</th>
                <th>Total Price</th>
                <th>Customer Name</th>
                <th>Customer Phone</th>
                <th>Customer Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($salesData)): ?>
                <?php $s_no = 1; ?>
                <?php foreach ($salesData as $sale): ?>
                    <tr>
                        <td><?= $s_no++ ?></td>
                        <td><?= esc($sale['date_sold']) ?></td>
                        <td><?= esc($sale['product_name']) ?></td>
                        <td><?= esc($sale['quantity_sold']) ?></td>
                        <td>₹<?= number_format($sale['price_per_unit'], 2) ?></td>
                        <td>₹<?= number_format($sale['discount'], 2) ?></td>
                        <td>₹<?= number_format($sale['total_price'], 2) ?></td>
                        <td><?= esc($sale['customer_name']) ?></td>
                        <td><?= esc($sale['customer_phone']) ?></td>
                        <td><?= esc($sale['customer_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align: center;">No sales records found for this marketing person.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>