<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <a href="<?= base_url('stock-consumption') ?>" class="btn btn-light btn-sm">Back to Consumption Records</a>
        </div>
        <div class="card-body">
            <?= form_open('stock-consumption/store') ?>
            <div class="row">
                <!-- Product Name Selection -->
                <div class="col-md-6 mb-3">
                    <label for="product_id" class="form-label">Product Name</label>
                    <select class="form-select" id="product_id" name="product_id">
                        <option value="">Select a Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= esc($product['id']) ?>"><?= esc($product['product_name']) ?> (<?= esc($product['available_stock']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Display validation error for product_id -->
                    <?php if (session('errors.product_id')): ?>
                        <p class="text-danger mt-1"><?= session('errors.product_id') ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Quantity Consumed -->
                <div class="col-md-6 mb-3">
                    <label for="quantity_consumed" class="form-label">Quantity Consumed</label>
                    <input type="number" class="form-control" id="quantity_consumed" name="quantity_consumed" min="1" required>
                    <!-- Display validation error for quantity_consumed -->
                    <?php if (session('errors.quantity_consumed')): ?>
                        <p class="text-danger mt-1"><?= session('errors.quantity_consumed') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Date Consumed -->
                <div class="col-md-6 mb-3">
                    <label for="date_consumed" class="form-label">Date Consumed</label>
                    <input type="date" class="form-control" id="date_consumed" name="date_consumed" required>
                    <!-- Display validation error for date_consumed -->
                    <?php if (session('errors.date_consumed')): ?>
                        <p class="text-danger mt-1"><?= session('errors.date_consumed') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Used By -->
                <div class="col-md-6 mb-3">
                    <label for="used_by" class="form-label">Used By</label>
                    <input type="text" class="form-control" id="used_by" name="used_by" required>
                    <!-- Display validation error for used_by -->
                    <?php if (session('errors.used_by')): ?>
                        <p class="text-danger mt-1"><?= session('errors.used_by') ?></p>
                    <?php endif; ?>
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">Save Consumption</button>
                    <a href="<?= base_url('stock-consumption') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
