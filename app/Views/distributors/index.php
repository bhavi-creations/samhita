<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2><?= esc($title) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mt-3">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-3">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= base_url('distributors/add') ?>" class="btn btn-primary me-2">Add New Distributor</a>
        <a href="<?= base_url('distributors/exportExcel') ?>" class="btn btn-success me-2">Export to Excel</a>
        <a href="<?= base_url('distributors/exportPdf') ?>" class="btn btn-danger">Export to PDF</a>
    </div>

    <?php if (empty($distributors)): ?>
        <div class="alert alert-info" role="alert">
            No distributors found. Click "Add New Distributor" to get started!
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Custom ID</th>
                        <th>Agency Name</th>
                        <th>Owner Name</th>
                        <th>Owner Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $s_no = 1; ?>
                    <?php foreach ($distributors as $distributor): ?>
                        <tr>
                            <td><?= $s_no++ ?></td>
                            <td><?= esc($distributor['custom_id']) ?></td>
                            <td><?= esc($distributor['agency_name']) ?></td>
                            <td><?= esc($distributor['owner_name']) ?></td>
                            <td><?= esc($distributor['owner_phone']) ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch ($distributor['status']) {
                                    case 'Active':
                                        $status_class = 'bg-success';
                                        break;
                                    case 'Inactive':
                                        $status_class = 'bg-danger';
                                        break;
                                    case 'On Hold':
                                        $status_class = 'bg-warning text-dark';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $status_class ?>"><?= esc($distributor['status']) ?></span>
                            </td>
                            <td>
                                <a href="<?= base_url('distributors/view/' . $distributor['id']) ?>" class="btn btn-info btn-sm">View</a>
                                <a href="<?= base_url('distributors/edit/' . $distributor['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?= base_url('distributors/delete/' . $distributor['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this distributor?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>