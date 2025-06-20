<?php $this->extend('layouts/main'); // Adjust to your actual layout file 
?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>

    <?php if (session('validation')): ?>
        <div class="alert alert-danger">
            <?= session('validation')->listErrors(); ?>
        </div>
    <?php endif; ?>

    <?= form_open(base_url('distributor-sales/update/' . $sales_order['id'])) ?>
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

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
                        <option value="<?= esc($distributor['id']) ?>"
                            <?= set_select('distributor_id', $distributor['id'], $distributor['id'] == ($sales_order['distributor_id'] ?? null)) ?>>
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
                <label for="invoice_number" class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                    value="<?= esc($sales_order['invoice_number'] ?? old('invoice_number')) ?>" readonly>
                <?php if (session('validation') && session('validation')->hasError('invoice_number')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('invoice_number') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="order_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="order_date" name="order_date"
                    value="<?= set_value('order_date', $sales_order['invoice_date'] ?? date('Y-m-d')) ?>" required>
                <?php if (session('validation') && session('validation')->hasError('order_date')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('order_date') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= set_value('notes', $sales_order['notes'] ?? '') ?></textarea>
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
                // Use old input for product rows if validation failed, otherwise use existing sales order items
                $old_product_data = old('products') ?? null;
                $display_product_data = [];

                if (!empty($old_product_data)) {
                    $display_product_data = $old_product_data;
                } elseif (!empty($sales_order_items)) {
                    // Transform existing items into the 'products' array structure
                    foreach ($sales_order_items as $item) {
                        $display_product_data[] = [
                            'product_id'    => $item['product_id'],
                            'quantity'      => $item['quantity'],
                            'gst_rate_id'   => $item['gst_rate_id'], // Assuming gst_rate_id is stored in sales_order_items
                        ];
                    }
                } else {
                    // Default to one empty row if no items exist (shouldn't happen for existing orders)
                    $display_product_data = [[]];
                }

                foreach ($display_product_data as $key => $productData):
                    // Ensure product_id, quantity, and gst_rate_id exist for the current key
                    $current_product_id = $productData['product_id'] ?? null;
                    $current_quantity = $productData['quantity'] ?? 1;
                    $current_gst_rate_id = $productData['gst_rate_id'] ?? null;
                ?>
                    <div class="row product-item mb-3 gx-2 align-items-center border-bottom pb-2">
                        <div class="col-md-4">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-control product-select" name="products[<?= $key ?>][product_id]" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= esc($product['id']) ?>" data-price="<?= esc($product['selling_price']) ?>"
                                        <?= set_select('products.' . $key . '.product_id', $product['id'], (string)$current_product_id === (string)$product['id']) ?>>
                                        <?= esc($product['name']) ?> (₹<?= number_format($product['selling_price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.product_id')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('products.' . $key . '.product_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control quantity-input" name="products[<?= $key ?>][quantity]" min="1"
                                value="<?= set_value('products.' . $key . '.quantity', $current_quantity) ?>" required>
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
                            <?php // Always allow removal of dynamically added rows or if more than one initial row ?>
                            <button type="button" class="btn btn-danger btn-sm remove-product-row">X</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-end mb-2">
                    <strong>Total Amount (Excl. GST):</strong> <span id="overallAmountBeforeGst">0.00</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <strong>Total GST Amount:</strong> <span id="overallGstAmount">0.00</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <strong>Grand Total:</strong> <span id="overallGrandTotal" class="fs-5 text-primary">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Payment Details (Initial payment cannot be edited here. Add new payments via "Add Payment" option)
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="discount_amount" class="form-label">Discount Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control" id="discount_amount" name="discount_amount"
                        value="<?= set_value('discount_amount', $sales_order['discount_amount'] ?? '0.00') ?>" min="0">
                </div>
                <?php if (session('validation') && session('validation')->hasError('discount_amount')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('discount_amount') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Total Paid (Current)</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="text" class="form-control" value="<?= number_format((float)($sales_order['amount_paid'] ?? 0), 2) ?>" readonly>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Calculated Due Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="text" class="form-control" id="calculatedDueAmountDisplay" value="0.00" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Original Due Amount (from last save)</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="text" class="form-control" value="<?= number_format((float)($sales_order['due_amount'] ?? 0), 2) ?>" readonly>
                </div>
            </div>

            <div class="alert alert-info">
                Note: To record new payments, please use the "Add Payment" action from the Sales Order list or details page.
                This form is only for editing the sales order's product details and general info.
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Sales Order</button>
    <a href="<?= base_url('distributor-sales') ?>" class="btn btn-secondary">Cancel</a>

    <?= form_close() ?>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productRowsContainer = document.getElementById('productRows');
        const addProductRowButton = document.getElementById('addProductRow');
        // Corrected ID to match the input name `discount_amount`
        const discountInput = document.getElementById('discount_amount'); 
        const overallAmountBeforeGstDisplay = document.getElementById('overallAmountBeforeGst');
        const overallGstAmountDisplay = document.getElementById('overallGstAmount');
        const overallGrandTotalDisplay = document.getElementById('overallGrandTotal');

        // Get the element to display the calculated due amount
        const calculatedDueAmountDisplay = document.getElementById('calculatedDueAmountDisplay');

        // Pass original amount_paid from PHP to JavaScript
        const originalAmountPaid = parseFloat(<?= json_encode($sales_order['amount_paid'] ?? 0) ?>) || 0;

        // Function to get product data from PHP into JS
        const products = <?= json_encode($products) ?>;
        const gstRates = <?= json_encode($gst_rates) ?>;

        // Helper to find product and GST rate details
        function getProductDetails(productId) {
            return products.find(p => String(p.id) === String(productId));
        }

        function getGstRateDetails(gstRateId) {
            return gstRates.find(g => String(g.id) === String(gstRateId));
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
            let grandTotalBeforeDiscount = 0; // Sum of all itemFinalTotal

            document.querySelectorAll('.product-item').forEach(row => {
                const rowTotals = calculateRow(row);
                totalAmountBeforeGst += rowTotals.amountBeforeGst;
                totalGstAmount += rowTotals.gstAmount;
                grandTotalBeforeDiscount += rowTotals.finalTotal;
            });

            // Get discount from the input field
            const discount = parseFloat(discountInput.value) || 0; 
            const finalGrandTotal = grandTotalBeforeDiscount - discount;

            // Calculate the new due amount
            const newDueAmount = finalGrandTotal - originalAmountPaid;

            overallAmountBeforeGstDisplay.textContent = totalAmountBeforeGst.toFixed(2);
            overallGstAmountDisplay.textContent = totalGstAmount.toFixed(2);
            overallGrandTotalDisplay.textContent = finalGrandTotal.toFixed(2);

            // Update the calculated due amount display
            calculatedDueAmountDisplay.value = newDueAmount.toFixed(2);
        }

        // Function to add a new product row (identical to new.php)
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
                        <option value="<?= esc($product['id']) ?>" data-price="<?= esc($product['selling_price']) ?>">
                            <?= esc($product['name']) ?> (₹<?= number_format((float)($product['selling_price'] ?? 0), 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
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
            updateOverallTotals(); // Recalculate totals after adding a new row
        }

        // Function to attach event listeners to a given row's inputs/selects
        function attachEventListenersToRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const gstRateSelect = row.querySelector('.gst-rate-select');
            const removeButton = row.querySelector('.remove-product-row');

            if (productSelect) {
                productSelect.addEventListener('change', updateOverallTotals);
            }
            if (quantityInput) {
                quantityInput.addEventListener('input', updateOverallTotals);
            }
            if (gstRateSelect) {
                gstRateSelect.addEventListener('change', updateOverallTotals);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    // Ensure at least one row remains
                    if (productRowsContainer.children.length > 1) {
                        row.remove();
                        updateOverallTotals(); // Recalculate totals after removing a row
                    } else {
                        alert('A sales order must have at least one product.');
                    }
                });
            }
        }

        addProductRowButton.addEventListener('click', addProductRow);
        discountInput.addEventListener('input', updateOverallTotals); // Listen to discount changes

        // Attach listeners to existing rows on initial load
        document.querySelectorAll('.product-item').forEach(attachEventListenersToRow);

        // Initial calculation when the page loads (important for edit view)
        updateOverallTotals();
    });
</script>
<?php $this->endSection(); ?>