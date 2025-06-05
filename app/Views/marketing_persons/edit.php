<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="content">
  <div class="container-fluid">
    <h2>Edit Marketing Person</h2>

    <form method="post" action="<?= base_url('marketing-persons/update/'.$person['id']) ?>">
      <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?= esc($person['name']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= esc($person['phone']) ?>">
      </div>
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= esc($person['email']) ?>">
      </div>
      <div class="mb-3">
        <label>Address</label>
        <textarea name="address" class="form-control"><?= esc($person['address']) ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="<?= base_url('marketing-persons') ?>" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</section>

<?= $this->endSection() ?>
