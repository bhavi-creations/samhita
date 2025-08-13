<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorSalesOrderModel extends Model
{
    protected $table               = 'distributor_sales_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields      = true;
    protected $allowedFields = [
        'distributor_id',
        'pricing_tier',
        'marketing_person_id',
        'overall_gst_rate_ids',
        'overall_gst_percentage_at_sale',
        'invoice_number',
        'invoice_date',
        'total_amount_before_gst',
        'total_gst_amount',
        'final_total_amount',
        'discount_amount',
        'amount_paid',
        'due_amount',
        'status',
        'notes',
        // These fields were added to match the database table
        'sub_total',
        'gst_amount',
        'overall_discount',
        'total_amount',
        'initial_payment_amount',
        'balance_amount',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat      = 'datetime';
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    // The `deleted_at` field is not used since useSoftDeletes is false.
    // protected $deletedField   = 'deleted_at';

    // Validation
    protected $validationRules = [
        'distributor_id'                         => 'required|integer|is_not_unique[distributors.id]',
        'pricing_tier'                            => 'required|in_list[dealer,farmer]',
        // Corrected Code
 
        'marketing_person_id'                  => 'required|integer|is_not_unique[marketing_persons.id]',
        // The rule for this field has been changed to be handled by callbacks
        'invoice_number'                         => 'required|max_length[50]|is_unique[distributor_sales_orders.invoice_number,id,{id}]',
        'invoice_date'                            => 'required|valid_date',
        'total_amount_before_gst'            => 'required|numeric',
        'total_gst_amount'                      => 'required|numeric',
        'final_total_amount'                   => 'required|numeric',
        'discount_amount'                        => 'permit_empty|numeric|greater_than_equal_to[0]',
        'amount_paid'                              => 'required|numeric|greater_than_equal_to[0]',
        'due_amount'                               => 'required|numeric',
        'status'                                     => 'required|in_list[Pending,Partially Paid,Paid,Cancelled]',
        'notes'                                       => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'distributor_id' => [
            'required'      => 'The distributor is required.',
            'is_not_unique' => 'The selected distributor does not exist.',
        ],
        'pricing_tier' => [
            'required'      => 'The pricing tier is required.',
            'in_list'       => 'The selected pricing tier is not valid.',
        ],
        'marketing_person_id' => [
            'required'      => 'The marketing person is required.',
            'is_not_unique' => 'The selected marketing person does not exist.',
        ],
        'overall_gst_rate_ids' => [
            'required'      => 'At least one GST rate must be selected.',
        ],
        'invoice_number' => [
            'required'      => 'An invoice number is required.',
            'is_unique'    => 'This invoice number is already in use.',
        ],
        'invoice_date' => [
            'required'      => 'The invoice date is required.',
        ],
    ];

    // Callbacks to handle array serialization for 'overall_gst_rate_ids'
    protected $allowCallbacks = true;
    protected $beforeInsert    = ['handleGstRates'];
    protected $beforeUpdate    = ['handleGstRates'];
    protected $afterFind         = ['decodeGstRates'];

    /**
     * Callback method to serialize the GST rate IDs array into a JSON string.
     * * @param array $data
     * @return array
     */
    protected function handleGstRates(array $data): array
    {
        if (isset($data['data']['overall_gst_rate_ids']) && is_array($data['data']['overall_gst_rate_ids'])) {
            $data['data']['overall_gst_rate_ids'] = json_encode($data['data']['overall_gst_rate_ids']);
        } else if (isset($data['data']['overall_gst_rate_ids']) && !is_string($data['data']['overall_gst_rate_ids'])) {
            // If the field is not an array or a string, set it to null.
            $data['data']['overall_gst_rate_ids'] = null;
        }

        return $data;
    }

    /**
     * Callback method to decode the JSON string of GST rate IDs back into an array.
     * * @param array $data
     * @return array
     */
    protected function decodeGstRates(array $data): array
    {
        if (isset($data['data']['overall_gst_rate_ids']) && is_string($data['data']['overall_gst_rate_ids'])) {
            $decoded = json_decode($data['data']['overall_gst_rate_ids'], true);
            $data['data']['overall_gst_rate_ids'] = is_array($decoded) ? $decoded : [];
        }

        return $data;
    }
}
