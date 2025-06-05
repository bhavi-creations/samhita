<?php

namespace App\Models;
use CodeIgniter\Model;

class MarketingDistributionModel extends Model
{
    protected $table = 'marketing_distribution';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'marketing_person_id', 'quantity_issued', 'date_issued'];
}
