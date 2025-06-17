<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorModel extends Model
{
    protected $table            = 'distributors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Or 'object' if you prefer objects
    protected $useSoftDeletes   = false; // Assuming no soft deletes for now

    protected $allowedFields = [
        'custom_id',
        'agency_name',
        'owner_name',
        'owner_phone',
        'agent_name',
        'agent_phone',
        'agency_gst_number',
        'agency_address',
        'gmail',
        'status',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true; // Enable created_at and updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'custom_id'          => 'required|max_length[50]|is_unique[distributors.custom_id]',
        'agency_name'        => 'required|max_length[255]',
        'owner_name'         => 'required|max_length[255]',
        'owner_phone'        => 'required|max_length[20]|numeric|is_unique[distributors.owner_phone]',
        'agency_address'     => 'required|max_length[1000]', // Adjust max_length as needed for address
        'agency_gst_number'  => 'permit_empty|max_length[15]|is_unique[distributors.agency_gst_number]',
        'gmail'              => 'permit_empty|valid_email|max_length[255]|is_unique[distributors.gmail]',
        'status'             => 'required|in_list[Active,Inactive,On Hold]',
        'agent_name'         => 'permit_empty|max_length[255]',
        'agent_phone'        => 'permit_empty|max_length[20]|numeric',
        'notes'              => 'permit_empty|max_length[1000]', // Adjust max_length as needed
    ];

    protected $validationMessages = [
        'custom_id' => [
            'required'   => 'The custom ID is required.',
            'is_unique'  => 'This custom ID is already taken. Please use a different one.',
        ],
        'agency_name' => [
            'required' => 'The agency name is required.',
        ],
        'owner_name' => [
            'required' => 'The owner name is required.',
        ],
        'owner_phone' => [
            'required'  => 'The owner phone number is required.',
            'numeric'   => 'The owner phone number must contain only digits.',
            'is_unique' => 'This owner phone number is already registered.',
        ],
        'agency_address' => [
            'required' => 'The agency address is required.',
        ],
        'agency_gst_number' => [
            'is_unique' => 'This GST number is already registered.',
        ],
        'gmail' => [
            'valid_email' => 'Please enter a valid email address.',
            'is_unique'   => 'This email address is already registered.',
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list'  => 'Invalid status provided.',
        ],
    ];

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
}