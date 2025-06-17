<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('distributors/add') ?>" class="btn btn-primary">Add New Distributor</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($distributors)): ?>
                <p>No distributors found. Click "Add New Distributor" to get started.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Custom ID</th>
                                <th>Agency Name</th>
                                <th>Owner Name</th>
                                <th>Owner Phone</th>
                                <th>GST No.</th>
                                <th>Address</th>
                                <th>Gmail</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($distributors as $distributor): ?>
                                <tr>
                                    <td><?= esc($distributor['custom_id']) ?></td>
                                    <td><?= esc($distributor['agency_name']) ?></td>
                                    <td><?= esc($distributor['owner_name']) ?></td>
                                    <td><?= esc($distributor['owner_phone']) ?></td>
                                    <td><?= esc($distributor['agency_gst_number'] ?? 'N/A') ?></td>
                                    <td><?= esc(substr($distributor['agency_address'], 0, 50)) ?><?= (strlen($distributor['agency_address']) > 50) ? '...' : '' ?></td>
                                    <td><?= esc($distributor['gmail'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-<?= ($distributor['status'] == 'Active') ? 'success' : (($distributor['status'] == 'Inactive') ? 'danger' : 'warning') ?>"><?= esc($distributor['status']) ?></span></td>
                                    <td>

                                        <a href="<?= base_url('distributors/view/' . $distributor['id']) ?>" class="btn btn-sm btn-info" title="View Details"><i class="bi bi-eye"></i></a>
                                        <a href="<?= base_url('distributors/edit/' . $distributor['id']) ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>