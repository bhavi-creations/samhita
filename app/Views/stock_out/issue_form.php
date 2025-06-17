<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('stock-out') ?>" class="btn btn-secondary">Back to Stock Out List</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= base_url('stock-out/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-control <?= (session('errors.product_id')) ? 'is-invalid' : '' ?>" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= esc($product['id']) ?>" <?= set_select('product_id', $product['id']) ?>>
                                <?= esc($product['name']) ?> (Current Price: ₹<?= number_format($product['selling_price'] ?? 0, 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (session('errors.product_id')): ?>
                        <div class="invalid-feedback">
                            <?= session('errors.product_id') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="quantity_out" class="form-label">Quantity Out</label>
                    <input type="number" name="quantity_out" id="quantity_out" class="form-control <?= (session('errors.quantity_out')) ? 'is-invalid' : '' ?>" value="<?= old('quantity_out') ?>" min="1" required>
                    <?php if (session('errors.quantity_out')): ?>
                        <div class="invalid-feedback">
                            <?= session('errors.quantity_out') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-control <?= (session('errors.transaction_type')) ? 'is-invalid' : '' ?>" required>
                        <option value="">Select Type</option>
                        <?php foreach ($transaction_types as $type): ?>
                            <option value="<?= esc($type) ?>" <?= set_select('transaction_type', $type) ?>>
                                <?= esc($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (session('errors.transaction_type')): ?>
                        <div class="invalid-feedback">
                            <?= session('errors.transaction_type') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="issued_date" class="form-label">Issued Date</label>
                    <input type="date" name="issued_date" id="issued_date" class="form-control <?= (session('errors.issued_date')) ? 'is-invalid' : '' ?>" value="<?= old('issued_date', date('Y-m-d')) ?>" required>
                    <?php if (session('errors.issued_date')): ?>
                        <div class="invalid-feedback">
                            <?= session('errors.issued_date') ?>
                        </div>
                    <?php endif; ?>
                </div>
 
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control <?= (session('errors.notes')) ? 'is-invalid' : '' ?>" rows="3"><?= old('notes') ?></textarea>
                    <?php if (session('errors.notes')): ?>
                        <div class="invalid-feedback">
                            <?= session('errors.notes') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Record Stock Out</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>