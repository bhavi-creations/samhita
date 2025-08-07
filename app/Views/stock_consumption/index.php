<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <!-- New section to show available purchased stock -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Available Purchased Stock</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($availablePurchasedStocks)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Available Stock</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availablePurchasedStocks as $stock): ?>
                                <tr>
                                    <td><?= esc($stock['product_name']) ?></td>
                                    <td><?= esc($stock['available_stock']) ?></td>
                                    <td><?= esc($stock['unit_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0" role="alert">
                    No purchased stock records available or all stock has been consumed.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Original content for stock consumption records -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <a href="<?= base_url('stock-consumption/create') ?>" class="btn btn-light btn-sm">Add New Consumption</a>
        </div>
        <div class="card-body">
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

            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by product name or user...">
            </div>

            <?php if (empty($consumptionEntries)): ?>
                <div class="alert alert-info" role="alert">
                    No stock consumption records found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">S.No</th>
                                <th scope="col">Date Consumed</th>
                                <th scope="col">Product Name</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Used By</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="consumptionTableBody">
                            <?php $sno = 1; ?>
                            <?php foreach ($consumptionEntries as $entry): ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($entry['date_consumed']) ?></td>
                                    <td><?= esc($entry['product_name']) ?> (<?= esc($entry['unit_name']) ?>)</td>
                                    <td><?= esc($entry['quantity_consumed']) ?></td>
                                    <td><?= esc($entry['used_by']) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="<?= base_url('stock-consumption/edit/' . $entry['id']) ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="<?= base_url('stock-consumption/delete/' . $entry['id']) ?>" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this consumption record?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('consumptionTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            let sno = 1;
            for (let i = 0; i < rows.length; i++) {
                const productCell = rows[i].getElementsByTagName('td')[2];
                const usedByCell = rows[i].getElementsByTagName('td')[4];
                if (productCell || usedByCell) {
                    const productText = productCell.textContent || productCell.innerText;
                    const usedByText = usedByCell.textContent || usedByCell.innerText;
                    if (productText.toLowerCase().indexOf(filter) > -1 || usedByText.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                        rows[i].getElementsByTagName('td')[0].textContent = sno++;
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>
