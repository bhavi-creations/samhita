<?php

namespace App\Models;

use CodeIgniter\Model;

class StockInModel extends Model
{
    protected $table = 'stock_in';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'quantity', 'date_received', 'notes'];
    protected $useTimestamps = false; // You don't have created_at or updated_at
}
