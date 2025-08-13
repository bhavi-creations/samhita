<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h1>Vendor Report</h1>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= base_url('vendors/vendorReport') ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="vendor_id">Vendor</label>
                        <select id="vendor_id" name="vendor_id" class="form-control">
                            <option value="">All Vendors</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= esc($vendor['id']) ?>" <?= (isset($_GET['vendor_id']) && $_GET['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                                    <?= esc($vendor['agency_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="product_id">Product</label>
                        <select id="product_id" name="product_id" class="form-control">
                            <option value="">All Products</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= esc($product['id']) ?>" <?= (isset($_GET['product_id']) && $_GET['product_id'] == $product['id']) ? 'selected' : '' ?>>
                                    <?= esc($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?= esc($_GET['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?= esc($_GET['end_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end mb-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table -->
    <?php if (empty($stock_in_entries)): ?>
        <div class="alert alert-info">No stock-in entries found for the selected filters.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Vendor Name</th>
                        <th>Product Name</th>
                        <th>Date Received</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($stock_in_entries as $entry): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= esc($entry['vendor_name']) ?></td>
                            <td><?= esc($entry['product_name']) ?></td>
                            <td><?= esc($entry['date_received']) ?></td>
                            <td><?= esc($entry['quantity']) ?></td>
                            <td><?= esc($entry['purchase_price']) ?></td>
                            <td><?= esc($entry['quantity'] * $entry['purchase_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
