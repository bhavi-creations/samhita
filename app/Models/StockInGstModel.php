<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * This model handles all database interactions for the 'stock_in_gsts' table.
 * The $allowedFields property has been defined to allow the 'stock_in_id'
 * and 'gst_rate_id' columns to be written to the database, which fixes
 * the SQL syntax error.
 */
class StockInGstModel extends Model
{
    // Corrected table name to match the user's database schema
    protected $table            = 'stock_in_gst';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['stock_in_id', 'gst_rate_id'];

    // Dates
    // We are setting this to false to prevent the model from trying to
    // add an 'updated_at' column, which does not exist in the table.
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at'; // This will now be ignored
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
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
