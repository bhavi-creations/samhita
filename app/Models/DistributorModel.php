<?php

namespace App\Models;

use CodeIgniter\Model;

class DistributorModel extends Model
{
    protected $table            = 'distributors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'custom_id',
        'agency_name',
        'owner_name',
        'owner_phone',
        'agent_name',
        'agent_phone',
        'agency_gst_number',
        'gmail',
        'agency_address',
        'status',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // For soft deletes if enabled

    // Callbacks
    protected $allowCallbacks = true;
    // This callback will run BEFORE an insert operation
    protected $beforeInsert   = ['generateCustomIdValue'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generates a custom distributor ID using the 'sequences' table.
     * This callback runs automatically before an insert operation.
     */
    protected function generateCustomIdValue(array $data)
    {
        // Only generate custom_id if it's an insert operation and custom_id is not already set
        // The !isset($data['id']) check ensures it's an insert (updates will have an ID)
        if (!isset($data['id']) && (!isset($data['data']['custom_id']) || empty($data['data']['custom_id']))) {

            // Get the database connection
            $db = \Config\Database::connect();
            $builder = $db->table('sequences');

            // Start a transaction to ensure atomic update of sequence number
            $db->transStart();

            // Get the current sequence value with a row lock to prevent race conditions
            // For MySQL, 'FOR UPDATE' locks the selected row
            $sequence = $builder->where('name', 'distributor_custom_id')->get()->getRow();

            $currentValue = 0;

            if ($sequence) {
                $currentValue = $sequence->current_value + 1;
                $builder->where('name', 'distributor_custom_id')->update(['current_value' => $currentValue, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                // This fallback creates the sequence if it doesn't exist, though it should ideally be created by migration.
                $currentValue = 1;
                $builder->insert(['name' => 'distributor_custom_id', 'current_value' => $currentValue, 'updated_at' => date('Y-m-d H:i:s')]);
            }

            $db->transComplete(); // Complete the transaction

            if ($db->transStatus() === false) {
                // Transaction failed, log error and do not generate custom ID
                log_message('error', 'DistributorModel::generateCustomIdValue - Failed to update distributor_custom_id sequence transactionally.');
                // You might want to throw an exception or return an error here to stop the insert
                return $data; // Return original data to potentially cause a database error
            } else {
                // Transaction successful, generate and set the custom ID
                $datePart = date('ymd'); // YYMMDD
                $paddedId = sprintf('%04d', $currentValue); // Pad with leading zeros to 4 digits
                $customId = 'DSSS-' . $datePart . '-' . $paddedId;

                // Add the generated custom_id to the data that will be inserted
                $data['data']['custom_id'] = $customId;
            }
        }

        return $data; // Always return the data array
    }

    /**
     * Define validation rules for distributor data.
     * This method dynamically adjusts unique rules for updates.
     * Note: Since 'UNIQUE' constraints were removed from DB for these fields,
     * application-level validation becomes even more important.
     */
    protected $validationRules = []; // Initialize as empty, will be set by getValidationRules()

    public function getValidationRules(array $data = []): array
    {
        $ownerPhoneRules        = 'required|exact_length[10]|numeric';
        $agencyGstNumberRules   = 'permit_empty|max_length[15]';
        $gmailRules             = 'permit_empty|valid_email|max_length[255]';

        // Adjust 'is_unique' rules based on whether it's an update or insert
        if (isset($data['id']) && !empty($data['id'])) {
            // For updates, allow the current record's value to be non-unique against itself
            $id = (int) $data['id'];
            $ownerPhoneRules      .= '|is_unique[distributors.owner_phone,id,' . $id . ']';
            $agencyGstNumberRules .= '|is_unique[distributors.agency_gst_number,id,' . $id . ']';
            $gmailRules           .= '|is_unique[distributors.gmail,id,' . $id . ']';
        } else {
            // For new inserts, all fields must be unique
            $ownerPhoneRules      .= '|is_unique[distributors.owner_phone]';
            $agencyGstNumberRules .= '|is_unique[distributors.agency_gst_number]';
            $gmailRules           .= '|is_unique[distributors.gmail]';
        }

        // Now define the full $rules array, incorporating the constructed rule strings
        $rules = [
            'agency_name'       => 'required|min_length[3]|max_length[255]',
            'owner_name'        => 'required|min_length[3]|max_length[255]',
            'owner_phone'       => [
                'label'  => 'Owner Phone',
                'rules'  => $ownerPhoneRules,
                'errors' => [
                    'exact_length' => 'The {field} field must be exactly 10 digits long.',
                    'numeric'      => 'The {field} field must contain only numbers.',
                    'is_unique'    => 'The {field} is already registered.'
                ]
            ],
            'agency_address'    => 'required|min_length[5]|max_length[1000]',
            'status'            => 'required|in_list[Active,Inactive,On Hold]',
            'agent_name'        => 'permit_empty|min_length[3]|max_length[255]',
            'agent_phone'       => 'permit_empty|exact_length[10]|numeric',
            'agency_gst_number' => [
                'label'  => 'Agency GST Number',
                'rules'  => $agencyGstNumberRules,
                'errors' => [
                    'is_unique' => 'The {field} is already registered.'
                ]
            ],
            'gmail'             => [
                'label'  => 'Gmail',
                'rules'  => $gmailRules,
                'errors' => [
                    'is_unique' => 'The {field} is already registered.'
                ]
            ],
            'notes'             => 'permit_empty|max_length[1000]',
        ];

        return $rules;
    }

    /**
     * Override the validate method to use our dynamic rules.
     * Correctly uses the Validation service instance and sets model errors.
     */
    public function validate($data): bool
    {
        // If it's an update, ensure 'id' is in the data so 'is_unique' rule can exclude current record
        $rules = $this->getValidationRules($data);

        /** @var \CodeIgniter\Validation\Validation $validation */
        $validation = \Config\Services::validation();

        $validation->setRules($rules);

        $isValid = $validation->run($data);

        if ($isValid === false) {
            // REMOVE the line that caused the error:
            // $this->setValidationErrors($validation->getErrors()); // THIS LINE IS GONE
            log_message('error', 'DistributorModel::validate - Validation FAILED. Errors: ' . json_encode($validation->getErrors())); // Log directly from $validation
        } else {
            log_message('debug', 'DistributorModel::validate - Validation PASSED.');
        }

        return $isValid;
    }
}
