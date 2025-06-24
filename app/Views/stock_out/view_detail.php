<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($title) ?></h2>
        <a href="<?= base_url('stock-out') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Stock Out List</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Stock Out Details #<?= esc($record['id']) ?></h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Product Name:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['product_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Quantity Out:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['quantity_out']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Transaction Type:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['transaction_type']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Transaction ID:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['transaction_id'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Distributed To:</strong>
                </div>
                <div class="col-md-6">
                    <?php if ($record['transaction_type'] === 'distributor_sale' && !empty($record['related_transaction_details'])): ?>
                        Agency Name: <?= esc($record['related_transaction_details']['agency_name'] ?? 'N/A') ?><br>
                        Owner Name: <?= esc($record['related_transaction_details']['owner_name'] ?? 'N/A') ?>
                    <?php elseif ($record['transaction_type'] === 'Sale' && !empty($record['related_transaction_details'])): ?>
                        Marketing Person: <?= esc($record['related_transaction_details']['marketing_person'] ?? 'N/A') ?>
                    <?php elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['related_transaction_details'])): ?>
                        Marketing Person: <?= esc($record['related_transaction_details']['marketing_person_name'] ?? 'N/A') ?> (ID: <?= esc($record['related_transaction_details']['marketing_person_custom_id'] ?? 'N/A') ?>)
                    <?php elseif ($record['transaction_type'] === 'Damage'): ?>
                        N/A (Recorded as Damage)
                    <?php elseif ($record['transaction_type'] === 'Sample'): ?>
                        N/A (Issued as Sample)
                    <?php elseif ($record['transaction_type'] === 'Internal Use'): ?>
                        N/A (Internal Use)
                    <?php else: ?>
                        N/A (No specific person)
                    <?php endif; ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Issued Date:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['issued_date']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Notes:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['notes'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Recorded At:</strong>
                </div>
                <div class="col-md-6">
                    <?= esc($record['created_at']) ?>
                </div>
            </div>
            <?php if (!empty($record['updated_at']) && $record['updated_at'] !== $record['created_at']): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Last Updated:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['updated_at']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($record['transaction_type'] === 'distributor_sale' && !empty($record['related_transaction_details']['distributor_db_id'])): ?>
                <hr>
                <h5 class="mt-4 mb-3">Related Distributor Sales Order Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Sales Order ID:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['sales_order_id'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Invoice Number:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['invoice_number'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Invoice Date:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['invoice_date'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <a href="<?= base_url('distributors/view/' . $record['related_transaction_details']['distributor_db_id']) ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-user"></i> View Distributor Details
                        </a>
                        <a href="<?= base_url('distributor-sales/show/' . $record['related_transaction_details']['sales_order_id']) ?>" class="btn btn-info btn-sm ms-2">
                            <i class="fas fa-file-invoice"></i> View Sales Order
                        </a>
                    </div>
                </div>
            <?php elseif ($record['transaction_type'] === 'Sale' && !empty($record['related_transaction_details'])): ?>
                <hr>
                <h5 class="mt-4 mb-3">Related Sale Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Sale ID:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['sale_id'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Marketing Person:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['marketing_person'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Sale Date:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['sale_date'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Quantity Sold (in Sale):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['quantity_sold_in_sale'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Price Per Unit (in Sale):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= number_format($record['related_transaction_details']['price_per_unit_in_sale'] ?? 0, 2) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Total Price (in Sale):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= number_format($record['related_transaction_details']['total_price_in_sale'] ?? 0, 2) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Payment Status (from Sale):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['payment_status_from_person'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Amount Remitted (from Sale):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= number_format($record['related_transaction_details']['amount_received_from_person'] ?? 0, 2) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Balance Due (from Sale):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= number_format($record['related_transaction_details']['balance_from_person'] ?? 0, 2) ?>
                    </div>
                </div>
                <?php if (!empty($record['related_transaction_details']['marketing_person_db_id'])): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <a href="<?= base_url('marketing-persons/view/' . $record['related_transaction_details']['marketing_person_db_id']) ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-user-tie"></i> View Marketing Person Details
                            </a>
                             <a href="<?= base_url('sales/view/' . $record['related_transaction_details']['sale_id']) ?>" class="btn btn-info btn-sm ms-2">
                                <i class="fas fa-receipt"></i> View Sale Details
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['related_transaction_details'])): ?>
                <hr>
                <h5 class="mt-4 mb-3">Related Marketing Distribution Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Distribution ID:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['distribution_id'] ?? 'N/A') ?>
                    </div>
                </div>
                 <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Marketing Person:</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['marketing_person_name'] ?? 'N/A') ?> (ID: <?= esc($record['related_transaction_details']['marketing_person_custom_id'] ?? 'N/A') ?>)
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Quantity Issued (in Distribution):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['quantity_issued_in_dist'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date Issued (in Distribution):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['date_issued_in_dist'] ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Notes (from Distribution):</strong>
                    </div>
                    <div class="col-md-6">
                        <?= esc($record['related_transaction_details']['notes_in_dist'] ?? 'N/A') ?>
                    </div>
                </div>
                <?php if (!empty($record['related_transaction_details']['marketing_person_db_id'])): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <a href="<?= base_url('marketing-persons/view/' . $record['related_transaction_details']['marketing_person_db_id']) ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-user-tie"></i> View Marketing Person Details
                            </a>
                            <!-- Removed the "View Distribution Details" button as it has no dedicated view -->
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
