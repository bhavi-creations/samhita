<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <h1>Marketing Distribution</h1>
        <a href="<?= base_url('marketing-distribution/create') ?>" class="btn btn-primary mb-3">Add Distribution</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form method="get" class="mb-3 row g-3 align-items-end">
            <div class="col-md-3">
                <label for="product_id" class="form-label">Product</label>
                <select name="product_id" id="product_id" class="form-control">
                    <option value="">All Products</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == (isset($selected_product_id) ? $selected_product_id : '')) ? 'selected' : '' ?>>
                            <?= esc($p['name']) ?> (<?= esc($p['unit_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="marketing_person_id" class="form-label">Marketing Person</label>
                <select name="marketing_person_id" id="marketing_person_id" class="form-control">
                    <option value="">All Marketing Persons</option>
                    <?php foreach ($marketing_persons as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= ($mp['id'] == (isset($selected_person_id) ? $selected_person_id : '')) ? 'selected' : '' ?>>
                            <?= esc($mp['custom_id']) ?> - <?= esc($mp['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_issued" class="form-label">Date Issued</label>
                <input type="date" name="date_issued" id="date_issued" class="form-control" value="<?= esc(isset($selected_date_issued) ? $selected_date_issued : '') ?>">
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search product/person/notes" value="<?= esc(isset($search_query) ? $search_query : '') ?>">
            </div>
            <div class="col-md-auto mt-4">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= base_url('marketing-distribution') ?>" class="btn btn-secondary">Reset</a>
            </div>
            <div class="col-md-auto mt-4">
                <a href="<?= base_url('marketing-distribution/export-excel') ?>" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Excel</a>
                <a href="<?= base_url('marketing-distribution/export-pdf') ?>" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Export PDF</a>
            </div>
        </form>

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Product (Unit)</th>
                    <th>Marketing Person</th>
                    <th>Quantity Issued</th>
                    <th>Date Issued</th>
                    <th>Notes</th>
                    <th>Current Available Stock</th> <?php // This will be updated by the controller ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($distributions)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No marketing distribution records found.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    // Calculate the starting serial number for the current page
                    $currentPage = $pager->getCurrentPage();
                    $perPage = $pager->getPerPage();
                    $serialNumber = (($currentPage - 1) * $perPage);
                    ?>
                    <?php foreach ($distributions as $index => $d): ?>
                        <tr>
                            <td><?= esc($serialNumber + $index + 1) ?></td>
                            <td>
                                <?= esc($d['product_name']) ?> (<?= esc($d['unit_name']) ?>)
                            </td>
                            <td><?= esc($d['custom_id']) ?> - <?= esc($d['person_name']) ?></td>
                            <td><?= esc($d['quantity_issued']) ?></td>
                            <td><?= esc($d['date_issued']) ?></td>
                            <td><?= esc($d['notes']) ?></td>
                            <td>
                                <?php // Assuming 'current_product_stock' will be passed from the controller ?>
                                <span class="badge bg-info"><?= esc($d['current_product_stock'] ?? 'N/A') ?> <?= esc($d['unit_name']) ?></span> 
                            </td>
                            <td>
                                <a href="<?= base_url('marketing-distribution/edit/' . $d['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= base_url('marketing-distribution/delete/' . $d['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>

    </div>
</section>
<?= $this->endSection() ?>


