<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
 

<h2>Units List</h2>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<a href="<?= base_url('units/create') ?>" class="btn btn-primary mb-3">Add Unit</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Unit Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($units as $unit): ?>
        <tr>
            <td><?= esc($unit['id']) ?></td>
            <td><?= esc($unit['name']) ?></td>
            <td>
                <a href="<?= base_url('units/edit/'.$unit['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                <form method="post" action="<?= base_url('units/delete/'.$unit['id']) ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->endSection() ?>
