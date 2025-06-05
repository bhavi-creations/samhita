<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Add Unit</h2>
<form method="post" action="<?= base_url('units/store') ?>">
    <div class="form-group">
        <label>Unit Name</label>
        <input type="text" name="name" class="form-control" required value="">
    </div>
    <br>
    <button type="submit" class="btn btn-success">Save</button>
</form>
<?= $this->endSection() ?>
