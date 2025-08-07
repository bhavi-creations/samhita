<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= esc($title) ?></h4>
            <a href="<?= base_url('stock-in/create') ?>" class="btn btn-light btn-sm">Add New Entry</a>
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
                <input type="text" id="searchInput" class="form-control" placeholder="Search by date or vendor name...">
            </div>

            <?php if (empty($stockInEntries)): ?>
                <div class="alert alert-info" role="alert">
                    No stock-in entries found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">S.No</th>
                                <th scope="col">Date Received</th>
                                <th scope="col">Vendor</th>
                                <th scope="col">Grand Total</th>
                                <th scope="col">Amount Paid</th>
                                <th scope="col">Balance</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stockInTableBody">
                            <?php 
                            // This assumes the data is already sorted by date in descending order in the controller.
                            $sno = 1;
                            foreach ($stockInEntries as $entry): 
                            ?>
                                <tr>
                                    <td><?= $sno++ ?></td>
                                    <td><?= esc($entry['date_received']) ?></td>
                                    <td><?= esc($entry['vendor_name'] ?? 'N/A') ?></td>
                                    <td>₹ <?= number_format(esc($entry['final_grand_total']), 2) ?></td>
                                    <td>₹ <?= number_format(esc($entry['initial_amount_paid']), 2) ?></td>
                                    <td>₹ <?= number_format(esc($entry['balance_amount']), 2) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="<?= base_url('stock-in/view/' . $entry['id']) ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="<?= base_url('stock-in/edit/' . $entry['id']) ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="<?= base_url('stock-in/delete/' . $entry['id']) ?>" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
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
        const tableBody = document.getElementById('stockInTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            let sno = 1;
            for (let i = 0; i < rows.length; i++) {
                const vendorCell = rows[i].getElementsByTagName('td')[2];
                const dateCell = rows[i].getElementsByTagName('td')[1];
                if (vendorCell || dateCell) {
                    const vendorText = vendorCell.textContent || vendorCell.innerText;
                    const dateText = dateCell.textContent || dateCell.innerText;
                    if (vendorText.toLowerCase().indexOf(filter) > -1 || dateText.toLowerCase().indexOf(filter) > -1) {
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
