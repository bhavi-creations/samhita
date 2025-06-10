<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketingDistributionModel extends Model
{
    protected $table      = 'marketing_distribution'; // <--- CORRECTED TABLE NAME
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array'; // or 'object'
    protected $useSoftDeletes = false; // Based on your DESC, you don't have deleted_at

    // <--- CORRECTED ALLOWED FIELDS based on your DESC output
    protected $allowedFields = ['product_id', 'marketing_person_id', 'quantity_issued', 'notes', 'date_issued'];

    protected $useTimestamps = false; // <--- Set to false if you don't have created_at/updated_at
    // protected $createdField  = 'created_at'; // Not in your DESC
    // protected $updatedField  = 'updated_at'; // Not in your DESC
    // protected $deletedField  = 'deleted_at'; // Not used

    // Validation rules (optional)
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}