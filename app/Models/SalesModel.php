<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table            = 'sales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true; // Added: Common default for primary keys
    protected $returnType       = 'array'; // Added: Ensures data is returned as arrays (or change to 'object')
    protected $useSoftDeletes   = false; // Added: Common default, explicitly states no soft deletes
    protected $protectFields    = true; // Added: Recommended for mass assignment protection

    protected $allowedFields = [
        'product_id',
        'marketing_person_id',
        'quantity_sold',
        'price_per_unit',
        'discount',
        'total_price',
        'amount_received_from_person',
        'balance_from_person',
        'payment_status_from_person',
        'last_remittance_date',
        'date_sold',
        'customer_name',
        'customer_phone',
        'customer_address',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // Added: Specify datetime for timestamp columns
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Uncomment if you enable $useSoftDeletes

    // Add validation rules here if you uncommented them in your previous code
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks are usually not needed unless you have specific logic before/after CRUD operations
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Fetches details for a specific sale, including related product and marketing person names.
     *
     * @param int $saleId The ID of the sale to fetch.
     * @return array|null An array of sale details or null if not found.
     */
    public function getSaleDetails(int $saleId): ?array
    {
        return $this->select('sales.*, products.name as product_name, marketing_persons.name as marketing_person_name')
                    ->join('products', 'products.id = sales.product_id', 'left')
                    ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                    ->find($saleId);
    }
}