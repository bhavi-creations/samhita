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
        'gst_rate_at_sale',
        'item_total_before_gst',
        'item_gst_amount',
        'item_final_total',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'distributor_sales_order_id' => 'required|integer',
        'product_id'                 => 'required|integer',
        'gst_rate_id'                => 'required|integer',
        'quantity'                   => 'required|integer|greater_than[0]',
        'unit_price_at_sale'         => 'required|numeric|greater_than_equal_to[0]',
        'gst_rate_at_sale'           => 'required|numeric|greater_than_equal_to[0]',
        'item_total_before_gst'      => 'required|numeric',
        'item_gst_amount'            => 'required|numeric',
        'item_final_total'           => 'required|numeric',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;

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