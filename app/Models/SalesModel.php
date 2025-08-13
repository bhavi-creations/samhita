<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table          = 'sales';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;

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
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    // These rules ensure that the data being saved is valid and complete.
    protected $validationRules = [
        'product_id'                 => 'required|integer|is_not_unique[selling_products.id]',
        'marketing_person_id'        => 'required|integer|is_not_unique[marketing_persons.id]',
        'quantity_sold'              => 'required|integer|greater_than[0]',
        'price_per_unit'             => 'required|numeric|greater_than_equal_to[0]',
        'discount'                   => 'numeric|greater_than_equal_to[0]',
        'total_price'                => 'numeric|greater_than_equal_to[0]',
        'amount_received_from_person'=> 'numeric|greater_than_equal_to[0]',
        'balance_from_person'        => 'numeric',
        'payment_status_from_person' => 'in_list[Pending,Paid,Partially Paid]',
        'last_remittance_date'       => 'permit_empty|valid_date',
        'date_sold'                  => 'required|valid_date',
        'customer_name'              => 'permit_empty|max_length[255]',
        'customer_phone'             => 'permit_empty|max_length[20]',
        'customer_address'           => 'permit_empty',
    ];

    protected $validationMessages = [
        'product_id' => [
            'is_not_unique' => 'The selected product does not exist.',
        ],
        'marketing_person_id' => [
            'is_not_unique' => 'The selected marketing person does not exist.',
        ],
        'quantity_sold' => [
            'greater_than' => 'The quantity sold must be a positive number.',
        ],
        'payment_status_from_person' => [
            'in_list' => 'The payment status is not a valid option.',
        ],
    ];

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
