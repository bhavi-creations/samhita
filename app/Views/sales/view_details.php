<?php // Path: app/Views/sales/view_details.php ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sales Details for  *<?= esc($marketingPersonName) ?>*  (ID:  *<?= esc($marketingPersonCustomId) ?>* )</h2>
        <a href="<?= base_url('sales') ?>" class="btn btn-secondary">Back to Sales List</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

 
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
                            <th></th> <th>Qty Issued</th>
                            <th>Qty Sold</th>
                            <th>Remaining</th>
                            <th>Total Value</th>
                            </tr>
                    </thead>
                    <tbody>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td><?= number_format($summary['total_qty_issued'] ?? 0) ?></td>
                            <td><?= number_format($summary['total_quantity_sold'] ?? 0) ?></td>
                            <td><?= number_format($summary['total_remaining'] ?? 0) ?></td>
                            <td>₹<?= number_format($summary['overall_total_price'] ?? 0, 2) ?></td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-end">
        <div class="col-md-4">
            <form method="get" action="<?= base_url('sales/view/' . $specificSaleId) ?>">
                <label for="filter_product_id" class="form-label">Filter by Product:</label>
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
            <a href="<?= base_url('sales/export-person-sales-excel/' . $marketingPersonId . '?' . $exportQueryString) ?>" class="btn btn-success me-2">Export Excel</a>
            <a href="<?= base_url('sales/export-person-sales-pdf/' . $marketingPersonId . '?' . $exportQueryString) ?>" class="btn btn-danger">Export PDF</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Sale Date</th>
                    <th>Product</th>
                    <th>Quantity Sold</th>
                    <th>Price/Unit</th>
                    <th>Discount</th>
                    <th>Total Price</th>
                    <th>Customer Name</th>
                    <th>Customer Phone</th>
                    <th>Customer Address</th>
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
                            <td><?= esc($sale['customer_name']) ?></td>
                            <td><?= esc($sale['customer_phone']) ?></td>
                            <td><?= esc($sale['customer_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No sales records found for this marketing person
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