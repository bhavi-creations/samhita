<?php $this->extend('layouts/main'); // Adjust to your actual layout file
?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2>Sales Order Details: <?= esc($sales_order['invoice_number']) ?></h2>

    <?= session()->getFlashdata('success') ? '<div class="alert alert-success">' . session()->getFlashdata('success') . '</div>' : '' ?>
    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= base_url('distributor-sales') ?>" class="btn btn-secondary">Back to List</a>
            <?php if ($sales_order['status'] != 'Paid' && $sales_order['status'] != 'Cancelled'): ?>
                <a href="<?= base_url('distributor-sales/add-payment/' . $sales_order['id']) ?>" class="btn btn-success">Add New Payment</a>
            <?php endif; ?>
            <a href="<?= base_url('distributor-sales/edit/' . $sales_order['id']) ?>" class="btn btn-primary">Edit Sales Order</a>
        </div>
        <div class="d-flex">
            <a href="<?= base_url('distributor-sales/export/invoice-excel/' . $sales_order['id']) ?>" class="btn btn-success me-2" target="_blank">Export to Excel</a>
            <a href="<?= base_url('distributor-sales/export/invoice-pdf/' . $sales_order['id']) ?>" class="btn btn-danger" target="_blank">Export to PDF</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Invoice Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Invoice Number:</strong> <?= esc($sales_order['invoice_number']) ?></p>
                    <p><strong>Invoice Date:</strong> <?= esc(date('d-M-Y', strtotime($sales_order['invoice_date']))) ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?= ($sales_order['status'] == 'Paid') ? 'success' : (($sales_order['status'] == 'Partially Paid') ? 'warning' : 'danger') ?>"><?= esc($sales_order['status']) ?></span></p>
                    <p><strong>Notes:</strong> <?= esc($sales_order['notes'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <?php
                    $totalBeforeDiscount = $sales_order['total_amount_before_gst'] + $sales_order['total_gst_amount'];
                    ?>
                    <p><strong>Total Amount (Before GST):</strong> ₹<?= number_format($sales_order['total_amount_before_gst'], 2) ?></p>
                    <p><strong>Total GST Amount:</strong> ₹<?= number_format($sales_order['total_gst_amount'], 2) ?></p>
                    <p><strong>Gross Total (Before Discount):</strong> ₹<?= number_format($totalBeforeDiscount, 2) ?></p>
                    <p><strong>Discount Amount:</strong> ₹<?= number_format($sales_order['discount_amount'], 2) ?></p>
                    <p><strong>Final Total Amount:</strong> ₹<?= number_format($sales_order['final_total_amount'], 2) ?></p>
                    <p><strong>Amount Paid:</strong> ₹<?= number_format($sales_order['amount_paid'], 2) ?></p>
                    <p><strong>Due Amount:</strong> ₹<?= number_format($sales_order['due_amount'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Distributor Information</h4>
        </div>
        <div class="card-body">
            <p><strong>Agency Name:</strong> <?= esc($sales_order['agency_name'] ?? ($distributor['agency_name'] ?? 'N/A')) ?></p>
            <p><strong>Owner Name:</strong> <?= esc($sales_order['owner_name'] ?? ($distributor['owner_name'] ?? 'N/A')) ?></p>
            <p><strong>Owner Phone:</strong> <?= esc($sales_order['owner_phone'] ?? ($distributor['owner_phone'] ?? 'N/A')) ?></p>
            <p><strong>Agency Address:</strong> <?= esc($sales_order['agency_address'] ?? ($distributor['agency_address'] ?? 'N/A')) ?></p>
            <p><strong>Agency GST Number:</strong> <?= esc($sales_order['agency_gst_number'] ?? ($distributor['agency_gst_number'] ?? 'N/A')) ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Invoice Items</h4>
        </div>
        <div class="card-body">
            <?php if (empty($sales_order_items)): ?>
                <p>No items found for this sales order.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <!-- Changed Quantity header to include Unit -->
                                <th>Quantity (Unit)</th>
                                <th>Unit Price</th>
                                <th>GST Rate (%)</th>
                                <th>Amount Before GST</th>
                                <th>GST Amount</th>
                                <th>Final Item Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_order_items as $item): ?>
                                <tr>
                                    <td><?= esc($item['product_name']) ?></td>
                                     
                                    <td><?= esc($item['quantity']) ?> <?= esc($item['unit_name']) ?></td>
                                    <td>₹<?= number_format($item['unit_price_at_sale'], 2) ?></td>
                                    <td><?= number_format($item['gst_rate_at_sale'], 2) ?>%</td>
                                    <td>₹<?= number_format($item['item_total_before_gst'], 2) ?></td>
                                    <td>₹<?= number_format($item['item_gst_amount'], 2) ?></td>
                                    <td>₹<?= number_format($item['item_final_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Payment History</h4>
        </div>
        <div class="card-body">
            <?php if (empty($payments)): ?>
                <p>No payments recorded for this sales order yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= esc(date('d-M-Y', strtotime($payment['payment_date']))) ?></td>
                                    <td>₹<?= number_format($payment['amount'], 2) ?></td>
                                    <td><?= esc($payment['payment_method'] ?? 'N/A') ?></td>
                                    <td><?= esc($payment['transaction_id'] ?? 'N/A') ?></td>
                                    <td><?= esc($payment['notes'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php $this->endSection(); ?>
