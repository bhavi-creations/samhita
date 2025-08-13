<?= $this->extend('layouts/main'); // Adjust to your actual layout file ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?php if (empty($sold_stock)): ?>
        <div class="alert alert-info">No stock has been sold yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Product Name</th>
                       
                        <th>Total Quantity Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $s_no = 1; ?>
                    <?php foreach ($sold_stock as $item): ?>
                        <tr>
                            <td><?= $s_no++ ?></td>
                            <td><?= esc($item['product_name']) ?></td>
                            <td><?= esc($item['total_sold_quantity']) ?>   <?= esc($item['unit']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
