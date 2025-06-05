<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h2>Edit Sale</h2>
    <form action="<?= base_url('sales/update/' . $sale['id']) ?>" method="post">
        <div class="mb-3">
            <label>Product</label>
            <select name="product_id" class="form-control" required>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($sale['product_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= $p['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Marketing Person</label>
            <select name="marketing_person_id" class="form-control" required>
                <?php foreach ($marketing_persons as $mp): ?>
                    <option value="<?= $mp['id'] ?>" <?= ($sale['marketing_person_id'] == $mp['id']) ? 'selected' : '' ?>>
                        <?= $mp['custom_id'] ?> - <?= $mp['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Quantity Sold</label>
            <input type="number" name="quantity_sold" class="form-control" value="<?= $sale['quantity_sold'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Price per Unit</label>
            <input type="number" step="0.01" name="price_per_unit" class="form-control" value="<?= $sale['price_per_unit'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Date Sold</label>
            <input type="date" name="date_sold" class="form-control" value="<?= $sale['date_sold'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Sale</button>
    </form>
</div>

<?= $this->endSection() ?>
