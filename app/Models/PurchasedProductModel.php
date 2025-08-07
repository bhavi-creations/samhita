<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchasedProductModel extends Model
{
    protected $table          = 'purchased_products';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'name',
        'description',
        'unit_id',
        'current_stock',
    ];

    /**
     * Fetches all purchased products, calculating the available stock
     * by subtracting total consumed quantity from total purchased quantity.
     */
    public function getProductsWithUnitDetails()
    {
        // Subquery to get the total quantity of each product that has been consumed
        $consumptionSubquery = $this->db->table('stock_consumption')
            ->select('product_id, SUM(quantity_consumed) AS total_consumed')
            ->groupBy('product_id')
            ->getCompiledSelect();

        // Subquery to get the total quantity of each product that has been purchased/added to stock
        $purchasedSubquery = $this->db->table('stock_in_products')
            ->select('product_id, SUM(quantity) AS total_purchased')
            ->groupBy('product_id')
            ->getCompiledSelect();
        
        return $this->select('
            purchased_products.id,
            purchased_products.name AS product_name,
            COALESCE(purchased_data.total_purchased, 0) - COALESCE(consumption_data.total_consumed, 0) AS available_stock,
            units.name AS unit_name
        ')
            ->join('units', 'units.id = purchased_products.unit_id', 'left')
            ->join("({$purchasedSubquery}) AS purchased_data", 'purchased_data.product_id = purchased_products.id', 'left')
            ->join("({$consumptionSubquery}) AS consumption_data", 'consumption_data.product_id = purchased_products.id', 'left')
            ->groupBy('purchased_products.id')
            ->findAll();
    }
}
