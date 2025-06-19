<!DOCTYPE html>
<html>
<head>
    <title>Distributor Details - PDF Export</title>
    <style>
        body { font-family: sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .card { border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
        .card-header { background-color: #f2f2f2; padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; }
        .card-body { padding: 15px; }
        .detail-row { display: flex; margin-bottom: 8px; }
        .detail-label { font-weight: bold; width: 150px; flex-shrink: 0; }
        .detail-value { flex-grow: 1; }
        .half-width { width: 48%; display: inline-block; vertical-align: top; margin-right: 1%; }
        .full-width { width: 100%; }
        h6 { margin-top: 15px; margin-bottom: 10px; color: #007bff; }
        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }

        /* Badge styling for PDF */
        .badge {
            display: inline-block;
            padding: .35em .65em;
            font-size: .75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
        .bg-success { background-color: #198754; color: #fff; }
        .bg-danger { background-color: #dc3545; color: #fff; }
        .bg-warning { background-color: #ffc107; color: #212529; }
        .bg-secondary { background-color: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Distributor Details</h2>
        <h3><?= esc($distributor['agency_name']) ?></h3>
        <p>Export Date: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <div class="card">
        <div class="card-header">Distributor Information</div>
        <div class="card-body">
            <div class="half-width">
                <p><strong>Custom ID:</strong> <?= esc($distributor['custom_id']) ?></p>
                <p><strong>Owner Name:</strong> <?= esc($distributor['owner_name']) ?></p>
                <p><strong>Owner Phone:</strong> <?= esc($distributor['owner_phone']) ?></p>
                <p><strong>Gmail:</strong> <?= esc($distributor['gmail']) ?: 'N/A' ?></p>
            </div>
            <div class="half-width">
                <p><strong>Agency Name:</strong> <?= esc($distributor['agency_name']) ?></p>
                <p><strong>Agency GST Number:</strong> <?= esc($distributor['agency_gst_number']) ?: 'N/A' ?></p>
                <p>
                    <strong>Status:</strong>
                    <?php
                    $status_class = '';
                    switch ($distributor['status']) {
                        case 'Active':
                            $status_class = 'bg-success';
                            break;
                        case 'Inactive':
                            $status_class = 'bg-danger';
                            break;
                        case 'On Hold':
                            $status_class = 'bg-warning text-dark';
                            break;
                        default:
                            $status_class = 'bg-secondary';
                            break;
                    }
                    ?>
                    <span class="badge <?= $status_class ?>"><?= esc($distributor['status']) ?></span>
                </p>
                <p><strong>Agent Name:</strong> <?= esc($distributor['agent_name']) ?: 'N/A' ?></p>
                <p><strong>Agent Phone:</strong> <?= esc($distributor['agent_phone']) ?: 'N/A' ?></p>
            </div>
            <div class="full-width">
                <p><strong>Agency Address:</strong> <?= nl2br(esc($distributor['agency_address'])) ?></p>
                <p><strong>Notes:</strong> <?= nl2br(esc($distributor['notes'])) ?: 'N/A' ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Timestamps</div>
        <div class="card-body">
            <p><strong>Created At:</strong> <?= esc($distributor['created_at']) ?></p>
            <p><strong>Last Updated At:</strong> <?= esc($distributor['updated_at']) ?></p>
        </div>
    </div>
</body>
</html>