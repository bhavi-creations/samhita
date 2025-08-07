<?php

namespace App\Controllers;

use App\Models\VendorModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\StockInModel;
use App\Models\UnitModel;
use App\Models\PurchasedProductModel; // Changed from SellingProductModel
use Dompdf\Dompdf;

class Vendors extends Controller
{
    protected $vendorModel;
    protected $stockInModel;
    protected $unitModel;
    protected $purchasedProductModel; // New property for PurchasedProductModel

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->stockInModel = new StockInModel(); // Initialize StockInModel
        $this->unitModel = new UnitModel();       // Initialize UnitModel
        $this->purchasedProductModel = new PurchasedProductModel(); // Initialize PurchasedProductModel
    }

    /**
     * Displays a list of active (non-deleted) vendors.
     * Soft-deleted vendors are automatically excluded by the model's default behavior.
     */
    public function index()
    {
        // findAll() with useSoftDeletes will automatically exclude soft-deleted records
        $data['vendors'] = $this->vendorModel->findAll();
        return view('vendors/index', $data);
    }

    /**
     * Displays the form to create a new vendor.
     */
    public function create()
    {
        return view('vendors/create');
    }

    /**
     * Stores a new vendor record in the database.
     */
    public function store()
    {
        $data = [
            'agency_name'    => $this->request->getPost('agency_name'),
            'name'           => $this->request->getPost('name'),
            'owner_phone'    => $this->request->getPost('owner_phone'),
            'contact_person' => $this->request->getPost('contact_person'),
            'contact_phone'  => $this->request->getPost('contact_phone'),
            'email'          => $this->request->getPost('email'),
            'address'        => $this->request->getPost('address'),
        ];

        if ($this->vendorModel->insert($data)) {
            return redirect()->to('/vendors')->with('success', 'Vendor added successfully');
        } else {
            // Handle validation errors or other insertion failures
            return redirect()->back()->withInput()->with('error', 'Failed to add vendor. Please check your input.');
        }
    }

    /**
     * Displays the form to edit an existing vendor.
     *
     * @param int $id The ID of the vendor to edit.
     */
    public function edit($id)
    {
        // find() will automatically exclude soft-deleted records if useSoftDeletes is true
        $data['vendor'] = $this->vendorModel->find($id);

        if (!$data['vendor']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Vendor not found or already deleted.');
        }

        return view('vendors/edit', $data);
    }

    /**
     * Updates an existing vendor record in the database.
     *
     * @param int $id The ID of the vendor to update.
     */
    public function update($id)
    {
        $data = [
            'agency_name'    => $this->request->getPost('agency_name'),
            'name'           => $this->request->getPost('name'),
            'owner_phone'    => $this->request->getPost('owner_phone'),
            'contact_person' => $this->request->getPost('contact_person'),
            'contact_phone'  => $this->request->getPost('contact_phone'),
            'email'          => $this->request->getPost('email'),
            'address'        => $this->request->getPost('address'),
        ];

        if ($this->vendorModel->update($id, $data)) {
            return redirect()->to('/vendors')->with('success', 'Vendor updated successfully');
        } else {
            // Handle validation errors or other update failures
            return redirect()->back()->withInput()->with('error', 'Failed to update vendor. Please check your input.');
        }
    }

    /**
     * Soft deletes a vendor record.
     * The record is not physically removed but marked as deleted in the database.
     *
     * @param int $id The ID of the vendor to delete.
     */
    public function delete($id)
    {
        if ($this->vendorModel->delete($id)) {
            return redirect()->to('/vendors')->with('success', 'Vendor deleted successfully (soft-deleted).');
        } else {
            return redirect()->to('/vendors')->with('error', 'Failed to delete vendor.');
        }
    }

    /**
     * Generates a report of stock-in entries, filtering by active vendors.
     */
    public function vendorReport()
    {
        // Use initialized models
        $data = [];
        $data['vendors'] = $this->vendorModel->findAll();
        // Fetch products from PurchasedProductModel
        $data['products'] = $this->purchasedProductModel->findAll();
        $data['units'] = $this->unitModel->findAll();

        $vendorId = $this->request->getGet('vendor_id');
        $productId = $this->request->getGet('product_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $query = $this->stockInModel->select('stock_in.*, vendors.name as vendor_name, purchased_products.name as product_name, units.name as unit_name')
            ->join('vendors', 'vendors.id = stock_in.vendor_id')
            // Join with purchased_products, not selling_products
            ->join('purchased_products', 'purchased_products.id = stock_in.product_id')
            // Join units via purchased_products
            ->join('units', 'units.id = purchased_products.unit_id');

        if ($vendorId) {
            $query->where('stock_in.vendor_id', $vendorId);
        }
        if ($productId) {
            $query->where('stock_in.product_id', $productId);
        }
        if ($startDate) {
            $query->where('stock_in.date >=', $startDate);
        }
        if ($endDate) {
            $query->where('stock_in.date <=', $endDate);
        }

        $data['stock_in_entries'] = $query->findAll();

        echo view('templates/header');
        echo view('vendors/vendor_report', $data);
        echo view('templates/footer');
    }

    /**
     * Exports the vendor report to an Excel file, filtering by active vendors.
     */
    public function vendorReportExport()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_in');
        $builder->select('
            stock_in.*,
            purchased_products.name as product_name,
            units.name as unit_name,
            vendors.agency_name as vendor_agency_name,
            vendors.name as vendor_name
        ');
        // Join with purchased_products, not generic 'products'
        $builder->join('purchased_products', 'purchased_products.id = stock_in.product_id');
        // Join units via purchased_products
        $builder->join('units', 'units.id = purchased_products.unit_id');
        // Join with vendors, ensuring only active vendors are considered for the report
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id AND vendors.deleted_at IS NULL', 'left');

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
        $sheet->setCellValue('G1', 'Selling Price'); // This might be from purchased_products or a calculated field
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
            $sheet->setCellValue('G' . $row, $entry['selling_price']); // Ensure this field exists in purchased_products or stock_in
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

    /**
     * Generates a PDF report of stock-in entries, filtering by active vendors.
     */
    public function vendorReportPDF()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_in');

        $builder->select('
            stock_in.*,
            purchased_products.name as product_name,
            units.name as unit_name,
            vendors.agency_name as vendor_agency_name,
            vendors.name as vendor_name
        ');
        // Join with purchased_products, not generic 'products'
        $builder->join('purchased_products', 'purchased_products.id = stock_in.product_id');
        // Join units via purchased_products
        $builder->join('units', 'units.id = purchased_products.unit_id');
        // Join with vendors, ensuring only active vendors are considered for the report
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id AND vendors.deleted_at IS NULL', 'left');

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
