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

            <!-- NEW FIELD: Sale By (Marketing Person) -->
            <div class="mb-3">
                <label for="marketing_person_id" class="form-label">Sale By (Marketing Person) <span class="text-danger">*</span></label>
                <select class="form-control" id="marketing_person_id" name="marketing_person_id" required>
                    <option value="">Select Marketing Person</option>
                    <?php foreach ($marketing_persons as $person): ?>
                        <option value="<?= esc($person['id']) ?>" <?= set_select('marketing_person_id', $person['id']) ?>>
                            <?= esc($person['name']) ?> (<?= esc($person['custom_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('validation') && session('validation')->hasError('marketing_person_id')): ?>
                    <div class="text-danger mt-1">
                        <?= session('validation')->getError('marketing_person_id') ?>
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
                $old_product_data = old('products') ?? [[]];
                if (empty($old_product_data[0])) {
                    $old_product_data = [[]];
                }

                foreach ($old_product_data as $key => $productData):
                    $current_product_id = $productData['product_id'] ?? null;
                    $current_quantity = $productData['quantity'] ?? 1;
                ?>
                    <div class="row product-item mb-3 gx-2 align-items-center border-bottom pb-2">
                        <div class="col-md-5">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-control product-select" name="products[<?= $key ?>][product_id]" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option
                                        value="<?= esc($product['id']) ?>"
                                        data-price="<?= esc($product['selling_price']) ?>"
                                        data-stock="<?= esc($product['current_stock']) ?>"
                                        <?= set_select('products.' . $key . '.product_id', $product['id'], (string)$current_product_id === (string)$product['id']) ?>>
                                        <?= esc($product['name']) ?> (₹<?= number_format($product['selling_price'] ?? 0, 2) ?>) - Stock: <?= esc($product['current_stock']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.product_id')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('products.' . $key . '.product_id') ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted product-stock-display mt-1">Available Stock: N/A</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control quantity-input" name="products[<?= $key ?>][quantity]" min="1" value="<?= esc($current_quantity) ?>" required>
                            <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.quantity')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('products.' . $key . '.quantity') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Item Total (Excl. GST)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="text" class="form-control item-total-before-gst-display" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="col-md-1 text-center">
                            <?php if (count($old_product_data) > 1 || $key > 0): ?>
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
            </div>
        </div>
    </div>

    <!-- NEW CARD: Overall GST Details (Now with multiple GST fields) -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            Overall GST Details
            <button type="button" class="btn btn-info btn-sm add-overall-gst-field">Add Another Overall GST</button>
        </div>
        <div class="card-body">
            <div id="overallGstFieldsContainer">
                <?php 
                // Handle old input for overall GST rates
                $old_overall_gst_rate_ids = old('overall_gst_rate_ids') ?? ['']; // Ensure at least one empty select
                if (!is_array($old_overall_gst_rate_ids)) {
                    $old_overall_gst_rate_ids = [$old_overall_gst_rate_ids]; // Convert single value to array
                }
                foreach ($old_overall_gst_rate_ids as $gst_idx => $selected_gst_id):
                ?>
                <div class="mb-3 overall-gst-field-group">
                    <label for="overall_gst_rate_id_<?= $gst_idx ?>" class="form-label">Overall GST Rate #<?= $gst_idx + 1 ?> <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="form-control overall-gst-rate-select" id="overall_gst_rate_id_<?= $gst_idx ?>" name="overall_gst_rate_ids[]" required>
                            <option value="">Select Overall GST Rate</option>
                            <?php foreach ($gst_rates as $gst): ?>
                                <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>"
                                    <?= set_select('overall_gst_rate_ids.' . $gst_idx, $gst['id'], (string)$selected_gst_id === (string)$gst['id']) ?>>
                                    <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($gst_idx > 0 || count($old_overall_gst_rate_ids) > 1): // Show remove button if not the first initial field or if there are multiple initially ?>
                            <button type="button" class="btn btn-outline-danger remove-overall-gst-field">X</button>
                        <?php endif; ?>
                    </div>
                    <?php if (session('validation') && session('validation')->hasError('overall_gst_rate_ids.' . $gst_idx)): ?>
                        <div class="text-danger mt-1">
                            <?= session('validation')->getError('overall_gst_rate_ids.' . $gst_idx) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex justify-content-end mb-2">
                <strong>Total GST Amount:</strong> ₹<span id="overallGstAmount">0.00</span>
            </div>
            <div class="d-flex justify-content-end mb-2">
                <strong>Grand Total (Before Discount):</strong> ₹<span id="grandTotalBeforeDiscount">0.00</span>
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
        // Corrected order of variable declarations to ensure elements exist when accessed
        const productRowsContainer = document.getElementById('productRows');
        const addProductRowButton = document.getElementById('addProductRow');
        const overallAmountBeforeGstDisplay = document.getElementById('overallAmountBeforeGst');
        const overallGstAmountDisplay = document.getElementById('overallGstAmount');
        const grandTotalBeforeDiscountDisplay = document.getElementById('grandTotalBeforeDiscount');
        const overallGrandTotalDisplay = document.getElementById('overallGrandTotal'); 
        const overallGstFieldsContainer = document.getElementById('overallGstFieldsContainer');
        const addOverallGstButton = document.querySelector('.add-overall-gst-field');
        
        // Payment related elements
        const initialPaymentAmountInput = document.getElementById('initial_payment_amount');
        const balanceAmountDisplay = document.getElementById('balance_amount_display');
        const overallDiscountInput = document.getElementById('discount');
        const paymentMethodSelect = document.getElementById('payment_method');
        const transactionIdInput = document.getElementById('transaction_id');

        // New variable to store the latest calculated grand total
        let latestGrandTotal = 0; 


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
                    if (stockDisplay) stockDisplay.textContent = `Available Stock: ${product.current_stock}`;
                } else {
                    if (stockDisplay) stockDisplay.textContent = 'Available Stock: N/A';
                }
            } else {
                if (stockDisplay) stockDisplay.textContent = 'Available Stock: N/A';
            }
        }

        // Function to calculate and update the current row's individual totals (only before GST)
        function calculateRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const itemTotalBeforeGstDisplay = row.querySelector('.item-total-before-gst-display');

            const productId = productSelect.value;
            const quantity = parseFloat(quantityInput.value) || 0;

            let unitPrice = 0;

            if (productId) {
                const product = getProductDetails(productId);
                if (product) {
                    unitPrice = parseFloat(product.selling_price);
                }
            }

            const amountBeforeGst = unitPrice * quantity;
            if (itemTotalBeforeGstDisplay) itemTotalBeforeGstDisplay.value = amountBeforeGst.toFixed(2);

            return {
                amountBeforeGst: amountBeforeGst
            };
        }

        // Function to calculate and update overall totals
        function updateOverallTotals() {
            let totalAmountBeforeGst = 0;
            const discountAmount = parseFloat(overallDiscountInput.value) || 0;

            document.querySelectorAll('.product-item').forEach(row => {
                updateProductStockDisplay(row);
                const rowTotals = calculateRow(row);
                totalAmountBeforeGst += rowTotals.amountBeforeGst;
            });

            // Calculate overall GST based on all selected overall GST rates
            let totalOverallGstPercentage = 0;
            overallGstFieldsContainer.querySelectorAll('.overall-gst-rate-select').forEach(select => {
                const selectedGstOption = select.options[select.selectedIndex];
                totalOverallGstPercentage += parseFloat(selectedGstOption.dataset.rate) || 0;
            });

            const overallGstAmount = totalAmountBeforeGst * (totalOverallGstPercentage / 100);
            const grandTotalBeforeDiscount = totalAmountBeforeGst + overallGstAmount;
            const finalGrandTotal = grandTotalBeforeDiscount - discountAmount;

            if (overallAmountBeforeGstDisplay) overallAmountBeforeGstDisplay.textContent = totalAmountBeforeGst.toFixed(2);
            if (overallGstAmountDisplay) overallGstAmountDisplay.textContent = overallGstAmount.toFixed(2);
            if (grandTotalBeforeDiscountDisplay) grandTotalBeforeDiscountDisplay.textContent = grandTotalBeforeDiscount.toFixed(2);
            if (overallGrandTotalDisplay) overallGrandTotalDisplay.textContent = finalGrandTotal.toFixed(2);

            // Store the calculated finalGrandTotal
            latestGrandTotal = finalGrandTotal; 
            // Pass the stored value to updateBalanceAmount
            updateBalanceAmount(latestGrandTotal); 
        }

        // Function to update balance amount - now accepts grandTotal as an argument
        function updateBalanceAmount(grandTotal) {
            const amountPaid = parseFloat(initialPaymentAmountInput.value) || 0;
            const balance = grandTotal - amountPaid;
            
            if (balanceAmountDisplay) balanceAmountDisplay.value = balance.toFixed(2);
        }

        // Function to toggle required attribute for payment method based on amount paid
        function togglePaymentFieldsRequired() {
            const amountPaid = parseFloat(initialPaymentAmountInput.value) || 0;

            if (paymentMethodSelect) { // Ensure paymentMethodSelect exists
                if (amountPaid > 0) {
                    paymentMethodSelect.setAttribute('required', 'required');
                } else {
                    paymentMethodSelect.removeAttribute('required');
                    paymentMethodSelect.value = ''; // Clear selection if not required
                }
            }
        }


        // Function to add a new overall GST field
        function addOverallGstField() {
            const newGstFieldGroup = document.createElement('div');
            newGstFieldGroup.className = 'mb-3 overall-gst-field-group';
            const newGstIndex = overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length;
            newGstFieldGroup.innerHTML = `
                <label for="overall_gst_rate_id_${newGstIndex}" class="form-label">Overall GST Rate #${newGstIndex + 1} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control overall-gst-rate-select" id="overall_gst_rate_id_${newGstIndex}" name="overall_gst_rate_ids[]" required>
                        <option value="">Select Overall GST Rate</option>
                        <?php foreach ($gst_rates as $gst): ?>
                            <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>">
                                <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-danger remove-overall-gst-field">X</button>
                </div>
            `;
            overallGstFieldsContainer.appendChild(newGstFieldGroup);
            
            // Attach listeners to the new elements
            newGstFieldGroup.querySelector('.overall-gst-rate-select').addEventListener('change', updateOverallTotals);
            newGstFieldGroup.querySelector('.remove-overall-gst-field').addEventListener('click', function() {
                if (overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length > 1) {
                    this.closest('.overall-gst-field-group').remove(); // Use 'this' to refer to the clicked button
                    updateOverallTotals();
                } else {
                    alert('You must have at least one overall GST rate selected.');
                }
            });
            updateOverallTotals(); // Recalculate totals after adding new GST field
        }

        // Function to add a new product row
        function addProductRow() {
            const newRowIndex = productRowsContainer.children.length;
            const newRow = document.createElement('div');
            newRow.className = 'row product-item mb-3 gx-2 align-items-center border-bottom pb-2';
            newRow.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label">Product <span class="text-danger">*</span></label>
                    <select class="form-control product-select" name="products[${newRowIndex}][product_id]" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option 
                                value="<?= esc($product['id']) ?>" 
                                data-price="<?= esc($product['selling_price']) ?>"
                                data-stock="<?= esc($product['current_stock']) ?>">
                                <?= esc($product['name']) ?> (₹<?= number_format($product['selling_price'] ?? 0, 2) ?>) - Stock: <?= esc($product['current_stock']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted product-stock-display mt-1">Available Stock: N/A</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control quantity-input" name="products[${newRowIndex}][quantity]" min="1" value="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item Total (Excl. GST)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="text" class="form-control item-total-before-gst-display" value="0.00" readonly>
                    </div>
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
            const removeProductButton = row.querySelector('.remove-product-row');

            if (productSelect) {
                productSelect.addEventListener('change', updateOverallTotals);
                productSelect.addEventListener('change', () => updateProductStockDisplay(row));
            }
            if (quantityInput) {
                quantityInput.addEventListener('input', updateOverallTotals);
            }
            
            if (removeProductButton) {
                removeProductButton.addEventListener('click', function() {
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
            calculateRow(row); // Also calculate initial row totals
        }

        // Event listener for adding a product row
        addProductRowButton.addEventListener('click', addProductRow);

        // Initial payment amount input listener
        initialPaymentAmountInput.addEventListener('input', function() {
            // Now, we use the globally stored latestGrandTotal
            updateBalanceAmount(latestGrandTotal);
            togglePaymentFieldsRequired(); // Call to toggle required attributes
        });

        // Discount input listener
        overallDiscountInput.addEventListener('input', updateOverallTotals);

        // Listener for adding overall GST fields
        addOverallGstButton.addEventListener('click', addOverallGstField);

        // Attach listeners to existing overall GST selects and remove buttons on initial load (for old inputs)
        overallGstFieldsContainer.querySelectorAll('.overall-gst-rate-select').forEach(select => {
            select.addEventListener('change', updateOverallTotals);
        });
        overallGstFieldsContainer.querySelectorAll('.remove-overall-gst-field').forEach(button => {
            button.addEventListener('click', function() {
                if (overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length > 1) {
                    this.closest('.overall-gst-field-group').remove(); // Use 'this' to refer to the clicked button
                    updateOverallTotals();
                } else {
                    alert('You must have at least one overall GST rate selected.');
                }
            });
        });

        // Attach listeners to existing product rows on initial load (for old inputs on validation error)
        document.querySelectorAll('.product-item').forEach(attachEventListenersToRow);

        // Initial calculation and required toggle when the page loads (useful if old inputs are present)
        updateOverallTotals();
        togglePaymentFieldsRequired(); // Initial call to set required attributes
    });
</script>
<?php $this->endSection(); ?>
