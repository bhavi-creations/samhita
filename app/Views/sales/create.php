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

            <div class="mb-3  col-4">
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

            <div id="overallRemainingQtyDisplay" class="alert alert-info    col-4">
                <strong>Overall Remaining Stock for Selected Product/Person: <span id="currentOverallRemainingQty">0</span></strong>
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

        // Simple client-side validation for overall quantity
        if (currentRemaining < 0) {
            document.getElementById('overallRemainingQtyDisplay').classList.remove('alert-info');
            document.getElementById('overallRemainingQtyDisplay').classList.add('alert-danger');
            // You might want a less intrusive warning here, or prevent submission
            // alert('Warning: Total quantity sold across all entries exceeds remaining stock!');
        } else {
            document.getElementById('overallRemainingQtyDisplay').classList.remove('alert-danger');
            document.getElementById('overallRemainingQtyDisplay').classList.add('alert-info');
        }
    };


    // Function to calculate and display prices for a single entry
    const calculateAndDisplayEntryTotals = (entryDiv) => {
        const quantitySold = parseFloat(entryDiv.querySelector('.quantity-sold').value) || 0;
        const pricePerUnit = parseFloat(entryDiv.querySelector('.price-per-unit').value) || 0; // Ensure this is read
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
                    <h4> Enter Sale Details</h4>

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
                    <h4> Enter Coustmer Details</h4>
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
                    <div class="col-md-1 mt-4">
                        <button type="button" class="btn btn-danger remove-sale-entry"><i class="fas fa-minus-circle"></i></button>
                    </div>
                </div>

                 
            </div>
        `;
        document.getElementById('saleEntriesContainer').insertAdjacentHTML('beforeend', saleEntryHtml);

        // Get reference to the newly added entry div
        const newEntryDiv = document.querySelector(`.sale-entry[data-index="${index}"]`);

        // Attach event listeners for calculations
        newEntryDiv.querySelector('.quantity-sold').addEventListener('input', () => {
            calculateAndDisplayEntryTotals(newEntryDiv);
            updateOverallRemainingDisplay(); // Update overall remaining when quantity changes
        });
        newEntryDiv.querySelector('.discount').addEventListener('input', () => {
            calculateAndDisplayEntryTotals(newEntryDiv);
        });

        // *** IMPORTANT FIX: Call updateProductDetails here to ensure price_per_unit is set for the new entry ***
        // This will also re-calculate totals for ALL entries, ensuring consistency.
        updateProductDetails(); // This function already iterates through all .sale-entry divs

        saleEntryCount++; // Increment for the next entry
    };

    // Add first entry on page load
    document.addEventListener('DOMContentLoaded', () => {
        addSaleEntry();
        // Initial update for overall remaining stock (this will also populate price_per_unit for the first entry)
        updateMainDropdowns();
    });

    // Event listener for adding new entries
    document.getElementById('addSaleEntryBtn').addEventListener('click', addSaleEntry);

    // Event listener for removing entries (delegated)
    document.getElementById('saleEntriesContainer').addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-sale-entry') || event.target.closest('.remove-sale-entry')) {
            // Prevent removing the last entry
            if (document.querySelectorAll('.sale-entry').length > 1) {
                event.target.closest('.sale-entry').remove();
                updateOverallRemainingDisplay(); // Update overall remaining after removing
            } else {
                alert('At least one sales entry is required.');
            }
        }
    });

    // Function to fetch product details and update fields (now also updates global stock)
    const updateProductDetails = async () => {
        const productId = document.getElementById('product_id').value;
        const marketingPersonId = document.getElementById('marketing_person_id').value;

        if (productId && marketingPersonId) {
            try {
                const response = await fetch(`<?= base_url('sales/product-details') ?>?product_id=${productId}&marketing_person_id=${marketingPersonId}`);
                const data = await response.json();

                // Update the global initial stock
                initialDbRemainingStock = data.remaining_qty || 0;

                // Update price for ALL existing entries and recalculate their totals
                document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                    entryDiv.querySelector(`.price-per-unit`).value = data.price_per_unit || 0; // Set price_per_unit here
                    calculateAndDisplayEntryTotals(entryDiv); // Recalculate totals for this entry
                });

                updateOverallRemainingDisplay(); // Update the overall remaining display
            } catch (error) {
                console.error('Error fetching product details:', error);
                initialDbRemainingStock = 0; // Reset on error
                document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                    entryDiv.querySelector(`.price-per-unit`).value = 0; // Reset price_per_unit on error
                    calculateAndDisplayEntryTotals(entryDiv);
                });
                updateOverallRemainingDisplay();
            }
        } else {
            // If product or person is not selected, reset everything
            initialDbRemainingStock = 0;
            document.querySelectorAll('.sale-entry').forEach(entryDiv => {
                entryDiv.querySelector(`.price-per-unit`).value = 0; // Reset price_per_unit
                calculateAndDisplayEntryTotals(entryDiv);
            });
            updateOverallRemainingDisplay();
        }
    };

    // Consolidated function to call when main dropdowns change
    const updateMainDropdowns = () => {
        updateProductDetails();
    };

    // Event listener for changes in Product or Marketing Person selects
    document.getElementById('product_id').addEventListener('change', updateMainDropdowns);
    document.getElementById('marketing_person_id').addEventListener('change', updateMainDropdowns);

    // Initial update on page load if selects have pre-selected values (e.g., after validation error)
    document.addEventListener('DOMContentLoaded', () => {
        updateMainDropdowns(); // This ensures product details (including price) are fetched and set for initial entries
        // No need for a separate loop here, as updateMainDropdowns calls updateProductDetails
        // which already iterates and calls calculateAndDisplayEntryTotals for all entries.
    });
</script>
<?= $this->endSection() ?>