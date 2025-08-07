<?php namespace App\Models;

use CodeIgniter\Model;

class AvailablePurchasedStockModel extends Model
{
    // The name of the database table associated with this model.
    protected $table = 'available_purchased_stock';

    // The primary key for the table.
    protected $primaryKey = 'id';

    // The type of the primary key.
    protected $returnType = 'array';

    // Whether to use soft deletes.
    protected $useSoftDeletes = false;

    // The fields that are allowed to be mass assigned.
    protected $allowedFields = ['product_id', 'balance'];

    // Whether to use timestamps.
    protected $useTimestamps = true;

    // The name of the created at field.
    protected $createdField = 'created_at';

    // The name of the updated at field.
    protected $updatedField = 'updated_at';

    // The name of the deleted at field (since useSoftDeletes is false, this is not used).
    protected $deletedField = 'deleted_at';

    // Validation rules for the fields.
    protected $validationRules = [];

    // Custom validation messages for the rules.
    protected $validationMessages = [];

    // Whether to skip validation.
    protected $skipValidation = false;
}
