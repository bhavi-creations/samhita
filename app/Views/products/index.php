<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Products</h1>
        <a href="<?= base_url('products/create') ?>" class="btn btn-primary mb-3">Add Product</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= esc($p['id']) ?></td>
                        <td><?= esc($p['name']) ?></td>
                        <td><?= esc($p['description']) ?></td>
                        <td><?= esc($p['unit_name']) ?></td>
                        <td>
                            <a href="<?= base_url('products/edit/' . $p['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?= base_url('products/delete/' . $p['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>