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
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('stock-in/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="card mb-4">
                    <div class="card-header">
                        Stock In Details
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select class="form-control" id="vendor_id" name="vendor_id" required>
                                <option value="">Select Vendor</option>
                                <?php foreach ($vendors as $vendor): ?>
                                    <option value="<?= esc($vendor['id']) ?>" <?= set_select('vendor_id', $vendor['id']) ?>>
                                        <?= esc($vendor['agency_name']) ?> (<?= esc($vendor['name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('validation') && session('validation')->hasError('vendor_id')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('vendor_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="date_received" class="form-label">Date Received <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_received" name="date_received" value="<?= set_value('date_received', date('Y-m-d')) ?>" required>
                            <?php if (session('validation') && session('validation')->hasError('date_received')): ?>
                                <div class="text-danger mt-1">
                                    <?= session('validation')->getError('date_received') ?>
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
                        Products to Stock In
                        <button type="button" class="btn btn-success btn-sm" id="addProductRow">Add Product</button>
                    </div>
                    <div class="card-body">
                        <div id="productRows">
                            <?php
                            // Handle old input for products (for validation errors)
                            $old_product_data = old('products') ?? [[]];
                            if (empty($old_product_data[0])) {
                                $old_product_data = [[]];
                            }

                            foreach ($old_product_data as $key => $productData):
                                $current_product_id = $productData['product_id'] ?? null;
                                $current_quantity = $productData['quantity'] ?? 1;
                                $current_purchase_price = $productData['purchase_price'] ?? 0.00;
                            ?>
                                <div class="row product-item mb-3 gx-2 align-items-center border-bottom pb-2">
                                    <div class="col-md-3">
                                        <label class="form-label">Product <span class="text-danger">*</span></label>
                                        <select class="form-control product-select" name="products[<?= $key ?>][product_id]" required>
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option
                                                    value="<?= esc($product['id']) ?>"
                                                    data-unit="<?= esc($product['unit_name'] ?? 'Unit') ?>"
                                                    <?= set_select('products.' . $key . '.product_id', $product['id'], (string)$current_product_id === (string)$product['id']) ?>>
                                                    <?= esc($product['name']) ?> (<?= esc($product['unit_name'] ?? 'Unit') ?>)
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
                                        <input type="number" class="form-control quantity-input" name="products[<?= $key ?>][quantity]" min="0.01" step="any" value="<?= esc($current_quantity) ?>" required>
                                        <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.quantity')): ?>
                                            <div class="text-danger mt-1">
                                                <?= session('validation')->getError('products.' . $key . '.quantity') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Purchase Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" class="form-control purchase-price-input" name="products[<?= $key ?>][purchase_price]" value="<?= esc($current_purchase_price) ?>" required>
                                        </div>
                                        <?php if (session('validation') && session('validation')->hasError('products.' . $key . '.purchase_price')): ?>
                                            <div class="text-danger mt-1">
                                                <?= session('validation')->getError('products.' . $key . '.purchase_price') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Total</label>
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

                <!-- Overall GST Details (Now with multiple GST fields) -->
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
                                            <?php foreach ($gstRates as $gst): ?>
                                                <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>"
                                                    <?= set_select('overall_gst_rate_ids.' . $gst_idx, $gst['id'], (string)$selected_gst_id === (string)$gst['id']) ?>>
                                                    <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($gst_idx > 0 || count($old_overall_gst_rate_ids) > 1): ?>
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
                        Payment Details
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

                <!-- Hidden fields to submit calculated values -->
                <input type="hidden" id="total_amount_before_gst_hidden" name="total_amount_before_gst" value="0.00">
                <input type="hidden" id="gst_amount_hidden" name="gst_amount" value="0.00">
                <input type="hidden" id="grand_total_hidden" name="grand_total" value="0.00">
                <input type="hidden" id="amount_pending_hidden" name="amount_pending" value="0.00">


                <button type="submit" class="btn btn-primary">Add Stock In</button>
                <a href="<?= base_url('stock-in') ?>" class="btn btn-secondary">Cancel</a>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get references to key DOM elements
        const productRowsContainer = document.getElementById('productRows');
        const addProductRowButton = document.getElementById('addProductRow');
        const overallAmountBeforeGstDisplay = document.getElementById('overallAmountBeforeGst');
        const overallGstAmountDisplay = document.getElementById('overallGstAmount');
        const grandTotalBeforeDiscountDisplay = document.getElementById('grandTotalBeforeDiscount');

        const overallGstFieldsContainer = document.getElementById('overallGstFieldsContainer');
        const addOverallGstButton = document.querySelector('.add-overall-gst-field');

        // Payment related elements
        const initialPaymentAmountInput = document.getElementById('initial_payment_amount');
        const balanceAmountDisplay = document.getElementById('balance_amount_display');
        const overallDiscountInput = document.getElementById('discount');
        const paymentMethodSelect = document.getElementById('payment_method');

        // Hidden input fields for form submission
        const totalAmountBeforeGstHidden = document.getElementById('total_amount_before_gst_hidden');
        const gstAmountHidden = document.getElementById('gst_amount_hidden');
        const grandTotalHidden = document.getElementById('grand_total_hidden');
        const amountPendingHidden = document.getElementById('amount_pending_hidden');

        // Variable to store the latest calculated grand total (after discount)
        let latestGrandTotalAfterDiscount = 0;

        // Fetch product and GST rate data from PHP (ensure these are passed from controller)
        const products = <?= json_encode($products) ?>;
        const gstRates = <?= json_encode($gstRates) ?>;

        // Helper to find product details by ID
        function getProductDetails(productId) {
            return products.find(p => String(p.id) === String(productId));
        }
        
        /**
         * Calculates the total for a single product row (Quantity * Purchase Price).
         * @param {HTMLElement} row - The product row element.
         * @returns {number} The calculated amount before GST for the row.
         */
        function calculateRowTotal(row) {
            const quantityInput = row.querySelector('.quantity-input');
            const purchasePriceInput = row.querySelector('.purchase-price-input');
            const itemTotalBeforeGstDisplay = row.querySelector('.item-total-before-gst-display');

            const quantity = parseFloat(quantityInput.value) || 0;
            const purchasePrice = parseFloat(purchasePriceInput.value) || 0;

            const amountBeforeGst = quantity * purchasePrice;
            if (itemTotalBeforeGstDisplay) itemTotalBeforeGstDisplay.value = amountBeforeGst.toFixed(2);

            return amountBeforeGst;
        }

        /**
         * Calculates and updates all overall totals:
         * Total Amount (Excl. GST), Total GST Amount, Grand Total (Before Discount),
         * Final Grand Total (after discount), and Balance Amount.
         */
        function updateAllCalculations() {
            let totalAmountBeforeGst = 0;
            const discountAmount = parseFloat(overallDiscountInput.value) || 0;

            // Calculate total amount before GST from all product rows
            document.querySelectorAll('.product-item').forEach(row => {
                totalAmountBeforeGst += calculateRowTotal(row);
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

            // Update display elements
            if (overallAmountBeforeGstDisplay) overallAmountBeforeGstDisplay.textContent = totalAmountBeforeGst.toFixed(2);
            if (overallGstAmountDisplay) overallGstAmountDisplay.textContent = overallGstAmount.toFixed(2);
            if (grandTotalBeforeDiscountDisplay) grandTotalBeforeDiscountDisplay.textContent = grandTotalBeforeDiscount.toFixed(2);

            // Update hidden fields for form submission
            totalAmountBeforeGstHidden.value = totalAmountBeforeGst.toFixed(2);
            gstAmountHidden.value = overallGstAmount.toFixed(2);
            grandTotalHidden.value = finalGrandTotal.toFixed(2);
            amountPendingHidden.value = (finalGrandTotal - (parseFloat(initialPaymentAmountInput.value) || 0)).toFixed(2);


            // Store the calculated finalGrandTotal for balance calculation
            latestGrandTotalAfterDiscount = finalGrandTotal;
            updateBalanceAmount();
        }

        /**
         * Updates the balance amount based on the grand total and initial amount paid.
         */
        function updateBalanceAmount() {
            const amountPaid = parseFloat(initialPaymentAmountInput.value) || 0;
            const balance = latestGrandTotalAfterDiscount - amountPaid;

            if (balanceAmountDisplay) balanceAmountDisplay.value = balance.toFixed(2);
            amountPendingHidden.value = balance.toFixed(2);
        }

        /**
         * Adds a new overall GST field group to the form.
         */
        function addOverallGstField() {
            const newGstFieldGroup = document.createElement('div');
            newGstFieldGroup.className = 'mb-3 overall-gst-field-group';
            const newGstIndex = overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length;
            newGstFieldGroup.innerHTML = `
                <label for="overall_gst_rate_id_${newGstIndex}" class="form-label">Overall GST Rate #${newGstIndex + 1} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control overall-gst-rate-select" id="overall_gst_rate_id_${newGstIndex}" name="overall_gst_rate_ids[]" required>
                        <option value="">Select Overall GST Rate</option>
                        <?php foreach ($gstRates as $gst): ?>
                            <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>">
                                <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-danger remove-overall-gst-field">X</button>
                </div>
            `;
            overallGstFieldsContainer.appendChild(newGstFieldGroup);

            // Attach listeners to the newly created elements
            newGstFieldGroup.querySelector('.overall-gst-rate-select').addEventListener('change', updateAllCalculations);
            newGstFieldGroup.querySelector('.remove-overall-gst-field').addEventListener('click', function() {
                if (overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length > 1) {
                    this.closest('.overall-gst-field-group').remove();
                    updateAllCalculations();
                } else {
                    // Using a custom message box as window.alert() is not allowed.
                    const messageBox = document.createElement('div');
                    messageBox.className = 'alert alert-warning alert-dismissible fade show mt-3';
                    messageBox.innerHTML = `
                        You must have at least one overall GST rate selected.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    overallGstFieldsContainer.prepend(messageBox);
                }
            });
            updateAllCalculations(); // Recalculate totals after adding new GST field
        }

        /**
         * Adds a new product row to the form.
         */
        function addProductRow() {
            const newRowIndex = productRowsContainer.children.length;
            const newRow = document.createElement('div');
            newRow.className = 'row product-item mb-3 gx-2 align-items-center border-bottom pb-2';
            newRow.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label">Product <span class="text-danger">*</span></label>
                    <select class="form-control product-select" name="products[${newRowIndex}][product_id]" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?= esc($product['id']) ?>"
                                data-unit="<?= esc($product['unit_name'] ?? 'Unit') ?>">
                                <?= esc($product['name']) ?> (<?= esc($product['unit_name'] ?? 'Unit') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control quantity-input" name="products[${newRowIndex}][quantity]" min="0.01" step="any" value="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Purchase Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control purchase-price-input" name="products[${newRowIndex}][purchase_price]" value="0.00" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
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
            attachEventListenersToProductRow(newRow);
            updateAllCalculations();
        }

        /**
         * Attaches event listeners to the inputs/selects within a given product row.
         * @param {HTMLElement} row - The product row element.
         */
        function attachEventListenersToProductRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const purchasePriceInput = row.querySelector('.purchase-price-input');
            const removeProductButton = row.querySelector('.remove-product-row');

            if (productSelect) {
                productSelect.addEventListener('change', updateAllCalculations);
            }
            if (quantityInput) {
                quantityInput.addEventListener('input', updateAllCalculations);
            }
            if (purchasePriceInput) {
                purchasePriceInput.addEventListener('input', updateAllCalculations);
            }

            if (removeProductButton) {
                removeProductButton.addEventListener('click', function() {
                    if (productRowsContainer.children.length > 1) {
                        row.remove();
                        updateAllCalculations();
                    } else {
                        // Using a custom message box as window.alert() is not allowed.
                        const messageBox = document.createElement('div');
                        messageBox.className = 'alert alert-warning alert-dismissible fade show mt-3';
                        messageBox.innerHTML = `
                            You must have at least one product in the stock-in entry.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        productRowsContainer.prepend(messageBox);
                    }
                });
            }
            // Initial calculation for this row
            calculateRowTotal(row);
        }

        // --- Event Listeners Initialization ---

        // Event listener for adding a product row
        addProductRowButton.addEventListener('click', addProductRow);

        // Initial payment amount input listener
        initialPaymentAmountInput.addEventListener('input', updateBalanceAmount);

        // Discount input listener
        overallDiscountInput.addEventListener('input', updateAllCalculations);

        // Listener for adding overall GST fields
        addOverallGstButton.addEventListener('click', addOverallGstField);

        // Attach listeners to existing overall GST selects and remove buttons on initial load (for old inputs)
        overallGstFieldsContainer.querySelectorAll('.overall-gst-rate-select').forEach(select => {
            select.addEventListener('change', updateAllCalculations);
        });
        overallGstFieldsContainer.querySelectorAll('.remove-overall-gst-field').forEach(button => {
            button.addEventListener('click', function() {
                if (overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length > 1) {
                    this.closest('.overall-gst-field-group').remove();
                    updateAllCalculations();
                } else {
                    const messageBox = document.createElement('div');
                    messageBox.className = 'alert alert-warning alert-dismissible fade show mt-3';
                    messageBox.innerHTML = `
                        You must have at least one overall GST rate selected.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    overallGstFieldsContainer.prepend(messageBox);
                }
            });
        });

        // Attach listeners to existing product rows on initial load (for old inputs on validation error)
        document.querySelectorAll('.product-item').forEach(attachEventListenersToProductRow);

        // Initial calculation when the page loads (useful if old inputs are present)
        updateAllCalculations();
    });
</script>
<?= $this->endSection() ?>
