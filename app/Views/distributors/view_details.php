<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('distributors') ?>" class="btn btn-secondary">Back to Distributors List</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Distributor: <?= esc($distributor['agency_name']) ?> (<?= esc($distributor['custom_id']) ?>)</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Custom ID:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['custom_id']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Agency Name:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['agency_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Owner Name:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['owner_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Owner Phone:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['owner_phone']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Agent Name:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['agent_name'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Agent Phone:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['agent_phone'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Agency GST Number:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['agency_gst_number'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Gmail:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['gmail'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Agency Address:</strong>
                </div>
                <div class="col-md-6">
                    <?= nl2br(esc($distributor['agency_address'])) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Status:</strong>
                </div>
                <div class="col-md-6">
                    <span class="badge bg-<?= ($distributor['status'] == 'Active') ? 'success' : (($distributor['status'] == 'Inactive') ? 'danger' : 'warning') ?>"><?= esc($distributor['status']) ?></span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Notes:</strong>
                </div>
                <div class="col-md-6">
                    <?= nl2br(esc($distributor['notes'] ?? 'N/A')) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Created At:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['created_at']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Last Updated At:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($distributor['updated_at']) ?>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('distributors/edit/' . $distributor['id']) ?>" class="btn btn-warning me-2">Edit Distributor</a>
            <a href="<?= base_url('distributors/delete/' . $distributor['id']) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this distributor?');">Delete Distributor</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>