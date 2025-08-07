<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <div class="d-flex gap-2">
                <a href="<?= base_url('stock-in') ?>" class="btn btn-light btn-sm">Back to List</a>
                <a href="<?= base_url('stock-in/edit/' . $stockInEntry['id']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <form action="<?= base_url('stock-in/delete/' . $stockInEntry['id']) ?>" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">General Details</div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Date Received:</strong> <?= esc($stockInEntry['date_received']) ?></li>
                                <li class="list-group-item"><strong>Vendor:</strong> <?= esc($stockInEntry['vendor_name'] ?? 'N/A') ?></li>
                                <li class="list-group-item"><strong>Notes:</strong> <?= nl2br(esc($stockInEntry['notes'])) ?></li>
                                <li class="list-group-item">
                                    <strong>Overall GST Rates:</strong>
                                    <?php if (!empty($stockInEntry['gst_rates'])): ?>
                                        <div class="mt-2">
                                            <?php $totalGstRate = 0; ?>
                                            <?php foreach ($stockInEntry['gst_rates'] as $rate): ?>
                                                <p class="mb-1">
                                                    <strong><?= esc($rate['name']) ?> :</strong>
                                                    <span class="badge bg-secondary"><?= number_format(esc($rate['rate']), 2) ?>%</span>
                                                </p>
                                                <?php $totalGstRate += $rate['rate']; ?>
                                            <?php endforeach; ?>
                                            <hr class="my-2">
                                            <p class="h6">
                                                <strong>Total GST %:</strong> <?= number_format($totalGstRate, 2) ?>%
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">Financial Summary</div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Total Amount (before GST):</strong> ₹ <?= number_format(esc($stockInEntry['total_amount_before_gst']), 2) ?></li>
                                <li class="list-group-item"><strong>Total GST Amount:</strong> ₹ <?= number_format(esc($stockInEntry['gst_amount']), 2) ?></li>
                                <li class="list-group-item"><strong>Grand Total (before discount):</strong> ₹ <?= number_format(esc($stockInEntry['grand_total']), 2) ?></li>
                                <li class="list-group-item"><strong>Discount:</strong> ₹ <?= number_format(esc($stockInEntry['discount_amount']), 2) ?></li>
                                <li class="list-group-item"><strong>Final Grand Total:</strong> ₹ <?= number_format(esc($stockInEntry['final_grand_total']), 2) ?></li>
                                <li class="list-group-item"><strong>Amount Paid:</strong> ₹ <?= number_format(esc($stockInEntry['initial_amount_paid']), 2) ?></li>
                                <li class="list-group-item"><strong>Balance Amount:</strong> ₹ <?= number_format(esc($stockInEntry['balance_amount']), 2) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW SECTION: Add Payments -->
            <?php if ($stockInEntry['balance_amount'] > 0): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Add New Payment</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Remaining Balance:</strong> ₹ <?= number_format(esc($stockInEntry['balance_amount']), 2) ?>
                        </div>
                        <form action="<?= base_url('stock-in/add-payment/' . $stockInEntry['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="payment_amount" class="form-label">Payment Amount</label>
                                    <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" max="<?= esc($stockInEntry['balance_amount']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select class="form-select" id="payment_type" name="payment_type" required>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="card">Card</option>
                                        <option value="upi">UPI</option>
                                    </select>
                                </div>
                                <!-- MODIFIED SECTION: Add Transaction ID field -->
                                <div class="col-md-3">
                                    <label for="transaction_id" class="form-label">Transaction ID (Optional)</label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                                </div>
                                <!-- END MODIFIED SECTION -->
                                <div class="col-12">
                                    <label for="notes" class="form-label">Payment Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-success">Save Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <!-- END NEW SECTION -->

            <h5 class="mt-4">Products</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Product Name</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Purchase Price</th>
                            <th scope="col">Item Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stockInEntry['products'])): ?>
                            <?php foreach ($stockInEntry['products'] as $product): ?>
                                <tr>
                                    <td><?= esc($product['product_name']) ?> (<?= esc($product['unit_name']) ?>)</td>
                                    <td><?= number_format(esc($product['quantity']), 2) ?></td>
                                    <td>₹ <?= number_format(esc($product['purchase_price']), 2) ?></td>
                                    <td>₹ <?= number_format(esc($product['item_total']), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No products listed for this entry.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="mt-4">Payments History</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Payment Date</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Type</th>
                            <th scope="col">Transaction ID</th>
                            <th scope="col">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stockInEntry['payments'])): ?>
                            <?php foreach ($stockInEntry['payments'] as $payment): ?>
                                <tr>
                                    <td><?= esc($payment['payment_date']) ?></td>
                                    <td>₹ <?= number_format(esc($payment['payment_amount']), 2) ?></td>
                                    <td><?= esc(ucwords(str_replace('_', ' ', $payment['payment_type']))) ?></td>
                                    <td><?= esc($payment['transaction_id'] ?? 'N/A') ?></td>
                                    <td><?= esc($payment['notes'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No payment records found for this entry.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
