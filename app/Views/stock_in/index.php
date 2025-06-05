<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <h1>Stock In Entries</h1>
    <a href="<?= base_url('stock-in/create') ?>" class="btn btn-primary mb-3">Add Stock</a>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Date Received</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stock_entries as $in): ?>
                <tr>
                    <td><?= esc($in['id']) ?></td>
                    <td><?= esc($in['product_name']) ?></td>
                    <td><?= esc($in['quantity']) ?></td>
                    <td><?= esc($in['unit_name']) ?></td>
                    <td><?= esc($in['date_received']) ?></td>
                    <td><?= esc($in['notes']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
<?= $this->endSection() ?>