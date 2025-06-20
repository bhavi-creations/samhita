<?php $this->extend('layouts/main'); // Adjust 'layouts/main' to your actual layout file 
?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('success') ? '<div class="alert alert-success">' . session()->getFlashdata('success') . '</div>' : '' ?>
    <?= session()->getFlashdata('error') ? '<div class="alert alert-danger">' . session()->getFlashdata('error') . '</div>' : '' ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="<?= base_url('distributor-sales/new') ?>" class="btn btn-primary">Create New Sales Order</a>
        <div class="d-flex">
            <a href="<?= base_url('distributor-sales/export/excel-index') ?>" class="btn btn-success me-2">Export to Excel</a>
            <a href="<?= base_url('distributor-sales/export/pdf-index') ?>" class="btn btn-danger" target="_blank">Export to PDF</a>
        </div>
    </div>

    <?php if (empty($sales_orders)): ?>
        <div class="alert alert-info">No sales orders found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>S.No.</th> <th>Invoice Number</th>
                        <th>Distributor</th>
                        <th>Invoice Date</th>
                        <th>Total Amount</th>
                        <th>Amount Paid</th>
                        <th>Due Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $s_no = 1; // Initialize serial number ?>
                    <?php foreach ($sales_orders as $order): ?>
                        <tr>
                            <td><?= $s_no++ ?></td> <td><?= esc($order['invoice_number']) ?></td>
                            <td><?= esc($order['agency_name']) ?></td>
                            <td><?= esc($order['invoice_date']) ?></td>
                            <td>₹<?= number_format($order['final_total_amount'], 2) ?></td>
                            <td>₹<?= number_format($order['amount_paid'], 2) ?></td>
                            <td>₹<?= number_format($order['due_amount'], 2) ?></td>
                            <td><span class="badge bg-<?= ($order['status'] == 'Paid') ? 'success' : (($order['status'] == 'Partially Paid') ? 'warning' : 'danger') ?>"><?= esc($order['status']) ?></span></td>
                            <td>
                                <a href="<?= base_url('distributor-sales/show/' . $order['id']) ?>" class="btn btn-info btn-sm">View</a>
                                <a href="<?= base_url('distributor-sales/edit/' . $order['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <?php if ($order['status'] != 'Paid' && $order['status'] != 'Cancelled'): ?>
                                    <a href="<?= base_url('distributor-sales/add-payment/' . $order['id']) ?>" class="btn btn-success btn-sm">Add Payment</a>
                                <?php endif; ?>
                                <form action="<?= base_url('distributor-sales/delete/' . $order['id']) ?>" method="post" class="d-inline delete-form" onsubmit="return confirmDelete()">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this sales order? This action cannot be undone.');
    }
</script>
<?php $this->endSection(); ?>