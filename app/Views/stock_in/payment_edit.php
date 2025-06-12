<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Payment - ID: <?= esc($payment['id']) ?></h4>
            <a href="<?= base_url('stock-in/view/' . $payment['stock_in_id']) ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Stock In Details</a>
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

            <form action="<?= base_url('stock-in/payment/update/' . $payment['id']) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT"> <input type="hidden" name="stock_in_id" value="<?= esc($payment['stock_in_id']) ?>">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="payment_amount" class="form-label">Payment Amount</label>
                        <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount" 
                               value="<?= old('payment_amount', $payment['payment_amount']) ?>" required>
                        <?php if (session('errors.payment_amount')): ?>
                            <div class="text-danger small"><?= session('errors.payment_amount') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                               value="<?= old('payment_date', $payment['payment_date']) ?>" required>
                        <?php if (session('errors.payment_date')): ?>
                            <div class="text-danger small"><?= session('errors.payment_date') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <input type="text" class="form-control" id="notes" name="notes" 
                               value="<?= old('notes', $payment['notes']) ?>">
                        <?php if (session('errors.notes')): ?>
                            <div class="text-danger small"><?= session('errors.notes') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-warning mt-2">Update Payment</button>
            </form>

        </div>
    </div>
</div>
<?= $this->endSection() ?>