<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Edit Product</h1>

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
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <form action="<?= base_url('products/update/'.$product['id']) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT"> <!-- Corrected to PUT -->

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control <?= (isset($validation) && $validation->hasError('name')) ? 'is-invalid' : '' ?>"
                       value="<?= old('name', $product['name']) ?>" required /> <!-- Retains old input and shows validation -->
                <?php if (isset($validation) && $validation->hasError('name')): ?>
                    <div class="invalid-feedback">
                        <?= $validation->getError('name') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control"><?= old('description', $product['description']) ?></textarea> <!-- Retains old input -->
            </div>

            <div class="mb-3">
                <label for="unit_id" class="form-label">Unit</label>
                <select name="unit_id" id="unit_id" class="form-control <?= (isset($validation) && $validation->hasError('unit_id')) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Unit</option>
                    <?php foreach($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= old('unit_id', $product['unit_id']) == $unit['id'] ? 'selected' : '' ?>>
                            <?= esc($unit['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('unit_id')): ?>
                    <div class="invalid-feedback">
                        <?= $validation->getError('unit_id') ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="<?= base_url('products') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
