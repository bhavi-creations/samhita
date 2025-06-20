<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorSalesOrderModel extends Model
{
    protected $table            = 'distributor_sales_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'distributor_id',
        'invoice_number',
        'invoice_date',
        'total_amount_before_gst',
        'total_gst_amount',
        'final_total_amount',
        'discount_amount', // Renamed from 'discount' to 'discount_amount' for clarity and consistency with other amount fields
        'amount_paid',
        'due_amount',
        'status',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Not used, but common to define

    // Validation
    protected $validationRules = [
        'distributor_id'            => 'required|integer',
        'invoice_number'            => 'required|max_length[50]',
        'invoice_date'              => 'required|valid_date',
        'total_amount_before_gst'   => 'required|numeric',
        'total_gst_amount'          => 'required|numeric',
        'final_total_amount'        => 'required|numeric',
        'discount_amount'           => 'permit_empty|numeric|greater_than_equal_to[0]', // Added validation for discount
        'amount_paid'               => 'required|numeric',
        'due_amount'                => 'required|numeric',
        'status'                    => 'required|in_list[Pending,Partially Paid,Paid,Cancelled]',
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