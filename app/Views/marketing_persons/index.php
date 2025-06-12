<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Marketing Persons</h3>
                <div class="card-tools">
                    <a href="<?= base_url('marketing-persons/create') ?>" class="btn btn-tool btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Person
                    </a>
                    <a href="<?= base_url('marketing-persons/export-all-excel') ?>" class="btn btn-tool btn-sm btn-success" title="Export All to Excel">
                        <i class="fas fa-file-excel"></i> Export All Excel
                    </a>
                    <a href="<?= base_url('marketing-persons/export-all-pdf') ?>" class="btn btn-tool btn-sm btn-danger" title="Export All to PDF">
                        <i class="fas fa-file-pdf"></i> Export All PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Custom ID</th>
                            <th>Name</th>
                            <th>Primary Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($persons)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No marketing persons found.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($persons as $person): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($person['custom_id']) ?></td>
                                    <td><?= esc($person['name']) ?></td>
                                    <td><?= esc($person['phone']) ?></td>
                                    <td><?= esc($person['email']) ?></td>
                                    <td>
                                        <a href="<?= base_url('marketing-persons/view/' . $person['id']) ?>" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?= base_url('marketing-persons/edit/' . $person['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="<?= base_url('marketing-persons/delete/' . $person['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this marketing person and all associated files?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>