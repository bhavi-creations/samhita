<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('stock-out/issue') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Issue New Stock Out
        </a>
    </div>

    <?= session()->getFlashdata('success') ?>
    <?= session()->getFlashdata('error') ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Filter Stock Out Records</h5>
        </div>
        <div class="card-body">
            <form action="<?= base_url('stock-out') ?>" method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-select">
                        <option value="">All Products</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= esc($product['id']) ?>" <?= (string)$selectedProductId === (string)$product['id'] ? 'selected' : '' ?>>
                                <?= esc($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($transactionTypes as $type): ?>
                            <option value="<?= esc($type) ?>" <?= (string)$selectedTransactionType === (string)$type ? 'selected' : '' ?>>
                                <?= esc($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="issued_date_start" class="form-label">Issued Date (Start)</label>
                    <input type="date" name="issued_date_start" id="issued_date_start" class="form-control" value="<?= esc($selectedIssuedDateStart) ?>">
                </div>
                <div class="col-md-2">
                    <label for="issued_date_end" class="form-label">Issued Date (End)</label>
                    <input type="date" name="issued_date_end" id="issued_date_end" class="form-control" value="<?= esc($selectedIssuedDateEnd) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('stock-out') ?>" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Clear Filters</a>
                </div>
                <div class="col-auto ms-auto">
                    <a href="<?= base_url('stock-out/export-excel') ?>?<?= http_build_query($request->getGet()) ?>" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Excel</a>
                    <a href="<?= base_url('stock-out/export-pdf') ?>?<?= http_build_query($request->getGet()) ?>" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Export PDF</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Stock Out List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Product Name</th>
                            <th>Quantity Out</th>
                            <th>Transaction Type</th>
                            <th>Distributed To</th>
                            <th>Issued Date</th>
                            <th>Notes</th>
                            <th>Recorded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stockOutRecords)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No stock out records found for the selected filters.</td>
                            </tr>
                        <?php else: ?>
                            <?php $sno = 1; ?>
                            <?php foreach ($stockOutRecords as $record): ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($record['product_name']) ?></td>
                                    <td><?= esc($record['quantity_out']) ?></td>
                                    <td><?= esc($record['transaction_type']) ?></td>
                                    <td>
                                        <?php if ($record['transaction_type'] === 'distributor_sale' && !empty($record['related_transaction_details'])): ?>
                                            <strong>Distributor:</strong> <?= esc($record['related_transaction_details']['agency_name'] ?? 'N/A') ?><br>
                                            <small>(Owner: <?= esc($record['related_transaction_details']['owner_name'] ?? 'N/A') ?>)</small>
                                        <?php elseif (($record['transaction_type'] === 'Sale' || $record['transaction_type'] === 'marketing_distribution') && !empty($record['related_transaction_details'])): ?>
                                            <strong>Marketing Person:</strong> <?= esc($record['related_transaction_details']['marketing_person_name'] ?? 'N/A') ?><br>
                                            <small>(ID: <?= esc($record['related_transaction_details']['marketing_person_custom_id'] ?? 'N/A') ?>)</small>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($record['issued_date']) ?></td>
                                    <td><?= esc($record['notes'] ?? 'N/A') ?></td>
                                    <td><?= esc($record['created_at']) ?></td>
                                    <td>
                                        <a href="<?= base_url('stock-out/view/' . $record['id']) ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
