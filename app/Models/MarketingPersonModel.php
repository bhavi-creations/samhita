<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketingPersonModel extends Model
{
    protected $table = 'marketing_persons';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'custom_id',
        'name',
        'phone',
        'secondary_phone_num', // <--- ADD THIS
        'email',
        'address',
        'aadhar_card_image',    // <--- ADD THIS
        'pan_card_image',       // <--- ADD THIS
        'driving_license_image',// <--- ADD THIS
        'address_proof_image'   // <--- ADD THIS
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at'; // <--- Ensure this is explicitly set for auto-update to work

    public function generateCustomId()
    {
        $prefix = 'SSS-';
        $datePart = date('ymd');

        // Find max number used regardless of date
        $builder = $this->builder();
        $builder->select('custom_id');
        // Modified to order by id for better reliability if custom_id has inconsistent numeric part
        $builder->orderBy('id', 'DESC'); 
        $row = $builder->get()->getRow();

        $lastNumber = 0;

        if ($row && isset($row->custom_id)) {
            $parts = explode('-', $row->custom_id);
            // Check if the custom_id matches the expected format SSS-YYMMDD-NNNN
            if (count($parts) === 3 && is_numeric($parts[2])) {
                $lastNumber = (int) $parts[2];
            }
        }

        $newNumber = $lastNumber + 1;
        $numberPart = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return $prefix . $datePart . '-' . $numberPart;
    }
}