<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanySettingModel extends Model
{
    protected $table            = 'company_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_name', 'setting_value'];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'setting_name'  => 'required|max_length[100]|is_unique[company_settings.setting_name,id,{id}]',
        'setting_value' => 'permit_empty|max_length[255]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

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

    /**
     * Get a specific setting value by name.
     *
     * @param string $settingName
     * @return string|null
     */
    public function getSetting(string $settingName): ?string
    {
        $setting = $this->where('setting_name', $settingName)->first();
        return $setting['setting_value'] ?? null;
    }

    /**
     * Update or insert a setting value.
     *
     * @param string $settingName
     * @param string|null $settingValue
     * @return bool
     */
    public function setSetting(string $settingName, ?string $settingValue): bool
    {
        $setting = $this->where('setting_name', $settingName)->first();

        if ($setting) {
            return $this->update($setting['id'], ['setting_value' => $settingValue]);
        } else {
            return $this->insert(['setting_name' => $settingName, 'setting_value' => $settingValue]);
        }
    }
}