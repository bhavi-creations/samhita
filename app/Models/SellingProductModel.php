<?php namespace App\Models;

use CodeIgniter\Model;

class SellingProductModel extends Model
{
    // The table this model works with
    protected $table          = 'selling_products';
    // The primary key for the table
    protected $primaryKey     = 'id';

    // Set to true if the primary key is auto-incrementing
    protected $useAutoIncrement = true;

    // The type of result to return
    protected $returnType     = 'array';

    // Whether or not to use soft deletes
    protected $useSoftDeletes = true;

    // The fields that can be mass-assigned
    protected $allowedFields  = ['name', 'description', 'dealer_price', 'farmer_price', 'current_stock', 'unit_id'];

    // Whether to use created_at and updated_at timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // The field to use for soft deletes
    protected $deletedField  = 'deleted_at';

    // The validation rules and messages have been removed from here.
    // Validation should now be handled at the controller or service layer.

    protected $skipValidation = false;
}

