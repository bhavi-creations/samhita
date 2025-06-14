<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payment History for Sale #<span class="text-primary"><?= esc($sale['id']) ?></span></h2>
        <a href="<?= base_url('sales/view/' . $sale['marketing_person_id']) ?>" class="btn btn-secondary">Back to Sales Details</a>
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

    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">Sale Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Product:</strong> <?= esc($sale['product_name']) ?></p>
                    <p><strong>Customer Name:</strong> <?= esc($sale['customer_name']) ?></p>
                    <p><strong>Sale Date:</strong> <?= esc($sale['date_sold']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total Sale Price:</strong> <span class="fw-bold text-success">₹<?= number_format($sale['total_price'], 2) ?></span></p>
                    <p><strong>Total Amount Remitted (Current):</strong> <span class="fw-bold text-info">₹<?= number_format($sale['amount_received_from_person'], 2) ?></span></p>
                    <p><strong>Balance Due (Current):</strong> <span class="fw-bold text-danger">₹<?= number_format($sale['balance_from_person'], 2) ?></span></p>
                    <p>
                        <strong>Payment Status:</strong>
                        <span class="badge
                            <?php
                            if ($sale['payment_status_from_person'] == 'Paid') echo 'bg-success';
                            elseif ($sale['payment_status_from_person'] == 'Partial') echo 'bg-warning text-dark';
                            else echo 'bg-danger';
                            ?>">
                            <?= esc($sale['payment_status_from_person']) ?>
                        </span>
                    </p>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <?php if ($sale['payment_status_from_person'] != 'Paid'): ?>
                    <a href="<?= base_url('sales/record-sale-payment-form/' . $sale['id']) ?>" class="btn btn-primary">Record New Payment</a>
                <?php else: ?>
                    <button class="btn btn-success" disabled><i class="fas fa-check-circle me-1"></i> Fully Paid</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mt-4 mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Individual Payment Transactions</h4>
            <div class="export-buttons">
                <a href="<?= base_url('sales/export-sale-payments-excel/' . $sale['id']) ?>" class="btn btn-success btn-sm me-2"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                <a href="<?= base_url('sales/export-sale-payments-pdf/' . $sale['id']) ?>" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> Export PDF</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Payment Date</th>
                                <th>Amount Paid</th>
                                <th>Method</th>
                                <th>Remarks</th>
                                <th>Recorded At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $s_no = 1; ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= $s_no++ ?></td>
                                    <td><?= esc($payment['payment_date']) ?></td>
                                    <td>₹<?= number_format($payment['amount_paid'], 2) ?></td>
                                    <td><?= esc($payment['payment_method'] ?: 'N/A') ?></td>
                                    <td><?= esc($payment['remarks'] ?: 'N/A') ?></td>
                                    <td><?= esc($payment['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No payments recorded for this sale yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>