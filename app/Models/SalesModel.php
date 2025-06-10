<?php

namespace App\Models;
use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'product_id',
        'marketing_person_id',
        'quantity_sold',
        'price_per_unit',
        'discount',
        'total_price',
        'date_sold',
        'customer_name',
        'customer_phone',
        'customer_address',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}