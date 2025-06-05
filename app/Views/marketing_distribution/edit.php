<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
  <div class="container-fluid">
    <h1>Edit Marketing Distribution</h1>

    <form action="<?= base_url('marketing-distribution/update/'.$distribution['id']) ?>" method="post">
      <div class="mb-3">
        <label>Product</label>
        <select name="product_id" class="form-control" required>
          <option value="">Select Product</option>
          <?php foreach($products as $product): ?>
          <option value="<?= $product['id'] ?>" <?= $product['id'] == $distribution['product_id'] ? 'selected' : '' ?>>
            <?= esc($product['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label>Marketing Person</label>
        <select name="marketing_person_id" class="form-control" required>
          <option value="">Select Marketing Person</option>
          <?php foreach($marketing_persons as $person): ?>
          <option value="<?= $person['id'] ?>" <?= $person['id'] == $distribution['marketing_person_id'] ? 'selected' : '' ?>>
            <?= esc($person['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label>Quantity Issued</label>
        <input type="number" name="quantity_issued" class="form-control" value="<?= esc($distribution['quantity_issued']) ?>" required />
      </div>

      <div class="mb-3">
        <label>Date Issued</label>
        <input type="date" name="date_issued" class="form-control" value="<?= esc($distribution['date_issued']) ?>" required />
      </div>

      <button type="submit" class="btn btn-primary">Update</button>
      <a href="<?= base_url('marketing-distribution') ?>" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
