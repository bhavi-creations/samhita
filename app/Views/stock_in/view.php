<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Stock In Details - ID: <?= esc($stock_in_entry['id']) ?></h4>
            <div class="d-flex align-items-center">
                <div class="btn-group me-2" role="group" aria-label="Export options">
                    <a href="<?= base_url('stock-in/export-excel/' . $stock_in_entry['id']) ?>" class="btn btn-success btn-sm" title="Export to Excel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="<?= base_url('stock-in/export-pdf/' . $stock_in_entry['id']) ?>" class="btn btn-danger btn-sm" title="Export to PDF">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
                <a href="<?= base_url('stock-in') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Stock In List
                </a>
            </div>
        </div>
        <div class="card-body">
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

            <h5 class="mb-3">Stock In Entry Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Date Received:</strong> <?= esc($stock_in_entry['date_received']) ?></p>
                    <p><strong>Vendor:</strong> <?= esc($stock_in_entry['vendor_agency_name']) ?> (<?= esc($stock_in_entry['vendor_name']) ?>)</p>
                    <p><strong>Product:</strong> <?= esc($stock_in_entry['product_name']) ?></p>
                    <p><strong>Quantity:</strong> <?= esc($stock_in_entry['quantity']) ?> <?= esc($stock_in_entry['unit_name']) ?></p>
                    <p><strong>Purchase Price:</strong> ₹<?= number_format($stock_in_entry['purchase_price'], 2) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>GST Rate:</strong> <?= esc($stock_in_entry['gst_rate_name']) ?> (<?= esc($stock_in_entry['gst_rate_percentage']) ?>%)</p>
                    <p><strong>Sub Total:</strong> ₹<?= number_format($stock_in_entry['total_amount_before_gst'], 2) ?></p>
                    <p><strong>GST Amount:</strong> ₹<?= number_format($stock_in_entry['gst_amount'], 2) ?></p>
                    <p><strong>Grand Total:</strong> ₹<?= number_format($stock_in_entry['grand_total'], 2) ?></p>
                    <p><strong>Amount Paid:</strong> ₹<?= number_format($stock_in_entry['amount_paid'], 2) ?></p>
                    <p class="mb-0"><strong>Amount Pending:</strong> <span class="<?= ($stock_in_entry['amount_pending'] > 0) ? 'text-danger fw-bold' : '' ?>">₹<?= number_format($stock_in_entry['amount_pending'], 2) ?></span></p>
                    <p><strong>Notes:</strong> <?= esc($stock_in_entry['notes']) ?></p>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Payment Transactions</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>S.No.</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Notes</th>
                            <th>Recorded At</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stock_in_payments)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No payments recorded for this entry yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; ?>
                            <?php foreach ($stock_in_payments as $payment): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($payment['payment_date']) ?></td>
                                    <td>₹<?= number_format($payment['payment_amount'], 2) ?></td>
                                    <td><?= esc($payment['notes']) ?></td>
                                    <td><?= esc($payment['created_at']) ?></td>
                                    <!-- <td>
                                        <a href="<?= base_url('stock-in/payment/edit/' . $payment['id']) ?>" class="btn btn-warning btn-sm" title="Edit Payment"><i class="fas fa-edit"></i></a>
                                        <form action="<?= base_url('stock-in/payment/delete/' . $payment['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete Payment"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td> -->
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="mb-3">Add New Payment</h5>
            <form action="<?= base_url('stock-in/payment/store') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="stock_in_id" value="<?= esc($stock_in_entry['id']) ?>">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="payment_amount" class="form-label">Payment Amount</label>
                        <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount" value="<?= old('payment_amount') ?>" required>
                        <?php if (session('errors.payment_amount')): ?>
                            <div class="text-danger small"><?= session('errors.payment_amount') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= old('payment_date') ?? date('Y-m-d') ?>" required>
                        <?php if (session('errors.payment_date')): ?>
                            <div class="text-danger small"><?= session('errors.payment_date') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <input type="text" class="form-control" id="notes" name="notes" value="<?= old('notes') ?>">
                        <?php if (session('errors.notes')): ?>
                            <div class="text-danger small"><?= session('errors.notes') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-2">Add Payment</button>
            </form>

        </div>
    </div>
</div>
<?= $this->endSection() ?>