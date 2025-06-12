<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('gst-rates/create') ?>" class="btn btn-primary">Add New GST Rate</a>
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

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Rate (%)</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($gstRates)): ?>
                    <?php foreach ($gstRates as $loopIndex => $gst): ?>
                        <tr>
                            <td><?= $loopIndex + 1 ?></td> <!-- S.No -->
                            <td><?= esc($gst['name']) ?></td>
                            <td><?= esc($gst['rate'] * 100) ?>%</td>
                            <td><?= esc($gst['created_at']) ?></td>
                            <td><?= esc($gst['updated_at']) ?></td>
                            <td>
                                <a href="<?= base_url('gst-rates/edit/' . $gst['id']) ?>" class="btn btn-sm btn-info me-2">Edit</a>
                                <a href="<?= base_url('gst-rates/delete/' . $gst['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this GST rate? This cannot be undone and may affect existing records!');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No GST rates found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>
<?= $this->endSection() ?>