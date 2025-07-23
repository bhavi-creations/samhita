<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanySettingModel;
use CodeIgniter\Files\File;

class CompanySettingsController extends BaseController
{
    protected $companySettingModel;
    protected $helpers = ['form', 'url']; // Load form and url helpers

    public function __construct()
    {
        $this->companySettingModel = new CompanySettingModel();
    }


    public function index()
    {
        // Fetch only the filenames from the database
        $company_logo_filename = $this->companySettingModel->getSetting('company_logo');
        $company_stamp_filename = $this->companySettingModel->getSetting('company_stamp');
        $company_signature_filename = $this->companySettingModel->getSetting('company_signature');

        $data = [
            'title' => 'Company Image Settings',
            // Construct the full public URL for the images
            'company_logo_path' => $company_logo_filename ? base_url('public/uploads/company_images/' . $company_logo_filename) : null,
            'company_stamp_path' => $company_stamp_filename ? base_url('public/uploads/company_images/' . $company_stamp_filename) : null,
            'company_signature_path' => $company_signature_filename ? base_url('public/uploads/company_images/' . $company_signature_filename) : null,
        ];

        return view('company_settings/index', $data);
    }



    public function uploadImage()
    {
        $imageType = $this->request->getPost('image_type');

        if (!in_array($imageType, ['company_logo', 'company_stamp', 'company_signature'])) {
            return redirect()->back()->with('error', 'Invalid image type specified.');
        }

        $validationRule = [
            'image_file' => [
                'label' => ucfirst(str_replace('_', ' ', $imageType)) . ' Image',
                'rules' => 'uploaded[image_file]|max_size[image_file,1024]|is_image[image_file]|mime_in[image_file,image/jpg,image/jpeg,image/png,image/webp]',
                'errors' => [
                    'uploaded' => 'Please select an image file to upload.',
                    'max_size' => 'The image file is too large. Max size is 1MB.',
                    'is_image' => 'The uploaded file is not a valid image.',
                    'mime_in'  => 'Only JPG, JPEG, PNG, and WEBP images are allowed.',
                ],
            ],
        ];

        if (!$this->validate($validationRule)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $img = $this->request->getFile('image_file');

        if ($img->isValid() && !$img->hasMoved()) {
            $newName = $imageType . '_' . $img->getRandomName();

            // **CORRECTED PATH using the proven ROOTPATH method**
            $uploadPath = ROOTPATH . 'public/uploads/company_images/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $img->move($uploadPath, $newName);

            // Save only the filename to the database
            $this->companySettingModel->setSetting($imageType, $newName);

            return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $imageType)) . ' uploaded successfully.');
        } else {
            return redirect()->back()->with('error', 'File upload failed. ' . $img->getErrorString());
        }
    }







    public function deleteImage($imageType)
    {
        if (!in_array($imageType, ['company_logo', 'company_stamp', 'company_signature'])) {
            return redirect()->back()->with('error', 'Invalid image type specified for deletion.');
        }

        $currentPath = $this->companySettingModel->getSetting($imageType);

        if ($currentPath) {
            $fullPath = WRITEPATH . $currentPath;
            if (file_exists($fullPath)) {
                unlink($fullPath); // Delete the file from the server
            }
            $this->companySettingModel->setSetting($imageType, null); // Clear path in DB
            return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $imageType)) . ' deleted successfully.');
        }

        return redirect()->back()->with('error', 'No ' . ucfirst(str_replace('_', ' ', $imageType)) . ' found to delete.');
    }
}
