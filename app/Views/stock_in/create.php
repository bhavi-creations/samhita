<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <h1>Add Stock</h1>

    <form action="<?= base_url('stock-in/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label>Product</label>
            <select name="product_id" class="form-control" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" required step="1">
        </div>


        <div class="mb-3">
            <label>Vendor</label>
            <select name="vendor_id" class="form-control" required>
                <option value="">Select Vendor</option>
                <?php foreach ($vendors as $vendor): ?>
                    <option value="<?= $vendor['id'] ?>">
                        <?= esc($vendor['agency_name']) ?> - <?= esc($vendor['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="mb-3">
            <label>Purchase Price (per unit)</label>
            <input type="number" name="purchase_price" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Selling Price (per unit)</label>
            <input type="number" name="selling_price" class="form-control" step="0.01" required>
        </div>



        <div class="mb-3">
            <label>Date Received</label>
            <input type="date" name="date_received" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>


        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Stock</button>
        <a href="<?= base_url('stock-in') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?= $this->endSection() ?>