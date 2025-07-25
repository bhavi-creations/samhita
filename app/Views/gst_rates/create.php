<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <a href="<?= base_url('gst-rates') ?>" class="btn btn-light btn-sm">Back to List</a>
        </div>
        <div class="card-body">
            <form action="<?= base_url('gst-rates/store') ?>" method="post">
                <?= csrf_field() ?>

<?php // --- TEMPORARY DEBUGGING START --- ?>
<p style="background-color: yellow; padding: 10px;">
    DEBUG: Validation Errors:
    <?php var_dump(session()->getFlashdata('errors')); ?>
    <br>
    DEBUG: Save Error:
    <?php var_dump(session()->getFlashdata('error')); ?>
</p>
<?php // --- TEMPORARY DEBUGGING END --- ?>

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

                <div class="mb-3">
                    <label for="name" class="form-label">GST Rate Name (e.g., GST 18%):</label>
                    <input type="text" name="name" id="name" class="form-control <?= (session()->getFlashdata('errors.name')) ? 'is-invalid' : '' ?>" value="<?= old('name') ?>" required>
                    <?php if (session()->getFlashdata('errors.name')): ?>
                        <div class="invalid-feedback">
                            <?= session()->getFlashdata('errors.name') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <!-- Updated label text for clarity -->
                    <label for="rate" class="form-label">Rate (as a percentage, e.g., 18 for 18%):</label>
                    <!-- Changed step to 0.01 to allow for decimal percentages (e.g., 0.5%), min to 0, max to 100 -->
                    <input type="number" name="rate" id="rate" class="form-control <?= (session()->getFlashdata('errors.rate')) ? 'is-invalid' : '' ?>" step="0.01" min="0" max="100" value="<?= old('rate') ?>" required>
                    <?php if (session()->getFlashdata('errors.rate')): ?>
                        <div class="invalid-feedback">
                            <?= session()->getFlashdata('errors.rate') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Add GST Rate</button>
                <a href="<?= base_url('gst-rates') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
