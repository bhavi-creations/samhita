<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1>Vendor Supply Report</h1>

<div class="d-flex">

    
    <a href="<?= base_url('vendors/vendorReportExport') ?>" class="btn btn-success mb-3 mx-4">
        Export CSV
    </a>
    
    
    <form method="get" action="<?= base_url('reports/vendor-report-pdf') ?>" target="_blank">
        <input type="hidden" name="vendor_id" value="<?= esc($selected_vendor_id) ?>">
        <input type="hidden" name="product_id" value="<?= esc($selected_product_id) ?>">
        <input type="hidden" name="start_date" value="<?= esc($start_date) ?>">
        <input type="hidden" name="end_date" value="<?= esc($end_date) ?>">
        <button type="submit" class="btn btn-danger">Export PDF</button>
    </form>
</div>

<form method="get" action="<?= base_url('vendors/vendorReport') ?>" class="row g-3 mb-4">
    <div class="col-md-3">
        <label for="vendor_id">Vendor:</label>
        <select name="vendor_id" class="form-select">
            <option value="">All Vendors</option>
            <?php foreach ($vendors as $vendor): ?>
                <option value="<?= $vendor['id'] ?>" <?= ($vendor['id'] == ($_GET['vendor_id'] ?? '')) ? 'selected' : '' ?>>
                    <?= esc($vendor['agency_name']) ?> - <?= esc($vendor['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label for="product_id">Product:</label>
        <select name="product_id" class="form-select">
            <option value="">All Products</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= $product['id'] ?>" <?= ($product['id'] == ($_GET['product_id'] ?? '')) ? 'selected' : '' ?>>
                    <?= esc($product['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" class="form-control" value="<?= esc($_GET['start_date'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" class="form-control" value="<?= esc($_GET['end_date'] ?? '') ?>">
    </div>

    <div class="col-md-1 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>

    <div class="col-md-1 d-flex align-items-end">
        <a href="<?= base_url('vendors/vendorReport') ?>" class="btn btn-secondary w-100">Reset</a>
    </div>
</form>



<table class="table table-bordered">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Vendor</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Purchase Price</th>
           
            <th>Date Received</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1;
        foreach ($stock_entries as $entry): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($entry['vendor_agency_name']) ?> (<?= esc($entry['vendor_name']) ?>)</td>
                <td><?= esc($entry['product_name']) ?></td>
                <td><?= esc($entry['quantity']) ?></td>
                <td><?= esc($entry['unit_name']) ?></td>
                <td><?= esc($entry['purchase_price']) ?></td>
               
                <td><?= esc($entry['date_received']) ?></td>
                <td><?= esc($entry['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->endSection() ?>