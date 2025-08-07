<?php

namespace App\Models;

use CodeIgniter\Model;

class StockConsumptionModel extends Model
{
    protected $table          = 'stock_consumption';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'product_id',
        'quantity_consumed',
        'date_consumed', // Corrected column name
        'used_by',
        'reason',
    ];

    /**
     * Fetches all stock consumption entries along with the product name and unit name.
     */
    public function getConsumptionEntriesWithDetails()
    {
        return $this->select('stock_consumption.*, purchased_products.name AS product_name, units.name AS unit_name')
                    ->join('purchased_products', 'purchased_products.id = stock_consumption.product_id')
                    ->join('units', 'units.id = purchased_products.unit_id')
                    ->findAll();
    }
}
