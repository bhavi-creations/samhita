<?php

namespace App\Models;

use CodeIgniter\Model;

class StockInModel extends Model
{
    protected $table = 'stock_in';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'product_id',
        'quantity',
        'vendor_id',
        'purchase_price',
        'selling_price',
        'date_received',
        'notes'
    ];
    protected $useTimestamps = false;
}
