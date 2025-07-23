<?= $this->extend('layouts/main'); // Adjust to your actual layout file 
?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('success') ? '<div class="alert alert-success">' . session()->getFlashdata('success') . '</div>' : '' ?>
    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

   
    <div class="row">
        <?php
        $imageTypes = [
            'company_logo' => 'Company Logo',
            'company_stamp' => 'Company Stamp',
            'company_signature' => 'Company Signature'
        ];
        ?>

        <?php foreach ($imageTypes as $type_key => $type_name): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?= $type_name ?></h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        // Dynamically get the variable passed from the controller, e.g., $company_logo_path
                        $path_var_name = $type_key . '_path';
                        $image_path = isset($$path_var_name) ? $$path_var_name : null;
                        ?>

                        <?php if ($image_path): ?>
                            <p>Current <?= $type_name ?>:</p>
                            <img src="<?= esc($image_path) ?>" alt="<?= esc($type_name) ?>" class="img-thumbnail mb-3" style="max-width: 150px; height: auto;">
                            <br>
                            <form action="<?= base_url('company-settings/delete-image/' . $type_key) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger mt-2" onclick="return confirm('Are you sure you want to delete this image?');">Delete</button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">No <?= $type_name ?> uploaded.</p>
                            <form action="<?= base_url('company-settings/upload-image') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="image_type" value="<?= esc($type_key) ?>">
                                <div class="input-group mt-3">
                                    <input type="file" name="image_file" class="form-control" accept="image/png, image/jpeg, image/webp">
                                    <button class="btn btn-primary" type="submit">Upload</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php $this->endSection(); ?>