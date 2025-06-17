<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\Validation;

class DistributorModel extends Model
{
    protected $table            = 'distributors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'custom_id', 'agency_name', 'owner_name', 'owner_phone',
        'agent_name', 'agent_phone', 'agency_gst_number', 'gmail',
        'agency_address', 'status', 'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Callbacks
    protected $allowCallbacks = true;
    // Keep this line commented out for this test to isolate the DB connection issue
    protected $beforeInsert   = []; // Temporarily disabled for debugging
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // CRITICAL FIX: Explicitly declare the $errors property to avoid deprecation warning
    protected $errors = []; // Initialize as an empty array

    /**
     * NEW DEBUGGING CONSTRUCTOR
     * This will run when the DistributorModel is first created.
     */
    public function __construct()
    {
        parent::__construct(); // Call the parent Model's constructor first

        // --- DEBUGGING: Check the database connection status immediately ---
        echo '<pre style="background-color: #ffcccc; padding: 10px; border: 1px solid red;">';
        echo 'DEBUG: Database Connection Object Status (inside DistributorModel\'s __construct()):<br>';

        // $this->db->connID holds the raw database connection resource
        if (is_object($this->db->connID)) {
            echo "Connection is an object (OK).<br>";
            echo "Hostname: " . $this->db->getHostname() . "<br>";
            echo "Database: " . $this->db->getDatabase() . "<br>";
            // Attempt a simple query to further test
            try {
                $testQuery = $this->db->query("SELECT 1+1 AS test");
                if ($testQuery) {
                    echo "Simple test query successful (result: " . $testQuery->getRow()->test . ")!<br>";
                } else {
                    echo "Simple test query FAILED.<br>";
                }
            } catch (\Exception $e) {
                echo "Exception during test query: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "Connection is NOT an object.<br>";
            echo "Type: " . gettype($this->db->connID) . "<br>";
            // If it's boolean false, this is where the connection itself failed
            echo "MySQLi Connect Error Message: " . mysqli_connect_error() . "<br>";
            echo "MySQLi Connect Error Number: " . mysqli_connect_errno() . "<br>";
            echo "This indicates a fundamental database connection issue (credentials, server not running, etc.).<br>";
        }
        echo '</pre>';
        // If the above reports issues, you might uncomment exit; to stop further execution and see the debug output clearly
        // exit;
    }


    /**
     * Generates a custom distributor ID before inserting a new record.
     * This callback is only for INSERT operations.
     * (Currently disabled by `protected $beforeInsert = [];` above)
     */
    protected function generateCustomIdValue(array $data)
    {
        // This method's code remains as is, but it won't be called for now.
        if (!isset($data['id']) && (!isset($data['data']['custom_id']) || empty($data['data']['custom_id']))) {
            $builder = $this->db->table('sequences');

            $this->db->transStart();

            $sequence = $builder->where('name', 'distributor_custom_id')->get()->getRow();

            if ($sequence) {
                $currentValue = $sequence->current_value + 1;
                $builder->where('name', 'distributor_custom_id')->update(['current_value' => $currentValue, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                $currentValue = 1;
                $builder->insert(['name' => 'distributor_custom_id', 'current_value' => $currentValue, 'updated_at' => date('Y-m-d H:i:s')]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'DistributorModel::generateCustomIdValue - Failed to update distributor_custom_id sequence transactionally.');
            } else {
                $datePart = date('ymd');
                $paddedId = sprintf('%04d', $currentValue);
                $customId = 'DSSS-' . $datePart . '-' . $paddedId;
                $data['data']['custom_id'] = $customId;
            }
        }
        return $data;
    }

    public function getValidationRules(array $data = []): array
    {
        // Keep the 'max_length[1]' on agency_name to force a validation error for testing purposes.
        $ownerPhoneRules       = 'required|exact_length[10]|numeric';
        $agencyGstNumberRules  = 'permit_empty|max_length[15]';
        $gmailRules            = 'permit_empty|valid_email|max_length[255]';

        $rules = [
            'agency_name'       => 'required|min_length[3]|max_length[255]|max_length[1]', // Keep max_length[1] here
            'owner_name'        => 'required|min_length[3]|max_length[255]',
            'owner_phone'       => [
                'label'  => 'Owner Phone',
                'rules'  => $ownerPhoneRules,
                'errors' => [
                    'exact_length' => 'The {field} field must be exactly 10 digits long.',
                    'numeric'      => 'The {field} field must contain only numbers.'
                ]
            ],
            'agency_address'    => 'required|min_length[5]|max_length[1000]',
            'status'            => 'required|in_list[Active,Inactive,On Hold]',
            'agent_name'        => 'permit_empty|min_length[3]|max_length[255]',
            'agent_phone'       => 'permit_empty|exact_length[10]|numeric',
            'agency_gst_number' => $agencyGstNumberRules,
            'gmail'             => $gmailRules,
            'notes'             => 'permit_empty|max_length[1000]',
        ];

        return $rules;
    }

    public function validate($row): bool
    {
        $rules = $this->getValidationRules($row);
        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        $isValid = $validation->run($row);

        if ($isValid === false) {
            // Keep these debugging lines for now.
            echo '<pre>DEBUG: Validation Errors (inside model\'s validate method):<br>';
            var_dump($validation->getErrors());
            echo '</pre>';
            $this->errors = $validation->getErrors();
        }

        return $isValid;
    }
}