<?php namespace App\Models;

use CodeIgniter\Model;

class StockInPaymentModel extends Model
{
    protected $table      = 'stock_in_payments';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['stock_in_id', 'payment_amount', 'payment_date', 'notes', 'created_at'];

    protected $useTimestamps = false; 
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    // Callbacks to update parent StockIn entry
    protected $afterInsert = ['updateStockInAmounts'];
    protected $afterUpdate = ['updateStockInAmounts'];
    protected $afterDelete = ['updateStockInAmounts'];

    protected function updateStockInAmounts(array $data)
    {
        log_message('debug', '--- updateStockInAmounts Callback Triggered ---');
        log_message('debug', 'Callback $data received: ' . json_encode($data));

        $stockInIdsToRecalculate = [];

        // Determine the stock_in_id(s) that need recalculation
        if (isset($data['data']) && is_array($data['data'])) {
            // This branch should handle:
            // - afterInsert: $data['data'] contains the newly inserted row
            // - afterUpdate: $data['data'] contains the updated data (or full row if returnType is object/array)
            // - afterDelete: $data['data'] contains the deleted row(s) (if returnType is array/object)

            if (isset($data['data']['stock_in_id'])) { // Single record (insert, update, single delete)
                $stockInIdsToRecalculate[] = $data['data']['stock_in_id'];
                log_message('debug', 'Identified stock_in_id from $data[data]: ' . $data['data']['stock_in_id']);
            } else { // Potentially multiple records (e.g., mass delete or updateBatch)
                foreach ($data['data'] as $record) {
                    if (is_array($record) && isset($record['stock_in_id'])) {
                        $stockInIdsToRecalculate[] = $record['stock_in_id'];
                        log_message('debug', 'Identified stock_in_id from $data[data] (multi): ' . $record['stock_in_id']);
                    }
                }
            }
        } 
        // Fallback for afterUpdate where $data['data'] doesn't explicitly contain 'stock_in_id'
        // (e.g., if stock_in_id wasn't part of the fields being updated).
        // This is safe for afterUpdate, but NOT for afterDelete (record already gone).
        else if (isset($data['id'])) { 
             log_message('debug', 'Attempting to fetch original record for ID: ' . $data['id']);
             $originalRecord = $this->find($data['id']); // Try to fetch the original record
             if ($originalRecord) {
                 $stockInIdsToRecalculate[] = $originalRecord['stock_in_id'];
                 log_message('debug', 'Identified stock_in_id from original record: ' . $originalRecord['stock_in_id']);
             } else {
                 log_message('debug', 'Original record not found for ID: ' . $data['id'] . '. (Likely afterDelete where record is gone)');
             }
        } else {
            log_message('debug', 'Could not determine stock_in_id from $data structure.');
        }
        
        // Ensure unique IDs and remove any nulls/empty values
        $stockInIdsToRecalculate = array_unique(array_filter($stockInIdsToRecalculate));
        log_message('debug', 'Final stockInIdsToRecalculate: ' . json_encode($stockInIdsToRecalculate));

        $stockInModel = new StockInModel();

        if (empty($stockInIdsToRecalculate)) {
            log_message('warning', 'No stock_in_id identified for recalculation.');
            return $data; // Exit if no IDs to process
        }

        foreach ($stockInIdsToRecalculate as $currentStockInId) {
            if ($currentStockInId) {
                log_message('debug', 'Processing stock_in_id: ' . $currentStockInId);

                // Calculate total paid for this stock_in_id
                $totalPaidResult = $this->selectSum('payment_amount')
                                         ->where('stock_in_id', $currentStockInId)
                                         ->get()
                                         ->getRow();
                $totalPaid = $totalPaidResult->payment_amount ?? 0;
                log_message('debug', 'Calculated totalPaid: ' . $totalPaid);

                // Get the grand_total from the parent stock_in entry
                $stockInEntry = $stockInModel->find($currentStockInId);
                if ($stockInEntry) {
                    $grandTotal = $stockInEntry['grand_total'];
                    $amountPending = $grandTotal - $totalPaid;
                    // log_message('debug', 'Grand Total: ' . $grandInEntry['grand_total'] . ', New Amount Pending: ' . $amountPending);  
                    log_message('debug', 'Grand Total: ' . $stockInEntry['grand_total'] . ', New Amount Pending: ' . $amountPending);


                    // Update the parent stock_in record
                    $updateResult = $stockInModel->update($currentStockInId, [
                        'amount_paid'    => $totalPaid,
                        'amount_pending' => $amountPending
                    ]);
                    if ($updateResult) {
                        log_message('debug', 'Stock In ID ' . $currentStockInId . ' amounts updated successfully.');
                    } else {
                        log_message('error', 'Failed to update Stock In ID ' . $currentStockInId . ' amounts.');
                    }
                } else {
                    log_message('warning', 'Parent Stock In entry (ID: ' . $currentStockInId . ') not found for recalculation.');
                }
            }
        }
        log_message('debug', '--- updateStockInAmounts Callback Finished ---');
        return $data; // Important: Always return the $data array from a callback
    }
}