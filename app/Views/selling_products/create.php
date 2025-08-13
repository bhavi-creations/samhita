<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4 rounded-lg">
        <!-- Card Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-t-lg">
            <h4 class="mb-0">Add New Selling Product</h4>
            <a href="<?= base_url('selling-products') ?>" class="btn btn-light btn-sm">Back to List</a>
        </div>
        
        <!-- Card Body with Form -->
        <div class="card-body">
            <!-- Display Validation Errors -->
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Product Creation Form -->
            <form action="<?= base_url('selling-products/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <!-- Product Details Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= old('name') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= old('description') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select class="form-select" id="unit_id" name="unit_id" required>
                                <option value="">Select a unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= esc($unit['id']) ?>" <?= (old('unit_id') == $unit['id']) ? 'selected' : '' ?>>
                                        <?= esc($unit['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Pricing and Stock Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="dealer_price" class="form-label">Dealer Price (₹)</label>
                            <input type="number" class="form-control" id="dealer_price" name="dealer_price" step="0.01" value="<?= old('dealer_price') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="farmer_price" class="form-label">Farmer Price (₹)</label>
                            <input type="number" class="form-control" id="farmer_price" name="farmer_price" step="0.01" value="<?= old('farmer_price') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="current_stock" class="form-label">Current Stock</label>
                            <input type="number" class="form-control" id="current_stock" name="current_stock" value="<?= old('current_stock') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
