<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4 rounded-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-t-lg">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <a href="<?= base_url('distributor-sales') ?>" class="btn btn-light btn-sm">Back to List</a>
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

            <?= form_open(base_url('distributor-sales/save')) ?>

            <div class="card mb-4">
                <div class="card-header">
                    Sales Order Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="distributor_id">Distributor</label>
                            <select name="distributor_id" id="distributor_id" class="form-control select2bs4" required>
                                <option value="">Select a Distributor</option>
                                <?php foreach ($distributors as $distributor): ?>
                                    <option value="<?= esc($distributor['id']) ?>"
                                        <?= (old('distributor_id') == $distributor['id']) ? 'selected' : '' ?>>
                                        <?= esc($distributor['agency_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="pricing_tier">Pricing Tier</label>
                            <select name="pricing_tier" id="pricing_tier" class="form-control select2bs4" required>
                                <option value="dealer" <?= (old('pricing_tier') == 'dealer' || empty(old('pricing_tier'))) ? 'selected' : '' ?>>Dealer Price</option>
                                <option value="farmer" <?= (old('pricing_tier') == 'farmer') ? 'selected' : '' ?>>Farmer Price</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="marketing_person_id">Marketing Person</label>
                            <select name="marketing_person_id" id="marketing_person_id" class="form-control select2bs4" required>
                                <option value="">Select a Marketing Person</option>
                                <?php foreach ($marketingPersons as $person): ?>
                                    <option value="<?= esc($person['id']) ?>"
                                        <?= (old('marketing_person_id') == $person['id']) ? 'selected' : '' ?>>
                                        <?= esc($person['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Products
                    <button type="button" class="btn btn-success btn-sm" id="addProductRowButton">Add a Product</button>
                </div>
                <div class="card-body">
                    <div id="product-items-container">
                        <div class="product-item row mb-3">
                            <div class="col-md-4">
                                <label for="product_id[]">Product</label>
                                <select name="product_id[]" class="form-control product-select select2bs4" required>
                                    <option value="">Select a Product</option>
                                    <?php
                                        // A check to ensure there is always at least one product line in the form
                                        if (!empty($products)):
                                            $firstProductId = old('product_id.0') ?? ($products[0]['id'] ?? '');
                                            foreach ($products as $product):
                                    ?>
                                        <option value="<?= esc($product['id']) ?>"
                                            data-available-stock="<?= esc($product['current_stock']) ?>"
                                            data-prices='<?= htmlspecialchars(json_encode(['dealer' => $product['dealer_price'], 'farmer' => $product['farmer_price']]), ENT_QUOTES) ?>'
                                            <?= ($firstProductId == $product['id']) ? 'selected' : '' ?>>
                                            <?= esc($product['name']) ?>
                                        </option>
                                    <?php endforeach; else: ?>
                                        <option value="">No products available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="quantity[]">Quantity</label>
                                <input type="number" name="quantity[]" class="form-control quantity-input" min="1" required
                                    value="<?= old('quantity.0') ?? 1 ?>">
                                <small class="text-muted available-stock-text"></small>
                            </div>
                            <div class="col-md-2">
                                <label for="rate[]">Rate</label>
                                <input type="number" name="rate[]" class="form-control rate-input" step="0.01" readonly
                                    value="<?= old('rate.0') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="amount[]">Amount</label>
                                <input type="number" name="amount[]" class="form-control amount-input" step="0.01" readonly
                                    value="<?= old('amount.0') ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-product-row" disabled>Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Totals and Payment Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="overall_discount">Overall Discount (₹)</label>
                                <input type="number" name="overall_discount" id="overall_discount" class="form-control" step="0.01" min="0"
                                    value="<?= old('overall_discount') ?>">
                            </div>
                            <div class="form-group">
                                <label>Overall GST Rates</label>
                                <div id="overall_gst_field_container">
                                    </div>
                                <button type="button" class="btn btn-info btn-sm mt-2" id="addOverallGstButton">Add GST Rate</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Sub-Total</td>
                                        <td class="text-right" id="subTotal">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Discount Amount</td>
                                        <td class="text-right" id="discountAmount">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>GST Amount</td>
                                        <td class="text-right" id="gstAmount">0.00</td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Amount</td>
                                        <td class="text-right" id="finalTotal">0.00</td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Initial Payment</td>
                                        <td class="text-right" id="initialPaymentDisplay">0.00</td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Remaining Balance</td>
                                        <td class="text-right" id="remainingBalanceDisplay">0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 form-group">
                            <label for="initial_payment_amount">Initial Payment</label>
                            <input type="number" name="initial_payment_amount" id="initial_payment_amount" class="form-control" step="0.01"
                                value="<?= old('initial_payment_amount') ?>">
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_type">Payment Type</label>
                                <select name="payment_type" id="payment_type" class="form-control">
                                    <option value="">Select Payment Type</option>
                                    <option value="Cash" <?= (old('payment_type') == 'Cash') ? 'selected' : '' ?>>Cash</option>
                                    <option value="Bank Transfer" <?= (old('payment_type') == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                                    <option value="UPI" <?= (old('payment_type') == 'UPI') ? 'selected' : '' ?>>UPI</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="transaction_id">Transaction ID</label>
                                <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                                    value="<?= old('transaction_id') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">Create Sales Order</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pass PHP product data to a JavaScript variable
        const productData = <?= json_encode($products); ?>;
        const gstRatesData = <?= json_encode($gstRates); ?>;

        const productItemsContainer = document.getElementById('product-items-container');
        const addProductRowButton = document.getElementById('addProductRowButton');
        const addOverallGstButton = document.getElementById('addOverallGstButton');
        const overallDiscountInput = document.getElementById('overall_discount');
        const initialPaymentAmountInput = document.getElementById('initial_payment_amount');
        const pricingTierSelect = document.getElementById('pricing_tier');
        const overallGstFieldContainer = document.getElementById('overall_gst_field_container');
        const form = document.querySelector('form');

        // New elements for remaining balance
        const initialPaymentDisplay = document.getElementById('initialPaymentDisplay');
        const remainingBalanceDisplay = document.getElementById('remainingBalanceDisplay');

        // safe JSON parse for data-prices
        function safeParsePrices(str) {
            if (!str) return {};
            try {
                return JSON.parse(str);
            } catch (err) {
                console.error('safeParsePrices: invalid JSON in data-prices ->', str, err);
                return {};
            }
        }

        // update single row's amount from qty * rate
        function updateProductRow(row) {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            row.querySelector('.amount-input').value = (qty * rate).toFixed(2);
        }

        // recalc totals across the page
        function updateAllTotals() {
            let subTotal = 0;
            document.querySelectorAll('.product-item').forEach(row => {
                subTotal += parseFloat(row.querySelector('.amount-input').value) || 0;
            });

            const overallDiscountAmount = parseFloat(overallDiscountInput.value) || 0;
            const subTotalAfterDiscount = subTotal - overallDiscountAmount;

            let gstAmount = 0;
            document.querySelectorAll('.overall-gst-field-group .overall-gst-rate-select').forEach(sel => {
                if (sel.value) {
                    const rate = parseFloat(sel.options[sel.selectedIndex].dataset.rate) || 0;
                    gstAmount += subTotalAfterDiscount * (rate / 100);
                }
            });

            const finalTotal = subTotalAfterDiscount + gstAmount;
            const initialPayment = parseFloat(initialPaymentAmountInput.value) || 0;
            const remainingBalance = finalTotal - initialPayment;

            document.getElementById('subTotal').textContent = subTotal.toFixed(2);
            document.getElementById('discountAmount').textContent = overallDiscountAmount.toFixed(2);
            document.getElementById('gstAmount').textContent = gstAmount.toFixed(2);
            document.getElementById('finalTotal').textContent = finalTotal.toFixed(2);
            initialPaymentDisplay.textContent = initialPayment.toFixed(2);
            remainingBalanceDisplay.textContent = remainingBalance.toFixed(2);
        }

        function togglePaymentFieldsRequired() {
            const paymentTypeInput = document.getElementById('payment_type');
            const transactionIdInput = document.getElementById('transaction_id');
            const initialPaymentAmount = parseFloat(initialPaymentAmountInput.value) || 0;
            if (initialPaymentAmount > 0) {
                paymentTypeInput.required = true;
            } else {
                paymentTypeInput.required = false;
                paymentTypeInput.value = '';
                transactionIdInput.value = '';
            }
        }

        // handle product selection change: set stock, set rate based on pricing tier, update amount/totals
        function handleProductSelectChange(selectEl) {
            const row = selectEl.closest('.product-item');
            if (!row) return;
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const availableStockText = row.querySelector('.available-stock-text');
            const quantityInput = row.querySelector('.quantity-input');
            const rateInput = row.querySelector('.rate-input');

            if (selectedOption && selectedOption.value) {
                const availableStock = selectedOption.dataset.availableStock || '';
                const prices = safeParsePrices(selectedOption.dataset.prices || '{}');
                const tier = pricingTierSelect.value;
                const productPrice = prices[tier] || 0;

                if (availableStock !== '') {
                    availableStockText.textContent = `Available Stock: ${availableStock}`;
                    quantityInput.max = availableStock;
                } else {
                    availableStockText.textContent = '';
                    quantityInput.removeAttribute('max');
                }

                rateInput.value = Number(productPrice).toFixed(2);
            } else {
                availableStockText.textContent = '';
                rateInput.value = '';
                quantityInput.removeAttribute('max');
            }

            updateProductRow(row);
            updateAllTotals();
        }
        
        // This function now only enables remove buttons on rows beyond the first one.
        function toggleRemoveButtons() {
            const rows = productItemsContainer.querySelectorAll('.product-item');
            // Enable remove button for all but the first row
            rows.forEach((row, index) => {
                const removeButton = row.querySelector('.remove-product-row');
                if (removeButton) {
                    removeButton.disabled = index === 0;
                }
            });
        }


        // event delegation: catches changes on dynamically added rows
        productItemsContainer.addEventListener('change', function(e) {
            if (e.target.matches('.product-select')) {
                handleProductSelectChange(e.target);
                return;
            }
        });

        overallGstFieldContainer.addEventListener('change', function(e) {
            if (e.target.matches('.overall-gst-rate-select')) {
                updateAllTotals();
            }
        });

        productItemsContainer.addEventListener('input', function(e) {
            if (e.target.matches('.quantity-input')) {
                const row = e.target.closest('.product-item');
                updateProductRow(row);
                updateAllTotals();
            }
            if (e.target.matches('.rate-input')) {
                const row = e.target.closest('.product-item');
                updateProductRow(row);
                updateAllTotals();
            }
        });

        // clicks for remove buttons (rows & gst fields)
        document.addEventListener('click', function(e) {
            const remBtn = e.target.closest('.remove-product-row');
            if (remBtn && !remBtn.disabled) {
                const row = remBtn.closest('.product-item');
                if (row) {
                    row.remove();
                    updateAllTotals();
                    toggleRemoveButtons();
                }
                return;
            }
            const remGst = e.target.closest('.remove-overall-gst-field');
            if (remGst) {
                const group = remGst.closest('.overall-gst-field-group');
                if (group) {
                    group.remove();
                    updateAllTotals();
                }
                return;
            }
        });

        function createProductRow() {
            const row = document.createElement('div');
            row.className = 'product-item row mb-3';

            let productOptions = '<option value="">Select a Product</option>';
            productData.forEach(product => {
                const pricesJson = JSON.stringify({ dealer: product.dealer_price, farmer: product.farmer_price });
                productOptions += `<option value="${product.id}"
                    data-available-stock="${product.current_stock}"
                    data-prices='${pricesJson}'>
                    ${product.name}
                </option>`;
            });

            row.innerHTML = `
                <div class="col-md-4">
                    <label>Product</label>
                    <select name="product_id[]" class="form-control product-select select2bs4" required>
                        ${productOptions}
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Quantity</label>
                    <input type="number" name="quantity[]" class="form-control quantity-input" min="1" required>
                    <small class="text-muted available-stock-text"></small>
                </div>
                <div class="col-md-2">
                    <label>Rate</label>
                    <input type="number" name="rate[]" class="form-control rate-input" step="0.01" readonly>
                </div>
                <div class="col-md-2">
                    <label>Amount</label>
                    <input type="number" name="amount[]" class="form-control amount-input" step="0.01" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-product-row">Remove</button>
                </div>
            `;
            productItemsContainer.appendChild(row);

            try {
                if (window.jQuery && $(row).find('.select2bs4').select2) {
                    $(row).find('.select2bs4').select2({
                        theme: 'bootstrap4',
                        placeholder: 'Select a Product'
                    });
                }
            } catch (err) {
                console.warn('Select2 init failed for new row:', err);
            }
            toggleRemoveButtons();
        }

        // --- UPDATED FUNCTION ---
        function createOverallGstField() {
            const fieldGroup = document.createElement('div');
            fieldGroup.className = 'overall-gst-field-group d-flex align-items-center w-100 mb-2';

            let gstOptions = '<option value="">Select GST Rate</option>';
            gstRatesData.forEach(gstRate => {
                gstOptions += `<option value="${gstRate.id}" data-rate="${gstRate.rate}">
                    ${gstRate.name} (${gstRate.rate}%)
                </option>`;
            });

            fieldGroup.innerHTML = `
                <select name="overall_gst_rate_ids[]" class="form-control overall-gst-rate-select" required>
                    ${gstOptions}
                </select>
                <button type="button" class="btn btn-danger btn-sm ml-2 remove-overall-gst-field">
                    ❌
                </button>
            `;
            overallGstFieldContainer.appendChild(fieldGroup);
            
            // Add event listener to the newly created remove button
            fieldGroup.querySelector('.remove-overall-gst-field').addEventListener('click', function() {
                fieldGroup.remove();
                updateAllTotals(); // Recalculate totals after removing a GST field
            });
        }
        // --- END OF UPDATED FUNCTION ---

        addProductRowButton.addEventListener('click', createProductRow);
        addOverallGstButton.addEventListener('click', createOverallGstField);

        initialPaymentAmountInput.addEventListener('input', function() {
            updateAllTotals();
            togglePaymentFieldsRequired();
        });
        overallDiscountInput.addEventListener('input', updateAllTotals);

        pricingTierSelect.addEventListener('change', function() {
            document.querySelectorAll('.product-item .product-select').forEach(sel => {
                if (sel.value) handleProductSelectChange(sel);
            });
            updateAllTotals();
        });

        try {
            if (window.jQuery && $.fn.select2) {
                $('.select2bs4').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select a Product'
                });
            }
        } catch (err) {
            console.warn('Select2 init error:', err);
        }

        document.querySelectorAll('.product-item .product-select').forEach(sel => {
            if (sel.value) {
                setTimeout(() => handleProductSelectChange(sel), 0);
            }
        });

        updateAllTotals();
        togglePaymentFieldsRequired();
        toggleRemoveButtons();

        window._salesFormDebug = function() {
            console.log('Number of product rows:', document.querySelectorAll('.product-item').length);
            document.querySelectorAll('.product-item').forEach((row, i) => {
                const sel = row.querySelector('.product-select');
                console.log(i,
                    'value=', sel && sel.value,
                    'data-prices=', sel && sel.dataset && sel.dataset.prices,
                    'rate=', row.querySelector('.rate-input').value,
                    'qty=', row.querySelector('.quantity-input').value,
                    'amount=', row.querySelector('.amount-input').value
                );
            });
        };
    });
</script>


<?php $this->endSection(); ?>