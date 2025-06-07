<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h1>Add Vendor</h1>

    <form action="<?= base_url('vendors/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label>Agency Name</label>
            <input type="text" name="agency_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Name (Owner)</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Owner Phone</label>
            <input type="text" name="owner_phone" class="form-control">
        </div>

        <div class="mb-3">
            <label>Contact Person</label>
            <input type="text" name="contact_person" class="form-control">
        </div>

        <div class="mb-3">
            <label>Contact Phone</label>
            <input type="text" name="contact_phone" class="form-control">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Vendor</button>
        <a href="<?= base_url('vendors') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?= $this->endSection() ?>
