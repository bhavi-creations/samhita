<?php

namespace App\Models;

use CodeIgniter\Model;

class EwayBillModel extends Model
{
    protected $table = 'eway_bills';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'distributor_sales_order_id',
        'eway_bill_no',
        'vehicle_number',
        'generated_at',
        'valid_until',
        'api_response',
        'place_of_dispatch',
        'place_of_delivery',
        'reason_for_transportation',
        'bill_generated_by',
        'transaction_type',
        'driver_name',
        // New fields
        'distance',
        'transport_mode',
        'multiple_veh_info',
        'cewb_no',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $useSoftDeletes = true;
}
