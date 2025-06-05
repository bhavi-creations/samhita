<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h3>Sales Export</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Marketing Person</th>
                <th>Quantity Sold</th>
                <th>Price Per Unit</th>
                <th>Date Sold</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= esc($sale['id']) ?></td>
                    <td><?= esc($sale['product_name']) ?></td>
                    <td><?= esc($sale['person_name']) ?></td>
                    <td><?= esc($sale['quantity_sold']) ?></td>
                    <td><?= esc($sale['price_per_unit']) ?></td>
                    <td><?= esc($sale['date_sold']) ?></td>
                    <td><?= esc($sale['quantity_sold'] * $sale['price_per_unit']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
