<?php // Path: app/Views/sales/create.php ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <h2>Add New Sale</h2>

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

    <form action="<?= base_url('sales/store-multiple') ?>" method="post">
        <?= csrf_field() ?>

        <div class="row mb-4">
            <div class="mb-3 col-4">
                <label for="product_id" class="form-label">Product</label>
                <select class="form-select" id="product_id" name="product_id" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" <?= set_select('product_id', $product['id']) ?>>
                            <?= $product['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 col-4">
                <label for="marketing_person_id" class="form-label">Marketing Person</label>
                <select class="form-select" id="marketing_person_id" name="marketing_person_id" required>
                    <option value="">Select Marketing Person</option>
                    <?php foreach ($marketing_persons as $person): ?>
                        <option value="<?= $person['id'] ?>" <?= set_select('marketing_person_id', $person['id']) ?>>
                            <?= $person['name'] ?> (<?= $person['custom_id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="overallRemainingQtyDisplay" class="alert alert-info col-4 d-flex align-items-center justify-content-center">
                <strong>Overall Remaining Stock for Selected Product/Person: <span id="currentOverallRemainingQty" class="ms-2">0</span></strong>
            </div>
        </div>

        <div id="saleEntriesContainer">
            </div>

        <button type="button" class="btn btn-primary mb-3" id="addSaleEntryBtn">Add Sale Entry</button>
        <br>
        <button type="submit" class="btn btn-success">Save Sales</button>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let saleEntryCount = 0; // Initialize a counter for unique IDs/names
    let initialDbRemainingStock = 0; // Stores the stock fetched from the database for the selected product/person
    let currentProductSellingPrice = 0; // Stores the selling price for the selected product

    // Function to calculate total quantity sold across all entries in the form
    const getTotalQuantitiesSoldInForm = () => {
        let totalSold = 0;
        document.querySelectorAll('.sale-entry .quantity-sold').forEach(input => {
            totalSold += parseInt(input.value) || 0;
        });
        return totalSold;
    };

    // Function to update the overall remaining stock display and validate quantities
    const updateOverallRemainingDisplay = () => {
        const totalSoldInForm = getTotalQuantitiesSoldInForm();
        const currentRemaining = initialDbRemainingStock - totalSoldInForm;
        document.getElementById('currentOverallRemainingQty').textContent = currentRemaining;

        // Visual feedback for stock
        const overallDisplay = document.getElementById('overallRemainingQtyDisplay');
        overallDisplay.classList.remove('alert-info', 'alert-danger'); // Clean existing classes
        if (currentRemaining < 0) {
            overallDisplay.classList.add('alert-danger');
        } else {
            overallDisplay.classList.add('alert-info');
        }
    };


    // Function to calculate and display prices for a single entry
    const calculateAndDisplayEntryTotals = (entryDiv) => {
        const quantitySold = parseFloat(entryDiv.querySelector('.quantity-sold').value) || 0;
        const pricePerUnit = parseFloat(entryDiv.querySelector('.price-per-unit').value) || 0;
        const discount = parseFloat(entryDiv.querySelector('.discount').value) || 0;

        const totalNoDiscount = quantitySold * pricePerUnit;
        const totalWithDiscount = totalNoDiscount - discount;

        entryDiv.querySelector('.total-no-discount-display').textContent = totalNoDiscount.toFixed(2);
        entryDiv.querySelector('.total-with-discount-display').textContent = totalWithDiscount.toFixed(2);
    };

    // Function to add a new sale entry block
    const addSaleEntry = () => {
        let index = saleEntryCount; // Get the current unique index for this block
        const saleEntryHtml = `
            <div class="sale-entry row g-3 align-items-end border p-3 mb-3" data-index="${index}">
                <div class="row mt-3"> 
                    <h4>Sale Entry #<span class="entry-number">${index + 1}</span></h4>

                    <div class="col-md-2">
                        <label for="date_sold_${index}" class="form-label">Date Sold</label>
                        <input type="date" class="form-control" id="date_sold_${index}" name="sales[${index}][date_sold]" value="${new Date().toISOString().slice(0, 10)}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity_sold_${index}" class="form-label">Quantity Sold</label>
                        <input type="number" class="form-control quantity-sold" id="quantity_sold_${index}" name="sales[${index}][quantity_sold]" placeholder="Quantity Sold" required min="1">
                    </div>
                    <div class="col-md-2">
                        <label for="price_per_unit_${index}" class="form-label">Price Per Unit</label>
                        <input type="number" step="0.01" class="form-control price-per-unit" id="price_per_unit_${index}" name="sales[${index}][price_per_unit]" placeholder="Price Per Unit" readonly required>
                    </div>
                    <div class="col-md-2">
                        <label for="discount_${index}" class="form-label">Discount</label>
                        <input type="number" step="0.01" class="form-control discount" id="discount_${index}" name="sales[${index}][discount]" value="0.00" min="0">
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
                    <div class="col-md-3">
                        <label for="customer_name_${index}" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name_${index}" name="sales[${index}][customer_name]" placeholder="Customer Name" required>
                    </div>
                    <div class="col-md-3">
                        <label for="customer_phone_${index}" class="form-label">Customer Phone</label>
                        <input type="tel" class="form-control" id="customer_phone_${index}" name="sales[${index}][customer_phone]" placeholder="Customer Phone" pattern="[0-9]{10}" title="10 digit phone number" required>
                    </div>
                    <div class="col-md-4">
                        <label for="customer_address_${index}" class="form-label">Customer Address</label>
                        <textarea class="form-control" id="customer_address_${index}" name="sales[${index}][customer_address]" placeholder="Customer Address" rows="1"></textarea>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-danger remove-sale-entry"><i class="fas fa-minus-circle"></i> Remove</button>
                    </div>
                </div>
            </div>
        `;
        const container = document.getElementById('saleEntriesContainer');
        container.insertAdjacentHTML('beforeend', saleEntryHtml);

        // Get reference to the newly added entry div
        const newEntryDiv = container.lastElementChild;

        // Set the price_per_unit for the new entry based on the currently selected product
        newEntryDiv.querySelector('.price-per-unit').value = currentProductSellingPrice.toFixed(2);
        
        // Attach event listeners for calculations
        newEntryDiv.querySelector('.quantity-sold').addEventListener('input', () => {
            calculateAndDisplayEntryTotals(newEntryDiv);
            updateOverallRemainingDisplay(); // Update overall remaining when quantity changes
        });
        newEntryDiv.querySelector('.price-per-unit').addEventListener('input', () => { // Even if readonly, add for robustness
            calculateAndDisplayEntryTotals(newEntryDiv);
        });
        newEntryDiv.querySelector('.discount').addEventListener('input', () => {
            calculateAndDisplayEntryTotals(newEntryDiv);
        });

        calculateAndDisplayEntryTotals(newEntryDiv); // Initial calculation for the new entry
        updateOverallRemainingDisplay(); // Update overall remaining display after adding a new entry

        saleEntryCount++; // Increment for the next entry
        updateEntryNumbers(); // Re-number entries
    };

    // Function to re-number sale entries after add/remove
    const updateEntryNumbers = () => {
        document.querySelectorAll('.sale-entry').forEach((entryDiv, index) => {
            entryDiv.querySelector('.entry-number').textContent = index + 1;
        });
    };

    // Add first entry on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Only add an initial entry if there are none (e.g., not after a validation error where entries are repopulated)
        if (document.querySelectorAll('.sale-entry').length === 0) {
            addSaleEntry();
        }
        // Initial fetch for overall remaining stock and product price
        updateMainDropdowns();
    });

    // Event listener for adding new entries
    document.getElementById('addSaleEntryBtn').addEventListener('click', addSaleEntry);

    // Event listener for removing entries (delegated)
    document.getElementById('saleEntriesContainer').addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-sale-entry') || event.target.closest('.remove-sale-entry')) {
            const entryDiv = event.target.closest('.sale-entry');
            if (document.querySelectorAll('.sale-entry').length > 1) {
                entryDiv.remove();
                updateOverallRemainingDisplay(); // Update overall remaining after removing
                updateEntryNumbers(); // Re-number entries
            } else {
                alert('At least one sales entry is required.');
            }
        }
    });

    // Function to fetch product details and update fields (now also updates global stock and price)
    const updateProductDetailsAndStock = async () => {
        const productId = document.getElementById('product_id').value;
        const marketingPersonId = document.getElementById('marketing_person_id').value;

        if (productId && marketingPersonId) {
            try {
                // IMPORTANT: Ensure this URL correctly points to your new 'getRemainingStock' method
                const response = await fetch(`<?= base_url('sales/get-remaining-stock') ?>?product_id=${productId}&marketing_person_id=${marketingPersonId}`);
                const data = await response.json();

                if (data.status === 'success') {
                    initialDbRemainingStock = data.remaining_qty;
                    currentProductSellingPrice = data.price_per_unit; // This will now fetch the price

                    // Update price for ALL existing entries and recalculate their totals
                    document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                        entryDiv.querySelector(`.price-per-unit`).value = currentProductSellingPrice.toFixed(2);
                        calculateAndDisplayEntryTotals(entryDiv); // Recalculate totals for this entry
                    });
                } else {
                    console.error('Error fetching product details:', data.message);
                    initialDbRemainingStock = 0;
                    currentProductSellingPrice = 0;
                    document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                        entryDiv.querySelector(`.price-per-unit`).value = 0;
                        calculateAndDisplayEntryTotals(entryDiv);
                    });
                    alert(data.message || 'Error fetching product/stock details.'); // Show a user-friendly alert
                }
            } catch (error) {
                console.error('Network or parsing error fetching product details:', error);
                initialDbRemainingStock = 0;
                currentProductSellingPrice = 0;
                document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                    entryDiv.querySelector(`.price-per-unit`).value = 0;
                    calculateAndDisplayEntryTotals(entryDiv);
                });
                alert('Could not fetch product details or stock. Please try again.');
            }
        } else {
            // If product or person is not selected, reset everything
            initialDbRemainingStock = 0;
            currentProductSellingPrice = 0;
            document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                entryDiv.querySelector(`.price-per-unit`).value = 0;
                calculateAndDisplayEntryTotals(entryDiv);
            });
        }
        updateOverallRemainingDisplay(); // Always update display
    };

    // Consolidated function to call when main dropdowns change
    const updateMainDropdowns = () => {
        updateProductDetailsAndStock();
    };

    // Event listener for changes in Product or Marketing Person selects
    document.getElementById('product_id').addEventListener('change', updateMainDropdowns);
    document.getElementById('marketing_person_id').addEventListener('change', updateMainDropdowns);

    // Initial update on page load (important if there are pre-selected values from set_select)
    document.addEventListener('DOMContentLoaded', () => {
        updateMainDropdowns();
    });
</script>
<?= $this->endSection() ?>