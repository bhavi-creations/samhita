<?php namespace App\Models;

use CodeIgniter\Model;

class GstRateModel extends Model
{
    protected $table          = 'gst_rates';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array'; // Or 'object'
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'rate']; // 'name' (e.g., "GST 18%"), 'rate' (e.g., 18.00)

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Uncomment if you add this column to your table

    // Validation rules for adding/updating GST rates
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[50]',
        // --- CHANGE START ---
        // Rate can now be a whole number percentage (e.g., 18 for 18%)
        // Changed 'greater_than_equal_to[0.01]' to 'greater_than_equal_to[0]' to allow 0%
        'rate' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]' // Rate as a percentage (e.g., 18 for 18%)
        // --- CHANGE END ---
    ];
    protected $validationMessages = [
        'name' => [
            'required'   => 'GST Rate Name is required.',
            'min_length' => 'GST Rate Name must be at least 2 characters long.',
            'max_length' => 'GST Rate Name cannot exceed 50 characters.',
            'is_unique'  => 'This GST Rate Name already exists.'
        ],
        'rate' => [
            'required'               => 'GST Rate Percentage is required.',
            'numeric'                => 'GST Rate must be a number.',
            // Updated message to reflect allowing 0
            'greater_than_equal_to'  => 'GST Rate cannot be negative.',
            'less_than_equal_to'     => 'GST Rate cannot be greater than 100%.'
        ]
    ];
    protected $skipValidation     = false;
}
