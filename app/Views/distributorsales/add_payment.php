<?php $this->extend('layouts/main'); // Adjust to your actual layout file ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>

    <?php if (session('validation')): ?>
        <div class="alert alert-danger">
            <?= session('validation')->listErrors(); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            Invoice: <strong><?= esc($sales_order['invoice_number']) ?></strong>
        </div>
        <div class="card-body">
            <p><strong>Final Total:</strong> ₹<?= number_format($sales_order['final_total_amount'], 2) ?></p>
            <p><strong>Amount Paid:</strong> ₹<?= number_format($sales_order['amount_paid'], 2) ?></p>
            <p><strong>Due Amount:</strong> <span class="text-danger" id="originalDueAmount">₹<?= number_format($sales_order['due_amount'], 2) ?></span></p>
            <p><strong>Status:</strong> <span class="badge bg-<?= ($sales_order['status'] == 'Paid') ? 'success' : (($sales_order['status'] == 'Partially Paid') ? 'warning' : 'danger') ?>"><?= esc($sales_order['status']) ?></span></p>
        </div>
    </div>

    <?= form_open(base_url('distributor-sales/save-payment')) ?>
        <input type="hidden" name="sales_order_id" value="<?= esc($sales_order['id']) ?>">

        <div class="mb-3">
            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= set_value('payment_date', date('Y-m-d')) ?>" required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                value="<?= set_value('amount', number_format($sales_order['due_amount'], 2, '.', '')) ?>"
                min="0.01" max="<?= number_format($sales_order['due_amount'], 2, '.', '') ?>" required>
            <small class="form-text text-muted">Max amount to pay: ₹<?= number_format($sales_order['due_amount'], 2) ?></small>
            <div class="mt-2">
                <strong>Remaining Due:</strong> <span id="remainingDueAmount" class="text-info fs-5">₹<?= number_format($sales_order['due_amount'], 2) ?></span>
            </div>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select class="form-control" id="payment_method" name="payment_method">
                <option value="">Select Payment Method (Optional)</option>
                <?php foreach ($paymentMethods as $key => $value): ?>
                    <option value="<?= esc($key) ?>" <?= set_select('payment_method', $key) ?>>
                        <?= esc($value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (session('validation') && session('validation')->hasError('payment_method')): ?>
                <div class="text-danger mt-1">
                    <?= session('validation')->getError('payment_method') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID (if applicable)</label>
            <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="<?= set_value('transaction_id') ?>">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?= set_value('notes') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Record Payment</button>
        <a href="<?= base_url('distributor-sales/view/' . $sales_order['id']) ?>" class="btn btn-secondary">Cancel</a>
    <?= form_close() ?>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // The JavaScript also needs to reference the correct input ID, which is `amount`.
    const amountInput = document.getElementById('amount');
    const originalDueAmountSpan = document.getElementById('originalDueAmount');
    const remainingDueAmountSpan = document.getElementById('remainingDueAmount');

    // Extract the numeric value from the span text (e.g., "₹1,234.56" -> 1234.56)
    const originalDueAmountText = originalDueAmountSpan.textContent.replace('₹', '').replace(/,/g, '');
    const originalDueAmount = parseFloat(originalDueAmountText);

    function updateRemainingDue() {
        let enteredAmount = parseFloat(amountInput.value) || 0;

        // Ensure entered amount does not exceed original due amount or fall below zero
        if (enteredAmount < 0) {
            enteredAmount = 0;
            amountInput.value = enteredAmount.toFixed(2);
        }
        if (enteredAmount > originalDueAmount) {
            enteredAmount = originalDueAmount;
            amountInput.value = enteredAmount.toFixed(2);
        }

        const remaining = originalDueAmount - enteredAmount;
        remainingDueAmountSpan.textContent = '₹' + remaining.toFixed(2);

        // Optionally, change color if remaining is 0 or negative
        if (remaining <= 0) {
            remainingDueAmountSpan.classList.remove('text-info');
            remainingDueAmountSpan.classList.add('text-success');
        } else {
            remainingDueAmountSpan.classList.remove('text-success');
            remainingDueAmountSpan.classList.add('text-info');
        }
    }

    // Attach event listener to the amount input
    amountInput.addEventListener('input', updateRemainingDue);

    // Call it once on page load to set the initial remaining amount
    updateRemainingDue();
});
</script>
<?php $this->endSection(); ?>
