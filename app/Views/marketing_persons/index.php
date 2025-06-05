<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="content">
    <div class="container-fluid">
        <h2>Marketing Persons</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <a href="<?= base_url('marketing-persons/create') ?>" class="btn btn-primary mb-3">Add Person</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Custom ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($persons as $person): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= esc($person['custom_id']) ?></td>
                        <td><?= esc($person['name']) ?></td>
                        <td><?= esc($person['phone']) ?></td>
                        <td><?= esc($person['email']) ?></td>
                        <td><?= esc($person['address']) ?></td>
                        <td>
                            <a href="<?= base_url('marketing-persons/edit/' . $person['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?= base_url('marketing-persons/delete/' . $person['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this person?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?= $this->endSection() ?>
