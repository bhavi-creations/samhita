<?php

namespace App\Controllers;

use App\Models\MarketingDistributionModel;
use App\Models\ProductModel;
use App\Models\MarketingPersonModel;
use App\Models\StockOutModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf; // For PDF export
use Dompdf\Options; // For PDF options

class MarketingDistribution extends BaseController
{
    protected $distributionModel;
    protected $productModel;
    protected $marketingPersonModel;
    protected $stockOutModel;
    protected $db;

    public function __construct()
    {
        $this->distributionModel    = new MarketingDistributionModel();
        $this->productModel         = new ProductModel();
        $this->marketingPersonModel = new MarketingPersonModel();
        $this->stockOutModel        = new StockOutModel();
        $this->db                   = \Config\Database::connect();
    }

    public function index()
    {
        $productId = $this->request->getGet('product_id');
        $personId = $this->request->getGet('marketing_person_id');
        $dateIssued = $this->request->getGet('date_issued');
        $searchQuery = $this->request->getGet('search');
        $perPage = 10;

        $query = $this->distributionModel
            ->select('marketing_distribution.*, products.name as product_name, products.current_stock as current_product_stock, units.name as unit_name, marketing_persons.name as person_name, marketing_persons.custom_id') // NEW: Select current_stock
            ->join('products', 'products.id = marketing_distribution.product_id')
            ->join('units', 'units.id = products.unit_id')
            ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id');

        if ($productId) {
            $query->where('marketing_distribution.product_id', $productId);
        }
        if ($personId) {
            $query->where('marketing_distribution.marketing_person_id', $personId);
        }
        if ($dateIssued) {
            $query->where('marketing_distribution.date_issued', $dateIssued);
        }

        if ($searchQuery) {
            $query->groupStart()
                ->like('products.name', $searchQuery)
                ->orLike('marketing_persons.name', $searchQuery)
                ->orLike('marketing_persons.custom_id', $searchQuery)
                ->orLike('marketing_distribution.notes', $searchQuery)
                ->groupEnd();
        }

        $query->orderBy('marketing_distribution.date_issued', 'DESC');
        $query->orderBy('marketing_distribution.id', 'DESC');

        $data['distributions'] = $query->paginate($perPage);
        $data['pager'] = $this->distributionModel->pager;

        // Fetch all products with current stock and unit names for filter dropdowns and create/edit forms
        $data['products'] = $this->productModel->select('products.id, products.name, products.current_stock, units.name as unit_name')
                                               ->join('units', 'units.id = products.unit_id')
                                               ->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();

        $data['selected_product_id'] = $productId;
        $data['selected_person_id'] = $personId;
        $data['selected_date_issued'] = $dateIssued;
        $data['search_query'] = $searchQuery;

        return view('marketing_distribution/index', $data);
    }

    public function create()
    {
        // Get products with unit names and current_stock for the form
        $data['products'] = $this->productModel->select('products.id, products.name, products.current_stock, units.name as unit_name')
                                               ->join('units', 'units.id = products.unit_id')
                                               ->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();
        $data['validation'] = \Config\Services::validation();
        return view('marketing_distribution/create', $data);
    }

    public function store()
    {
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

        $productId      = $this->request->getPost('product_id');
        $quantityIssued = (int) $this->request->getPost('quantity_issued');
        $dateIssued     = $this->request->getPost('date_issued');
        $notes          = $this->request->getPost('notes');

        $this->db->transStart();

        try {
            // Get product to check current stock
            $product = $this->productModel->find($productId);
            if (!$product) {
                throw new \Exception('Selected product not found.');
            }

            // Stock Availability Check against products.current_stock
            if ($quantityIssued > (int)$product['current_stock']) {
                $this->db->transRollback(); // Rollback any pending transaction changes
                return redirect()->back()->withInput()->with('error', 'Not enough stock available for "' . esc($product['name']) . '". Available: ' . (int)$product['current_stock'] . ' ' . esc($product['unit_name']) . '. Requested: ' . $quantityIssued);
            }

            // 1. Save Marketing Distribution Record
            $this->distributionModel->save([
                'product_id'          => $productId,
                'marketing_person_id' => $this->request->getPost('marketing_person_id'),
                'quantity_issued'     => $quantityIssued,
                'date_issued'         => $dateIssued,
                'notes'               => $notes,
            ]);

            $marketingDistributionId = $this->distributionModel->getInsertID();

            if (!$marketingDistributionId) {
                throw new \Exception('Failed to save marketing distribution record.');
            }

            // 2. Decrement products.current_stock
            $newProductStock = (int)$product['current_stock'] - $quantityIssued;
            if (!$this->productModel->update($productId, ['current_stock' => $newProductStock])) {
                $dbError = $this->db->error();
                throw new \Exception('Failed to decrement product stock: ' . ($dbError['message'] ?? 'Unknown DB error.'));
            }

            // 3. Create Stock Out Record
            if (!$this->stockOutModel->insert([
                'product_id'        => $productId,
                'quantity_out'      => $quantityIssued,
                'transaction_type'  => 'marketing_distribution',
                'transaction_id'    => $marketingDistributionId,
                'issued_date'       => $dateIssued,
                'notes'             => 'Marketing Distribution for Product ID: ' . $productId . ', Quantity: ' . $quantityIssued,
            ])) {
                $modelErrors = $this->stockOutModel->errors();
                throw new \Exception('Failed to record stock out: ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Marketing Distribution Store Transaction Failed: ' . json_encode($this->db->error()));
                throw new \Exception('Failed to add distribution due to a database error. Please try again.');
            }

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Marketing Distribution Store Database Exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'A database error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->db->transRollback();
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

        // Get products with unit names and current_stock for the form
        $data['products'] = $this->productModel->select('products.id, products.name, products.current_stock, units.name as unit_name')
                                               ->join('units', 'units.id = products.unit_id')
                                               ->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();
        $data['validation'] = \Config\Services::validation();
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

        $productId          = $this->request->getPost('product_id');
        $quantityIssued     = (int) $this->request->getPost('quantity_issued');
        $dateIssued         = $this->request->getPost('date_issued');
        $notes              = $this->request->getPost('notes');

        $this->db->transStart();

        try {
            $oldProductId      = $oldDistribution['product_id'];
            $oldQuantityIssued = (int)$oldDistribution['quantity_issued'];

            // Fetch current product details (old and new if different) for stock calculations
            $currentProduct = $this->productModel->find($oldProductId);
            if (!$currentProduct) {
                throw new \Exception('Original product not found for stock adjustment.');
            }

            $targetProduct = $this->productModel->find($productId);
            if (!$targetProduct) {
                throw new \Exception('Target product not found for stock adjustment.');
            }

            // --- Stock Adjustment Logic ---
            if ($productId != $oldProductId) {
                // Product has changed:
                // 1. Return old quantity to old product's stock
                $this->productModel->update($oldProductId, ['current_stock' => (int)$currentProduct['current_stock'] + $oldQuantityIssued]);

                // 2. Check stock for new product and deduct new quantity
                if ($quantityIssued > (int)$targetProduct['current_stock']) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Not enough stock for the new product (' . esc($targetProduct['name']) . '). Available: ' . (int)$targetProduct['current_stock'] . '. Requested: ' . $quantityIssued);
                }
                $this->productModel->update($productId, ['current_stock' => (int)$targetProduct['current_stock'] - $quantityIssued]);

            } else {
                // Product is the same, only quantity might have changed
                $quantityDifference = $quantityIssued - $oldQuantityIssued; // Positive means increased, negative means decreased

                if ($quantityDifference > 0) { // Quantity increased
                    if ($quantityDifference > (int)$currentProduct['current_stock']) {
                        $this->db->transRollback();
                        return redirect()->back()->withInput()->with('error', 'Not enough stock available to increase quantity for "' . esc($currentProduct['name']) . '". Needs ' . $quantityDifference . ' more, but only ' . (int)$currentProduct['current_stock'] . ' available.');
                    }
                }
                // Update product's current_stock directly based on the difference
                $newProductStock = (int)$currentProduct['current_stock'] - $quantityDifference;
                if (!$this->productModel->update($productId, ['current_stock' => $newProductStock])) {
                    $dbError = $this->db->error();
                    throw new \Exception('Failed to update product stock: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                }
            }

            // 1. Update Marketing Distribution Record
            if (!$this->distributionModel->update($id, [
                'product_id'          => $productId,
                'marketing_person_id' => $this->request->getPost('marketing_person_id'),
                'quantity_issued'     => $quantityIssued,
                'date_issued'         => $dateIssued,
                'notes'               => $notes,
            ])) {
                $modelErrors = $this->distributionModel->errors();
                throw new \Exception('Failed to update marketing distribution record: ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
            }

            // 2. Update Stock Out Record
            // Find the stock_out record linked to this marketing_distribution
            $stockOutRecord = $this->stockOutModel->where('transaction_type', 'marketing_distribution')
                                                  ->where('transaction_id', $id)
                                                  ->first();

            $stockOutData = [
                'product_id'        => $productId, // Update product_id if it changed
                'quantity_out'      => $quantityIssued,
                'transaction_type'  => 'marketing_distribution',
                'transaction_id'    => $id,
                'issued_date'       => $dateIssued,
                'notes'             => 'Marketing Distribution for Product ID: ' . $productId . ', Quantity: ' . $quantityIssued . ' (Updated)',
            ];

            if ($stockOutRecord) {
                if (!$this->stockOutModel->update($stockOutRecord['id'], $stockOutData)) {
                    $modelErrors = $this->stockOutModel->errors();
                    throw new \Exception('Failed to update stock out record: ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
                }
            } else {
                // This case ideally shouldn't happen if the record was created correctly,
                // but as a fallback, create a new one if it's missing.
                log_message('warning', 'MarketingDistribution::update - Stock out record not found for marketing distribution ID ' . $id . '. Creating new one.');
                if (!$this->stockOutModel->insert($stockOutData)) {
                    $modelErrors = $this->stockOutModel->errors();
                    throw new \Exception('Failed to create missing stock out record: ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Marketing Distribution Update Transaction Failed: ' . json_encode($this->db->error()));
                throw new \Exception('Failed to update distribution due to a database error. Please try again.');
            }

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Marketing Distribution Update Database Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'A database error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Marketing Distribution Update Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record updated successfully.');
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('/marketing-distribution')->with('error', 'No distribution ID provided for deletion.');
        }

        $this->db->transStart();

        try {
            $distribution = $this->distributionModel->find($id);
            if (!$distribution) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Marketing Distribution record not found for ID: ' . $id);
            }

            $productId        = $distribution['product_id'];
            $quantityIssued   = (int)$distribution['quantity_issued'];

            // 1. Delete Marketing Distribution Record
            if (!$this->distributionModel->delete($id)) {
                $modelErrors = $this->distributionModel->errors();
                throw new \Exception('Failed to delete marketing distribution record: ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
            }

            // 2. Return stock to products.current_stock
            $product = $this->productModel->find($productId);
            if ($product) {
                $newProductStock = (int)$product['current_stock'] + $quantityIssued;
                if (!$this->productModel->update($productId, ['current_stock' => $newProductStock])) {
                    $dbError = $this->db->error();
                    throw new \Exception('Failed to increment product stock during deletion: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                }
            } else {
                log_message('error', 'MarketingDistribution::delete - Product (ID: ' . $productId . ') not found while attempting to return stock on deletion of distribution ID: ' . $id);
            }

            // 3. Delete corresponding Stock Out Record
            if (!$this->stockOutModel->where('transaction_type', 'marketing_distribution')
                                      ->where('transaction_id', $id)
                                      ->delete()) {
                $modelErrors = $this->stockOutModel->errors();
                throw new \Exception('Failed to delete associated stock out record: ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Marketing Distribution Delete Transaction Failed: ' . json_encode($this->db->error()));
                throw new \Exception('Failed to delete distribution due to a database error. Please try again.');
            }

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Marketing Distribution Delete Database Exception: ' . $e->getMessage());
            return redirect()->to('/marketing-distribution')->with('error', 'A database error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Marketing Distribution Delete Exception: ' . $e->getMessage());
            return redirect()->to('/marketing-distribution')->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record deleted successfully.');
    }

    public function exportExcel()
    {
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Quantity Issued');
        $sheet->setCellValue('D1', 'Unit');
        $sheet->setCellValue('E1', 'Marketing Person ID');
        $sheet->setCellValue('F1', 'Marketing Person Name');
        $sheet->setCellValue('G1', 'Date Issued');
        $sheet->setCellValue('H1', 'Notes');

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

        $fileName = 'Marketing_Distributions_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportPdf()
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

        $data = [
            'title'         => 'Marketing Distributions Report',
            'distributions' => $distributions,
            'currentDate'   => date('Y-m-d H:i:s')
        ];

        $html = view('marketing_distribution/pdf_report', $data); // You'll need to create this view

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans'); // Ensure this font is available or use a common one

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Marketing_Distributions_Report_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => 1));
        exit;
    }
}
