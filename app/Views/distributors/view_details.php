<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2><?= esc($title) ?></h2>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5>Distributor Details - <?= esc($distributor['agency_name']) ?></h5>
            <div class="btn-group" role="group" aria-label="Export options">
                <a href="<?= base_url('distributors/exportSingleExcel/' . $distributor['id']) ?>" class="btn btn-success btn-sm me-2">Export to Excel</a>
                <a href="<?= base_url('distributors/exportSinglePdf/' . $distributor['id']) ?>" class="btn btn-danger btn-sm">Export to PDF</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Custom ID:</strong> <span class="text-primary fw-bold"><?= esc($distributor['custom_id']) ?></span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Status:</strong>
                        <?php
                        // Logic for status badge color, similar to index page
                        $status_class = '';
                        switch ($distributor['status']) {
                            case 'Active':
                                $status_class = 'bg-success'; // Green for Active
                                break;
                            case 'Inactive':
                                $status_class = 'bg-danger'; // Red for Inactive
                                break;
                            case 'On Hold':
                                $status_class = 'bg-warning text-dark'; // Yellow for On Hold, with dark text
                                break;
                            default:
                                $status_class = 'bg-secondary'; // Grey for any other status
                                break;
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= esc($distributor['status']) ?></span>
                    </p>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Owner & Contact Information</h6>
                    <p class="mb-1"><strong>Owner Name:</strong> <?= esc($distributor['owner_name']) ?></p>
                    <p class="mb-1"><strong>Owner Phone:</strong> <?= esc($distributor['owner_phone']) ?></p>
                    <p class="mb-1"><strong>Gmail:</strong> <?= esc($distributor['gmail']) ?: 'N/A' ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Agency Details</h6>
                    <p class="mb-1"><strong>Agency Name:</strong> <?= esc($distributor['agency_name']) ?></p>
                    <p class="mb-1"><strong>Agency Address:</strong> <?= nl2br(esc($distributor['agency_address'])) ?></p>
                    <p class="mb-1"><strong>Agency GST Number:</strong> <?= esc($distributor['agency_gst_number']) ?: 'N/A' ?></p>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Agent Information</h6>
                    <p class="mb-1"><strong>Agent Name:</strong> <?= esc($distributor['agent_name']) ?: 'N/A' ?></p>
                    <p class="mb-1"><strong>Agent Phone:</strong> <?= esc($distributor['agent_phone']) ?: 'N/A' ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Additional Notes</h6>
                    <p class="mb-1"><strong>Notes:</strong> <?= nl2br(esc($distributor['notes'])) ?: 'N/A' ?></p>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <p class="card-text"><small class="text-muted">Created At: <?= esc($distributor['created_at']) ?></small></p>
                </div>
                <div class="col-md-6">
                    <p class="card-text"><small class="text-muted">Last Updated At: <?= esc($distributor['updated_at']) ?></small></p>
                </div>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="<?= base_url('distributors/edit/' . $distributor['id']) ?>" class="btn btn-warning me-2">Edit</a>
            <a href="<?= base_url('distributors') ?>" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>