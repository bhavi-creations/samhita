<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Add Product</h1>

        <form action="<?= base_url('products/store') ?>" method="post">
             <?= csrf_field() ?>
            <div class="mb-3">
                <label  class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required />
            </div>

            <div class="mb-3">
                <label  class="form-label">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label  class="form-label">Unit</label>
                <select name="unit_id" class="form-control" required>
                    <option value="">Select Unit</option>
                    <?php foreach($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="<?= base_url('products') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
