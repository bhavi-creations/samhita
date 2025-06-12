<?php

namespace App\Controllers;

use App\Models\MarketingDistributionModel;
use App\Models\ProductModel;
use App\Models\MarketingPersonModel;
use App\Models\StockOutModel; // <--- NEW: Import StockOutModel
use CodeIgniter\Database\Exceptions\DatabaseException; // <--- NEW: Import for transaction error handling
use PhpOffice\PhpSpreadsheet\Spreadsheet; // <--- NEW: For Excel export
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // <--- NEW: For Excel export


class MarketingDistribution extends BaseController
{
    protected $distributionModel;
    protected $productModel;
    protected $marketingPersonModel;
    protected $stockOutModel; // <--- NEW: Declare StockOutModel
    protected $db; // <--- NEW: Declare DB connection for transactions

    public function __construct()
    {
        $this->distributionModel = new MarketingDistributionModel();
        $this->productModel = new ProductModel();
        $this->marketingPersonModel = new MarketingPersonModel();
        $this->stockOutModel = new StockOutModel(); // <--- NEW: Initialize StockOutModel
        $this->db = \Config\Database::connect(); // <--- NEW: Initialize DB connection
    }

    // --- NEW: Helper function to calculate available stock ---
    private function calculateAvailableStock(int $productId): int
    {
        $stockIn = $this->db->table('stock_in')
            ->selectSum('quantity')
            ->where('product_id', $productId)
            ->get()
            ->getRow()->quantity ?? 0;

        $stockOut = $this->db->table('stock_out')
            ->selectSum('quantity_out')
            ->where('product_id', $productId)
            ->get()
            ->getRow()->quantity_out ?? 0;

        return $stockIn - $stockOut;
    }

   public function index()
    {
        // --- Existing Filters ---
        $productId = $this->request->getGet('product_id');
        $personId = $this->request->getGet('marketing_person_id');
        $dateIssued = $this->request->getGet('date_issued');

        // --- Search Term and Pagination Setup ---
        $searchQuery = $this->request->getGet('search');
        $perPage = 10; // Number of items per page

        // --- CORRECTED: Chain methods directly onto the Model instance ---
        $query = $this->distributionModel
            ->select('marketing_distribution.*, products.name as product_name, units.name as unit_name, marketing_persons.name as person_name, marketing_persons.custom_id')
            ->join('products', 'products.id = marketing_distribution.product_id')
            ->join('units', 'units.id = products.unit_id')
            ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id');

        // Apply filters
        if ($productId) {
            $query->where('marketing_distribution.product_id', $productId);
        }
        if ($personId) {
            $query->where('marketing_distribution.marketing_person_id', $personId);
        }
        if ($dateIssued) {
            $query->where('marketing_distribution.date_issued', $dateIssued);
        }

        // Apply search query
        if ($searchQuery) {
            $query->groupStart()
                    ->like('products.name', $searchQuery)
                    ->orLike('marketing_persons.name', $searchQuery)
                    ->orLike('marketing_persons.custom_id', $searchQuery)
                    ->orLike('marketing_distribution.notes', $searchQuery)
                    ->groupEnd();
        }

        // Order by latest distributions first
        $query->orderBy('marketing_distribution.date_issued', 'DESC');
        $query->orderBy('marketing_distribution.id', 'DESC');


        // --- NOW, call paginate() on the Model instance ($this->distributionModel) ---
        $data['distributions'] = $query->paginate($perPage);
        $data['pager'] = $this->distributionModel->pager; // Get the pager from the model

        // Calculate and add available stock for each record (as already implemented)
        foreach ($data['distributions'] as &$dist) {
            $dist['current_available_stock'] = $this->calculateAvailableStock($dist['product_id']);
        }
        unset($dist); // Unset the reference to avoid issues in later loops

        $data['products'] = $this->productModel->select('products.id, products.name, units.name as unit_name')
                               ->join('units', 'units.id = products.unit_id')
                               ->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();

        // Pass back current filter values for form persistence
        $data['selected_product_id'] = $productId;
        $data['selected_person_id'] = $personId;
        $data['selected_date_issued'] = $dateIssued;
        $data['search_query'] = $searchQuery;

        return view('marketing_distribution/index', $data);
    }

    public function create()
    {
        // Get products with unit names
        $data['products'] = $this->productModel->select('products.id, products.name, units.name as unit_name')
                               ->join('units', 'units.id = products.unit_id')
                               ->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();
        $data['validation'] = \Config\Services::validation(); // For displaying validation errors
        return view('marketing_distribution/create', $data);
    }

    public function store()
    {
        // --- NEW: Validation Rules ---
        $rules = [
            'product_id'          => 'required|integer|is_not_unique[products.id]',
            'marketing_person_id' => 'required|integer|is_not_unique[marketing_persons.id]',
            'quantity_issued'     => 'required|integer|greater_than[0]',
            'date_issued'         => 'required|valid_date',
            'notes'               => 'permit_empty|string|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $productId = $this->request->getPost('product_id');
        $quantityIssued = $this->request->getPost('quantity_issued');
        $dateIssued = $this->request->getPost('date_issued');
        $notes = $this->request->getPost('notes');

        // --- NEW: Stock Availability Check ---
        $availableStock = $this->calculateAvailableStock($productId);

        if ($quantityIssued > $availableStock) {
            return redirect()->back()->withInput()->with('error', 'Not enough stock available for this product. Available: ' . $availableStock);
        }

        $this->db->transStart(); // <--- NEW: Start transaction

        try {
            // 1. Save Marketing Distribution Record
            $this->distributionModel->save([
                'product_id'        => $productId,
                'marketing_person_id' => $this->request->getPost('marketing_person_id'),
                'quantity_issued'   => $quantityIssued,
                'date_issued'       => $dateIssued,
                'notes'             => $notes,
            ]);

            $marketingDistributionId = $this->distributionModel->getInsertID(); // Get the ID of the newly inserted record

            // 2. Create Stock Out Record
            $this->stockOutModel->save([
                'product_id'       => $productId,
                'quantity_out'     => $quantityIssued,
                'transaction_type' => 'marketing_distribution', // Link to the source transaction type
                'transaction_id'   => $marketingDistributionId,  // Link to the specific marketing_distribution ID
                'issued_date'      => $dateIssued,
                'notes'            => $notes,
            ]);

            $this->db->transComplete(); // <--- NEW: Complete transaction (commits if successful)

            if ($this->db->transStatus() === false) {
                // Transaction failed, log the error (optional)
                log_message('error', 'Marketing Distribution Store Transaction Failed: ' . $this->db->error()['message']);
                return redirect()->back()->withInput()->with('error', 'Failed to add distribution due to a database error. Please try again.');
            }

        } catch (DatabaseException $e) {
            $this->db->transRollback(); // <--- NEW: Rollback on database error
            log_message('error', 'Marketing Distribution Store Database Exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'A database error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->db->transRollback(); // <--- NEW: Rollback on any other exception
            log_message('error', 'Marketing Distribution Store Exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record added successfully.');
    }

    public function edit($id = null)
    {
        $distribution = $this->distributionModel->find($id);
        if (!$distribution) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Record not found');
        }
        $data['distribution'] = $distribution;
        // Get products with unit names
        $data['products'] = $this->productModel->select('products.id, products.name, units.name as unit_name')
                               ->join('units', 'units.id = products.unit_id')
                               ->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();
        $data['validation'] = \Config\Services::validation(); // For displaying validation errors
        return view('marketing_distribution/edit', $data);
    }

    public function update($id = null)
    {
        if ($id === null) {
            return redirect()->to('/marketing-distribution')->with('error', 'No distribution ID provided for update.');
        }

        $oldDistribution = $this->distributionModel->find($id);
        if (!$oldDistribution) {
            return redirect()->to('/marketing-distribution')->with('error', 'Distribution record not found for update.');
        }

        // --- NEW: Validation Rules ---
        $rules = [
            'product_id'          => 'required|integer|is_not_unique[products.id]',
            'marketing_person_id' => 'required|integer|is_not_unique[marketing_persons.id]',
            'quantity_issued'     => 'required|integer|greater_than[0]',
            'date_issued'         => 'required|valid_date',
            'notes'               => 'permit_empty|string|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $productId = $this->request->getPost('product_id');
        $quantityIssued = $this->request->getPost('quantity_issued');
        $dateIssued = $this->request->getPost('date_issued');
        $notes = $this->request->getPost('notes');

        $this->db->transStart(); // <--- NEW: Start transaction

        try {
            // Check stock availability if quantity is increasing or product is changing
            $oldProductId = $oldDistribution['product_id'];
            $oldQuantityIssued = $oldDistribution['quantity_issued'];

            // If product changes, or quantity increases, we need to check stock
            if ($productId != $oldProductId || $quantityIssued > $oldQuantityIssued) {
                // Temporarily reverse the old quantity from stock_out for accurate calculation
                // This is a more robust way than just checking total available stock
                $currentStockAfterOldDistReversal = $this->calculateAvailableStock($oldProductId) + $oldQuantityIssued; // Add back the original distributed quantity for that product

                if ($quantityIssued > $currentStockAfterOldDistReversal) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Not enough stock available for this update. Available (after reversing old quantity): ' . $currentStockAfterOldDistReversal);
                }
            }


            // 1. Update Marketing Distribution Record
            $this->distributionModel->update($id, [
                'product_id'        => $productId,
                'marketing_person_id' => $this->request->getPost('marketing_person_id'),
                'quantity_issued'   => $quantityIssued,
                'date_issued'       => $dateIssued,
                'notes'             => $notes,
            ]);

            // 2. Update or Create Stock Out Record (if not found, create; otherwise update)
            $existingStockOut = $this->stockOutModel->where('transaction_type', 'marketing_distribution')
                                                    ->where('transaction_id', $id)
                                                    ->first();
            $stockOutData = [
                'product_id'       => $productId,
                'quantity_out'     => $quantityIssued,
                'transaction_type' => 'marketing_distribution',
                'transaction_id'   => $id,
                'issued_date'      => $dateIssued,
                'notes'            => $notes,
            ];

            if ($existingStockOut) {
                $this->stockOutModel->update($existingStockOut['id'], $stockOutData);
            } else {
                $this->stockOutModel->save($stockOutData); // Should ideally not happen if created correctly
            }

            $this->db->transComplete(); // <--- NEW: Complete transaction (commits if successful)

            if ($this->db->transStatus() === false) {
                 log_message('error', 'Marketing Distribution Update Transaction Failed: ' . $this->db->error()['message']);
                 return redirect()->back()->withInput()->with('error', 'Failed to update distribution due to a database error. Please try again.');
            }

        } catch (DatabaseException $e) {
            $this->db->transRollback(); // <--- NEW: Rollback on database error
            log_message('error', 'Marketing Distribution Update Database Exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'A database error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->db->transRollback(); // <--- NEW: Rollback on any other exception
            log_message('error', 'Marketing Distribution Update Exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record updated successfully.');
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('/marketing-distribution')->with('error', 'No distribution ID provided for deletion.');
        }

        $this->db->transStart(); // <--- NEW: Start transaction

        try {
            // 1. Delete Marketing Distribution Record
            $this->distributionModel->delete($id);

            // 2. Delete corresponding Stock Out Record
            $this->stockOutModel->where('transaction_type', 'marketing_distribution')
                                ->where('transaction_id', $id)
                                ->delete();

            $this->db->transComplete(); // <--- NEW: Complete transaction

            if ($this->db->transStatus() === false) {
                 log_message('error', 'Marketing Distribution Delete Transaction Failed: ' . $this->db->error()['message']);
                 return redirect()->back()->with('error', 'Failed to delete distribution due to a database error. Please try again.');
            }

        } catch (DatabaseException $e) {
            $this->db->transRollback(); // <--- NEW: Rollback on database error
            log_message('error', 'Marketing Distribution Delete Database Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'A database error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->db->transRollback(); // <--- NEW: Rollback on any other exception
            log_message('error', 'Marketing Distribution Delete Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record deleted successfully.');
    }

    // --- NEW: Export to Excel ---
    public function exportExcel()
    {
        // Fetch data with joins for comprehensive export
        $builder = $this->distributionModel->builder();
        $builder->select('
            marketing_distribution.id,
            products.name as product_name,
            units.name as unit_name,
            marketing_persons.custom_id,
            marketing_persons.name as person_name,
            marketing_distribution.quantity_issued,
            marketing_distribution.date_issued,
            marketing_distribution.notes
            ');
        $builder->join('products', 'products.id = marketing_distribution.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id');
        $builder->orderBy('marketing_distribution.date_issued', 'DESC');

        $distributions = $builder->get()->getResultArray();

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Quantity Issued');
        $sheet->setCellValue('D1', 'Unit');
        $sheet->setCellValue('E1', 'Marketing Person ID');
        $sheet->setCellValue('F1', 'Marketing Person Name');
        $sheet->setCellValue('G1', 'Date Issued');
        $sheet->setCellValue('H1', 'Notes');

        // Populate data rows
        $row = 2;
        foreach ($distributions as $dist) {
            $sheet->setCellValue('A' . $row, $dist['id']);
            $sheet->setCellValue('B' . $row, $dist['product_name']);
            $sheet->setCellValue('C' . $row, $dist['quantity_issued']);
            $sheet->setCellValue('D' . $row, $dist['unit_name']);
            $sheet->setCellValue('E' . $row, $dist['custom_id']);
            $sheet->setCellValue('F' . $row, $dist['person_name']);
            $sheet->setCellValue('G' . $row, $dist['date_issued']);
            $sheet->setCellValue('H' . $row, $dist['notes']);
            $row++;
        }

        // Set headers for download
        $fileName = 'Marketing_Distributions_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        // Save to php://output
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --- NEW: Placeholder for Export to PDF ---
    public function exportPdf()
    {
        // For PDF export, you'll need a library like "dompdf/dompdf" or "mpdf/mpdf".
        // Example with dompdf (after installing via Composer: composer require dompdf/dompdf)
        // You would typically render a view into HTML, then pass that HTML to dompdf.

        // Example:
        // $data['distributions'] = $this->distributionModel->select('...') // Fetch data similar to exportExcel
        //                                            ->findAll();
        // $html = view('marketing_distribution/pdf_template', $data); // Create a new view for PDF content

        // $dompdf = new \Dompdf\Dompdf();
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape'); // or 'portrait'
        // $dompdf->render();
        // $dompdf->stream('Marketing_Distributions_' . date('Ymd') . '.pdf', ['Attachment' => 1]);
        exit("PDF export not yet implemented. Please install a PDF library like dompdf and uncomment the code.");
    }
}