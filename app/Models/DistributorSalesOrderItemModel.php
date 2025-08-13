<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorSalesOrderItemModel extends Model
{
    protected $table            = 'distributor_sales_order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'distributor_sales_order_id',
        'product_id',
        'gst_rate_id', 
        'quantity',
        'unit_price_at_sale',
        'item_total',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'distributor_sales_order_id' => 'required|integer',
        'product_id'                 => 'required|integer|is_not_unique[selling_products.id]',
        'quantity'                   => 'required|integer|greater_than[0]',
        'unit_price_at_sale'         => 'required|numeric|greater_than_equal_to[0]',
        'item_total'                 => 'required|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'product_id' => [
            'is_not_unique' => 'The selected product does not exist.',
        ],
        'quantity' => [
            'greater_than' => 'The quantity must be a positive number.',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
