<?php

namespace App\Models;

use CodeIgniter\Model;

class StockInModel extends Model
{
    protected $table            = 'stock_in';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'vendor_id',
        'date_received',
        'notes',
        'discount_amount',
        'initial_amount_paid',
        'balance_amount',
        'payment_type',
        'transaction_id',
        'payment_notes',
        'total_amount_before_gst',
        'gst_amount',
        'grand_total',
        'final_grand_total',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Fetches all stock-in entries along with their vendor names.
     * @return array
     */
    public function getStockInEntriesWithVendors()
    {
        // Select all fields from the stock_in table and alias the vendor's name as 'vendor_name'
        return $this->select('stock_in.*, vendors.name as vendor_name')
                    ->join('vendors', 'vendors.id = stock_in.vendor_id', 'left')
                    ->findAll();
    }
}
