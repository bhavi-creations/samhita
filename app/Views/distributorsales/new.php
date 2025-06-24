
<?= $this->extend('layouts/main'); // Adjust to your actual layout file 
?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>

    <?php // Display validation errors at the top if any 
    ?>
    <?php if (session('validation')): ?>
        <div class="alert alert-danger">
            <?= session('validation')->listErrors(); ?>
        </div>
    <?php endif; ?>

    <?= form_open(base_url('distributor-sales/save')) ?>

    <div class="card mb-4">
        <div class="card-header">
            Sales Order Details
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="distributor_id" class="form-label">Distributor <span class="text-danger">*</span></label>
                <select class="form-control" id="distributor_id" name="distributor_id" required>
                    <option value="">Select Distributor</option>
                    <?php foreach ($distributors as $distributor): ?>
                        <option value="<?= esc($distributor['id']) ?>" <?= set_select('distributor_id', $distributor['id']) ?>>
                            <?= esc($distributor['agency_name']) ?> (<?= esc($distributor['owner_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('validation') && session('validation')->hasError('distributor_id')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('distributor_id') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="invoice_date" name="order_date" value="<?= set_value('order_date', date('Y-m-d')) ?>" required>
                <?php if (session('validation') && session('validation')->hasError('order_date')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('order_date') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= set_value('notes') ?></textarea>
                <?php if (session('validation') && session('validation')->hasError('notes')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('notes') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            Products
            <button type="button" class="btn btn-success btn-sm" id="addProductRow">Add Product</button>
        </div>
        <div class="card-body">
            <div id="productRows">
                <?php
                // products passed from controller now include 'current_stock'
                // Ensure 'products' data is available for JS, including current_stock
                // The $products variable already contains 'current_stock' if the ProductsController was updated correctly.

                $old_product_data = old('products') ?? [[]]; // Now expects an array of arrays

                foreach ($old_product_data as $key => $productData):
                    $current_product_id = $productData['product_id'] ?? null;
                    $current_quantity = $productData['quantity'] ?? 1;
                    $current_gst_rate_id = $productData['gst_rate_id'] ?? '';
                ?>
                    <div class="row product-item mb-3 gx-2 align-items-center border-bottom pb-2">
                        <div class="col-md-4">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-control product-select" name="products[<?= $key ?>][product_id]" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option
                                        value="<?= esc($product['id']) ?>"
                                        data-price="<?= esc($product['selling_price']) ?>"
                                        data-stock="<?= esc($product['current_stock']) ?>" <?php // NEW: Pass current_stock 
                                                                                            ?>
                                        <?= set_select('products.' . $key . '.product_id', $product['id'], (string)$current_product_id === (string)$product['id']) ?>>
                                        <?= esc($product['name']) ?> (₹<?= number_format($product['selling_price'], 2) ?>) - Stock: <?= esc($product['current_stock']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.product_id')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('products.' . $key . '.product_id') ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted product-stock-display mt-1">Available Stock: N/A</small> <?php // NEW: Display for selected stock 
                                                                                                                ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control quantity-input" name="products[<?= $key ?>][quantity]" min="1" value="<?= esc($current_quantity) ?>" required>
                            <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.quantity')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('products.' . $key . '.quantity') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">GST Rate <span class="text-danger">*</span></label>
                            <select class="form-control gst-rate-select" name="products[<?= $key ?>][gst_rate_id]" required>
                                <option value="">Select GST Rate</option>
                                <?php foreach ($gst_rates as $gst): ?>
                                    <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>"
                                        <?= set_select('products.' . $key . '.gst_rate_id', $gst['id'], (string)$current_gst_rate_id === (string)$gst['id']) ?>>
                                        <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.gst_rate_id')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('products.' . $key . '.gst_rate_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="text" class="form-control item-total-display" value="0.00" readonly>
                            </div>
                            <small class="text-muted">Incl. GST: <span class="item-total-gst-display">0.00</span></small><br>
                            <small class="text-muted">Excl. GST: <span class="item-total-before-gst-display">0.00</span></small>
                        </div>
                        <div class="col-md-1 text-center">
                            <?php if (count($old_product_data) > 1 || $key > 0): // Allow removal if more than one row, or if it's not the first (initial) row 
                            ?>
                                <button type="button" class="btn btn-danger btn-sm remove-product-row">X</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-end mb-2">
                    <strong>Total Amount (Excl. GST):</strong> ₹<span id="overallAmountBeforeGst">0.00</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <strong>Total GST Amount:</strong> ₹<span id="overallGstAmount">0.00</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <strong>Grand Total:</strong> ₹<span id="overallGrandTotal" class="fs-5 text-primary">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Initial Payment Details (Optional)
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="discount" class="form-label">Discount Amount (₹)</label>
                <input type="number" step="0.01" class="form-control" id="discount" name="discount_amount" value="<?= set_value('discount_amount', '0.00') ?>" min="0">
                <?php if (session('validation') && session('validation')->hasError('discount_amount')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('discount_amount') ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="initial_payment_amount" class="form-label">Amount Paid Now</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control" id="initial_payment_amount" name="initial_payment_amount" value="<?= set_value('initial_payment_amount', '0.00') ?>" min="0">
                </div>
                <?php if (session('validation') && session('validation')->hasError('initial_payment_amount')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('initial_payment_amount') ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="balance_amount_display" class="form-label">Balance Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="text" class="form-control" id="balance_amount_display" value="0.00" readonly>
                </div>
            </div>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_type">
                    <option value="">Select Payment Method</option>
                    <?php foreach ($paymentMethods as $key_method => $method_name): ?>
                        <option value="<?= esc($key_method) ?>" <?= set_select('payment_type', $key_method) ?>>
                            <?= esc($method_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('validation') && session('validation')->hasError('payment_type')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('payment_type') ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="transaction_id" class="form-label">Transaction ID (if applicable)</label>
                <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="<?= set_value('transaction_id') ?>">
                <?php if (session('validation') && session('validation')->hasError('transaction_id')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('transaction_id') ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="payment_notes" class="form-label">Payment Notes</label>
                <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"><?= set_value('payment_notes') ?></textarea>
                <?php if (session('validation') && session('validation')->hasError('payment_notes')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('payment_notes') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Create Sales Order</button>
    <a href="<?= base_url('distributor-sales') ?>" class="btn btn-secondary">Cancel</a>

    <?= form_close() ?>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productRowsContainer = document.getElementById('productRows');
        const addProductRowButton = document.getElementById('addProductRow');
        const initialPaymentAmountInput = document.getElementById('initial_payment_amount');
        const balanceAmountDisplay = document.getElementById('balance_amount_display');
        const overallAmountBeforeGstDisplay = document.getElementById('overallAmountBeforeGst');
        const overallGstAmountDisplay = document.getElementById('overallGstAmount');
        const overallDiscountInput = document.getElementById('discount');
        const overallGrandTotalDisplay = document.getElementById('overallGrandTotal');

        // Function to get product data from PHP into JS, including 'current_stock'
        const products = <?= json_encode($products) ?>;
        const gstRates = <?= json_encode($gst_rates) ?>;

        // Helper to find product and GST rate details
        function getProductDetails(productId) {
            return products.find(p => String(p.id) === String(productId));
        }

        function getGstRateDetails(gstRateId) {
            return gstRates.find(g => String(g.id) === String(gstRateId));
        }

        // Function to update the available stock display for a row
        function updateProductStockDisplay(row) {
            const productSelect = row.querySelector('.product-select');
            const stockDisplay = row.querySelector('.product-stock-display');
            const productId = productSelect.value;

            if (productId) {
                const product = getProductDetails(productId);
                if (product) {
                    stockDisplay.textContent = `Available Stock: ${product.current_stock}`;
                } else {
                    stockDisplay.textContent = 'Available Stock: N/A';
                }
            } else {
                stockDisplay.textContent = 'Available Stock: N/A';
            }
        }

        // Function to calculate and update the current row's individual totals
        function calculateRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const gstRateSelect = row.querySelector('.gst-rate-select');
            const itemTotalDisplay = row.querySelector('.item-total-display');
            const itemTotalGstDisplay = row.querySelector('.item-total-gst-display');
            const itemTotalBeforeGstDisplay = row.querySelector('.item-total-before-gst-display');

            const productId = productSelect.value;
            const quantity = parseFloat(quantityInput.value) || 0;
            const gstRateId = gstRateSelect.value;

            let unitPrice = 0;
            let gstPercentage = 0;

            if (productId) {
                const product = getProductDetails(productId);
                if (product) {
                    unitPrice = parseFloat(product.selling_price);
                }
            }

            if (gstRateId) {
                const gst = getGstRateDetails(gstRateId);
                if (gst) {
                    gstPercentage = parseFloat(gst.rate);
                }
            }

            const amountBeforeGst = unitPrice * quantity;
            const gstAmount = amountBeforeGst * (gstPercentage / 100);
            const finalTotal = amountBeforeGst + gstAmount;

            itemTotalDisplay.value = finalTotal.toFixed(2);
            itemTotalGstDisplay.textContent = gstAmount.toFixed(2);
            itemTotalBeforeGstDisplay.textContent = amountBeforeGst.toFixed(2);

            return {
                amountBeforeGst: amountBeforeGst,
                gstAmount: gstAmount,
                finalTotal: finalTotal
            };
        }

        // Function to calculate and update overall totals
        function updateOverallTotals() {
            let totalAmountBeforeGst = 0;
            let totalGstAmount = 0;
            let grandTotalBeforeDiscount = 0;
            const discountAmount = parseFloat(overallDiscountInput.value) || 0;

            document.querySelectorAll('.product-item').forEach(row => {
                updateProductStockDisplay(row); // Update stock display for each row
                const rowTotals = calculateRow(row);
                totalAmountBeforeGst += rowTotals.amountBeforeGst;
                totalGstAmount += rowTotals.gstAmount;
                grandTotalBeforeDiscount += rowTotals.finalTotal;
            });

            const finalGrandTotal = grandTotalBeforeDiscount - discountAmount;

            overallAmountBeforeGstDisplay.textContent = totalAmountBeforeGst.toFixed(2);
            overallGstAmountDisplay.textContent = totalGstAmount.toFixed(2);
            overallGrandTotalDisplay.textContent = finalGrandTotal.toFixed(2);

            updateBalanceAmount();
        }

        // Function to update balance amount
        function updateBalanceAmount() {
            const grandTotal = parseFloat(overallGrandTotalDisplay.textContent) || 0;
            const amountPaid = parseFloat(initialPaymentAmountInput.value) || 0;
            const balance = grandTotal - amountPaid;
            balanceAmountDisplay.value = balance.toFixed(2);
        }

        // Function to add a new product row
        function addProductRow() {
            const newRowIndex = productRowsContainer.children.length;
            const newRow = document.createElement('div');
            newRow.className = 'row product-item mb-3 gx-2 align-items-center border-bottom pb-2';
            newRow.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select class="form-control product-select" name="products[${newRowIndex}][product_id]" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option 
                            value="<?= esc($product['id']) ?>" 
                            data-price="<?= esc($product['selling_price']) ?>"
                            data-stock="<?= esc($product['current_stock']) ?>"> <?php // NEW: Pass current_stock 
                                                                                ?>
                            <?= esc($product['name']) ?> (₹<?= number_format($product['selling_price'], 2) ?>) - Stock: <?= esc($product['current_stock']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted product-stock-display mt-1">Available Stock: N/A</small> <?php // NEW: Display for selected stock 
                                                                                                    ?>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control quantity-input" name="products[${newRowIndex}][quantity]" min="1" value="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">GST Rate <span class="text-danger">*</span></label>
                <select class="form-control gst-rate-select" name="products[${newRowIndex}][gst_rate_id]" required>
                    <option value="">Select GST Rate</option>
                    <?php foreach ($gst_rates as $gst): ?>
                        <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>">
                            <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="text" class="form-control item-total-display" value="0.00" readonly>
                </div>
                <small class="text-muted">Incl. GST: <span class="item-total-gst-display">0.00</span></small><br>
                <small class="text-muted">Excl. GST: <span class="item-total-before-gst-display">0.00</span></small>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-product-row">X</button>
            </div>
        `;
            productRowsContainer.appendChild(newRow);
            attachEventListenersToRow(newRow);
            updateOverallTotals(); // Recalculate totals and stock display after adding row
        }

        // Function to attach event listeners to a given row's inputs/selects
        function attachEventListenersToRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const gstRateSelect = row.querySelector('.gst-rate-select');
            const removeButton = row.querySelector('.remove-product-row');

            if (productSelect) {
                productSelect.addEventListener('change', updateOverallTotals);
                productSelect.addEventListener('change', () => updateProductStockDisplay(row)); // NEW: Update stock display on product change
            }
            if (quantityInput) {
                quantityInput.addEventListener('input', updateOverallTotals);
            }
            if (gstRateSelect) {
                gstRateSelect.addEventListener('change', updateOverallTotals);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    if (productRowsContainer.children.length > 1) {
                        row.remove();
                        updateOverallTotals();
                    } else {
                        alert('You must have at least one product in the sales order.');
                    }
                });
            }
            // Initial stock display for this row
            updateProductStockDisplay(row);
        }

        // Event listener for adding a product row
        addProductRowButton.addEventListener('click', addProductRow);

        // Initial payment amount input listener
        initialPaymentAmountInput.addEventListener('input', updateBalanceAmount);

        // Discount input listener
        overallDiscountInput.addEventListener('input', updateOverallTotals);

        // Attach listeners to existing rows on initial load (for old inputs on validation error)
        document.querySelectorAll('.product-item').forEach(attachEventListenersToRow);

        // Initial calculation when the page loads (useful if old inputs are present)
        updateOverallTotals();
    });
</script>
<?php $this->endSection(); ?>