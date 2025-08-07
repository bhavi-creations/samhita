<?php

namespace App\Models;

use CodeIgniter\Model;

class StockInProductModel extends Model
{
    protected $table          = 'stock_in_products';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'stock_in_id',
        'product_id',
        'quantity',
        'purchase_price',
        'item_total', // Calculated field
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Fetches all stock entries with product and unit details by joining tables.
     */
    public function getAllStockWithDetails()
    {
        return $this->select('
            stock_in_products.quantity,
            purchased_products.name AS product_name,
            purchased_products.current_stock AS available_stock,
            units.name AS unit_name
        ')
                    ->join('purchased_products', 'purchased_products.id = stock_in_products.product_id', 'left')
                    ->join('units', 'units.id = purchased_products.unit_id', 'left')
                    ->findAll();
    }
}
