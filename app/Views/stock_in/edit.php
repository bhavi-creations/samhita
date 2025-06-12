<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Stock In Entry (ID: <?= esc($stock_entry['id']) ?>)</h4>
            <a href="<?= base_url('stock-in') ?>" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
        <div class="card-body">
            <form action="<?= base_url('stock-in/update/' . $stock_entry['id']) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="POST"> 

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="product_id" class="form-label">Product:</label>
                        <select class="form-control" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= esc($product['id']) ?>"
                                    <?= ($product['id'] == $stock_entry['product_id']) ? 'selected' : '' ?>>
                                    <?= esc($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="vendor_id" class="form-label">Vendor:</label>
                        <select class="form-control" id="vendor_id" name="vendor_id">
                            <option value="">Select Vendor (Optional)</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= esc($vendor['id']) ?>"
                                    <?= ($vendor['id'] == $stock_entry['vendor_id']) ? 'selected' : '' ?>>
                                    <?= esc($vendor['agency_name']) ?> (<?= esc($vendor['name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" step="any" class="form-control" id="quantity" name="quantity" value="<?= esc($stock_entry['quantity']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="purchase_price" class="form-label">Purchase Price (per unit):</label>
                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="<?= esc($stock_entry['purchase_price']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="gst_rate_id" class="form-label">GST Rate:</label>
                        <select class="form-control" id="gst_rate_id" name="gst_rate_id" required>
                            <option value="">Select GST Rate</option>
                            <?php foreach ($gstRates as $gstRate): ?>
                                <option value="<?= esc($gstRate['id']) ?>" data-rate="<?= esc($gstRate['rate']) ?>"
                                    <?= ($gstRate['id'] == $stock_entry['gst_rate_id']) ? 'selected' : '' ?>>
                                    <?= esc($gstRate['name']) ?> (<?= esc($gstRate['rate'] * 100) ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="total_amount_before_gst" class="form-label">Sub Total (before GST):</label>
                        <input type="text" id="total_amount_before_gst" class="form-control bg-light" value="<?= number_format($stock_entry['total_amount_before_gst'] ?? 0, 2) ?>" readonly>
                        <input type="hidden" name="total_amount_hidden" id="total_amount_hidden" value="<?= esc($stock_entry['total_amount_before_gst'] ?? 0) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="gst_amount" class="form-label">GST Amount:</label>
                        <input type="text" id="gst_amount" class="form-control bg-light" value="<?= number_format($stock_entry['gst_amount'] ?? 0, 2) ?>" readonly>
                        <input type="hidden" name="gst_amount" id="gst_amount_hidden" value="<?= esc($stock_entry['gst_amount'] ?? 0) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="grand_total" class="form-label">Grand Total (incl. GST):</label>
                        <input type="text" id="grand_total" class="form-control bg-info text-white fw-bold fs-5" value="<?= number_format($stock_entry['grand_total'] ?? 0, 2) ?>" readonly>
                        <input type="hidden" name="grand_total_hidden" id="grand_total_hidden" value="<?= esc($stock_entry['grand_total'] ?? 0) ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="current_amount_paid" class="form-label">Current Amount Paid:</label>
                        <input type="text" id="current_amount_paid" class="form-control bg-light" value="₹<?= number_format($stock_entry['amount_paid'] ?? 0, 2) ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="current_amount_pending" class="form-label">Current Amount Pending:</label>
                        <input type="text" id="current_amount_pending" class="form-control bg-warning text-dark fw-bold fs-5" value="₹<?= number_format($stock_entry['amount_pending'] ?? 0, 2) ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_received" class="form-label">Date Received:</label>
                        <input type="date" class="form-control" id="date_received" name="date_received" value="<?= esc($stock_entry['date_received']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes:</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= esc($stock_entry['notes']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Stock Details</button>
                <a href="<?= base_url('stock-in') ?>" class="btn btn-secondary">Cancel</a>
            </form>
            <hr class="my-4">

            <h5 class="mb-3">Payment History</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Payment Date</th>
                            <th>Amount Paid</th>
                            <th>Notes</th>
                            <th>Recorded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stock_entry_payments)): ?>
                            <?php foreach ($stock_entry_payments as $payment): ?>
                                <tr>
                                    <td><?= esc($payment['payment_date']) ?></td>
                                    <td>₹<?= number_format($payment['payment_amount'], 2) ?></td>
                                    <td><?= esc($payment['notes'] ?? 'N/A') ?></td>
                                    <td><?= esc($payment['created_at']) ?></td>
                                    <td>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No payment history found for this entry.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    Add New Payment
                </div>
                <div class="card-body">
                    <form action="<?= base_url('stock-in/add-payment/' . $stock_entry['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="new_payment_amount" class="form-label">Payment Amount:</label>
                                <input type="number" step="0.01" class="form-control" id="new_payment_amount" name="new_payment_amount" required min="0.01" max="<?= ($stock_entry['amount_pending'] ?? 0) ?>">
                                <div class="form-text">Max: ₹<?= number_format($stock_entry['amount_pending'] ?? 0, 2) ?> pending</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="new_payment_date" class="form-label">Payment Date:</label>
                                <input type="date" class="form-control" id="new_payment_date" name="new_payment_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="new_payment_notes" class="form-label">Notes (Optional):</label>
                                <input type="text" class="form-control" id="new_payment_notes" name="new_payment_notes" placeholder="e.g., Via bank transfer">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Record New Payment</button>
                    </form>
                </div>
            </div>
            </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // This script block should be the same as your create.php's script block,
    // ensuring calculations work on edit page.
    function calculateTotals() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const purchasePrice = parseFloat(document.getElementById('purchase_price').value) || 0;
        const gstRateSelect = document.getElementById('gst_rate_id');
        const selectedGstOption = gstRateSelect.options[gstRateSelect.selectedIndex];
        const gstRate = parseFloat(selectedGstOption.dataset.rate) || 0;
        
        // No longer using an input for amount_paid, so removed from calculations
        // const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0; 

        const totalAmountBeforeGst = quantity * purchasePrice;
        const gstAmount = totalAmountBeforeGst * gstRate;
        const grandTotal = totalAmountBeforeGst + gstAmount;
        // amountPending is now managed by backend based on payment history
        // const amountPending = grandTotal - amountPaid; 

        document.getElementById('total_amount_before_gst').value = totalAmountBeforeGst.toFixed(2);
        document.getElementById('total_amount_hidden').value = totalAmountBeforeGst.toFixed(2); 

        document.getElementById('gst_amount').value = gstAmount.toFixed(2);
        document.getElementById('gst_amount_hidden').value = gstAmount.toFixed(2); 

        document.getElementById('grand_total').value = grandTotal.toFixed(2);
        document.getElementById('grand_total_hidden').value = grandTotal.toFixed(2); 

        // Current Amount Paid and Pending are read-only and updated by backend/on load
        // document.getElementById('amount_pending').value = amountPending.toFixed(2);
        // document.getElementById('amount_pending_hidden').value = amountPending.toFixed(2); 
    }

    // Attach event listeners for main form fields
    document.getElementById('quantity').addEventListener('input', calculateTotals);
    document.getElementById('purchase_price').addEventListener('input', calculateTotals);
    document.getElementById('gst_rate_id').addEventListener('change', calculateTotals);
    // Removed amount_paid listener as it's no longer a direct input for main calculations

    // Call calculateTotals on page load to initialize values for existing entry
    window.addEventListener('load', calculateTotals);
</script>
<?= $this->endSection() ?>