<?php

namespace App\Controllers;

use App\Models\MarketingPersonModel;
use CodeIgniter\Files\File;
use Config\Services;

// Add these for export functionality
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;


class MarketingPersons extends BaseController
{
    protected $personModel;

    public function __construct()
    {
        $this->personModel = new MarketingPersonModel();
    }

    public function index()
    {
        $data['persons'] = $this->personModel->findAll();
        return view('marketing_persons/index', $data);
    }

    public function create()
    {
        return view('marketing_persons/create');
    }

    // Your existing store method (now working, without debug points)
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'required|regex_match[/^[0-9]{10}$/]',
            'secondary_phone_num' => 'permit_empty|regex_match[/^[0-9]{10}$/]',
            'email' => 'permit_empty|valid_email|max_length[255]|is_unique[marketing_persons.email]',
            'address' => 'permit_empty|max_length[1000]',
            'aadhar_card_image' => 'permit_empty|uploaded[aadhar_card_image]|max_size[aadhar_card_image,2048]|ext_in[aadhar_card_image,jpg,jpeg,png,pdf]',
            'pan_card_image' => 'permit_empty|uploaded[pan_card_image]|max_size[pan_card_image,2048]|ext_in[pan_card_image,jpg,jpeg,png,pdf]',
            'driving_license_image' => 'permit_empty|uploaded[driving_license_image]|max_size[driving_license_image,2048]|ext_in[driving_license_image,jpg,jpeg,png,pdf]',
            'address_proof_image' => 'permit_empty|uploaded[address_proof_image]|max_size[address_proof_image,2048]|ext_in[address_proof_image,jpg,jpeg,png,pdf]',
        ];

        $errors = [
            'phone' => ['regex_match' => 'The {field} field must be a 10-digit number.'],
            'secondary_phone_num' => ['regex_match' => 'The {field} field must be a 10-digit number.'],
            'email' => ['is_unique' => 'This email is already registered.'],
            'aadhar_card_image' => ['uploaded' => 'Please select an Aadhar Card image.', 'max_size' => 'Aadhar Card image file is too large (max 2MB).', 'ext_in' => 'Aadhar Card image must be JPG, JPEG, PNG, or PDF.'],
            'pan_card_image' => ['uploaded' => 'Please select a PAN Card image.', 'max_size' => 'PAN Card image file is too large (max 2MB).', 'ext_in' => 'PAN Card image must be JPG, JPEG, PNG, or PDF.'],
            'driving_license_image' => ['uploaded' => 'Please select a Driving License image.', 'max_size' => 'Driving License image file is too large (max 2MB).', 'ext_in' => 'Driving License image must be JPG, JPEG, PNG, or PDF.'],
            'address_proof_image' => ['uploaded' => 'Please select an Address Proof image.', 'max_size' => 'Address Proof image file is too large (max 2MB).', 'ext_in' => 'Address Proof image must be JPG, JPEG, PNG, or PDF.'],
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customId = $this->personModel->generateCustomId();
        $data = [
            'custom_id' => $customId,
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'secondary_phone_num' => $this->request->getPost('secondary_phone_num'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
        ];

        $uploadFields = [
            'aadhar_card_image',
            'pan_card_image',
            'driving_license_image',
            'address_proof_image'
        ];
        $uploadPath = ROOTPATH . 'public/uploads/marketing_persons/';

        foreach ($uploadFields as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                $data[$field] = $newName;
            }
        }

        $this->personModel->save($data);

        return redirect()->to('/marketing-persons')->with('success', 'Marketing person added successfully!');
    }


    public function edit($id)
    {
        $data['person'] = $this->personModel->find($id);
        if (empty($data['person'])) {
            return redirect()->to('/marketing-persons')->with('error', 'Marketing person not found.');
        }
        return view('marketing_persons/edit', $data);
    }

    // Your existing update method
    public function update($id)
    {
        $person = $this->personModel->find($id);
        if (empty($person)) {
            return redirect()->to('/marketing-persons')->with('error', 'Marketing person not found.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'required|regex_match[/^[0-9]{10}$/]',
            'secondary_phone_num' => 'permit_empty|regex_match[/^[0-9]{10}$/]',
            'email' => 'permit_empty|valid_email|max_length[255]|is_unique[marketing_persons.email,id,' . $id . ']',
            'address' => 'permit_empty|max_length[1000]',
            'aadhar_card_image' => 'permit_empty|uploaded[aadhar_card_image]|max_size[aadhar_card_image,2048]|ext_in[aadhar_card_image,jpg,jpeg,png,pdf]',
            'pan_card_image' => 'permit_empty|uploaded[pan_card_image]|max_size[pan_card_image,2048]|ext_in[pan_card_image,jpg,jpeg,png,pdf]',
            'driving_license_image' => 'permit_empty|uploaded[driving_license_image]|max_size[driving_license_image,2048]|ext_in[driving_license_image,jpg,jpeg,png,pdf]',
            'address_proof_image' => 'permit_empty|uploaded[address_proof_image]|max_size[address_proof_image,2048]|ext_in[address_proof_image,jpg,jpeg,png,pdf]',
        ];

        $errors = [
            'phone' => ['regex_match' => 'The {field} field must be a 10-digit number.'],
            'secondary_phone_num' => ['regex_match' => 'The {field} field must be a 10-digit number.'],
            'email' => ['is_unique' => 'This email is already registered.'],
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'secondary_phone_num' => $this->request->getPost('secondary_phone_num'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
        ];

        $uploadFields = [
            'aadhar_card_image',
            'pan_card_image',
            'driving_license_image',
            'address_proof_image'
        ];
        $uploadPath = ROOTPATH . 'public/uploads/marketing_persons/';

        foreach ($uploadFields as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Delete old file if it exists
                if (!empty($person[$field]) && file_exists($uploadPath . $person[$field])) {
                    unlink($uploadPath . $person[$field]);
                }
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                $data[$field] = $newName;
            } else {
                // If no new file uploaded, keep the old one
                if (!isset($data[$field])) { // Only assign if it's not already in $data (e.g. from an old() value)
                    $data[$field] = $person[$field];
                }
            }
        }

        $this->personModel->update($id, $data);

        return redirect()->to('/marketing-persons')->with('success', 'Marketing person updated successfully!');
    }

    // Your existing delete method
    public function delete($id)
    {
        $person = $this->personModel->find($id);
        if (empty($person)) {
            return redirect()->to('/marketing-persons')->with('error', 'Marketing person not found.');
        }

        $uploadPath = ROOTPATH . 'public/uploads/marketing_persons/';
        $imageFields = [
            'aadhar_card_image',
            'pan_card_image',
            'driving_license_image',
            'address_proof_image'
        ];

        // Delete associated image files
        foreach ($imageFields as $field) {
            if (!empty($person[$field]) && file_exists($uploadPath . $person[$field])) {
                unlink($uploadPath . $person[$field]);
            }
        }

        $this->personModel->delete($id);
        return redirect()->to('/marketing-persons')->with('success', 'Marketing person deleted successfully!');
    }

    // New: Method to display a single marketing person's details
    public function view($id)
    {
        $data['person'] = $this->personModel->find($id);
        if (empty($data['person'])) {
            return redirect()->to('/marketing-persons')->with('error', 'Marketing person not found.');
        }
        return view('marketing_persons/view', $data);
    }

    // New: Export All Marketing Persons to Excel
    public function exportAllExcel()
    {
        $persons = $this->personModel->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('All Marketing Persons');

        // Set headers
        $headers = [
            'S.No.',
            'Custom ID',
            'Name',
            'Primary Phone',
            'Secondary Phone',
            'Email',
            'Address',
            'Aadhar Image',
            'PAN Image',
            'Driving License Image',
            'Address Proof Image',
            'Created At',
            'Updated At'
        ];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Add data
        $row = 2;
        foreach ($persons as $index => $person) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $person['custom_id']);
            $sheet->setCellValue('C' . $row, $person['name']);
            $sheet->setCellValue('D' . $row, $person['phone']);
            $sheet->setCellValue('E' . $row, $person['secondary_phone_num']);
            $sheet->setCellValue('F' . $row, $person['email']);
            $sheet->setCellValue('G' . $row, $person['address']);
            $sheet->setCellValue('H' . $row, $person['aadhar_card_image'] ? 'Uploaded' : 'N/A');
            $sheet->setCellValue('I' . $row, $person['pan_card_image'] ? 'Uploaded' : 'N/A');
            $sheet->setCellValue('J' . $row, $person['driving_license_image'] ? 'Uploaded' : 'N/A');
            $sheet->setCellValue('K' . $row, $person['address_proof_image'] ? 'Uploaded' : 'N/A');
            $sheet->setCellValue('L' . $row, $person['created_at']);
            $sheet->setCellValue('M' . $row, $person['updated_at']);
            $row++;
        }

        // Auto-size columns for better readability
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Marketing_Persons_All_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // New: Export a Single Marketing Person to Excel
    public function exportExcel($id)
    {
        $person = $this->personModel->find($id);

        if (empty($person)) {
            return redirect()->to('/marketing-persons')->with('error', 'Marketing person not found for export.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Marketing Person Details');

        // Data for the specific person
        $data = [
            ['Field', 'Value'],
            ['Custom ID', $person['custom_id']],
            ['Name', $person['name']],
            ['Primary Phone', $person['phone']],
            ['Secondary Phone', $person['secondary_phone_num']],
            ['Email', $person['email']],
            ['Address', $person['address']],
            ['Aadhar Card Image', $person['aadhar_card_image'] ? 'Yes (' . $person['aadhar_card_image'] . ')' : 'No'],
            ['PAN Card Image', $person['pan_card_image'] ? 'Yes (' . $person['pan_card_image'] . ')' : 'No'],
            ['Driving License Image', $person['driving_license_image'] ? 'Yes (' . $person['driving_license_image'] . ')' : 'No'],
            ['Address Proof Image', $person['address_proof_image'] ? 'Yes (' . $person['address_proof_image'] . ')' : 'No'],
            ['Created At', $person['created_at']],
            ['Updated At', $person['updated_at']],
        ];
        $sheet->fromArray($data, NULL, 'A1');

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Marketing_Person_' . $person['custom_id'] . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // In app/Controllers/MarketingPersons.php

    public function exportAllPdf()
    {
        $persons = $this->personModel->findAll();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // --- ADD THIS DATA TO BE PASSED TO THE TEMPLATE ---
        $data = [
            'persons' => $persons,
            'upload_base_url' => base_url('public/uploads/marketing_persons') // Pass the base URL for images
        ];

        // --- UPDATE THE VIEW CALL TO USE $data ---
        $html = view('marketing_persons/all_persons_template', $data);
        $dompdf->loadHtml($html);

        // Keep landscape for more space
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'Marketing_Persons_All_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => 1)); // 1 = download, 0 = preview
        exit;
    }

    // New: Export a Single Marketing Person to PDF
    // In app/Controllers/MarketingPersons.php

    public function exportPdf($id)
    {
        $person = $this->personModel->find($id);

        if (empty($person)) {
            return redirect()->to('/marketing-persons')->with('error', 'Marketing person not found for export.');
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $data = ['person' => $person, 'upload_base_url' => base_url('public/uploads/marketing_persons')];

        // CHANGE THIS LINE: Remove '/pdf'
        $html = view('marketing_persons/single_person_template', $data);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Marketing_Person_' . $person['custom_id'] . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => 1));
        exit;
    }
}
