<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?> for Stock In ID: <?= esc($payment['stock_in_id']) ?></h4>
            <a href="<?= base_url('stock-in/show/' . $payment['stock_in_id']) ?>" class="btn btn-light btn-sm">Back to Stock In Details</a>
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

            <form action="<?= base_url('stock-in/update-payment/' . $payment['id']) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT"> <!-- Method spoofing for PUT request -->

                <div class="mb-3">
                    <label for="payment_amount" class="form-label">Payment Amount:</label>
                    <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount"
                           value="<?= old('payment_amount', $payment['payment_amount']) ?>" required min="0.01">
                </div>

                <div class="mb-3">
                    <label for="payment_date" class="form-label">Payment Date:</label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                           value="<?= old('payment_date', $payment['payment_date']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional):</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes', $payment['notes']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-info">Update Payment</button>
                <a href="<?= base_url('stock-in/show/' . $payment['stock_in_id']) ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
