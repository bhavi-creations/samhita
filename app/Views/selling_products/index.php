<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= esc($error_message) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Selling Products</h4>
            <a href="<?= base_url('selling-products/create') ?>" class="btn btn-light btn-sm">Add New Product</a>
        </div>
        <div class="card-body">
            <!-- Search/Filter Input -->
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Search for products...">
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Dealer Price</th>
                            <th>Farmer Price</th>
                            <th>Current Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products) && is_array($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr data-search-terms="<?= esc(strtolower($product['name'] . ' ' . $product['description'] . ' ' . $product['unit_name'])) ?>">
                                    <td class="sno-cell"></td>
                                    <td><?= esc($product['name']) ?></td>
                                    <td><?= esc($product['description']) ?></td>
                                    <td><?= esc($product['unit_name']) ?></td>
                                    <td>₹ <?= number_format(esc($product['dealer_price']), 2) ?></td>
                                    <td>₹ <?= number_format(esc($product['farmer_price']), 2) ?></td>
                                    <td><?= esc($product['current_stock']) ?></td>
                                    <td>
                                        <a href="<?= base_url('selling-products/edit/' . esc($product['id'])) ?>" class="btn btn-warning btn-sm me-2">Edit</a>
                                        <form action="<?= base_url('selling-products/delete/' . $product['id']) ?>" method="post" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No selling products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.querySelector('#productsTable tbody');

        // Function to update the serial numbers
        function updateSno() {
            const visibleRows = tableBody.querySelectorAll('tr:not(.d-none)');
            let sno = 1;
            visibleRows.forEach(row => {
                const snoCell = row.querySelector('.sno-cell');
                if (snoCell) {
                    snoCell.textContent = sno++;
                }
            });
        }

        searchInput.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const searchTerms = row.getAttribute('data-search-terms');
                if (searchTerms && searchTerms.includes(searchTerm)) {
                    row.classList.remove('d-none');
                } else {
                    row.classList.add('d-none');
                }
            });
            updateSno(); // Recalculate S.No after filtering
        });

        // Initial update of serial numbers on page load
        updateSno();
    });
</script>

<?= $this->endSection() ?>
