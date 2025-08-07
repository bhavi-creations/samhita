<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Stock In Entry</h4>
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

            <form action="<?= base_url('stock-in/update/' . $stockInEntry['id']) ?>" method="post" id="stockInForm">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

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
                                    <option value="<?= esc($vendor['id']) ?>" <?= (esc($stockInEntry['vendor_id']) == $vendor['id']) ? 'selected' : '' ?>>
                                        <?= esc($vendor['agency_name']) ?> (<?= esc($vendor['name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date_received" class="form-label">Date Received <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_received" name="date_received" value="<?= esc($stockInEntry['date_received']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= esc($stockInEntry['notes']) ?></textarea>
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
                            <?php foreach ($stockInEntry['products'] as $key => $productData): ?>
                                <div class="row product-item mb-3 gx-2 align-items-center border-bottom pb-2">
                                    <input type="hidden" name="products[<?= $key ?>][id]" value="<?= esc($productData['id']) ?>">
                                    <div class="col-md-3">
                                        <label class="form-label">Product <span class="text-danger">*</span></label>
                                        <select class="form-control product-select" name="products[<?= $key ?>][product_id]" required>
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option
                                                    value="<?= esc($product['id']) ?>"
                                                    data-stock="<?= esc($product['current_stock']) ?>"
                                                    data-unit="<?= esc($product['unit_name'] ?? 'Unit') ?>"
                                                    <?= ($productData['product_id'] == $product['id']) ? 'selected' : '' ?>>
                                                    <?= esc($product['name']) ?> (<?= esc($product['unit_name'] ?? 'Unit') ?>) - Stock: <?= esc($product['current_stock']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted product-stock-display mt-1">Available Stock: N/A</small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Unit</label>
                                        <input type="text" class="form-control product-unit-display" value="<?= esc($productData['unit_name'] ?? 'N/A') ?>" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control quantity-input" name="products[<?= $key ?>][quantity]" min="0.01" step="any" value="<?= esc($productData['quantity']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Purchase Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" class="form-control purchase-price-input" name="products[<?= $key ?>][purchase_price]" value="<?= esc($productData['purchase_price']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Total</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="text" class="form-control item-total-before-gst-display" value="<?= number_format($productData['item_total'], 2) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <?php if (count($stockInEntry['products']) > 1): ?>
                                            <button type="button" class="btn btn-danger btn-sm remove-product-row">X</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-end mb-2">
                                <strong>Total Amount (Excl. GST):</strong> ₹<span id="overallAmountBeforeGst"><?= number_format($stockInEntry['total_amount_before_gst'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Overall GST Details
                        <button type="button" class="btn btn-info btn-sm add-overall-gst-field">Add Another Overall GST</button>
                    </div>
                    <div class="card-body">
                        <div id="overallGstFieldsContainer">
                            <?php foreach ($stockInEntry['gst_rates'] as $gst_idx => $rateData): ?>
                                <div class="mb-3 overall-gst-field-group">
                                    <input type="hidden" name="gst_rate_entry_ids[<?= $gst_idx ?>]" value="<?= esc($rateData['id']) ?>">
                                    <label for="overall_gst_rate_id_<?= $gst_idx ?>" class="form-label">Overall GST Rate #<?= $gst_idx + 1 ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control overall-gst-rate-select" id="overall_gst_rate_id_<?= $gst_idx ?>" name="overall_gst_rate_ids[]" required>
                                            <option value="">Select Overall GST Rate</option>
                                            <?php foreach ($gstRates as $gst): ?>
                                                <option value="<?= esc($gst['id']) ?>" data-rate="<?= esc($gst['rate']) ?>"
                                                    <?= ($rateData['gst_rate_id'] == $gst['id']) ? 'selected' : '' ?>>
                                                    <?= esc($gst['name']) ?> (<?= esc($gst['rate']) ?>%)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (count($stockInEntry['gst_rates']) > 1): ?>
                                            <button type="button" class="btn btn-outline-danger remove-overall-gst-field">X</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-end mb-2">
                            <strong>Total GST Amount:</strong> ₹<span id="overallGstAmount"><?= number_format($stockInEntry['gst_amount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-end mb-2">
                            <strong>Grand Total (Before Discount):</strong> ₹<span id="grandTotalBeforeDiscount"><?= number_format($stockInEntry['grand_total'], 2) ?></span>
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
                            <input type="number" step="0.01" class="form-control" id="discount" name="discount_amount" value="<?= esc($stockInEntry['discount_amount']) ?>" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="initial_payment_amount" class="form-label">Amount Paid Now</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="initial_payment_amount" name="initial_payment_amount" value="<?= esc($stockInEntry['initial_amount_paid']) ?>" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="balance_amount_display" class="form-label">Balance Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="text" class="form-control" id="balance_amount_display" value="<?= number_format($stockInEntry['balance_amount'], 2) ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_type">
                                <option value="">Select Payment Method</option>
                                <?php foreach ($paymentMethods as $key_method => $method_name): ?>
                                    <option value="<?= esc($key_method) ?>" <?= (esc($stockInEntry['payment_type']) == $key_method) ? 'selected' : '' ?>>
                                        <?= esc($method_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID (if applicable)</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="<?= esc($stockInEntry['transaction_id']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Payment Notes</label>
                            <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"><?= esc($stockInEntry['payment_notes']) ?></textarea>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="total_amount_before_gst_hidden" name="total_amount_before_gst" value="<?= esc($stockInEntry['total_amount_before_gst']) ?>">
                <input type="hidden" id="gst_amount_hidden" name="gst_amount" value="<?= esc($stockInEntry['gst_amount']) ?>">
                <input type="hidden" id="grand_total_hidden" name="grand_total" value="<?= esc($stockInEntry['grand_total']) ?>">
                <input type="hidden" id="amount_pending_hidden" name="amount_pending" value="<?= esc($stockInEntry['balance_amount']) ?>">

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= base_url('stock-in') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productRowsContainer = document.getElementById('productRows');
        const addProductRowButton = document.getElementById('addProductRow');
        const overallAmountBeforeGstDisplay = document.getElementById('overallAmountBeforeGst');
        const overallGstAmountDisplay = document.getElementById('overallGstAmount');
        const grandTotalBeforeDiscountDisplay = document.getElementById('grandTotalBeforeDiscount');

        const overallGstFieldsContainer = document.getElementById('overallGstFieldsContainer');
        const addOverallGstButton = document.querySelector('.add-overall-gst-field');

        const initialPaymentAmountInput = document.getElementById('initial_payment_amount');
        const balanceAmountDisplay = document.getElementById('balance_amount_display');
        const overallDiscountInput = document.getElementById('discount');
        const paymentMethodSelect = document.getElementById('payment_method');

        const totalAmountBeforeGstHidden = document.getElementById('total_amount_before_gst_hidden');
        const gstAmountHidden = document.getElementById('gst_amount_hidden');
        const grandTotalHidden = document.getElementById('grand_total_hidden');
        const amountPendingHidden = document.getElementById('amount_pending_hidden');
        
        let latestGrandTotalAfterDiscount = 0;

        const products = <?= json_encode($products) ?>;
        const gstRates = <?= json_encode($gstRates) ?>;

        function getProductDetails(productId) {
            return products.find(p => String(p.id) === String(productId));
        }
        
        function updateProductInfoDisplay(row) {
            const productSelect = row.querySelector('.product-select');
            const stockDisplay = row.querySelector('.product-stock-display');
            const unitDisplay = row.querySelector('.product-unit-display');
            const productId = productSelect.value;
            
            if (productId) {
                const product = getProductDetails(productId);
                if (product) {
                    if (stockDisplay) stockDisplay.textContent = `Available Stock: ${product.current_stock}`;
                    if (unitDisplay) unitDisplay.value = product.unit_name || 'N/A';
                } else {
                    if (stockDisplay) stockDisplay.textContent = 'Available Stock: N/A';
                    if (unitDisplay) unitDisplay.value = 'N/A';
                }
            } else {
                if (stockDisplay) stockDisplay.textContent = 'Available Stock: N/A';
                if (unitDisplay) unitDisplay.value = 'N/A';
            }
        }
        
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

        function updateAllCalculations() {
            let totalAmountBeforeGst = 0;
            const discountAmount = parseFloat(overallDiscountInput.value) || 0;

            document.querySelectorAll('.product-item').forEach(row => {
                updateProductInfoDisplay(row);
                totalAmountBeforeGst += calculateRowTotal(row);
            });
            
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
            
            totalAmountBeforeGstHidden.value = totalAmountBeforeGst.toFixed(2);
            gstAmountHidden.value = overallGstAmount.toFixed(2);
            grandTotalHidden.value = finalGrandTotal.toFixed(2);
            
            latestGrandTotalAfterDiscount = finalGrandTotal;
            updateBalanceAmount();
        }

        function updateBalanceAmount() {
            const amountPaid = parseFloat(initialPaymentAmountInput.value) || 0;
            const balance = latestGrandTotalAfterDiscount - amountPaid;
            
            if (balanceAmountDisplay) balanceAmountDisplay.value = balance.toFixed(2);
            amountPendingHidden.value = balance.toFixed(2);
        }

        function addOverallGstField(rateData = {}) {
            const newGstIndex = overallGstFieldsContainer.querySelectorAll('.overall-gst-field-group').length;
            const newGstFieldGroup = document.createElement('div');
            newGstFieldGroup.className = 'mb-3 overall-gst-field-group';
            newGstFieldGroup.innerHTML = `
                <input type="hidden" name="gst_rate_entry_ids[${newGstIndex}]" value="${rateData.id || ''}">
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
            
            const select = newGstFieldGroup.querySelector('.overall-gst-rate-select');
            if (rateData.gst_rate_id) {
                select.value = rateData.gst_rate_id;
            }
            
            select.addEventListener('change', updateAllCalculations);
            newGstFieldGroup.querySelector('.remove-overall-gst-field').addEventListener('click', function() {
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
            updateAllCalculations();
        }

        function addProductRow(productData = {}) {
            const newRowIndex = productRowsContainer.children.length;
            const newRow = document.createElement('div');
            newRow.className = 'row product-item mb-3 gx-2 align-items-center border-bottom pb-2';
            newRow.innerHTML = `
                <input type="hidden" name="products[${newRowIndex}][id]" value="${productData.id || ''}">
                <div class="col-md-3">
                    <label class="form-label">Product <span class="text-danger">*</span></label>
                    <select class="form-control product-select" name="products[${newRowIndex}][product_id]" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?= esc($product['id']) ?>"
                                data-stock="<?= esc($product['current_stock']) ?>"
                                data-unit="<?= esc($product['unit_name'] ?? 'Unit') ?>">
                                <?= esc($product['name']) ?> (<?= esc($product['unit_name'] ?? 'Unit') ?>) - Stock: <?= esc($product['current_stock']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted product-stock-display mt-1">Available Stock: N/A</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input type="text" class="form-control product-unit-display" value="N/A" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control quantity-input" name="products[${newRowIndex}][quantity]" min="0.01" step="any" value="${productData.quantity || '1'}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Purchase Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control purchase-price-input" name="products[${newRowIndex}][purchase_price]" value="${productData.purchase_price || '0.00'}" required>
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
            if (productData.product_id) {
                newRow.querySelector('.product-select').value = productData.product_id;
            }
            updateAllCalculations();
        }

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
            updateProductInfoDisplay(row);
            calculateRowTotal(row);
        }

        // --- Event Listeners Initialization ---
        addProductRowButton.addEventListener('click', addProductRow);
        initialPaymentAmountInput.addEventListener('input', updateBalanceAmount);
        overallDiscountInput.addEventListener('input', updateAllCalculations);
        addOverallGstButton.addEventListener('click', addOverallGstField);

        document.querySelectorAll('.product-item').forEach(attachEventListenersToProductRow);
        
        document.querySelectorAll('.overall-gst-field-group').forEach(row => {
            const select = row.querySelector('.overall-gst-rate-select');
            const removeButton = row.querySelector('.remove-overall-gst-field');
            
            if (select) {
                select.addEventListener('change', updateAllCalculations);
            }
            if (removeButton) {
                removeButton.addEventListener('click', function() {
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
            }
        });

        // Initial calculation when the page loads
        latestGrandTotalAfterDiscount = parseFloat(grandTotalHidden.value);
        updateAllCalculations();
    });
</script>
<?= $this->endSection() ?>
