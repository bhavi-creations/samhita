<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Stock In Entries</h4>
            <a href="<?= base_url('stock-in/create') ?>" class="btn btn-light btn-sm">Add New Stock In</a>
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

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>S.No.</th>
                            <th>Date Received</th>
                            <th>Vendor</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Purchase Price</th>
                            <th>GST Rate Name</th>
                            <th>Sub Total</th>
                            <th>GST Amount</th>
                            <th>Grand Total</th>
                            <th>Amount Paid</th>
                            <th>Amount Pending</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stock_entries)): ?>
                            <tr>
                                <td colspan="15" class="text-center">No stock in entries found.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; ?>
                            <?php foreach ($stock_entries as $in): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($in['date_received']) ?></td>
                                    <td><?= esc($in['vendor_agency_name']) ?> (<?= esc($in['vendor_name']) ?>)</td>
                                    <td><?= esc($in['product_name']) ?></td>
                                    <td><?= esc($in['quantity']) ?></td>
                                    <td><?= esc($in['unit_name']) ?></td>
                                    <td>₹<?= number_format($in['purchase_price'], 2) ?></td>
                                    <td><?= esc($in['gst_rate_name']) ?> (<?= esc($in['gst_rate_percentage']) ?>%)</td>
                                    <td>₹<?= number_format($in['total_amount_before_gst'], 2) ?></td>
                                    <td>₹<?= number_format($in['gst_amount'], 2) ?></td>
                                    <td>₹<?= number_format($in['grand_total'], 2) ?></td>
                                    <td>₹<?= number_format($in['amount_paid'], 2) ?></td>
                                    <td class="<?= ($in['amount_pending'] > 0) ? 'text-danger fw-bold' : '' ?>">
                                        ₹<?= number_format($in['amount_pending'], 2) ?>
                                    </td>
                                    <td><?= esc($in['notes']) ?></td>
                                    <td>
                                        <a href="<?= base_url('stock-in/view/' . $in['id']) ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="<?= base_url('stock-in/edit/' . $in['id']) ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                        <form action="<?= base_url('stock-in/delete/' . $in['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>