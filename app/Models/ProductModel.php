<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Or 'object' based on your preference
    protected $useSoftDeletes   = false; // Set to true if you implement soft deletes (requires 'deleted_at' column in DB)

    // Fields that can be mass-assigned using insert/update/save methods
    protected $allowedFields = [
        'name',
        'description',
        'selling_price',         // <--- Added for managing product unit price
        'default_selling_price', // <--- Included as it's in your DB, even if its use is unclear for now
        'current_stock',         // <--- Included as it's in your DB
        'unit_id',               // 'created_at' is managed automatically by useTimestamps, no need here
        'updated_at',            // <--- Important: Allow this field to be updated automatically by useTimestamps
    ];

    protected bool $allowEmptyInserts = false; // Set to true if you want to allow empty inserts

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Only needed if $useSoftDeletes is true

    // Validation
    // These are example rules. Adjust them based on your exact requirements.
    protected $validationRules = [
        'name'            => 'required|min_length[3]|max_length[255]',
        'description'     => 'permit_empty|max_length[1000]', // 'permit_empty' allows field to be optional
        'selling_price'   => 'permit_empty|numeric|greater_than_equal_to[0]|decimal[10,2]', // Validation for price format
        'default_selling_price' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal[10,2]',
        'current_stock'   => 'permit_empty|integer|greater_than_equal_to[0]',
        'unit_id'         => 'permit_empty|integer', // Assuming it's an integer ID
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks (e.g., to hash passwords, prepare data before/after DB operations)
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