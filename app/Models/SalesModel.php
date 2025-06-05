<?php

namespace App\Models;
use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'marketing_person_id', 'quantity_sold', 'price_per_unit', 'date_sold'];
}
