<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorPaymentModel extends Model
{
    protected $table            = 'distributor_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'distributor_sales_order_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_id',
        'notes',
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
        'payment_date'               => 'required|valid_date',
        'amount'                     => 'required|numeric|greater_than[0]',
        'payment_method'             => 'permit_empty|max_length[50]',
        'transaction_id'             => 'permit_empty|max_length[100]',
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