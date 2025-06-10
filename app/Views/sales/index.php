<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <h2>Sales List</h2>

    <form method="get" class="row mb-3">
        <div class="col-md-3">
            <label for="product_id" class="form-label visually-hidden">Product</label>
            <select name="product_id" id="product_id" class="form-control">
                <option value="">All Products</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == @$_GET['product_id']) ? 'selected' : '' ?>>
                        <?= esc($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="marketing_person_id" class="form-label visually-hidden">Marketing Person</label>
            <select name="marketing_person_id" id="marketing_person_id" class="form-control">
                <option value="">All Persons</option>
                <?php foreach ($marketing_persons as $mp): ?>
                    <option value="<?= $mp['id'] ?>" <?= ($mp['id'] == @$_GET['marketing_person_id']) ? 'selected' : '' ?>>
                        <?= esc($mp['custom_id']) ?> - <?= esc($mp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="date_sold" class="form-label visually-hidden">Date Sold</label>
            <input type="date" name="date_sold" id="date_sold" class="form-control" value="<?= esc(@$_GET['date_sold']) ?>">
        </div>
        <div class="col-md-3 d-flex">
            <button class="btn btn-primary me-2">Filter</button>
            <a href="<?= base_url('sales') ?>" class="btn btn-secondary me-2">Reset</a>
            <a href="<?= base_url('sales/export-excel') ?>" class="btn btn-success me-2">Export Excel</a>
            <a href="<?= base_url('sales/export-pdf') ?>" class="btn btn-danger">Export PDF</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Product</th>
                    <th>Marketing Person</th>
                    <th>Quantity</th>
                    <th>Price/Unit</th>
                    <th>Discount</th>
                    <th>Total Price</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sales)): ?>
                    <?php $s_no = 1; ?>
                    <?php foreach ($sales as $s): ?>
                        <tr>
                            <td><?= $s_no++ ?></td>
                            <td><?= esc($s['product_name']) ?></td>
                            <td><?= esc($s['custom_id']) ?> - <?= esc($s['person_name']) ?></td>
                            <td><?= esc($s['quantity_sold']) ?></td>
                            <td>₹<?= number_format($s['price_per_unit'], 2) ?></td>
                            <td>₹<?= number_format($s['discount'], 2) ?></td>
                            <td>₹<?= number_format($s['total_price'], 2) ?></td>
                            <td><?= esc($s['date_sold']) ?></td>
                            <td>
                                <a href="<?= base_url('sales/view/' . $s['id']) ?>" class="btn btn-sm btn-info me-1" title="View Details">View</a>
                                <a href="<?= base_url('sales/edit/' . $s['id']) ?>" class="btn btn-sm btn-warning me-1" title="Edit Sale">Edit</a>
                                <a href="<?= base_url('sales/delete/' . $s['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this sale entry?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No sales records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>