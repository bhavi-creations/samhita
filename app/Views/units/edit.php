<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Edit Unit</h2>
<form method="post" action="<?= base_url('units/update/'.$unit['id']) ?>">
    <div class="form-group">
        <label>Unit Name</label>
        <input type="text" name="name" class="form-control" required value="<?= esc($unit['name']) ?>">
    </div>
    <br>
    <button type="submit" class="btn btn-success">Update</button>
</form>
<?= $this->endSection() ?>
