<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
    </div>

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

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product Name</th>
                                <th>Dealer's Price</th> <!-- Changed from Current Selling Price -->
                                <th>Farmer's Price</th> <!-- NEW -->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; ?> <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($product['name']) ?></td>
                                    <td>₹<?= number_format($product['selling_price'] ?? 0, 2) ?></td>
                                    <td>₹<?= number_format($product['farmer_price'] ?? 0, 2) ?></td> <!-- NEW -->
                                    <td>
                                        <a href="<?= base_url('products/edit-price/' . $product['id']) ?>" class="btn btn-sm btn-info">Edit Prices</a> <!-- Updated button text -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
