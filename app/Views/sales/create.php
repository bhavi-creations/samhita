<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h2>Add New Sale</h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>



    <form action="<?= base_url('sales/store') ?>" method="post">
        <div class="mb-3">
            <label>Product</label>
            <select name="product_id" class="form-control" required>
                <option value="">-- Select Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Marketing Person</label>
            <select name="marketing_person_id" class="form-control" required>
                <option value="">-- Select Person --</option>
                <?php foreach ($marketing_persons as $mp): ?>
                    <option value="<?= $mp['id'] ?>"><?= $mp['custom_id'] ?> - <?= $mp['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Quantity Sold</label>
            <input type="number" name="quantity_sold" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Remaining Quantity Available</label>
            <input type="number" id="remaining_quantity" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label>Price per Unit</label>
            <input type="number" step="0.01" name="price_per_unit" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Date Sold</label>
            <input type="date" name="date_sold" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Save Sale</button>
    </form>
</div>


<script>
    const productSelect = document.querySelector('select[name="product_id"]');
    const personSelect = document.querySelector('select[name="marketing_person_id"]');
    const quantityInput = document.querySelector('input[name="quantity_sold"]');
    const remainingInput = document.getElementById('remaining_quantity');
    const form = document.querySelector('form');

    async function fetchRemainingStock() {
        const productId = productSelect.value;
        const personId = personSelect.value;

        if (productId && personId) {
            const response = await fetch(`<?= base_url('sales/remaining-stock') ?>?product_id=${productId}&marketing_person_id=${personId}`);
            const data = await response.json();
            remainingInput.value = data.remaining ?? 0;
        } else {
            remainingInput.value = '';
        }
    }

    // Fetch remaining on change
    productSelect.addEventListener('change', fetchRemainingStock);
    personSelect.addEventListener('change', fetchRemainingStock);

    // Prevent entering more than available
    quantityInput.addEventListener('input', () => {
        const entered = parseInt(quantityInput.value) || 0;
        const remaining = parseInt(remainingInput.value) || 0;

        if (entered > remaining) {
            quantityInput.setCustomValidity(`You can sell only up to ${remaining} units`);
        } else {
            quantityInput.setCustomValidity('');
        }
    });
</script>


<?= $this->endSection() ?>