<!DOCTYPE html>
<html>
<head>
    <title>All Marketing Persons</title>
    <style>
        /* General body and table styles */
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; } /* Slightly smaller font for more content */
        h1 { text-align: center; font-size: 16px; margin-bottom: 15px; } /* Smaller heading */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; vertical-align: top;} /* Smaller padding, top align for docs */
        th { background-color: #f2f2f2; }

        /* Specific styles for document thumbnails/links */
        .doc-item { margin-bottom: 3px; } /* Small margin between document items */
        .doc-label { font-weight: bold; font-size: 8px; display: inline-block; min-width: 35px;} /* Label for each doc */
        .doc-thumbnail {
            max-width: 35px; /* Very small thumbnail size */
            max-height: 35px;
            height: auto;
            display: inline-block; /* Aligns next to label */
            border: 1px solid #ddd;
            vertical-align: middle;
            margin-left: 2px;
        }
        .doc-link {
            font-size: 8px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 2px;
            color: #007bff; /* Bootstrap blue for links */
            text-decoration: none;
        }
        .no-docs { font-size: 8px; color: #888; }
    </style>
</head>
<body>
    <h1>All Marketing Persons</h1>
    <table>
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Custom ID</th>
                <th>Name</th>
                <th>Primary Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th style="width: 20%;">Documents</th> <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($persons)): ?>
                <tr>
                    <td colspan="8" class="text-center">No marketing persons found.</td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($persons as $person): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= esc($person['custom_id']) ?></td>
                        <td><?= esc($person['name']) ?></td>
                        <td><?= esc($person['phone']) ?></td>
                        <td><?= esc($person['email']) ?></td>
                        <td><?= nl2br(esc($person['address'])) ?></td>
                        <td>
                            <?php
                            // Define document fields and their display labels
                            $documentFields = [
                                'aadhar_card_image' => 'Aadhar',
                                'pan_card_image' => 'PAN',
                                'driving_license_image' => 'DL',
                                'address_proof_image' => 'Address Proof'
                            ];
                            $doc_found = false;
                            foreach ($documentFields as $field => $label):
                                if (!empty($person[$field])):
                                    $doc_found = true;
                                    $fileExtension = pathinfo($person[$field], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>
                                    <div class="doc-item">
                                        <span class="doc-label"><?= $label ?>:</span>
                                        <?php if ($isImage): ?>
                                            <img src="<?= esc($upload_base_url . '/' . $person[$field]) ?>" class="doc-thumbnail" alt="<?= esc($label) ?>">
                                        <?php else: ?>
                                            <a href="<?= esc($upload_base_url . '/' . $person[$field]) ?>" class="doc-link" target="_blank">[PDF]</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif;
                            endforeach;
                            if (!$doc_found): ?>
                                <span class="no-docs">No documents</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($person['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>