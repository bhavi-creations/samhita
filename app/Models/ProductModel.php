<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'description', 'unit_id', 'created_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
}
