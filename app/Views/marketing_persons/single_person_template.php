<!DOCTYPE html>
<html>
<head>
    <title>Marketing Person Details - <?= esc($person['name']) ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; width: 30%; }
        .document-section { margin-top: 30px; }
        .document-item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed #eee; }
        .document-item img { max-width: 200px; max-height: 200px; border: 1px solid #ccc; padding: 3px; }
    </style>
</head>
<body>
    <h1>Marketing Person Details</h1>
    <h2><?= esc($person['name']) ?> (<?= esc($person['custom_id']) ?>)</h2>

    <table>
        <tr>
            <th>Custom ID</th>
            <td><?= esc($person['custom_id']) ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?= esc($person['name']) ?></td>
        </tr>
        <tr>
            <th>Primary Phone</th>
            <td><?= esc($person['phone']) ?></td>
        </tr>
        <tr>
            <th>Secondary Phone</th>
            <td><?= esc($person['secondary_phone_num'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= esc($person['email'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= nl2br(esc($person['address'] ?? 'N/A')) ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?= esc($person['created_at']) ?></td>
        </tr>
        <tr>
            <th>Last Updated</th>
            <td><?= esc($person['updated_at']) ?></td>
        </tr>
    </table>

    <div class="document-section">
        <h3>Documents</h3>
        <?php 
        $documentFields = [
            'aadhar_card_image' => 'Aadhar Card',
            'pan_card_image' => 'PAN Card',
            'driving_license_image' => 'Driving License',
            'address_proof_image' => 'Address Proof'
        ];
        ?>
        <?php foreach ($documentFields as $field => $label): ?>
            <div class="document-item">
                <strong><?= $label ?>:</strong>
                <?php if (!empty($person[$field])): ?>
                    <?php 
                    $fileExtension = pathinfo($person[$field], PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                    ?>
                    <?php if ($isImage): ?>
                        <br><img src="<?= esc($upload_base_url . '/' . $person[$field]) ?>" alt="<?= esc($label) ?>">
                    <?php else: ?>
                        <br><a href="<?= esc($upload_base_url . '/' . $person[$field]) ?>" target="_blank">View PDF</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span>Not uploaded</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>