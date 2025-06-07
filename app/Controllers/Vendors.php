<?php

namespace App\Controllers;

use App\Models\VendorModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Vendors extends Controller
{
    protected $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $data['vendors'] = $this->vendorModel->findAll();
        return view('vendors/index', $data);
    }

    public function create()
    {
        return view('vendors/create');
    }

    public function store()
    {
        $data = [
            'agency_name'     => $this->request->getPost('agency_name'),
            'name'            => $this->request->getPost('name'),
            'owner_phone'     => $this->request->getPost('owner_phone'),
            'contact_person'  => $this->request->getPost('contact_person'),
            'contact_phone'   => $this->request->getPost('contact_phone'),
            'email'           => $this->request->getPost('email'),
            'address'         => $this->request->getPost('address'),
        ];

        $this->vendorModel->insert($data);

        return redirect()->to('/vendors')->with('success', 'Vendor added successfully');
    }


    public function edit($id)
    {
        $data['vendor'] = $this->vendorModel->find($id);

        if (!$data['vendor']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Vendor not found');
        }

        return view('vendors/edit', $data);
    }

    public function update($id)
    {
        $data = [
            'agency_name'     => $this->request->getPost('agency_name'),
            'name'            => $this->request->getPost('name'),
            'owner_phone'     => $this->request->getPost('owner_phone'),
            'contact_person'  => $this->request->getPost('contact_person'),
            'contact_phone'   => $this->request->getPost('contact_phone'),
            'email'           => $this->request->getPost('email'),
            'address'         => $this->request->getPost('address'),
        ];

        $this->vendorModel->update($id, $data);

        return redirect()->to('/vendors')->with('success', 'Vendor updated successfully');
    }


    public function delete($id)
    {
        $this->vendorModel->delete($id);
        return redirect()->to('/vendors')->with('success', 'Vendor deleted successfully');
    }


    public function vendorReport()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_in');

        $builder->select('
        stock_in.*,
        products.name as product_name,
        units.name as unit_name,
        vendors.agency_name as vendor_agency_name,
        vendors.name as vendor_name
    ');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id', 'left');

        // Filters
        $vendorId = $this->request->getGet('vendor_id');
        $productId = $this->request->getGet('product_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        if ($vendorId) {
            $builder->where('stock_in.vendor_id', $vendorId);
        }
        if ($productId) {
            $builder->where('stock_in.product_id', $productId);
        }
        if ($startDate) {
            $builder->where('stock_in.date_received >=', $startDate);
        }
        if ($endDate) {
            $builder->where('stock_in.date_received <=', $endDate);
        }

        $builder->orderBy('stock_in.date_received', 'DESC');
        $query = $builder->get();

        $data['stock_entries'] = $query->getResultArray();
        $data['vendors'] = $this->vendorModel->findAll(); // For vendor dropdown

        // Load product model and fetch products
        $productModel = new \App\Models\ProductModel();
        $data['products'] = $productModel->findAll(); // For product dropdown

        // Pass selected filters to view
        $data['selected_vendor_id'] = $vendorId;
        $data['selected_product_id'] = $productId;
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;

        return view('vendors/vendorReport', $data);
    }

    public function vendorReportExport()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_in');
        $builder->select('
        stock_in.*,
        products.name as product_name,
        units.name as unit_name,
        vendors.agency_name as vendor_agency_name,
        vendors.name as vendor_name
    ');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id', 'left');

        // Apply filters
        $vendorId = $this->request->getGet('vendor_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        if ($vendorId) {
            $builder->where('stock_in.vendor_id', $vendorId);
        }
        if ($startDate) {
            $builder->where('stock_in.date_received >=', $startDate);
        }
        if ($endDate) {
            $builder->where('stock_in.date_received <=', $endDate);
        }

        $builder->orderBy('stock_in.date_received', 'DESC');
        $query = $builder->get();
        $stockEntries = $query->getResultArray();

        // Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Header
        $sheet->setCellValue('A1', 'S.No');
        $sheet->setCellValue('B1', 'Vendor');
        $sheet->setCellValue('C1', 'Product');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->setCellValue('E1', 'Unit');
        $sheet->setCellValue('F1', 'Purchase Price');
        $sheet->setCellValue('G1', 'Selling Price');
        $sheet->setCellValue('H1', 'Date Received');
        $sheet->setCellValue('I1', 'Notes');

        // Populate Data
        $row = 2;
        $i = 1;
        foreach ($stockEntries as $entry) {
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $entry['vendor_agency_name'] . ' (' . $entry['vendor_name'] . ')');
            $sheet->setCellValue('C' . $row, $entry['product_name']);
            $sheet->setCellValue('D' . $row, $entry['quantity']);
            $sheet->setCellValue('E' . $row, $entry['unit_name']);
            $sheet->setCellValue('F' . $row, $entry['purchase_price']);
            $sheet->setCellValue('G' . $row, $entry['selling_price']);
            $sheet->setCellValue('H' . $row, $entry['date_received']);
            $sheet->setCellValue('I' . $row, $entry['notes']);
            $row++;
        }

        // Export to Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'vendor_supply_report_' . date('YmdHis') . '.xlsx';

        // Redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save('php://output');
        exit;
    }




    public function vendorReportPDF()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_in');

        $builder->select('
        stock_in.*,
        products.name as product_name,
        units.name as unit_name,
        vendors.agency_name as vendor_agency_name,
        vendors.name as vendor_name
    ');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id', 'left');

        // Apply filters
        $vendorId = $this->request->getGet('vendor_id');
        $productId = $this->request->getGet('product_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        if ($vendorId) {
            $builder->where('stock_in.vendor_id', $vendorId);
        }
        if ($productId) {
            $builder->where('stock_in.product_id', $productId);
        }
        if ($startDate) {
            $builder->where('stock_in.date_received >=', $startDate);
        }
        if ($endDate) {
            $builder->where('stock_in.date_received <=', $endDate);
        }

        $builder->orderBy('stock_in.date_received', 'DESC');
        $stockEntries = $builder->get()->getResultArray();

        // Pass data to view
        $html = view('vendors/vendorReport_pdf', ['stock_entries' => $stockEntries]);

        // Generate PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Vendor_Report_' . date('Ymd_His') . '.pdf', ['Attachment' => false]);
    }
}
