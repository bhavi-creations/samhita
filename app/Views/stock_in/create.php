<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add New Stock In Entry</h4>
            <a href="<?= base_url('stock-in') ?>" class="btn btn-light btn-sm">Back to List</a>
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

            <form action="<?= base_url('stock-in/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="product_id" class="form-label">Product:</label>
                        <select class="form-control" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= esc($product['id']) ?>" <?= set_select('product_id', $product['id']) ?>>
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
                                <option value="<?= esc($vendor['id']) ?>" <?= set_select('vendor_id', $vendor['id']) ?>>
                                    <?= esc($vendor['agency_name']) ?> (<?= esc($vendor['name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" step="any" class="form-control" id="quantity" name="quantity" value="<?= set_value('quantity', 0) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="purchase_price" class="form-label">Purchase Price (per unit):</label>
                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="<?= set_value('purchase_price', 0.00) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="gst_rate_id" class="form-label">GST Rate:</label>
                        <select class="form-control" id="gst_rate_id" name="gst_rate_id" required>
                            <option value="">Select GST Rate</option>
                            <?php foreach ($gstRates as $gstRate): ?>
                                <option value="<?= esc($gstRate['id']) ?>" data-rate="<?= esc($gstRate['rate']) ?>" <?= set_select('gst_rate_id', $gstRate['id']) ?>>
                                    <?= esc($gstRate['name']) ?> (<?= esc($gstRate['rate'] * 100) ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="total_amount_before_gst" class="form-label">Sub Total (before GST):</label>
                        <input type="text" id="total_amount_before_gst" class="form-control bg-light" value="<?= number_format(set_value('total_amount_hidden', 0), 2) ?>" readonly>
                        <input type="hidden" name="total_amount_hidden" id="total_amount_hidden" value="<?= set_value('total_amount_hidden', 0) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="gst_amount" class="form-label">GST Amount:</label>
                        <input type="text" id="gst_amount" class="form-control bg-light" value="<?= number_format(set_value('gst_amount', 0), 2) ?>" readonly>
                        <input type="hidden" name="gst_amount" id="gst_amount_hidden" value="<?= set_value('gst_amount', 0) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="grand_total" class="form-label">Grand Total (incl. GST):</label>
                        <input type="text" id="grand_total" class="form-control bg-info text-white fw-bold fs-5" value="<?= number_format(set_value('grand_total_hidden', 0), 2) ?>" readonly>
                        <input type="hidden" name="grand_total_hidden" id="grand_total_hidden" value="<?= set_value('grand_total_hidden', 0) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="amount_paid_initial" class="form-label">Initial Amount Paid:</label>
                        <input type="number" step="0.01" class="form-control" id="amount_paid_initial" name="amount_paid_initial" value="<?= set_value('amount_paid_initial', 0.00) ?>">
                        <div class="form-text">Enter the amount paid at the time of this stock-in.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="amount_pending_display" class="form-label">Amount Pending (Calculated):</label>
                        <input type="text" id="amount_pending_display" class="form-control bg-warning text-dark fw-bold fs-5" value="<?= number_format(set_value('amount_pending', 0), 2) ?>" readonly>
                        </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_received" class="form-label">Date Received:</label>
                        <input type="date" class="form-control" id="date_received" name="date_received" value="<?= set_value('date_received', date('Y-m-d')) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes:</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= set_value('notes') ?></textarea>
                </div>
                
                <!-- <div class="mb-3">
                    <label for="selling_price" class="form-label">Selling Price (per unit - Optional):</label>
                    <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" value="<?= set_value('selling_price') ?>">
                    <div class="form-text">If you have a default selling price for this stock batch.</div>
                </div> -->

                <button type="submit" class="btn btn-primary">Add Stock In</button>
                <a href="<?= base_url('stock-in') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function calculateTotals() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const purchasePrice = parseFloat(document.getElementById('purchase_price').value) || 0;
        const gstRateSelect = document.getElementById('gst_rate_id');
        const selectedGstOption = gstRateSelect.options[gstRateSelect.selectedIndex];
        // data-rate attribute holds the actual decimal rate (e.g., 0.05 for 5%)
        const gstRate = parseFloat(selectedGstOption.dataset.rate) || 0;
        
        // New: Get the initial amount paid from the input field
        const initialAmountPaid = parseFloat(document.getElementById('amount_paid_initial').value) || 0;

        const totalAmountBeforeGst = quantity * purchasePrice;
        const gstAmount = totalAmountBeforeGst * gstRate;
        const grandTotal = totalAmountBeforeGst + gstAmount;
        
        // Calculate amount pending for display purposes locally
        const amountPendingDisplay = grandTotal - initialAmountPaid;

        document.getElementById('total_amount_before_gst').value = totalAmountBeforeGst.toFixed(2);
        document.getElementById('total_amount_hidden').value = totalAmountBeforeGst.toFixed(2);

        document.getElementById('gst_amount').value = gstAmount.toFixed(2);
        document.getElementById('gst_amount_hidden').value = gstAmount.toFixed(2);

        document.getElementById('grand_total').value = grandTotal.toFixed(2);
        document.getElementById('grand_total_hidden').value = grandTotal.toFixed(2);

        // Update the display for amount pending
        document.getElementById('amount_pending_display').value = amountPendingDisplay.toFixed(2);
    }

    // Attach event listeners to trigger calculations
    document.getElementById('quantity').addEventListener('input', calculateTotals);
    document.getElementById('purchase_price').addEventListener('input', calculateTotals);
    document.getElementById('gst_rate_id').addEventListener('change', calculateTotals);
    document.getElementById('amount_paid_initial').addEventListener('input', calculateTotals); // Listener for the initial amount paid field

    // Call calculateTotals on page load to initialize values
    window.addEventListener('load', calculateTotals);
</script>
<?= $this->endSection() ?>