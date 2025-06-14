<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= $title ?></h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <strong>Sale ID:</strong> <?= $sale['id'] ?><br>
                        <strong>Product:</strong> <?= esc($sale['product_name']) ?><br>
                        <strong>Marketing Person:</strong> <?= esc($sale['person_name']) ?><br>
                        <strong>Date Sold:</strong> <?= esc($sale['date_sold']) ?><br>
                        <strong>Quantity Sold:</strong> <?= esc($sale['quantity_sold']) ?><br>
                        <strong>Price Per Unit:</strong> <?= number_format($sale['price_per_unit'], 2) ?><br>
                        <strong>Discount:</strong> <?= number_format($sale['discount'], 2) ?><br>
                        <hr>
                        <h4><strong>Total Sale Price:</strong> <span class="text-success"><?= number_format($sale['total_price'], 2) ?></span></h4>
                        <h4><strong>Amount Handed Over:</strong> <span class="text-info"><?= number_format($sale['amount_received_from_person'], 2) ?></span></h4>
                        <h4><strong>Balance Due:</strong> <span class="text-danger"><?= number_format($sale['balance_from_person'], 2) ?></span></h4>
                        <hr>
                        <strong>Current Status:</strong> <span class="badge 
                            <?php
                            if ($sale['payment_status_from_person'] == 'Paid') echo 'bg-success';
                            elseif ($sale['payment_status_from_person'] == 'Partial') echo 'bg-warning text-dark';
                            else echo 'bg-danger';
                            ?>">
                            <?= esc($sale['payment_status_from_person']) ?>
                        </span>
                    </div>

                    <?php if ($sale['payment_status_from_person'] != 'Paid'): ?>
                        <form action="<?= base_url('sales/processRemittance/' . $sale['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="amount_paid_now" class="form-label">Amount to Remit Now</label>
                                <input type="number" step="0.01" class="form-control" id="amount_paid_now" name="amount_paid_now" value="<?= old('amount_paid_now', $sale['balance_from_person']) ?>" required min="0.01" max="<?= $sale['balance_from_person'] ?>">
                            </div>
                            <button type="submit" class="btn btn-success">Record Remittance</button>
                            <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php else: ?>
                        <p class="alert alert-success">This sale is already fully remitted by the marketing person.</p>
                        <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="btn btn-primary">Back to Sale Details</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>