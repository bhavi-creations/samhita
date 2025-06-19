<!DOCTYPE html>
<html>
<head>
    <title>Distributors List - PDF Export</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 10px; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
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
        /* Basic colors for badges - Dompdf needs explicit colors */
        .bg-success { background-color: #198754; color: #fff; }
        .bg-danger { background-color: #dc3545; color: #fff; }
        .bg-warning { background-color: #ffc107; color: #212529; } /* Text color for warning badge */
        .bg-secondary { background-color: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Distributors List</h2>
        <p>Export Date: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Custom ID</th>
                <th>Agency Name</th>
                <th>Owner Name</th>
                <th>Owner Phone</th>
                <th>Agency Address</th>
                <th>Status</th>
                <th>Agent Name</th>
                <th>Agent Phone</th>
                <th>GST Number</th>
                <th>Gmail</th>
                <th>Notes</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            <?php $s_no = 1; ?>
            <?php foreach ($distributors as $distributor): ?>
                <tr>
                    <td><?= $s_no++ ?></td>
                    <td><?= esc($distributor['custom_id']) ?></td>
                    <td><?= esc($distributor['agency_name']) ?></td>
                    <td><?= esc($distributor['owner_name']) ?></td>
                    <td><?= esc($distributor['owner_phone']) ?></td>
                    <td><?= esc($distributor['agency_address']) ?></td>
                    <td>
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
                    </td>
                    <td><?= esc($distributor['agent_name']) ?></td>
                    <td><?= esc($distributor['agent_phone']) ?></td>
                    <td><?= esc($distributor['agency_gst_number']) ?></td>
                    <td><?= esc($distributor['gmail']) ?></td>
                    <td><?= esc($distributor['notes']) ?></td>
                    <td><?= esc($distributor['created_at']) ?></td>
                    <td><?= esc($distributor['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>