<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add Marketing Person</h3>
            </div>
            <form method="post" action="<?= base_url('marketing-persons/store') ?>" enctype="multipart/form-data">
                <div class="card-body">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" id="name" value="<?= old('name') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Primary Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" id="phone" value="<?= old('phone') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="secondary_phone_num">Secondary Phone</label>
                            <input type="text" name="secondary_phone_num" class="form-control" id="secondary_phone_num" value="<?= old('secondary_phone_num') ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" value="<?= old('email') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" class="form-control" id="address"><?= old('address') ?></textarea>
                    </div>

                    <hr>
                    <h4>Document Uploads (Max 2MB per file, JPG, JPEG, PNG, or PDF)</h4>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="aadhar_card_image">Aadhar Card Image/PDF</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="aadhar_card_image" name="aadhar_card_image" accept="image/*,.pdf">
                                <label class="custom-file-label" for="aadhar_card_image">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Upload Aadhar Card (JPG, PNG, PDF)</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pan_card_image">PAN Card Image/PDF</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="pan_card_image" name="pan_card_image" accept="image/*,.pdf">
                                <label class="custom-file-label" for="pan_card_image">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Upload PAN Card (JPG, PNG, PDF)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="driving_license_image">Driving License Image/PDF</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="driving_license_image" name="driving_license_image" accept="image/*,.pdf">
                                <label class="custom-file-label" for="driving_license_image">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Upload Driving License (JPG, PNG, PDF)</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="address_proof_image">Present Address Proof (e.g., Bill) Image/PDF</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="address_proof_image" name="address_proof_image" accept="image/*,.pdf">
                                <label class="custom-file-label" for="address_proof_image">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Upload Address Proof (JPG, PNG, PDF)</small>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Person</button>
                    <a href="<?= base_url('marketing-persons') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        bsCustomFileInput.init();
    });
</script>
<?= $this->endSection() ?>