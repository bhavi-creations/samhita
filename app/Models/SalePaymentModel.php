<?php

namespace App\Models;

use CodeIgniter\Model;

class SalePaymentModel extends Model
{
    protected $table = 'sale_payments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'sale_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'sale_id'       => 'required|integer',
        'payment_date'  => 'required|valid_date',
        'amount_paid'   => 'required|numeric|greater_than[0]',
        'payment_method'=> 'permit_empty|max_length[50]',
        'remarks'       => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'amount_paid' => [
            'greater_than' => 'The Amount Paid must be greater than zero.'
        ]
    ];
}