<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Add New Marketing Distribution</h1>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (isset($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('marketing-distribution/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                <select name="product_id" id="product_id" class="form-control <?= (isset($errors['product_id'])) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Product</option>
                    <?php foreach($products as $product): ?>
                    <option value="<?= $product['id'] ?>" data-stock="<?= esc($product['current_stock']) ?>" <?= set_select('product_id', $product['id']) ?>>
                        <?= esc($product['name']) ?> (<?= esc($product['unit_name']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['product_id'])): ?><div class="invalid-feedback"><?= $errors['product_id'] ?></div><?php endif; ?>
                <small class="text-muted" id="availableStockHint"></small> <?php // Display for selected product stock ?>
            </div>

            <div class="mb-3">
                <label for="marketing_person_id" class="form-label">Marketing Person <span class="text-danger">*</span></label>
                <select name="marketing_person_id" id="marketing_person_id" class="form-control <?= (isset($errors['marketing_person_id'])) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Marketing Person</option>
                    <?php foreach($marketing_persons as $person): ?>
                    <option value="<?= $person['id'] ?>" <?= set_select('marketing_person_id', $person['id']) ?>>
                        <?= esc($person['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['marketing_person_id'])): ?><div class="invalid-feedback"><?= $errors['marketing_person_id'] ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="quantity_issued" class="form-label">Quantity Issued <span class="text-danger">*</span></label>
                <input type="number" name="quantity_issued" id="quantity_issued" class="form-control <?= (isset($errors['quantity_issued'])) ? 'is-invalid' : '' ?>" value="<?= set_value('quantity_issued') ?>" required min="1" /> <?php // Added min="1" ?>
                <?php if (isset($errors['quantity_issued'])): ?><div class="invalid-feedback"><?= $errors['quantity_issued'] ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="date_issued" class="form-label">Date Issued <span class="text-danger">*</span></label>
                <input type="date" name="date_issued" id="date_issued" class="form-control <?= (isset($errors['date_issued'])) ? 'is-invalid' : '' ?>" value="<?= set_value('date_issued', date('Y-m-d')) ?>" required />
                <?php if (isset($errors['date_issued'])): ?><div class="invalid-feedback"><?= $errors['date_issued'] ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control <?= (isset($errors['notes'])) ? 'is-invalid' : '' ?>" rows="3"><?= set_value('notes') ?></textarea>
                <?php if (isset($errors['notes'])): ?><div class="invalid-feedback"><?= $errors['notes'] ?></div><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Add Distribution</button>
            <a href="<?= base_url('marketing-distribution') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Products data is available from the PHP context
    const products = <?= json_encode($products) ?>;

    function updateAvailableStockDisplay() {
        var productId = $('#product_id').val();
        var availableStockHint = $('#availableStockHint');
        
        if (productId) {
            const selectedProduct = products.find(p => String(p.id) === String(productId));
            if (selectedProduct) {
                availableStockHint.text('Available: ' + selectedProduct.current_stock + ' ' + selectedProduct.unit_name);
            } else {
                availableStockHint.text('Available: N/A');
            }
        } else {
            availableStockHint.text('');
        }
    }

    // Attach event listener to product select dropdown
    $('#product_id').change(updateAvailableStockDisplay);

    // Initial call to display stock if a product is pre-selected (e.g., after validation error)
    updateAvailableStockDisplay(); 
});
</script>
<?= $this->endSection() ?>
