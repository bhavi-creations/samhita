<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <div>
            <a href="<?= base_url('stock-out/issue') ?>" class="btn btn-primary me-2"><i class="fas fa-plus"></i> Issue New Stock Out</a>
            <a href="<?= base_url('stock-out/export-excel') . '?' . http_build_query($_GET) ?>" class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Export Excel</a>
            <a href="<?= base_url('stock-out/export-pdf') . '?' . http_build_query($_GET) ?>" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Export PDF</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filter Stock Out Records</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-select">
                        <option value="">All Products</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= set_select('product_id', $p['id'], ($p['id'] == $selectedProductId)) ?>>
                                <?= esc($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($transactionTypes as $type): ?>
                            <option value="<?= esc($type) ?>" <?= set_select('transaction_type', $type, ($type == $selectedTransactionType)) ?>>
                                <?= esc($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="issued_date_start" class="form-label">Date From</label>
                    <input type="date" name="issued_date_start" id="issued_date_start" class="form-control" value="<?= esc($selectedIssuedDateStart) ?>">
                </div>
                <div class="col-md-3">
                    <label for="issued_date_end" class="form-label">Date To</label>
                    <input type="date" name="issued_date_end" id="issued_date_end" class="form-control" value="<?= esc($selectedIssuedDateEnd) ?>">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?= base_url('stock-out') ?>" class="btn btn-secondary mt-3 ms-2"><i class="fas fa-sync-alt"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>


    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($stockOutRecords)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product Name</th>
                                <th>Quantity Out</th>
                                <th>Transaction Type</th>
                                <!-- <th>Transaction ID</th> -->
                                <!-- <th>Related To</th> -->
                                <th>Issued Date</th>
                                <!-- <th>Notes</th> -->
                                <th>Recorded At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; ?>
                            <?php foreach ($stockOutRecords as $record): ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($record['product_name']) ?></td>
                                    <td><?= esc($record['quantity_out']) ?></td>
                                    <td><?= esc($record['transaction_type']) ?></td>
                                    <!-- <td><?= esc($record['transaction_id'] ?? 'N/A') ?></td> -->
                                    <!-- <td>
                                        <?php if ($record['transaction_type'] === 'Sale' && !empty($record['related_transaction_details'])): ?>
                                            Customer: <?= esc($record['related_transaction_details']['customer_name']) ?><br>
                                            Marketing Person: <?= esc($record['related_transaction_details']['marketing_person']) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td> -->
                                    <td><?= esc($record['issued_date']) ?></td>
                                    <!-- <td><?= esc($record['notes'] ?? 'N/A') ?></td> -->
                                    <td><?= esc($record['created_at']) ?></td>
                                    <td>
                                        <a href="<?= base_url('stock-out/view/' . $record['id']) ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No stock out records found with the applied filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>