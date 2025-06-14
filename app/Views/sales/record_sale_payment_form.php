<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Record Payment for Sale #<span class="text-primary"><?= esc($sale['id']) ?></span></h2>
        <a href="<?= base_url('sales/view/' . $sale['marketing_person_id']) ?>" class="btn btn-secondary">Back to Sales Details</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Sale Payment Details</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Product:</strong> <?= esc($sale['product_name']) ?></p>
                    <p><strong>Customer:</strong> <?= esc($sale['customer_name']) ?></p>
                    <p><strong>Total Price:</strong> ₹<?= number_format($sale['total_price'], 2) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Amount Remitted (Current):</strong> <span class="fw-bold text-info">₹<?= number_format($sale['amount_received_from_person'], 2) ?></span></p>
                    <p><strong>Balance Due (Current):</strong> <span class="fw-bold text-danger">₹<?= number_format($sale['balance_from_person'], 2) ?></span></p>
                    <p><strong>Payment Status:</strong>
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
            <hr>

            <form action="<?= base_url('sales/record-sale-payment') ?>" method="post">

                <?= csrf_field() ?>
                <input type="hidden" name="sale_id" value="<?= esc($sale['id']) ?>">

                <div class="mb-3">
                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= old('payment_date', date('Y-m-d')) ?>" required>
                    <?php if ($validation->hasError('payment_date')): ?>
                        <div class="text-danger"><?= $validation->getError('payment_date') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="amount_paid" class="form-label">Amount Paid <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" value="<?= old('amount_paid', $sale['balance_from_person']) ?>" placeholder="e.g., 500.00" required min="0.01">
                    <?php if ($validation->hasError('amount_paid')): ?>
                        <div class="text-danger"><?= $validation->getError('amount_paid') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method (Optional)</label>
                    <input type="text" class="form-control" id="payment_method" name="payment_method" value="<?= old('payment_method') ?>" placeholder="e.g., Cash, Online Transfer">
                    <?php if ($validation->hasError('payment_method')): ?>
                        <div class="text-danger"><?= $validation->getError('payment_method') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3"><?= old('remarks') ?></textarea>
                    <?php if ($validation->hasError('remarks')): ?>
                        <div class="text-danger"><?= $validation->getError('remarks') ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Record Payment</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>