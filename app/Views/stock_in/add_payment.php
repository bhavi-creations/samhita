<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <a href="<?= base_url('stock-in/show/' . $stock_in_entry['id']) ?>" class="btn btn-light btn-sm">Back to Stock In Details</a>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="alert alert-info">
                <strong>Stock In Entry ID:</strong> <?= esc($stock_in_entry['id']) ?><br>
                <strong>Grand Total:</strong> ₹<?= number_format($stock_in_entry['grand_total'], 2) ?><br>
                <strong>Amount Paid:</strong> ₹<?= number_format($stock_in_entry['amount_paid'], 2) ?><br>
                <strong>Amount Pending:</strong> <span class="text-danger fw-bold">₹<?= number_format($stock_in_entry['amount_pending'], 2) ?></span>
            </div>

            <form action="<?= base_url('stock-in/save-payment') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="stock_in_id" value="<?= esc($stock_in_entry['id']) ?>">

                <div class="mb-3">
                    <label for="payment_amount" class="form-label">Payment Amount:</label>
                    <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount"
                           value="<?= old('payment_amount', $stock_in_entry['amount_pending'] > 0 ? $stock_in_entry['amount_pending'] : '0.00') ?>"
                           required min="0.01" max="<?= ($stock_in_entry['amount_pending'] ?? 0) ?>">
                    <div class="form-text">Max amount you can pay: ₹<?= number_format($stock_in_entry['amount_pending'] ?? 0, 2) ?></div>
                </div>

                <div class="mb-3">
                    <label for="payment_date" class="form-label">Payment Date:</label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                           value="<?= old('payment_date', date('Y-m-d')) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional):</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Record Payment</button>
                <a href="<?= base_url('stock-in/show/' . $stock_in_entry['id']) ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
