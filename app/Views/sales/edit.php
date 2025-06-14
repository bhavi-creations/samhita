<?php // Path: app/Views/sales/edit.php ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?> (ID: <?= esc($sale['id']) ?>)</h4>
            <a href="<?= base_url('sales') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Sales List</a>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('sales/update/' . esc($sale['id'])) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT"> <div class="row mb-4">
                    <div class="mb-3 col-4">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= esc($product['id']) ?>"
                                    <?= set_select('product_id', $product['id'], ($sale['product_id'] == $product['id'])) ?>>
                                    <?= esc($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 col-4">
                        <label for="marketing_person_id" class="form-label">Marketing Person</label>
                        <select class="form-select" id="marketing_person_id" name="marketing_person_id" required>
                            <option value="">Select Marketing Person</option>
                            <?php foreach ($marketing_persons as $person): ?>
                                <option value="<?= esc($person['id']) ?>"
                                    <?= set_select('marketing_person_id', $person['id'], ($sale['marketing_person_id'] == $person['id'])) ?>>
                                    <?= esc($person['name']) ?> (<?= esc($person['custom_id']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="overallRemainingQtyDisplay" class="alert alert-info col-4 d-flex align-items-center justify-content-center">
                        <strong>Overall Remaining Stock for Selected Product/Person: <span id="currentOverallRemainingQty" class="ms-2">0</span></strong>
                    </div>
                </div>

                <div id="saleEntriesContainer">
                    <div class="sale-entry row g-3 align-items-end border p-3 mb-3" data-index="0">
                        <div class="row mt-3">
                            <h4>Sale Details</h4>

                            <div class="col-md-2">
                                <label for="date_sold_0" class="form-label">Date Sold</label>
                                <input type="date" class="form-control" id="date_sold_0" name="date_sold"
                                       value="<?= set_value('date_sold', $sale['date_sold'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label for="quantity_sold_0" class="form-label">Quantity Sold</label>
                                <input type="number" class="form-control quantity-sold" id="quantity_sold_0"
                                       name="quantity_sold" placeholder="Quantity Sold" required min="1"
                                       value="<?= set_value('quantity_sold', $sale['quantity_sold']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="price_per_unit_0" class="form-label">Price Per Unit</label>
                                <input type="number" step="0.01" class="form-control price-per-unit"
                                       id="price_per_unit_0" name="price_per_unit" placeholder="Price Per Unit"
                                       value="<?= set_value('price_per_unit', $sale['price_per_unit']) ?>" readonly required>
                            </div>
                            <div class="col-md-2">
                                <label for="discount_0" class="form-label">Discount</label>
                                <input type="number" step="0.01" class="form-control discount" id="discount_0"
                                       name="discount" value="<?= set_value('discount', $sale['discount'] ?? '0.00') ?>" min="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total (No Discount)</label>
                                <p class="form-control-static">₹ <span class="total-no-discount-display">0.00</span></p>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total (After Discount)</label>
                                <p class="form-control-static">₹ <span class="total-with-discount-display">0.00</span></p>
                            </div>
                        </div>
                        <div class="row mt-4 mb-2">
                            <h4>Customer Details</h4>
                            <div class="col-md-4">
                                <label for="customer_name_0" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customer_name_0" name="customer_name"
                                       placeholder="Customer Name" required
                                       value="<?= set_value('customer_name', $sale['customer_name']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="customer_phone_0" class="form-label">Customer Phone</label>
                                <input type="tel" class="form-control" id="customer_phone_0" name="customer_phone"
                                       placeholder="Customer Phone" pattern="[0-9]{10}" title="10 digit phone number" required
                                       value="<?= set_value('customer_phone', $sale['customer_phone']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="customer_address_0" class="form-label">Customer Address</label>
                                <textarea class="form-control" id="customer_address_0" name="customer_address"
                                          placeholder="Customer Address" rows="1"><?= set_value('customer_address', $sale['customer_address']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let initialDbRemainingStock = 0;
    // Initialize currentProductSellingPrice with the value from the fetched sale
    let currentProductSellingPrice = parseFloat(document.getElementById('price_per_unit_0').value) || 0;
    
    // Original quantity of THIS sale record.
    // This is crucial for calculating the *actual* available stock for validation
    // because we are effectively "returning" the old quantity to stock before selling the new.
    let originalQuantitySoldForThisSale = parseFloat(<?= json_encode($sale['quantity_sold'] ?? 0) ?>) || 0;


    // Function to calculate and display prices for a single entry
    const calculateAndDisplayEntryTotals = (entryDiv) => {
        const quantitySold = parseFloat(entryDiv.querySelector('.quantity-sold').value) || 0;
        const pricePerUnit = parseFloat(entryDiv.querySelector('.price-per-unit').value) || 0;
        const discount = parseFloat(entryDiv.querySelector('.discount').value) || 0;

        const totalNoDiscount = quantitySold * pricePerUnit;
        let totalWithDiscount = totalNoDiscount - discount;
        if (totalWithDiscount < 0) totalWithDiscount = 0; // Prevent negative total

        entryDiv.querySelector('.total-no-discount-display').textContent = totalNoDiscount.toFixed(2);
        entryDiv.querySelector('.total-with-discount-display').textContent = totalWithDiscount.toFixed(2);
    };

    // Function to update the overall remaining stock display
    const updateOverallRemainingDisplay = () => {
        const currentQuantitySoldOnForm = parseFloat(document.querySelector('.sale-entry .quantity-sold').value) || 0;
        
        // When editing, the initialDbRemainingStock from the API is the stock *before* this sale.
        // To get the true current available stock, we need to add back the original quantity
        // of THIS sale before subtracting the new quantity being entered.
        const effectiveRemaining = initialDbRemainingStock + originalQuantitySoldForThisSale - currentQuantitySoldOnForm;
        
        const overallDisplayDiv = document.getElementById('overallRemainingQtyDisplay');
        const currentOverallQtySpan = document.getElementById('currentOverallRemainingQty');

        currentOverallQtySpan.textContent = effectiveRemaining;

        // Visual feedback for stock
        overallDisplayDiv.classList.remove('alert-info', 'alert-danger', 'alert-warning');
        if (effectiveRemaining < 0) {
            overallDisplayDiv.classList.add('alert-danger');
        } else if (effectiveRemaining <= 5) { // Example: Warning for low stock
            overallDisplayDiv.classList.add('alert-warning');
        } else {
            overallDisplayDiv.classList.add('alert-info');
        }
    };


    // Function to fetch product details and update fields (now also updates global stock and price)
    const updateProductDetailsAndStock = async () => {
        const productId = document.getElementById('product_id').value;
        const marketingPersonId = document.getElementById('marketing_person_id').value;
        
        // Get the current sale's ID to exclude its quantity from the initial stock calculation by the backend
        const saleId = <?= json_encode($sale['id']) ?>; // Pass the ID of the current sale to exclude from stock calc

        if (productId && marketingPersonId) {
            try {
                // IMPORTANT: Append the saleId to the request so the backend can exclude its own quantity
                const response = await fetch(`<?= base_url('sales/get-remaining-stock') ?>?product_id=${productId}&marketing_person_id=${marketingPersonId}&exclude_sale_id=${saleId}`);
                const data = await response.json();

                if (data.status === 'success') {
                    // initialDbRemainingStock here means the stock *before* THIS sale, *excluding* THIS sale's original quantity
                    initialDbRemainingStock = data.remaining_qty; 
                    currentProductSellingPrice = data.price_per_unit;

                    // Update price for the single existing entry
                    const mainEntryDiv = document.querySelector('.sale-entry[data-index="0"]');
                    if (mainEntryDiv) {
                        mainEntryDiv.querySelector(`.price-per-unit`).value = currentProductSellingPrice.toFixed(2);
                        calculateAndDisplayEntryTotals(mainEntryDiv); // Recalculate totals for this entry
                    }
                } else {
                    console.error('Error fetching product details:', data.message);
                    initialDbRemainingStock = 0;
                    currentProductSellingPrice = 0;
                    const mainEntryDiv = document.querySelector('.sale-entry[data-index="0"]');
                    if (mainEntryDiv) {
                        mainEntryDiv.querySelector(`.price-per-unit`).value = 0;
                        calculateAndDisplayEntryTotals(mainEntryDiv);
                    }
                    alert(data.message || 'Error fetching product/stock details.');
                }
            } catch (error) {
                console.error('Network or parsing error fetching product details:', error);
                initialDbRemainingStock = 0;
                currentProductSellingPrice = 0;
                const mainEntryDiv = document.querySelector('.sale-entry[data-index="0"]');
                if (mainEntryDiv) {
                    mainEntryDiv.querySelector(`.price-per-unit`).value = 0;
                    calculateAndDisplayEntryTotals(mainEntryDiv);
                }
                alert('Could not fetch product details or stock. Please try again.');
            }
        } else {
            // If product or person is not selected, reset everything
            initialDbRemainingStock = 0;
            currentProductSellingPrice = 0;
            const mainEntryDiv = document.querySelector('.sale-entry[data-index="0"]');
            if (mainEntryDiv) {
                mainEntryDiv.querySelector(`.price-per-unit`).value = 0;
                calculateAndDisplayEntryTotals(mainEntryDiv);
            }
        }
        updateOverallRemainingDisplay(); // Always update display
    };


    // Event listener for quantity_sold and discount_amount changes on the main entry
    document.addEventListener('DOMContentLoaded', () => {
        const mainEntryDiv = document.querySelector('.sale-entry[data-index="0"]');

        mainEntryDiv.querySelector('.quantity-sold').addEventListener('input', () => {
            calculateAndDisplayEntryTotals(mainEntryDiv);
            updateOverallRemainingDisplay();
        });

        mainEntryDiv.querySelector('.discount').addEventListener('input', () => {
            calculateAndDisplayEntryTotals(mainEntryDiv);
        });

        // Attach event listeners to product and marketing person dropdowns
        document.getElementById('product_id').addEventListener('change', updateProductDetailsAndStock);
        document.getElementById('marketing_person_id').addEventListener('change', updateProductDetailsAndStock);

        // Initial call to populate stock and price when page loads,
        // using the pre-selected values from the database.
        updateProductDetailsAndStock();
    });
</script>
<?= $this->endSection() ?>