<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h1>Edit Vendor</h1>

    <form action="<?= base_url('vendors/update/' . $vendor['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label>Agency Name</label>
            <input type="text" name="agency_name" class="form-control" value="<?= esc($vendor['agency_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Name (Owner)</label>
            <input type="text" name="name" class="form-control" value="<?= esc($vendor['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Owner Phone</label>
            <input type="text" name="owner_phone" class="form-control" value="<?= esc($vendor['owner_phone']) ?>">
        </div>

        <div class="mb-3">
            <label>Contact Person</label>
            <input type="text" name="contact_person" class="form-control" value="<?= esc($vendor['contact_person']) ?>">
        </div>

        <div class="mb-3">
            <label>Contact Phone</label>
            <input type="text" name="contact_phone" class="form-control" value="<?= esc($vendor['contact_phone']) ?>">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc($vendor['email']) ?>">
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= esc($vendor['address']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Vendor</button>
        <a href="<?= base_url('vendors') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?= $this->endSection() ?>
