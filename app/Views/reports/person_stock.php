<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h2>Marketing Person Stock Report</h2>

     <div class="mb-3 d-flex justify-content-end gap-2">
        <a href="<?= current_url() . '?' . http_build_query($_GET + ['export' => 'excel']) ?>" class="btn btn-success">
            Export to Excel
        </a>
        <a href="<?= current_url() . '?' . http_build_query($_GET + ['export' => 'pdf']) ?>" class="btn btn-danger">
            Export to PDF
        </a>
    </div>
    <div class="card p-3 mb-4">
        <form method="get" action="<?= base_url('reports/person-stock') ?>" class="row g-3">
            <div class="col-md-3">
                <label>Marketing Person</label>
                <select name="marketing_person_id" class="form-control">
                    <option value="">All</option>
                    <?php foreach ($persons as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($selected_person == $p['id']) ? 'selected' : '' ?>>
                            <?= $p['custom_id'] ?> - <?= $p['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Product</label>
                <select name="product_id" class="form-control">
                    <option value="">All</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?= $prod['id'] ?>" <?= ($selected_product == $prod['id']) ? 'selected' : '' ?>>
                            <?= $prod['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Date From</label>
                <input type="date" name="from_date" class="form-control" value="<?= esc($from_date) ?>">
            </div>
            <div class="col-md-2">
                <label>Date To</label>
                <input type="date" name="to_date" class="form-control" value="<?= esc($to_date) ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <a href="<?= base_url('reports/person-stock') ?>" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>

   


    <table class="table table-bordered table-striped">
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
            <?php $i = 1;
            foreach ($report as $row): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= esc($row['person_id']) ?></td>
                    <td><?= esc($row['person_name']) ?></td>
                    <td><?= esc($row['product_name']) ?></td>
                    <td><?= $row['total_issued'] ?></td>
                    <td><?= $row['total_sold'] ?></td>
                    <td><?= $row['total_issued'] - $row['total_sold'] ?></td>
                    <td><?= number_format($row['total_value_sold'], 2) ?></td>
                    <td><?= $row['latest_sale_date'] ?? '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="4" class="text-end">TOTAL</td>
                <td><?= $total_issued ?></td>
                <td><?= $total_sold ?></td>
                <td><?= $total_issued - $total_sold ?></td>
                <td><?= number_format($total_value, 2) ?></td>
                <td>-</td>
            </tr>
        </tfoot>
    </table>
</div>

<?= $this->endSection() ?>