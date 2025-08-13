<!-- This view now extends a generic 'layouts/main' template -->
<?= $this->extend('layouts/main') ?>

<!-- This section sets the content for the title of the page -->
<?= $this->section('title') ?>
    <?= esc($title) ?>
<?= $this->endSection() ?>

<!-- This section contains the main content of the page -->
<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= esc($title) ?></h3>
                <div class="card-tools">
                    <!-- Placeholder for future export buttons -->
                    <!-- Example:
                    <a href="#" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                    -->
                </div>
            </div>
            <div class="card-body">
                <!-- Filter form to select marketing person -->
                <form action="<?= base_url('marketing-sales') ?>" method="get" class="mb-3">
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <label class="sr-only" for="inlineFormInputGroup">Marketing Person</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="fas fa-user-tie"></i></div>
                                </div>
                                <select name="marketing_person_id" id="marketing_person_id" class="form-control">
                                    <option value="">All Marketing Persons</option>
                                    <?php foreach ($marketingPersons as $person): ?>
                                        <option value="<?= esc($person['id']) ?>" <?= ($person['id'] == $selectedPersonId) ? 'selected' : '' ?>>
                                            <?= esc($person['name']) ?>   <?= esc($person['custom_id']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-2">Filter</button>
                            <?php if ($selectedPersonId): ?>
                                <a href="<?= base_url('marketing-sales') ?>" class="btn btn-secondary mb-2">Clear Filter</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Sales for: <?= esc($selectedPersonName) ?></h4>
                    <span class="badge bg-primary">Total Sales Orders: <?= count($salesOrders) ?></span>
                </div>

                <!-- Table to display the sales orders -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <!-- Added S.No column -->
                                <th>S.No</th>
                                <th>Invoice No</th>
                                <th>Invoice Date</th>
                                <th>Distributor</th>
                                <th>Marketing Person</th>
                                <th>Total Amount</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($salesOrders)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No sales orders found for this selection.</td>
                                </tr>
                            <?php endif; ?>
                            <?php 
                            // Initialize a counter for the serial number
                            $i = 1;
                            foreach ($salesOrders as $order): ?>
                                <tr>
                                    <!-- Display and increment the serial number for each row -->
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($order['invoice_number']) ?></td>
                                    <td><?= esc(date('d-M-Y', strtotime($order['invoice_date']))) ?></td>
                                    <td><?= esc($order['agency_name']) ?> (<?= esc($order['owner_name']) ?>)</td>
                                    <td><?= esc($order['marketing_person_name']) ?></td>
                                    <td>₹<?= number_format(esc($order['grand_total'] ?? 0), 2) ?></td>
                                    <td><?= ($order['payment_status'] == 'Paid') ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-warning text-dark">Unpaid</span>' ?></td>
                                    <td>
                                        <a href="<?= base_url('distributor-sales/view/' . esc($order['id'])) ?>" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
