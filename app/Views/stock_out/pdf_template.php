<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1><?= esc($title) ?></h1>

    <table>
        <thead>
            <tr>
                <th>S.No.</th> <th>Product Name</th>
                <th>Quantity Out</th>
                <th>Transaction Type</th>
                <th>Transaction ID</th>
                <th>Issued Date</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stockOuts) && is_array($stockOuts)): ?>
                <?php $s_no = 1; ?> <?php foreach ($stockOuts as $record): ?>
                    <tr>
                        <td><?= $s_no++ ?></td> <td><?= esc($record['product_name']) ?></td>
                        <td class="text-center"><?= esc($record['quantity_out']) ?></td>
                        <td><?= esc($record['transaction_type']) ?></td>
                        <td><?= esc($record['transaction_id'] ?? 'N/A') ?></td>
                        <td><?= esc($record['issued_date']) ?></td>
                        <td><?= esc($record['notes'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No Stock Out records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="text-right">Generated on: <?= date('Y-m-d H:i:s') ?></p>
</body>
</html>