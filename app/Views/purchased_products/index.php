<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('purchased-products/create') ?>" class="btn btn-primary">Add New Purchased Product</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($purchasedProducts)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Unit</th>
                           
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; ?>
                            <?php foreach ($purchasedProducts as $product): ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($product['name']) ?></td>
                                    <td><?= esc($product['description'] ?? 'N/A') ?></td>
                                    <td><?= esc($product['unit_name']) ?></td>
                                    
                                    <td>
                                        <a href="<?= base_url('purchased-products/edit/' . $product['id']) ?>" class="btn btn-sm btn-info me-2">Edit</a>
                                        <a href="<?= base_url('purchased-products/delete/' . $product['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this purchased product? This may affect linked stock-in records!');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No purchased products found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
