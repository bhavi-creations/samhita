<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">Marketing Person Stock Report</h3>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Person ID</th>
                <th>Name</th>
                <th>Product</th>
                <th>Qty Issued</th>
                <th>Qty Sold</th>
                <th>Remaining</th>
                <th>Total Value</th>
                <th>Latest Sale Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($report as $row): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['person_id'] ?></td>
                    <td><?= $row['person_name'] ?></td>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['total_issued'] ?></td>
                    <td><?= $row['total_sold'] ?></td>
                    <td><?= $row['total_issued'] - $row['total_sold'] ?></td>
                    <td><?= number_format($row['total_value_sold'], 2) ?></td>
                    <td><?= $row['latest_sale_date'] ?? '-' ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight:bold;">
                <td colspan="4">TOTAL</td>
                <td><?= $total_issued ?></td>
                <td><?= $total_sold ?></td>
                <td><?= $total_issued - $total_sold ?></td>
                <td><?= number_format($total_value, 2) ?></td>
                <td>-</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
