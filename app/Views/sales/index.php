<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <h2>Sales List</h2>

    <form method="get" class="row mb-3">
        <div class="col-md-3">
            <select name="product_id" class="form-control">
                <option value="">All Products</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == @$_GET['product_id']) ? 'selected' : '' ?>>
                        <?= esc($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="marketing_person_id" class="form-control">
                <option value="">All Persons</option>
                <?php foreach ($marketing_persons as $mp): ?>
                    <option value="<?= $mp['id'] ?>" <?= ($mp['id'] == @$_GET['marketing_person_id']) ? 'selected' : '' ?>>
                        <?= esc($mp['custom_id']) ?> - <?= esc($mp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date_sold" class="form-control" value="<?= esc(@$_GET['date_sold']) ?>">
        </div>
        <div class="col-md-3 d-flex">
            <button class="btn btn-primary me-2">Filter</button>
            <a href="<?= base_url('sales') ?>" class="btn btn-secondary me-2">Reset</a>
            <a href="<?= base_url('sales/export-excel') ?>" class="btn btn-success me-2">Export Excel</a>
            <a href="<?= base_url('sales/export-pdf') ?>" class="btn btn-danger">Export PDF</a>

        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Marketing Person</th>
                <th>Quantity</th>
                <th>Price/Unit</th>
                <th>Total Price</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= $s['product_name'] ?></td>
                    <td><?= $s['custom_id'] ?> - <?= $s['person_name'] ?></td>
                    <td><?= $s['quantity_sold'] ?></td>
                    <td>₹<?= number_format($s['price_per_unit'], 2) ?></td>
                    <td>₹<?= number_format($s['quantity_sold'] * $s['price_per_unit'], 2) ?></td>
                    <td><?= $s['date_sold'] ?></td>
                    <td>
                        <a href="<?= base_url('sales/edit/' . $s['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?= base_url('sales/delete/' . $s['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>



            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>