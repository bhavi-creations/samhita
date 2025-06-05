<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Marketing Distribution</h1>
        <a href="<?= base_url('marketing-distribution/create') ?>" class="btn btn-primary mb-3">Add Distribution</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form method="get" class="mb-3 row">
            <div class="col-md-3">
                <select name="product_id" class="form-control">
                    <option value="">All Products</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == @$_GET['product_id']) ? 'selected' : '' ?>>
                            <?= esc($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="marketing_person_id" class="form-control">
                    <option value="">All Marketing Persons</option>
                    <?php foreach ($marketing_persons as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= ($mp['id'] == @$_GET['marketing_person_id']) ? 'selected' : '' ?>>
                            <?= esc($mp['custom_id']) ?> - <?= esc($mp['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_issued" class="form-control" value="<?= esc(@$_GET['date_issued']) ?>">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary">Filter</button>
                <a href="<?= base_url('marketing-distribution') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product (Unit)</th>
                    <th>Marketing Person</th>
                    <th>Quantity Issued</th>
                    <th>Date Issued</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($distributions as $d): ?>
                    <tr>
                        <td><?= esc($d['id']) ?></td>
                        <td>
                            <?= esc($d['product_name']) ?> (<?= esc($d['unit_name']) ?>)
                            <br>
                            <small class="text-muted">Available: <?= esc($d['total_stock'] - $d['total_issued']) ?> <?= esc($d['unit_name']) ?></small>
                        </td>

                        <td><?= esc($d['custom_id']) ?> - <?= esc($d['person_name']) ?></td>
                        <td><?= esc($d['quantity_issued']) ?></td>
                        <td><?= esc($d['date_issued']) ?></td>
                        <td>
                            <a href="<?= base_url('marketing-distribution/edit/' . $d['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?= base_url('marketing-distribution/delete/' . $d['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</section>
<?= $this->endSection() ?>