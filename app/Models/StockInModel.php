<?php

namespace App\Models;

use CodeIgniter\Model;

class StockInModel extends Model
{
    protected $table      = 'stock_in';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array'; // Ensure this is 'array' for consistent data handling
    protected $useSoftDeletes = false;

    // Make sure these fields are correctly listed as fillable
    protected $allowedFields = [
        'product_id',
        'quantity',
        'current_quantity',
        'vendor_id',
        'purchase_price',
        'total_amount_before_gst',
        'gst_rate_id',
        'gst_amount',
        'grand_total',
        'amount_paid',
        'amount_pending',
        'date_received',
        'notes'
    ];

    protected $useTimestamps = false; // Adjust based on your actual columns
    // If you have `created_at` and `updated_at` columns, set to true and define fields.
    // protected $useTimestamps = true;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';


    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    // Add these lines for callbacks
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = ['calculatePendingAmount']; // <<< ADD THIS LINE
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];


    /**
     * Callback to calculate amount_pending before updating a stock_in record.
     *
     * @param array $data The data being updated.
     * @return array Modified data.
     */
    protected function calculatePendingAmount(array $data)
    {
        log_message('debug', 'StockInModel: calculatePendingAmount callback triggered.');
        log_message('debug', 'StockInModel: Callback $data received: ' . json_encode($data));

        // Get the ID of the record being updated.
        // We'll extract the single ID, handling if $data['id'] is an array.
        $recordId = null;
        if (isset($data['id'])) {
            if (is_array($data['id'])) {
                // If it's an array of IDs, take the first one (for single record update context)
                $recordId = $data['id'][0] ?? null; 
            } else {
                // If it's a scalar ID (expected for single update)
                $recordId = $data['id']; 
            }
        }

        if (empty($recordId)) {
            log_message('error', 'StockInModel: No valid record ID found in callback $data. Cannot calculate pending amount.');
            return $data; // Exit early if no ID
        }

        // --- Grand Total Determination ---
        $newGrandTotal = $data['data']['grand_total'] ?? null;

        // If newGrandTotal is not in the updated data, fetch it from the existing record.
        if ($newGrandTotal === null) {
             log_message('debug', 'StockInModel: grand_total not in $data[data], fetching existing record for ID: ' . $recordId);
             $existingEntryForGrandTotal = $this->find($recordId); // Find by scalar ID
             if ($existingEntryForGrandTotal) {
                 $newGrandTotal = $existingEntryForGrandTotal['grand_total'];
                 log_message('debug', 'StockInModel: Fetched grand_total from existing record: ' . $newGrandTotal);
             } else {
                 log_message('error', 'StockInModel: Existing entry for grand_total not found for ID: ' . $recordId);
             }
        }
        
        // --- Current Amount Paid Determination ---
        // Always fetch currentAmountPaid from the existing database record.
        // The edit form does NOT send amount_paid, as it's managed by payments.
        $currentAmountPaid = 0; // Default to 0
        
        log_message('debug', 'StockInModel: Attempting to find existing entry for amount_paid using ID: ' . $recordId);
        $existingEntryForPaidAmount = $this->find($recordId); // Find by scalar ID

        if ($existingEntryForPaidAmount) {
            log_message('debug', 'StockInModel: Existing entry found for paid amount check: ' . json_encode($existingEntryForPaidAmount));
            // Check if 'amount_paid' key exists in the *single row array*
            if (array_key_exists('amount_paid', $existingEntryForPaidAmount)) { 
                $currentAmountPaid = $existingEntryForPaidAmount['amount_paid'];
                log_message('debug', 'StockInModel: Fetched currentAmountPaid from existing record: ' . $currentAmountPaid);
            } else {
                log_message('error', 'StockInModel: "amount_paid" key NOT FOUND in the fetched existingEntry. Entry: ' . json_encode($existingEntryForPaidAmount));
            }
        } else {
            log_message('error', 'StockInModel: Existing entry NOT FOUND for ID ' . $recordId . ' when trying to get amount_paid.');
        }
        
        // --- Calculation and Update ---
        $newGrandTotal = (float) $newGrandTotal;
        $currentAmountPaid = (float) $currentAmountPaid;

        if ($newGrandTotal !== null) { 
            $data['data']['amount_pending'] = $newGrandTotal - $currentAmountPaid;
            log_message('debug', 'StockInModel: Calculated amount_pending: ' . $data['data']['amount_pending']); 
        } else {
            log_message('warning', 'StockInModel: Could not calculate amount_pending. GrandTotal is null. Data: ' . json_encode($data));
        }

        log_message('debug', 'StockInModel: calculatePendingAmount callback finished. Returning data.');
        return $data; // Important: Return the modified data array
    }
}
