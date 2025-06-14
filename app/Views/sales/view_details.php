<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sales Details for <span class="text-primary"><?= esc($marketingPersonName) ?></span> (ID: <?= esc($marketingPersonCustomId) ?>)</h2>
        <a href="<?= base_url('sales') ?>" class="btn btn-secondary">Back to Sales List</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Details of Selected Sale #<?= esc($specificSale['id']) ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Product:</strong> <?= esc($specificSale['product_name']) ?></p>
                    <p><strong>Quantity Sold:</strong> <?= esc($specificSale['quantity_sold']) ?></p>
                    <p><strong>Price Per Unit:</strong> ₹<?= number_format($specificSale['price_per_unit'], 2) ?></p>
                    <p><strong>Discount:</strong> ₹<?= number_format($specificSale['discount'], 2) ?></p>
                    <p><strong>Total Sale Price:</strong> <span class="fw-bold text-success">₹<?= number_format($specificSale['total_price'], 2) ?></span></p>
                    <p><strong>Sale Date:</strong> <?= esc($specificSale['date_sold']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Customer Name:</strong> <?= esc($specificSale['customer_name']) ?></p>
                    <p><strong>Customer Phone:</strong> <?= esc($specificSale['customer_phone']) ?></p>
                    <p><strong>Customer Address:</strong> <?= esc($specificSale['customer_address']) ?></p>
                    <hr>
                    <p><strong>Amount Remitted:</strong> <span class="fw-bold text-info">₹<?= number_format($specificSale['amount_received_from_person'], 2) ?></span></p>
                    <p><strong>Balance Due:</strong> <span class="fw-bold text-danger">₹<?= number_format($specificSale['balance_from_person'], 2) ?></span></p>
                    <p>
                        <strong>Payment Status:</strong>
                        <span class="badge
                            <?php
                            if ($specificSale['payment_status_from_person'] == 'Paid') echo 'bg-success';
                            elseif ($specificSale['payment_status_from_person'] == 'Partial') echo 'bg-warning text-dark';
                            else echo 'bg-danger';
                            ?>">
                            <?= esc($specificSale['payment_status_from_person']) ?>
                        </span>
                    </p>
                    <?php if ($specificSale['last_remittance_date']): ?>
                        <p><strong>Last Remittance Date:</strong> <?= esc($specificSale['last_remittance_date']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <a href="<?= base_url('sales/edit/' . $specificSale['id']) ?>" class="btn btn-warning me-2">Edit Sale</a>
                <a href="<?= base_url('sales/delete/' . $specificSale['id']) ?>" class="btn btn-danger me-2" onclick="return confirm('Are you sure you want to delete this sale? This action cannot be undone.');">Delete Sale</a>
                <?php if ($specificSale['payment_status_from_person'] != 'Paid'): ?>
                    <a href="<?= base_url('sales/record-sale-payment-form/' . $specificSale['id']) ?>" class="btn btn-primary">Record Payment for Sale</a>
                <?php else: ?>
                    <button class="btn btn-success" disabled><i class="fas fa-check-circle me-1"></i> Fully Remitted</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    ---

    <div class="card mb-4">
        <div class="card-header">
            <h4>Sales Turnover Summary
                <?php if ($selectedProductId): ?>
                    <small>(Filtered for Product: <?= esc($productsForFilter[array_search($selectedProductId, array_column($productsForFilter, 'id'))]['name'] ?? 'N/A') ?>)</small>
                <?php endif; ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center">
                    <thead>
                        <tr class="table-info">
                            <th></th>
                            <th>Qty Issued</th>
                            <th>Qty Sold</th>
                            <th>Remaining</th>
                            <th>Total Value</th>
                            <th>Amount Remitted</th>
                            <th>Balance Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td><?= number_format($summary['total_qty_issued'] ?? 0) ?></td>
                            <td><?= number_format($summary['total_quantity_sold'] ?? 0) ?></td>
                            <td><?= number_format($summary['total_remaining'] ?? 0) ?></td>
                            <td>₹<?= number_format($summary['overall_total_price'] ?? 0, 2) ?></td>
                            <td class="text-info">₹<?= number_format($summary['total_amount_remitted'] ?? 0, 2) ?></td>
                            <td class="text-danger">₹<?= number_format($summary['total_balance_due'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    ---

    <div class="row mb-3 align-items-end">
        <div class="col-md-4">
            <form method="get" action="<?= base_url('sales/view/' . $specificSaleId) ?>">
                <label for="filter_product_id" class="form-label">Filter All Sales by Product:</label>
                <div class="input-group">
                    <select name="product_id" id="filter_product_id" class="form-select">
                        <option value="">All Products</option>
                        <?php foreach ($productsForFilter as $product): ?>
                            <option value="<?= $product['id'] ?>" <?= ($product['id'] == $selectedProductId) ? 'selected' : '' ?>>
                                <?= esc($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    <a href="<?= base_url('sales/view/' . $specificSaleId) ?>" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
        <div class="col-md-8 text-end">
            <?php
            // Build the query string for exports
            $exportQueryParams = ['product_id' => $selectedProductId];
            $exportQueryString = http_build_query(array_filter($exportQueryParams));
            ?>
            <a href="<?= base_url('sales/export-person-sales-excel/' . $marketingPersonId . '?' . $exportQueryString) ?>" class="btn btn-success me-2"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
            <a href="<?= base_url('sales/export-person-sales-pdf/' . $marketingPersonId . '?' . $exportQueryString) ?>" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> Export PDF</a>
        </div>
    </div>
    ---

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Sale Date</th>
                    <th>Product</th>
                    <th>Qty Sold</th>
                    <th>Price/Unit</th>
                    <th>Discount</th>
                    <th>Total Price</th>
                    <th>Amount Remitted</th>
                    <th>Balance Due</th>
                    <th>Payment Status</th>
                    <th>Customer Name</th>
                    <th>Customer Phone</th>
                    <th>Customer Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($allSalesOfPerson)): ?>
                    <?php $s_no = 1; ?>
                    <?php foreach ($allSalesOfPerson as $sale): ?>
                        <tr <?= ($sale['id'] == $specificSaleId) ? 'class="table-primary border-primary fw-bold"' : '' ?>>
                            <td><?= $s_no++ ?></td>
                            <td><?= esc($sale['date_sold']) ?></td>
                            <td><?= esc($sale['product_name']) ?></td>
                            <td><?= esc($sale['quantity_sold']) ?></td>
                            <td>₹<?= number_format($sale['price_per_unit'], 2) ?></td>
                            <td>₹<?= number_format($sale['discount'], 2) ?></td>
                            <td>₹<?= number_format($sale['total_price'], 2) ?></td>
                            <td class="text-info">₹<?= number_format($sale['amount_received_from_person'], 2) ?></td>
                            <td class="text-danger">₹<?= number_format($sale['balance_from_person'], 2) ?></td>
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
                            <td><?= esc($sale['customer_name']) ?></td>
                            <td><?= esc($sale['customer_phone']) ?></td>
                            <td><?= esc($sale['customer_address']) ?></td>
                            <td>
                                <a href="<?= base_url('sales/payment-history/' . $sale['id']) ?>" class="btn btn-sm btn-info">View Payments</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14" class="text-center">No sales records found for this marketing person
                            <?php if ($selectedProductId): ?>
                                for the selected product.
                            <?php else: ?>
                                .
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>