<?= $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= esc($error_message) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="<?= base_url('e-way-bills/create') ?>" class="btn btn-primary">Generate New E-way Bill</a>
    </div>

    <?php if (empty($eway_bills)): ?>
        <div class="alert alert-info">No e-way bills found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>E-way Bill No.</th>
                        <th>Invoice Number</th>
                        <th>Generated At</th>
                        <th>Valid Until</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $s_no = 1; ?>
                    <?php foreach ($eway_bills as $bill): ?>
                        <tr>
                            <td><?= $s_no++ ?></td>
                            
                            <td><?= esc($bill['eway_bill_no']) ?></td>
                            <td><?= esc($bill['invoice_number'] ?? 'N/A') ?></td>
                            <td><?= esc($bill['generated_at']) ?></td>
                            <td><?= esc($bill['valid_until']) ?></td>
                            <td>
                                <a href="<?= base_url('e-way-bills/view/' . $bill['id']) ?>" class="btn btn-info btn-sm">View</a>
                                <a href="<?= base_url('e-way-bills/edit/' . $bill['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <form action="<?= base_url('e-way-bills/delete/' . $bill['id']) ?>" method="post" class="d-inline delete-form" onsubmit="return confirmDelete()">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this e-way bill? This action cannot be undone.');
    }
</script>
<?php $this->endSection(); ?>
