<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false; // Set to true if you plan to soft delete users

    protected $allowedFields = ['username', 'email', 'password', 'role']; // Fields that can be mass-assigned

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Uncomment if useSoftDeletes is true

    // Validation rules (optional, but highly recommended)
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[255]|is_unique[users.username]',
        'email'    => 'required|max_length[255]|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[8]|max_length[255]', // Password will be hashed *before* saving
        'role'     => 'permit_empty|alpha_numeric_space|max_length[50]',
    ];
    protected $validationMessages = [];
    protected $skipValidation       = false; // Set to true to skip validation on insert/update
    protected $cleanValidationRules = true;

    // Callbacks to hash password before inserting/updating
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    // You can add custom methods here, e.g., for finding a user by username or email
    public function findByCredentials($usernameOrEmail)
    {
        return $this->where('username', $usernameOrEmail)
                    ->orWhere('email', $usernameOrEmail)
                    ->first();
    }
}
