<!DOCTYPE html>
<html>
<head>
    <title>Distributor Sales Orders Report</title>
    <style>
        body {
            /* Use a font that supports the Rupee symbol, like DejaVu Sans */
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            margin: 20mm; /* Standard margin for print */
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>Distributor Sales Orders Report</h1>
    
    <table>
        <thead>
            <tr>
                <th>Invoice No.</th>
                <th>Distributor</th>
                <th>Inv. Date</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Amount Paid</th>
                <th class="text-right">Due Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sales_orders)): ?>
                <?php 
                $grandTotalAmount = 0;
                $grandAmountPaid = 0;
                $grandDueAmount = 0;
                ?>
                <?php foreach ($sales_orders as $order): ?>
                    <tr>
                        <td><?= esc($order['invoice_number']) ?></td>
                        <td><?= esc($order['agency_name']) ?></td>
                        <td><?= esc(date('Y-m-d', strtotime($order['invoice_date']))) ?></td>
                        <td class="text-right">₹<?= number_format($order['final_total_amount'], 2) ?></td>
                        <td class="text-right">₹<?= number_format($order['amount_paid'], 2) ?></td>
                        <td class="text-right">₹<?= number_format($order['due_amount'], 2) ?></td>
                        <td><?= esc($order['status']) ?></td>
                    </tr>
                    <?php
                    $grandTotalAmount += $order['final_total_amount'];
                    $grandAmountPaid += $order['amount_paid'];
                    $grandDueAmount += $order['due_amount'];
                    ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-right"><strong>Grand Totals:</strong></td>
                    <td class="text-right"><strong>₹<?= number_format($grandTotalAmount, 2) ?></strong></td>
                    <td class="text-right"><strong>₹<?= number_format($grandAmountPaid, 2) ?></strong></td>
                    <td class="text-right"><strong>₹<?= number_format($grandDueAmount, 2) ?></strong></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No sales orders found for this report.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Report generated on <?= date('Y-m-d H:i:s') ?>
    </div>
</body>
</html>