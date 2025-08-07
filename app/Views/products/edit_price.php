<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?>: <span class="text-primary"><?= esc($product['name']) ?></span></h2>
        <a href="<?= base_url('products/manage-prices') ?>" class="btn btn-secondary">Back to Price List</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= base_url('products/update-price/' . $product['id']) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="POST">
                
                <div class="mb-3 row">
                    <label for="product_name" class="col-sm-3 col-form-label">Product Name:</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="product_name" value="<?= esc($product['name']) ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="current_selling_price" class="col-sm-3 col-form-label">Current Dealer's Price:</label> <!-- Changed label -->
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="current_selling_price" value="₹<?= number_format($product['selling_price'] ?? 0, 2) ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="current_farmer_price" class="col-sm-3 col-form-label">Current Farmer's Price:</label> <!-- NEW -->
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="current_farmer_price" value="₹<?= number_format($product['farmer_price'] ?? 0, 2) ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="selling_price" class="col-sm-3 col-form-label">New Dealer's Price (per unit):</label> <!-- Changed label -->
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price"
                               value="<?= old('selling_price', $product['selling_price'] ?? '') ?>" required>
                        <?php if (isset($validation) && $validation->hasError('selling_price')): ?>
                            <div class="text-danger mt-1">
                                <?= $validation->getError('selling_price') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="farmer_price" class="col-sm-3 col-form-label">New Farmer's Price (per unit):</label> <!-- NEW -->
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="farmer_price" name="farmer_price"
                               value="<?= old('farmer_price', $product['farmer_price'] ?? '') ?>" required>
                        <?php if (isset($validation) && $validation->hasError('farmer_price')): ?>
                            <div class="text-danger mt-1">
                                <?= $validation->getError('farmer_price') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Update Prices</button> <!-- Updated button text -->
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
