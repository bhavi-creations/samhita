<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2>Edit Sales Order: <?= esc($sales_order['invoice_number']) ?></h2>

   
    <form action="<?= base_url('distributor-sales/update/' . $sales_order['id']) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT"> <!-- Method spoofing for PUT request -->

        <!-- Invoice Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Invoice Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="mb-3 col-4">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="<?= old('invoice_number', esc($sales_order['invoice_number'])) ?>" readonly>
                    </div>
                    <div class="mb-3 col-3">
                        <label for="invoice_date" class="form-label">Invoice Date</label>
                        <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?= old('invoice_date', esc($sales_order['invoice_date'])) ?>">
                    </div>
                    <div class="mb-3 col-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <?php $statuses = ['Pending', 'Partially Paid', 'Paid', 'Cancelled']; ?>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= esc($status) ?>" <?= (old('status', $sales_order['status']) == $status) ? 'selected' : '' ?>><?= esc($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distributor and Marketing Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Distributor and Marketing Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="distributor_id" class="form-label">Distributor</label>
                            <select class="form-control" id="distributor_id" name="distributor_id">
                                <option value="">Select a Distributor</option>
                                <?php foreach ($distributors as $distributor_option): ?>
                                    <option value="<?= esc($distributor_option['id']) ?>" <?= (old('distributor_id', $sales_order['distributor_id']) == $distributor_option['id']) ? 'selected' : '' ?>>
                                        <?= esc($distributor_option['agency_name']) ?> (<?= esc($distributor_option['owner_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pricing_tier" class="form-label">Pricing Tier</label>
                            <select name="pricing_tier" id="pricing_tier" class="form-control" required>
                                <option value="dealer" <?= (old('pricing_tier', $sales_order['pricing_tier']) == 'dealer') ? 'selected' : '' ?>>Dealer Price</option>
                                <option value="farmer" <?= (old('pricing_tier', $sales_order['pricing_tier']) == 'farmer') ? 'selected' : '' ?>>Farmer Price</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="marketing_person_id" class="form-label">Marketing Person</label>
                            <select class="form-control" id="marketing_person_id" name="marketing_person_id">
                                <option value="">Select a Marketing Person</option>
                                <?php foreach ($marketing_persons as $mp_option): ?>
                                    <option value="<?= esc($mp_option['id']) ?>" <?= (old('marketing_person_id', $sales_order['marketing_person_id']) == $mp_option['id']) ? 'selected' : '' ?>>
                                        <?= esc($mp_option['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Invoice Items</h4>
                <button type="button" class="btn btn-sm btn-success" id="add-item-btn">Add Item</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales_order_items)): ?>
                                <?php foreach ($sales_order_items as $index => $item): ?>
                                    <tr>
                                        <td>
                                            <select class="form-control product-select" name="items[<?= $index ?>][product_id]">
                                                <option value="">Select a Product</option>
                                                <?php foreach ($products as $product_option): ?>
                                                    <?php
                                                    $isSelected = (isset($item['product_id']) && $item['product_id'] == $product_option['id']) || (old('items.' . $index . '.product_id') == $product_option['id']);
                                                    ?>
                                                    <option value="<?= esc($product_option['id']) ?>"
                                                        data-gst-rate-id="<?= esc($product_option['gst_rate_id'] ?? '') ?>"
                                                        data-dealer-price="<?= esc($product_option['dealer_price'] ?? 0) ?>"
                                                        data-farmer-price="<?= esc($product_option['farmer_price'] ?? 0) ?>"
                                                        <?= $isSelected ? 'selected' : '' ?>>
                                                        <?= esc($product_option['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control item-quantity" name="items[<?= $index ?>][quantity]" value="<?= old('items.' . $index . '.quantity', esc($item['quantity'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control item-unit-price" name="items[<?= $index ?>][unit_price_at_sale]" value="<?= old('items.' . $index . '.unit_price_at_sale', esc($item['unit_price_at_sale'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control item-total" readonly value="<?= esc($item['item_total'] ?? 0) ?>">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <select class="form-control product-select" name="items[0][product_id]">
                                            <option value="">Select a Product</option>
                                            <?php foreach ($products as $product_option): ?>
                                                <option value="<?= esc($product_option['id']) ?>"
                                                    data-gst-rate-id="<?= esc($product_option['gst_rate_id'] ?? '') ?>"
                                                    data-dealer-price="<?= esc($product_option['dealer_price'] ?? 0) ?>"
                                                    data-farmer-price="<?= esc($product_option['farmer_price'] ?? 0) ?>">
                                                    <?= esc($product_option['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control item-quantity" name="items[0][quantity]" value="<?= old('items.0.quantity') ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control item-unit-price" name="items[0][unit_price_at_sale]" value="<?= old('items.0.unit_price_at_sale') ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control item-total" readonly value="0.00">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Totals and Payment Details Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Totals and Payment Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="discount_amount">Overall Discount (₹)</label>
                            <input type="number" name="discount_amount" id="discount_amount" class="form-control" step="0.01" min="0"
                                value="<?= old('discount_amount', esc($sales_order['discount_amount'] ?? 0)) ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label>Overall GST Rates</label>
                            <div id="overall_gst_field_container">
                                <?php

                                $overallGsts = $sales_order['overall_gst'] ?? [];

                                if (is_array($overallGsts) && !empty($overallGsts)):
                                    foreach ($overallGsts as $index => $gst):
                                        // Use the data from the database to populate the fields.
                                        // The `old()` function provides a fallback in case a form validation fails.
                                        $selectedGstId = old("overall_gst.{$index}.gst_rate_id", $gst['gst_rate_id'] ?? null);
                                        $gstAmountValue = old("overall_gst.{$index}.amount", $gst['amount'] ?? 0);
                                        ?>
                                        <div class="input-group mb-2 overall-gst-item">
                                            <select class="form-control overall-gst-select" name="overall_gst[<?= $index ?>][gst_rate_id]">
                                                <option value="">Select a GST Rate</option>
                                                <?php foreach ($gst_rates as $gst_option): ?>
                                                    <option value="<?= esc($gst_option['id']) ?>"
                                                            data-rate="<?= esc($gst_option['rate']) ?>"
                                                            <?= ($selectedGstId == $gst_option['id']) ? 'selected' : '' ?>>
                                                        <?= esc($gst_option['name']) ?> (<?= esc($gst_option['rate']) ?>%)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="number" name="overall_gst[<?= $index ?>][amount]" class="form-control overall-gst-amount" readonly value="<?= esc($gstAmountValue) ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-overall-gst-btn">Remove</button>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                            <button type="button" class="btn btn-info btn-sm mt-2" id="addOverallGstButton">Add GST Rate</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Sub-Total</td>
                                    <td class="text-end" id="subTotal">0.00</td>
                                </tr>
                                <tr>
                                    <td>Discount Amount</td>
                                    <td class="text-end" id="discountAmount">0.00</td>
                                </tr>
                                <tr>
                                    <td>Total Amount (Before GST)</td>
                                    <td class="text-end" id="totalBeforeGst">0.00</td>
                                </tr>
                                <tr>
                                    <td>Total GST Amount</td>
                                    <td class="text-end" id="gstAmount">0.00</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Grand Total </td>
                                    <td class="text-end" id="finalTotal">0.00</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Amount Paid</td>
                                    <td class="text-end" id="initialPaymentDisplay">0.00</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Remaining Balance</td>
                                    <td class="text-end" id="remainingBalanceDisplay">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 form-group">
                        <!-- New: Payment Type Dropdown -->
                        <label for="payment_type">Payment Type</label>
                        <?php
                        // Check if a payment record exists to determine the initial payment type
                        $paymentType = 'Cash'; // Default
                        $transactionId = '';
                        if (isset($payments) && !empty($payments)) {
                            // Find the first payment and use its type.
                            $paymentType = $payments[0]['payment_method'];
                            $transactionId = $payments[0]['transaction_id'] ?? '';
                        } elseif ($sales_order['amount_paid'] == 0 && $sales_order['due_amount'] > 0) {
                            $paymentType = 'Credit'; // This order was likely created with a credit type
                        }
                        ?>
                        <select name="payment_type" id="payment_type" class="form-control">
                            <option value="Cash" <?= ($paymentType == 'Cash') ? 'selected' : '' ?>>Cash</option>
                            <option value="Online" <?= ($paymentType == 'Online') ? 'selected' : '' ?>>Online</option>
                            <option value="Cheque" <?= ($paymentType == 'Cheque') ? 'selected' : '' ?>>Cheque</option>
                            <option value="Credit" <?= ($paymentType == 'Credit') ? 'selected' : '' ?>>Credit</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group" id="initial-payment-field-group" style="display: <?= ($paymentType == 'Credit') ? 'none' : 'block' ?>;">
                        <label for="amount_paid">Initial Payment</label>
                        <input type="number" name="amount_paid" id="amount_paid" class="form-control" step="0.01"
                               value="<?= old('amount_paid', esc($sales_order['amount_paid'] ?? 0)) ?>">
                    </div>
                    <div class="col-md-6 form-group" id="transaction-id-field-group" style="display: <?= ($paymentType == 'Credit' || $paymentType == 'Cash') ? 'none' : 'block' ?>;">
                        <label for="transaction_id">Transaction ID</label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                               value="<?= esc($transactionId) ?>">
                    </div>
                </div>

                <div class="form-group mb-3 mt-4">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"><?= old('notes', esc($sales_order['notes'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Sales Order</button>
        <a href="<?= base_url('distributor-sales') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Reusable row template for adding new items -->
<div id="item-template" style="display:none;">
    <tr>
        <td>
            <select class="form-control product-select" name="items[__index__][product_id]">
                <option value="">Select a Product</option>
                <?php foreach ($products as $product_option): ?>
                    <option value="<?= esc($product_option['id']) ?>"
                            data-gst-rate-id="<?= esc($product_option['gst_rate_id'] ?? '') ?>"
                            data-dealer-price="<?= esc($product_option['dealer_price'] ?? 0) ?>"
                            data-farmer-price="<?= esc($product_option['farmer_price'] ?? 0) ?>">
                        <?= esc($product_option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control item-quantity" name="items[__index__][quantity]">
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control item-unit-price" name="items[__index__][unit_price_at_sale]">
        </td>
        <td>
            <input type="text" class="form-control item-total" readonly value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
        </td>
    </tr>
</div>

<!-- Reusable template for adding new GST rates -->
<div id="overall-gst-template" style="display:none;">
    <div class="input-group mb-2 overall-gst-item">
        <select class="form-control overall-gst-select" name="overall_gst[__index__][gst_rate_id]">
            <option value="">Select a GST Rate</option>
            <?php foreach ($gst_rates as $gst_option): ?>
                <option value="<?= esc($gst_option['id']) ?>" data-rate="<?= esc($gst_option['rate']) ?>">
                    <?= esc($gst_option['name']) ?> (<?= esc($gst_option['rate']) ?>%)
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="overall_gst[__index__][amount]" class="form-control overall-gst-amount" readonly value="0.00">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger remove-overall-gst-btn">Remove</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find all the necessary elements
        const form = document.querySelector('form');
        const itemsTable = document.getElementById('items-table');
        const overallGstContainer = document.getElementById('overall_gst_field_container');
        const overallDiscountInput = document.getElementById('discount_amount');
        const paymentTypeSelect = document.getElementById('payment_type');
        const initialPaymentFieldGroup = document.getElementById('initial-payment-field-group');
        const transactionIdFieldGroup = document.getElementById('transaction-id-field-group');
        const initialPaymentInput = document.getElementById('amount_paid');
        const addOverallGstButton = document.getElementById('addOverallGstButton');

        /**
         * Function to update a single row's total.
         * @param {Element} row
         */
        function updateRowTotals(row) {
            const quantityInput = row.querySelector('.item-quantity');
            const priceInput = row.querySelector('.item-unit-price');
            const totalInput = row.querySelector('.item-total');

            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;

            const rowTotal = quantity * price;
            totalInput.value = rowTotal.toFixed(2);
        }

        /**
         * Recalculates all totals based on the correct order of operations.
         * 1. Calculate sub-total from all items.
         * 2. Apply the overall discount to get the Total Amount (Before GST).
         * 3. Calculate overall GST based on the Total Amount (Before GST).
         * 4. Sum up the Total Amount (Before GST) and GST to get the final total.
         */
        function updateTotalSummary() {
            // 1. Calculate the raw sub-total from all line items.
            let subTotal = 0;
            itemsTable.querySelectorAll('tbody tr').forEach(row => {
                const itemTotal = parseFloat(row.querySelector('.item-total').value) || 0;
                subTotal += itemTotal;
            });

            // 2. Apply the overall discount to get the Total Amount (Before GST).
            const overallDiscount = parseFloat(overallDiscountInput.value) || 0;
            const totalAmountBeforeGst = subTotal - overallDiscount;

            // 3. Calculate total GST based on the discounted sub-total.
            let totalGstAmount = 0;
            overallGstContainer.querySelectorAll('.overall-gst-item').forEach(gstItem => {
                const selectElement = gstItem.querySelector('.overall-gst-select');
                const amountInput = gstItem.querySelector('.overall-gst-amount');
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const gstRate = parseFloat(selectedOption.dataset.rate) || 0;
                
                // Calculate GST on the totalAmountBeforeGst
                const gstOnSubtotal = totalAmountBeforeGst * (gstRate / 100);
                amountInput.value = gstOnSubtotal.toFixed(2);
                totalGstAmount += gstOnSubtotal;
            });

            // 4. Calculate the final total.
            const finalTotal = totalAmountBeforeGst + totalGstAmount;
            
            // Get initial payment, but check if payment type is 'Credit'. If so, it's 0.
            const initialPayment = (paymentTypeSelect.value === 'Credit') ? 0 : (parseFloat(initialPaymentInput.value) || 0);

            const remainingBalance = finalTotal - initialPayment;

            // Update display elements
            document.getElementById('subTotal').innerText = subTotal.toFixed(2);
            document.getElementById('discountAmount').innerText = overallDiscount.toFixed(2);
            document.getElementById('totalBeforeGst').innerText = totalAmountBeforeGst.toFixed(2);
            document.getElementById('gstAmount').innerText = totalGstAmount.toFixed(2);
            document.getElementById('finalTotal').innerText = finalTotal.toFixed(2);
            document.getElementById('initialPaymentDisplay').innerText = initialPayment.toFixed(2);
            document.getElementById('remainingBalanceDisplay').innerText = remainingBalance.toFixed(2);
        }

        /**
         * Toggles the visibility of payment fields based on the selected payment type.
         */
        function togglePaymentFields() {
            const paymentType = paymentTypeSelect.value;
            const isCredit = paymentType === 'Credit';
            const isCash = paymentType === 'Cash';

            if (isCredit) {
                initialPaymentFieldGroup.style.display = 'none';
                transactionIdFieldGroup.style.display = 'none';
                initialPaymentInput.value = '0'; // Set value to 0 for credit
            } else if (isCash) {
                initialPaymentFieldGroup.style.display = 'block';
                transactionIdFieldGroup.style.display = 'none';
            } else { // Online or Cheque
                initialPaymentFieldGroup.style.display = 'block';
                transactionIdFieldGroup.style.display = 'block';
            }
            updateTotalSummary(); // Recalculate totals after changing payment type
        }

        /**
         * Handles change events for product, quantity, and price inputs.
         * @param {Event} e
         */
        function handleInputOrChangeEvent(e) {
            const target = e.target;
            const row = target.closest('tr');
            if (!row) return;

            // Handle product select change
            if (target.classList.contains('product-select')) {
                const selectedOption = target.options[target.selectedIndex];
                const priceInput = row.querySelector('.item-unit-price');
                const pricingTierSelect = document.getElementById('pricing_tier');
                const selectedTier = pricingTierSelect.value;
                const newPrice = selectedOption.dataset[selectedTier + 'Price'];

                if (selectedOption.value && newPrice !== undefined) {
                    priceInput.value = parseFloat(newPrice).toFixed(2);
                } else {
                    priceInput.value = '0.00';
                }
            }
            updateRowTotals(row);
            updateTotalSummary();
        }

        /**
         * Adds a new item row to the table.
         */
        function addItemRow() {
            const template = document.getElementById('item-template');
            const newRow = template.content.cloneNode(true).querySelector('tr');
            const currentIndex = itemsTable.querySelectorAll('tbody tr').length;

            newRow.innerHTML = newRow.innerHTML.replace(/__index__/g, currentIndex);
            itemsTable.querySelector('tbody').appendChild(newRow);

            updateTotalSummary();
        }

        /**
         * Adds a new overall GST row.
         */
        function addOverallGstRow() {
            const template = document.getElementById('overall-gst-template');
            const newRow = template.content.cloneNode(true).querySelector('div');
            const currentIndex = overallGstContainer.querySelectorAll('.overall-gst-item').length;

            newRow.innerHTML = newRow.innerHTML.replace(/__index__/g, currentIndex);
            overallGstContainer.appendChild(newRow);

            updateTotalSummary();
        }

        // --- Event Listeners ---
        
        // Listen for changes on the payment type dropdown
        paymentTypeSelect.addEventListener('change', togglePaymentFields);

        // Listen for changes to product, quantity, unit price, overall discount, and initial payment
        form.addEventListener('change', function(e) {
            const target = e.target;

            if (target.classList.contains('product-select') || target.classList.contains('item-quantity') || target.classList.contains('item-unit-price')) {
                handleInputOrChangeEvent(e);
            } else if (target.id === 'discount_amount' || target.id === 'amount_paid' || target.classList.contains('overall-gst-select')) {
                updateTotalSummary();
            }
        });

        // Listen for clicks on the "Add Item" button
        document.getElementById('add-item-btn').addEventListener('click', addItemRow);

        // Listen for clicks on the "Add GST Rate" button
        addOverallGstButton.addEventListener('click', addOverallGstRow);

        // Listen for clicks on the "Remove Item" or "Remove GST Rate" buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item-btn')) {
                const row = e.target.closest('tr');
                if (itemsTable.querySelectorAll('tbody tr').length > 1) {
                    row.remove();
                    reindexRows();
                    updateTotalSummary();
                } else {
                    alert("Sales order must contain at least one product item.");
                }
            } else if (e.target.classList.contains('remove-overall-gst-btn')) {
                e.target.closest('.overall-gst-item').remove();
                updateTotalSummary();
            }
        });
        
        // Re-index rows after removing an item
        function reindexRows() {
            itemsTable.querySelectorAll('tbody tr').forEach((row, index) => {
                row.querySelectorAll('[name^="items["]').forEach(element => {
                    const newName = element.name.replace(/\[\d+\]/, `[${index}]`);
                    element.name = newName;
                });
            });
        }
        
        // Initial setup on page load
        updateTotalSummary();
        togglePaymentFields();
    });
</script>

<?php $this->endSection(); ?>
