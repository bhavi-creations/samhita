<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2>Edit Sales Order: <?= esc($sales_order['invoice_number']) ?></h2>

    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>
    <?= service('validation')->listErrors() ? '<div class="alert alert-danger">' . service('validation')->listErrors() . '</div>' : '' ?>

    <form action="<?= base_url('distributor-sales/update/' . $sales_order['id']) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT"> <!-- Method spoofing for PUT request -->

        <div class="card mb-4">
            <div class="card-header">
                <h4>Invoice Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="invoice_number" class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="<?= old('invoice_number', esc($sales_order['invoice_number'])) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="invoice_date" class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?= old('invoice_date', esc($sales_order['invoice_date'])) ?>">
                        </div>
                        <div class="mb-3">
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
        </div>
        
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
                                                    <option value="<?= esc($product_option['id']) ?>" data-gst-rate-id="<?= esc($product_option['gst_rate_id']) ?>" data-unit-price="<?= esc($product_option['unit_price'] ?? 0) ?>" <?= (old('items.'.$index.'.product_id', $item['product_id']) == $product_option['id']) ? 'selected' : '' ?>>
                                                        <?= esc($product_option['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control item-quantity" name="items[<?= $index ?>][quantity]" value="<?= old('items.'.$index.'.quantity', esc($item['quantity'])) ?>">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control item-unit-price" name="items[<?= $index ?>][unit_price_at_sale]" value="<?= old('items.'.$index.'.unit_price_at_sale', esc($item['unit_price_at_sale'])) ?>">
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
                                                <option value="<?= esc($product_option['id']) ?>" data-gst-rate-id="<?= esc($product_option['gst_rate_id']) ?>" data-unit-price="<?= esc($product_option['unit_price'] ?? 0) ?>">
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

        <div class="card mb-4">
            <div class="card-header">
                <h4>Invoice Summary</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Total Amount (Before GST)</td>
                            <td class="text-end" id="subtotal">0.00</td>
                        </tr>
                        <tr>
                            <td>Total GST</td>
                            <td class="text-end" id="total-gst">0.00</td>
                        </tr>
                        <tr>
                            <td>**Final Total Amount**</td>
                            <td class="text-end" id="final-total">**0.00**</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= base_url('distributor-sales/view/' . $sales_order['id']) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- JavaScript to handle adding/removing form rows and live calculations -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const products = <?= json_encode($products) ?>;
        const gstRates = <?= json_encode($gst_rates) ?>;

        const productData = products.reduce((acc, product) => {
            acc[product.id] = product;
            return acc;
        }, {});

        const gstData = gstRates.reduce((acc, gst) => {
            acc[gst.id] = gst.rate;
            return acc;
        }, {});

        const itemsTable = document.getElementById('items-table');
        const addItemBtn = document.getElementById('add-item-btn');

        function updateRowTotals(row) {
            const quantityInput = row.querySelector('.item-quantity');
            const priceInput = row.querySelector('.item-unit-price');
            const totalInput = row.querySelector('.item-total');
            const productSelect = row.querySelector('.product-select');

            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const rowTotal = quantity * price;

            totalInput.value = rowTotal.toFixed(2);
        }

        function updateTotalSummary() {
            let totalAmountBeforeGst = 0;
            let totalGstAmount = 0;

            itemsTable.querySelectorAll('tbody tr').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-unit-price').value) || 0;
                const productId = row.querySelector('.product-select').value;

                if (productId) {
                    const product = productData[productId];
                    const gstRate = gstData[product.gst_rate_id] || 0;
                    
                    const itemTotal = quantity * price;
                    const itemGst = (itemTotal * gstRate) / 100;
                    
                    totalAmountBeforeGst += itemTotal;
                    totalGstAmount += itemGst;
                }
            });

            const finalTotal = totalAmountBeforeGst + totalGstAmount;

            document.getElementById('subtotal').innerText = totalAmountBeforeGst.toFixed(2);
            document.getElementById('total-gst').innerText = totalGstAmount.toFixed(2);
            document.getElementById('final-total').innerText = finalTotal.toFixed(2);
        }

        function createProductRow(newIndex = 0) {
            const newRow = `
                <td>
                    <select class="form-control product-select" name="items[${newIndex}][product_id]">
                        <option value="">Select a Product</option>
                        <?php foreach ($products as $product_option): ?>
                            <option value="<?= esc($product_option['id']) ?>" data-gst-rate-id="<?= esc($product_option['gst_rate_id']) ?>" data-unit-price="<?= esc($product_option['unit_price'] ?? 0) ?>">
                                <?= esc($product_option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control item-quantity" name="items[${newIndex}][quantity]" value="">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control item-unit-price" name="items[${newIndex}][unit_price_at_sale]" value="">
                </td>
                <td>
                    <input type="text" class="form-control item-total" readonly value="0.00">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                </td>
            `;
            const tableBody = itemsTable.querySelector('tbody');
            const newTr = tableBody.insertRow();
            newTr.innerHTML = newRow;
        }

        // Event listener for adding new item rows
        addItemBtn.addEventListener('click', function() {
            const tableBody = itemsTable.querySelector('tbody');
            const newIndex = tableBody.rows.length;
            createProductRow(newIndex);
        });

        // Event delegation for table changes (select, quantity, price, remove)
        itemsTable.addEventListener('input', function(e) {
            const row = e.target.closest('tr');
            if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-unit-price')) {
                updateRowTotals(row);
                updateTotalSummary();
            }
        });
        
        itemsTable.addEventListener('change', function(e) {
            const row = e.target.closest('tr');
            if (e.target.classList.contains('product-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const unitPrice = selectedOption.dataset.unitPrice;
                row.querySelector('.item-unit-price').value = unitPrice;
                updateRowTotals(row);
                updateTotalSummary();
            }
        });

        itemsTable.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item-btn')) {
                const row = e.target.closest('tr');
                row.remove();
                updateTotalSummary();
            }
        });

        // Initial calculation on page load
        updateTotalSummary();

    });
</script>

<?php $this->endSection(); ?>
