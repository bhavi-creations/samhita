<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table            = 'vendors';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name',
        'agency_name',
        'owner_phone',
        'contact_person',
        'contact_phone',
        'email',
        'address',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // Leave empty if not using updated_at
}
