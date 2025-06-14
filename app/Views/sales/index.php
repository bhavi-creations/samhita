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
                    <th>ID</th>
                    <th>Product</th>
                    <th>Marketing Person</th>
                    <th>Quantity Sold</th>
                    <th>Price/Unit</th>
                    <th>Discount</th>
                    <th>Total Price</th>
                    <th>Amount Remitted</th>
                    <th>Balance Due</th>
                    <th>Payment Status</th>
                    <th>Date Sold</th>
                    <th>Customer Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sales)): ?>
                    <?php $s_no = 1; ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= esc($sale['id']) ?></td>
                            <td><?= esc($sale['product_name']) ?></td>
                            <td><?= esc($sale['person_name']) ?></td>
                            <td><?= esc($sale['quantity_sold']) ?></td>
                            <td><?= number_format($sale['price_per_unit'], 2) ?></td>
                            <td><?= number_format($sale['discount'], 2) ?></td>
                            <td><?= number_format($sale['total_price'], 2) ?></td>
                            <td class="text-info"><?= number_format($sale['amount_received_from_person'], 2) ?></td>
                            <td class="text-danger"><?= number_format($sale['balance_from_person'], 2) ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                if ($sale['payment_status_from_person'] == 'Paid') {
                                    $statusClass = 'badge bg-success';
                                } elseif ($sale['payment_status_from_person'] == 'Partial') {
                                    $statusClass = 'badge bg-warning text-dark';
                                } else {
                                    $statusClass = 'badge bg-danger';
                                }
                                ?>
                                <span class="<?= $statusClass ?>"><?= esc($sale['payment_status_from_person']) ?></span>
                            </td>
                            <td><?= esc($sale['date_sold']) ?></td>
                            <td><?= esc($sale['customer_name']) ?></td>
                            <td>
                                <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="btn btn-info btn-sm">View</a>
                                <a href="<?= base_url('sales/edit/' . $sale['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <?php if ($sale['payment_status_from_person'] != 'Paid'): ?>
                                    <a href="<?= base_url('sales/remitPayment/' . $sale['id']) ?>" class="btn btn-primary btn-sm">Remit</a>
                                <?php endif; ?>
                                <a href="<?= base_url('sales/delete/' . $sale['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
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