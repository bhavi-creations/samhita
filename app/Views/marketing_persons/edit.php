<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Marketing Person: <?= esc($person['name']) ?></h3>
            </div>
            <form method="post" action="<?= base_url('marketing-persons/update/' . $person['id']) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT"> 

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
                            <input type="text" name="name" class="form-control" id="name" value="<?= old('name', $person['name']) ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Primary Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" id="phone" value="<?= old('phone', $person['phone']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="secondary_phone_num">Secondary Phone</label>
                            <input type="text" name="secondary_phone_num" class="form-control" id="secondary_phone_num" value="<?= old('secondary_phone_num', $person['secondary_phone_num']) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" value="<?= old('email', $person['email']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" class="form-control" id="address"><?= old('address', $person['address']) ?></textarea>
                    </div>

                    <hr>
                    <h4>Document Uploads (Max 2MB per file, JPG, JPEG, PNG, or PDF)</h4>

                    <?php 
                    $uploadFields = [
                        'aadhar_card_image' => 'Aadhar Card Image/PDF',
                        'pan_card_image' => 'PAN Card Image/PDF',
                        'driving_license_image' => 'Driving License Image/PDF',
                        'address_proof_image' => 'Present Address Proof (e.g., Bill) Image/PDF'
                    ];
                    $uploadBasePath = base_url('public/uploads/marketing_persons') . '/';
                    ?>

                    <?php foreach ($uploadFields as $field => $label): ?>
                        <div class="form-group">
                            <label for="<?= $field ?>"><?= $label ?></label>
                            <?php if (!empty($person[$field])): ?>
                                <div class="mb-2">
                                    Current: 
                                    <?php 
                                    $fileExtension = pathinfo($person[$field], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>
                                    <?php if ($isImage): ?>
                                        <a href="<?= esc($uploadBasePath . $person[$field]) ?>" target="_blank">
                                            <img src="<?= esc($uploadBasePath . $person[$field]) ?>" alt="Current <?= esc($label) ?>" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 2px;">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= esc($uploadBasePath . $person[$field]) ?>" target="_blank">
                                            <i class="fas fa-file-pdf"></i> View Current PDF
                                        </a>
                                    <?php endif; ?>
                                    <span class="text-muted">(<?= esc($person[$field]) ?>)</span>
                                </div>
                            <?php endif; ?>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="<?= $field ?>" name="<?= $field ?>" accept="image/*,.pdf">
                                <label class="custom-file-label" for="<?= $field ?>">Choose new file (leave blank to keep current)</label>
                            </div>
                            <small class="form-text text-muted">Upload new file if you want to replace current one (JPG, PNG, PDF)</small>
                        </div>
                    <?php endforeach; ?>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Person</button>
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