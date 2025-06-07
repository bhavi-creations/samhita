<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h1>Vendors</h1>
    <a href="<?= base_url('vendors/create') ?>" class="btn btn-primary mb-3">Add Vendor</a>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>S.no</th>
                <th>Agency Name</th>
                <th>Name (Owner)</th>
                <th>Owner Phone</th>
                <th>Contact Person</th>
                <th>Contact Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vendors)): ?>
                <tr>
                    <td colspan="9" class="text-center">No vendors found.</td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($vendors as $vendor): ?>
                    <tr>
                        <td><?= $i++ ?></td> <!-- Serial Number -->
                        <td><?= esc($vendor['agency_name']) ?></td>
                        <td><?= esc($vendor['name']) ?></td>
                        <td><?= esc($vendor['owner_phone']) ?></td>
                        <td><?= esc($vendor['contact_person']) ?></td>
                        <td><?= esc($vendor['contact_phone']) ?></td>
                        <td><?= esc($vendor['email']) ?></td>
                        <td><?= esc($vendor['address']) ?></td>
                        <td>
                            <a href="<?= base_url('vendors/edit/' . $vendor['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?= base_url('vendors/delete/' . $vendor['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this vendor?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
