<?php namespace App\Models;

use CodeIgniter\Model;

class SellingProductModel extends Model
{
    protected $table      = 'selling_products';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // --- CHANGE START ---
    // Updated 'selling_price' to 'dealer_price'
    protected $allowedFields = ['name', 'description', 'dealer_price', 'farmer_price', 'current_stock', 'unit_id'];
    // --- CHANGE END ---

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'name'          => 'required|min_length[3]|max_length[255]|is_unique[selling_products.name,id,{id}]',
        'description'   => 'permit_empty|string|max_length[1000]',
        // --- CHANGE START ---
        // Updated 'selling_price' to 'dealer_price' in validation rules
        'dealer_price'  => 'required|numeric|greater_than_equal_to[0]|decimal[10,2]',
        // --- CHANGE END ---
        'farmer_price'  => 'required|numeric|greater_than_equal_to[0]|decimal[10,2]',
        'current_stock' => 'required|integer|greater_than_equal_to[0]',
        'unit_id'       => 'required|integer|is_not_unique[units.id]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Product name is required.',
            'min_length' => 'Product name must be at least 3 characters long.',
            'max_length' => 'Product name cannot exceed 255 characters.',
            'is_unique'  => 'This product name already exists.',
        ],
        // --- CHANGE START ---
        // Updated 'selling_price' to 'dealer_price' in validation messages
        'dealer_price' => [
            'required'              => 'Dealer price is required.',
            'numeric'               => 'Dealer price must be a number.',
            'greater_than_equal_to' => 'Dealer price cannot be negative.',
            'decimal'               => 'Dealer price must be a valid decimal number.',
        ],
        // --- CHANGE END ---
        'farmer_price' => [
            'required'              => 'Farmer price is required.',
            'numeric'               => 'Farmer price must be a number.',
            'greater_than_equal_to' => 'Farmer price cannot be negative.',
            'decimal'               => 'Farmer price must be a valid decimal number.',
        ],
        'current_stock' => [
            'required'              => 'Current stock is required.',
            'integer'               => 'Current stock must be a whole number.',
            'greater_than_equal_to' => 'Current stock cannot be negative.',
        ],
        'unit_id' => [
            'required'      => 'Unit is required.',
            'integer'       => 'Invalid unit ID.',
            'is_not_unique' => 'Selected unit does not exist.',
        ],
    ];
    protected $skipValidation     = false;
}
