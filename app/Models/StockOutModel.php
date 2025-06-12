<?php

namespace App\Models;

use CodeIgniter\Model;

class StockOutModel extends Model
{
    protected $table            = 'stock_out';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'product_id',
        'quantity_out',
        'transaction_type',
        'transaction_id',
        'issued_date',
        'notes',
    ];

    protected $useTimestamps = true; // Use created_at and updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'product_id'        => 'required|integer|is_not_unique[products.id]',
        'quantity_out'      => 'required|integer|greater_than[0]',
        'transaction_type'  => 'required|max_length[50]',
        'transaction_id'    => 'required|integer',
        'issued_date'       => 'required|valid_date',
    ];

    protected $validationMessages = [
        'product_id' => [
            'is_not_unique' => 'The product ID for stock out is invalid.',
        ],
        'quantity_out' => [
            'greater_than' => 'The quantity out must be a positive number.',
        ],
    ];
}