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
        'updated_at', // Added for automatic timestamp management
        // 'deleted_at' is managed automatically by useSoftDeletes
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at'; // Set to 'updated_at'
    protected $deletedField  = 'deleted_at'; // Set to 'deleted_at'

    // Enable soft deletes
    protected $useSoftDeletes = true;
}
