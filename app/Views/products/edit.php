<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Edit Product</h1>

        <form action="<?= base_url('products/update/'.$product['id']) ?>" method="post">
             <?= csrf_field() ?>
            <div class="mb-3">
                <label  class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc($product['name']) ?>" required />
            </div>

            <div class="mb-3">
                <label  class="form-label">Description</label>
                <textarea name="description" class="form-control"><?= esc($product['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label  class="form-label">Unit</label>
                <select name="unit_id" class="form-control" required>
                    <option value="">Select Unit</option>
                    <?php foreach($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $product['unit_id'] ? 'selected' : '' ?>>
                            <?= esc($unit['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="<?= base_url('products') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
