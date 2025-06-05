<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketingPersonModel extends Model
{
    protected $table = 'marketing_persons';
    protected $primaryKey = 'id';
    protected $allowedFields = ['custom_id', 'name', 'phone', 'email', 'address'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

   public function generateCustomId()
{
    $prefix = 'SSS-';
    $datePart = date('ymd');

    // Find max number used regardless of date
    $builder = $this->builder();
    $builder->select('custom_id');
    $builder->orderBy('id', 'DESC');
    $row = $builder->get()->getRow();

    $lastNumber = 0;

    if ($row && isset($row->custom_id)) {
        $parts = explode('-', $row->custom_id);
        if (count($parts) === 3 && is_numeric($parts[2])) {
            $lastNumber = (int) $parts[2];
        }
    }

    $newNumber = $lastNumber + 1;
    $numberPart = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

    return $prefix . $datePart . '-' . $numberPart;
}

}
