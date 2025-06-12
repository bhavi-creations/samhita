<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Marketing Person Details: <?= esc($person['name']) ?></h3>
                <div class="card-tools">
                    <a href="<?= base_url('marketing-persons/edit/' . $person['id']) ?>" class="btn btn-tool btn-sm btn-warning" title="Edit Person">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?= base_url('marketing-persons/export-excel/' . $person['id']) ?>" class="btn btn-tool btn-sm btn-success" title="Export to Excel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="<?= base_url('marketing-persons/export-pdf/' . $person['id']) ?>" class="btn btn-tool btn-sm btn-danger" title="Export to PDF">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="<?= base_url('marketing-persons') ?>" class="btn btn-tool btn-sm btn-secondary" title="Back to List">
                        <i class="fas fa-list"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Custom ID:</strong> <?= esc($person['custom_id']) ?></p>
                        <p><strong>Name:</strong> <?= esc($person['name']) ?></p>
                        <p><strong>Primary Phone:</strong> <?= esc($person['phone']) ?></p>
                        <p><strong>Secondary Phone:</strong> <?= esc($person['secondary_phone_num'] ?? 'N/A') ?></p>
                        <p><strong>Email:</strong> <?= esc($person['email'] ?? 'N/A') ?></p>
                        <p><strong>Address:</strong> <?= nl2br(esc($person['address'] ?? 'N/A')) ?></p>
                        <p><strong>Created At:</strong> <?= esc($person['created_at']) ?></p>
                        <p><strong>Last Updated:</strong> <?= esc($person['updated_at']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Documents:</h5>
                        <?php 
                        $documentFields = [
                            'aadhar_card_image' => 'Aadhar Card',
                            'pan_card_image' => 'PAN Card',
                            'driving_license_image' => 'Driving License',
                            'address_proof_image' => 'Address Proof'
                        ];
                        $uploadBasePath = base_url('public/uploads/marketing_persons') . '/';
                        ?>

                        <?php foreach ($documentFields as $field => $label): ?>
                            <div class="mb-3">
                                <strong><?= $label ?>:</strong>
                                <?php if (!empty($person[$field])): ?>
                                    <?php 
                                    $fileExtension = pathinfo($person[$field], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>
                                    <?php if ($isImage): ?>
                                        <a href="<?= esc($uploadBasePath . $person[$field]) ?>" target="_blank" class="d-block mt-1">
                                            <img src="<?= esc($uploadBasePath . $person[$field]) ?>" alt="<?= esc($label) ?>" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <br>View Image
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= esc($uploadBasePath . $person[$field]) ?>" target="_blank" class="btn btn-sm btn-outline-info mt-1">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not uploaded</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>