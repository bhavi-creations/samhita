<?php

namespace App\Controllers;

use App\Models\StockInModel;
use App\Models\ProductModel;
use App\Models\VendorModel;
use App\Models\GstRateModel;
use App\Models\StockInPaymentModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;
use CodeIgniter\HTTP\ResponseInterface; // Add this for type hinting response

class StockIn extends BaseController
{
    protected $stockInModel;
    protected $productModel;
    protected $vendorModel;
    protected $gstRateModel;
    protected $stockInPaymentModel;
    protected $session; // Added for session messages
    protected $validation; // Added for validation service
    protected $db; // Added for database transactions

    public function __construct()
    {
        $this->stockInModel = new StockInModel();
        $this->productModel = new ProductModel();
        $this->vendorModel  = new VendorModel();
        $this->gstRateModel = new GstRateModel();
        $this->stockInPaymentModel = new StockInPaymentModel();
        $this->session = \Config\Services::session(); // Initialize session
        $this->validation = \Config\Services::validation(); // Initialize validation
        $this->db = \Config\Database::connect(); // Initialize database connection for transactions
        helper(['form', 'url']); // Ensure form and URL helpers are loaded
    }

    public function index()
    {
        $builder = $this->stockInModel->builder();
        $builder->select('
            stock_in.*, 
            products.name as product_name, 
            units.name as unit_name, 
            vendors.agency_name as vendor_agency_name, 
            vendors.name as vendor_name,
            gst_rates.name as gst_rate_name,  
            (gst_rates.rate * 100) as gst_rate_percentage 
        ');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id', 'left');
        $builder->join('gst_rates', 'gst_rates.id = stock_in.gst_rate_id', 'left');
        $builder->orderBy('stock_in.id', 'DESC');

        $data['stock_entries'] = $builder->get()->getResultArray();

        return view('stock_in/index', $data);
    }

    public function create()
    {
        $products = $this->productModel->findAll();
        $vendors  = $this->vendorModel->findAll();
        $gstRates = $this->gstRateModel->findAll();

        return view('stock_in/create', [
            'products' => $products,
            'vendors'  => $vendors,
            'gstRates' => $gstRates,
            'validation' => \Config\Services::validation(), // Pass validation service to the view
        ]);
    }

    // --- MODIFIED STORE METHOD ---
    public function store(): ResponseInterface // Add ResponseInterface type hint
    {
        $rules = [
            'product_id'            => 'required|numeric|is_not_unique[products.id]', // Ensure product exists
            'quantity'              => 'required|numeric|greater_than[0]',
            'purchase_price'        => 'required|numeric|greater_than_equal_to[0]',
            'gst_rate_id'           => 'required|numeric|is_not_unique[gst_rates.id]', // Ensure GST rate exists
            'date_received'         => 'required|valid_date',
            'total_amount_hidden'   => 'required|numeric|greater_than_equal_to[0]',
            'gst_amount'            => 'required|numeric|greater_than_equal_to[0]',
            'grand_total_hidden'    => 'required|numeric|greater_than_equal_to[0]',
            'amount_paid_initial'   => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'StockIn::store - Validation failed. Errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Collect necessary data from POST
        $productId = $this->request->getPost('product_id');
        $quantityAdded = (int)$this->request->getPost('quantity'); // Cast to integer for stock calculation
        $initialAmountPaid = (float)$this->request->getPost('amount_paid_initial');

        $dataToSave = [
            'product_id'            => $productId,
            'quantity'              => $quantityAdded,
            'current_quantity'      => $quantityAdded, // This stores the current outstanding quantity for THIS stock_in record
            'vendor_id'             => $this->request->getPost('vendor_id'),
            'purchase_price'        => $this->request->getPost('purchase_price'),
            'date_received'         => $this->request->getPost('date_received'),
            'notes'                 => $this->request->getPost('notes'),
            'gst_rate_id'           => $this->request->getPost('gst_rate_id'),
            'total_amount_before_gst' => $this->request->getPost('total_amount_hidden'),
            'gst_amount'            => $this->request->getPost('gst_amount'),
            'grand_total'           => $this->request->getPost('grand_total_hidden'),
        ];

        $this->db->transBegin(); // Start database transaction

        try {
            // 1. Insert the new stock_in record
            $stockInId = $this->stockInModel->insert($dataToSave, true); // true returns the inserted ID
            log_message('debug', 'StockIn::store - StockIn record insertion result: ' . ($stockInId ? 'Success (ID: ' . $stockInId . ')' : 'Failed'));

            if (!$stockInId) {
                $this->db->transRollback();
                $dbError = $this->db->error();
                log_message('error', 'StockIn::store - Failed to insert stock_in record. DB Error: ' . json_encode($dbError) . ', Data: ' . json_encode($dataToSave));
                return redirect()->back()->withInput()->with('error', 'Failed to add stock. Database insert failed: ' . $dbError['message']);
            }

            // 2. Update the product's current_stock in the products table
            $product = $this->productModel->find($productId);
            if (!$product) {
                $this->db->transRollback();
                log_message('error', 'StockIn::store - Product not found for ID: ' . $productId . ' during stock update.');
                return redirect()->back()->withInput()->with('error', 'Product not found for stock update. Please check the selected product.');
            }

            $newProductStock = (int)$product['current_stock'] + $quantityAdded;
            $productUpdateResult = $this->productModel->update($productId, ['current_stock' => $newProductStock]);
            log_message('debug', 'StockIn::store - Product stock update attempt. Result: ' . ($productUpdateResult ? 'Success' : 'Failed') . ', New Stock: ' . $newProductStock);

            if (!$productUpdateResult) {
                $this->db->transRollback();
                $dbError = $this->db->error();
                log_message('error', 'StockIn::store - Failed to update product stock. Product ID: ' . $productId . ', DB Error: ' . json_encode($dbError));
                return redirect()->back()->withInput()->with('error', 'Failed to update product stock. Please try again.');
            }

            // 3. If an initial payment is provided, record it
            if ($initialAmountPaid > 0) {
                $paymentData = [
                    'stock_in_id'    => $stockInId,
                    'payment_amount' => $initialAmountPaid,
                    'payment_date'   => date('Y-m-d'), // Today's date for initial payment
                    'notes'          => 'Initial payment upon stock-in',
                    'created_at'     => date('Y-m-d H:i:s')
                ];

                $paymentSaveResult = $this->stockInPaymentModel->save($paymentData);
                log_message('debug', 'StockIn::store - StockInPayment save result: ' . ($paymentSaveResult ? 'Success' : 'Failed') . ', Payment Data: ' . json_encode($paymentData));

                if (!$paymentSaveResult) {
                    $this->db->transRollback();
                    $dbError = $this->db->error();
                    log_message('error', 'StockIn::store - Failed to record initial payment. DB Error: ' . json_encode($dbError));
                    return redirect()->back()->withInput()->with('error', 'Failed to record initial payment. Please try again.');
                }
            }

            // Check final transaction status before committing
            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                $dbError = $this->db->error();
                log_message('error', 'StockIn::store - Transaction final status is FALSE (before commit). DB Error: ' . json_encode($dbError));
                return redirect()->back()->withInput()->with('error', 'An internal database transaction error occurred. Please check system logs.');
            } else {
                $this->db->transCommit(); // Commit the transaction
                log_message('debug', 'StockIn::store - Transaction Committed Successfully.');
                return redirect()->to('/stock-in')->with('success', 'Stock added successfully and product inventory updated.');
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::store - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected system error occurred: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        // Fetch the main stock in entry details
        $stockInEntry = $this->stockInModel
            ->select('stock_in.*, 
                                            vendors.agency_name as vendor_agency_name, vendors.name as vendor_name,
                                            products.name as product_name,
                                            units.name as unit_name,
                                            gst_rates.name as gst_rate_name, gst_rates.rate as gst_rate_percentage')
            ->join('vendors', 'vendors.id = stock_in.vendor_id')
            ->join('products', 'products.id = stock_in.product_id')
            ->join('units', 'units.id = products.unit_id')
            ->join('gst_rates', 'gst_rates.id = stock_in.gst_rate_id')
            ->find($id);

        if (empty($stockInEntry)) {
            session()->setFlashdata('error', 'Stock In entry not found.');
            return redirect()->to(base_url('stock-in'));
        }

        // Fetch all payments related to this stock in entry
        $stockInPayments = $this->stockInPaymentModel
            ->where('stock_in_id', $id)
            ->orderBy('payment_date', 'asc') // Order by date for better readability
            ->findAll();

        $data = [
            'stock_in_entry' => $stockInEntry,
            'stock_in_payments' => $stockInPayments,
            'title' => 'View Stock In Entry and Payments'
        ];

        return view('stock_in/view', $data);
    }

    public function edit($id = null)
    {
        if ($id === null) {
            return redirect()->to('/stock-in')->with('error', 'No Stock In ID provided for editing.');
        }

        $builder = $this->stockInModel->builder();
        $builder->select('
            stock_in.*, 
            (gst_rates.rate * 100) as gst_rate_percentage 
        ');
        $builder->join('gst_rates', 'gst_rates.id = stock_in.gst_rate_id', 'left');
        $builder->where('stock_in.id', $id);
        $stockEntry = $builder->get()->getRowArray();


        if (empty($stockEntry)) {
            return redirect()->to('/stock-in')->with('error', 'Stock In entry not found for editing.');
        }

        // Fetch payment history for this stock entry
        $stockEntryPayments = $this->stockInPaymentModel
            ->where('stock_in_id', $id)
            ->orderBy('payment_date', 'ASC')
            ->orderBy('created_at', 'ASC') // For same-day payments
            ->findAll();

        $products = $this->productModel->findAll();
        $vendors  = $this->vendorModel->findAll();
        $gstRates = $this->gstRateModel->findAll();

        return view('stock_in/edit', [
            'stock_entry'          => $stockEntry,
            'stock_entry_payments' => $stockEntryPayments,
            'products'             => $products,
            'vendors'              => $vendors,
            'gstRates'             => $gstRates
        ]);
    }

        public function update($id): ResponseInterface
        {
            //   dd($this->request->getPost()); 
            $stockIn = $this->stockInModel->find($id);
            if (!$stockIn) {
                log_message('error', 'StockIn::update - Stock In record not found for ID: ' . $id);
                return redirect()->back()->with('error', 'Stock In record not found.');
            }

            $rules = [
                'product_id'            => 'required|numeric|is_not_unique[products.id]', // Ensure product exists
                'quantity'              => 'required|numeric|greater_than[0]',
                'purchase_price'        => 'required|numeric|greater_than_equal_to[0]', // Changed to greater_than_equal_to[0]
                'gst_rate_id'           => 'required|numeric|is_not_unique[gst_rates.id]', // Ensure GST rate exists
                'date_received'         => 'required|valid_date',
                'total_amount_hidden'   => 'required|numeric|greater_than_equal_to[0]',
                'gst_amount_hidden'     => 'required|numeric|greater_than_equal_to[0]', // Corrected field name
                'grand_total_hidden'    => 'required|numeric|greater_than_equal_to[0]',
            ];

            if (!$this->validate($rules)) {
                log_message('error', 'StockIn::update - Validation failed for ID: ' . $id . '. Errors: ' . json_encode($this->validator->getErrors()));
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Get the old quantity and product ID before updating
            $oldQuantity = (int)$stockIn['quantity'];
            $oldProductId = (int)$stockIn['product_id'];

            $newProductId = (int)$this->request->getPost('product_id');
            $newQuantity = (int)$this->request->getPost('quantity');

            $dataToUpdate = [
                'product_id'            => $newProductId,
                'quantity'              => $newQuantity,
                'current_quantity'      => $newQuantity, // Reset current_quantity to new quantity on update
                'vendor_id'             => $this->request->getPost('vendor_id'),
                'purchase_price'        => $this->request->getPost('purchase_price'),
                'date_received'         => $this->request->getPost('date_received'),
                'notes'                 => $this->request->getPost('notes'),
                'gst_rate_id'           => $this->request->getPost('gst_rate_id'),
                'total_amount_before_gst' => $this->request->getPost('total_amount_hidden'),
                'gst_amount'            => $this->request->getPost('gst_amount_hidden'),
                'grand_total'           => $this->request->getPost('grand_total_hidden'),
            ];

            $this->db->transBegin(); // Start transaction

            try {
                // Update the stock_in record
                $updateStockInResult = $this->stockInModel->update($id, $dataToUpdate);
                log_message('debug', 'StockIn::update - StockIn record update result: ' . ($updateStockInResult ? 'Success' : 'Failed') . ', ID: ' . $id);

                if (!$updateStockInResult) {
                    $this->db->transRollback();
                    $dbError = $this->db->error();
                    log_message('error', 'StockIn::update - Failed to update stock_in record. DB Error: ' . json_encode($dbError) . ', ID: ' . $id);
                    return redirect()->back()->withInput()->with('error', 'Failed to update stock in. Database update failed.');
                }

                // Adjust product stock based on changes
                if ($oldProductId === $newProductId) {
                    // Same product, adjust stock by the difference
                    $stockDifference = $newQuantity - $oldQuantity;
                    $product = $this->productModel->find($newProductId);
                    if ($product) {
                        $newProductStock = (int)$product['current_stock'] + $stockDifference;
                        // Ensure stock doesn't go below zero for safety (though stock-in usually adds)
                        if ($newProductStock < 0) $newProductStock = 0;
                        $updateProductResult = $this->productModel->update($newProductId, ['current_stock' => $newProductStock]);
                        log_message('debug', 'StockIn::update - Same Product Stock update. Old: ' . $oldQuantity . ', New: ' . $newQuantity . ', Diff: ' . $stockDifference . ', New Total Product Stock: ' . $newProductStock . ', Result: ' . ($updateProductResult ? 'Success' : 'Failed'));

                        if (!$updateProductResult) {
                            $this->db->transRollback();
                            $dbError = $this->db->error();
                            log_message('error', 'StockIn::update - Failed to adjust stock for product ID ' . $newProductId . '. DB Error: ' . json_encode($dbError));
                            return redirect()->back()->withInput()->with('error', 'Failed to adjust product stock. Please try again.');
                        }
                    } else {
                        $this->db->transRollback();
                        log_message('error', 'StockIn::update - Product ID ' . $newProductId . ' not found for stock adjustment during same product update.');
                        return redirect()->back()->withInput()->with('error', 'Product not found for stock adjustment. Please check the selected product.');
                    }
                } else {
                    // Product ID changed: subtract from old product, add to new product
                    $oldProduct = $this->productModel->find($oldProductId);
                    if ($oldProduct) {
                        $oldProductNewStock = (int)$oldProduct['current_stock'] - $oldQuantity;
                        if ($oldProductNewStock < 0) $oldProductNewStock = 0; // Prevent negative stock
                        $updateOldProductResult = $this->productModel->update($oldProductId, ['current_stock' => $oldProductNewStock]);
                        log_message('debug', 'StockIn::update - Old Product Stock decrease. Old ID: ' . $oldProductId . ', New Stock: ' . $oldProductNewStock . ', Result: ' . ($updateOldProductResult ? 'Success' : 'Failed'));
                        if (!$updateOldProductResult) {
                            $this->db->transRollback();
                            $dbError = $this->db->error();
                            log_message('error', 'StockIn::update - Failed to decrease stock for old product ID ' . $oldProductId . '. DB Error: ' . json_encode($dbError));
                            return redirect()->back()->withInput()->with('error', 'Failed to update old product stock. Please try again.');
                        }
                    } else {
                        log_message('warning', 'StockIn::update - Old Product ID ' . $oldProductId . ' not found for stock adjustment (might have been deleted).');
                        // Continue, as this might be an acceptable state if product was truly removed
                    }

                    $newProduct = $this->productModel->find($newProductId);
                    if ($newProduct) {
                        $newProductNewStock = (int)$newProduct['current_stock'] + $newQuantity;
                        $updateNewProductResult = $this->productModel->update($newProductId, ['current_stock' => $newProductNewStock]);
                        log_message('debug', 'StockIn::update - New Product Stock increase. New ID: ' . $newProductId . ', New Stock: ' . $newProductNewStock . ', Result: ' . ($updateNewProductResult ? 'Success' : 'Failed'));
                        if (!$updateNewProductResult) {
                            $this->db->transRollback();
                            $dbError = $this->db->error();
                            log_message('error', 'StockIn::update - Failed to increase stock for new product ID ' . $newProductId . '. DB Error: ' . json_encode($dbError));
                            return redirect()->back()->withInput()->with('error', 'Failed to update new product stock. Please try again.');
                        }
                    } else {
                        $this->db->transRollback();
                        log_message('error', 'StockIn::update - New Product ID ' . $newProductId . ' not found for stock adjustment.');
                        return redirect()->back()->withInput()->with('error', 'New product not found for stock adjustment. Please check the selected product.');
                    }
                }

                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    $dbError = $this->db->error();
                    log_message('error', 'StockIn::update - Transaction final status is FALSE (before commit). DB Error: ' . json_encode($dbError) . ', ID: ' . $id);
                    return redirect()->back()->withInput()->with('error', 'An internal database transaction error occurred during update. Please check system logs.');
                } else {
                    $this->db->transCommit();
                    log_message('debug', 'StockIn::update - Transaction Committed Successfully for update. ID: ' . $id);
                    return redirect()->to(base_url('stock-in/view/' . $id))->with('success', 'Stock In entry updated successfully and product inventory adjusted.');
                }
            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'StockIn::update - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
                return redirect()->back()->withInput()->with('error', 'An unexpected system error occurred during update: ' . $e->getMessage());
            }
        }

        // --- MODIFIED DELETE METHOD ---
        public function delete($id = null): ResponseInterface // Add ResponseInterface type hint
        {
            if ($id === null) {
                return redirect()->to('/stock-in')->with('error', 'No Stock In ID provided for deletion.');
            }

            // First, retrieve the stock_in record to get product_id and quantity
            $stockIn = $this->stockInModel->find($id);

            if (!$stockIn) {
                return redirect()->to('/stock-in')->with('error', 'Stock In record not found for deletion.');
            }

            $productId = (int)$stockIn['product_id'];
            $quantityRemoved = (int)$stockIn['quantity'];

            $this->db->transBegin(); // Start transaction

            try {
                // 1. Delete the stock_in record
                $deleteStockInResult = $this->stockInModel->delete($id);
                log_message('debug', 'StockIn::delete - StockIn record delete result: ' . ($deleteStockInResult ? 'Success' : 'Failed') . ', ID: ' . $id);

                if (!$deleteStockInResult) {
                    $this->db->transRollback();
                    $dbError = $this->db->error();
                    log_message('error', 'StockIn::delete - Failed to delete stock_in record. DB Error: ' . json_encode($dbError) . ', ID: ' . $id);
                    return redirect()->to('/stock-in')->with('error', 'Failed to delete stock in record.');
                }

                // 2. Decrease the product's current_stock in the products table
                $product = $this->productModel->find($productId);
                if ($product) {
                    $newProductStock = (int)$product['current_stock'] - $quantityRemoved;
                    // Ensure stock doesn't go below zero
                    if ($newProductStock < 0) $newProductStock = 0;

                    $updateProductResult = $this->productModel->update($productId, ['current_stock' => $newProductStock]);
                    log_message('debug', 'StockIn::delete - Product stock decrease attempt. Product ID: ' . $productId . ', Quantity Removed: ' . $quantityRemoved . ', New Stock: ' . $newProductStock . ', Result: ' . ($updateProductResult ? 'Success' : 'Failed'));

                    if (!$updateProductResult) {
                        $this->db->transRollback();
                        $dbError = $this->db->error();
                        log_message('error', 'StockIn::delete - Failed to decrease product stock after delete. Product ID: ' . $productId . ', DB Error: ' . json_encode($dbError));
                        return redirect()->to('/stock-in')->with('error', 'Failed to adjust product stock after deleting record.');
                    }
                } else {
                    log_message('warning', 'StockIn::delete - Product ID ' . $productId . ' not found for stock adjustment during delete (might have been deleted).');
                    // Continue even if product not found, as the main record was deleted
                }

                // Check final transaction status before committing
                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    $dbError = $this->db->error();
                    log_message('error', 'StockIn::delete - Transaction final status is FALSE (before commit). DB Error: ' . json_encode($dbError) . ', ID: ' . $id);
                    return redirect()->to('/stock-in')->with('error', 'An internal database transaction error occurred during deletion. Please check system logs.');
                } else {
                    $this->db->transCommit(); // Commit the transaction
                    log_message('debug', 'StockIn::delete - Transaction Committed Successfully for deletion. ID: ' . $id);
                    return redirect()->to('/stock-in')->with('success', 'Stock In record deleted successfully and product inventory adjusted.');
                }
            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'StockIn::delete - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
                return redirect()->to('/stock-in')->with('error', 'An unexpected system error occurred during deletion: ' . $e->getMessage());
            }
        }

    public function exportToExcel($id = null)
    {
        if ($id === null) {
            return redirect()->to('/stock-in')->with('error', 'No Stock In ID provided for Excel export.');
        }

        $builder = $this->stockInModel->builder();
        $builder->select('
            stock_in.*, 
            products.name as product_name, 
            units.name as unit_name, 
            vendors.agency_name as vendor_agency_name, 
            vendors.name as vendor_name,
            gst_rates.name as gst_rate_name,  
            (gst_rates.rate * 100) as gst_rate_percentage 
        ');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id', 'left');
        $builder->join('gst_rates', 'gst_rates.id = stock_in.gst_rate_id', 'left');
        $builder->where('stock_in.id', $id);

        $stockEntry = $builder->get()->getRowArray();

        if (empty($stockEntry)) {
            return redirect()->to('/stock-in')->with('error', 'Stock In entry not found for Excel export.');
        }

        // Fetch related payments
        $stockPayments = $this->stockInPaymentModel->where('stock_in_id', $id)->orderBy('payment_date', 'asc')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock In Details - ID ' . $id);

        // Header
        $sheet->setCellValue('A1', 'Stock In Details');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Data for main Stock In Entry
        $row = 3;
        $sheet->setCellValue('A' . $row, 'ID:');
        $sheet->setCellValue('B' . $row, $stockEntry['id']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Date Received:');
        $sheet->setCellValue('B' . $row, $stockEntry['date_received']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Vendor:');
        $sheet->setCellValue('B' . $row, $stockEntry['vendor_agency_name'] . ' (' . $stockEntry['vendor_name'] . ')');
        $row++;
        $sheet->setCellValue('A' . $row, 'Product:');
        $sheet->setCellValue('B' . $row, $stockEntry['product_name']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Quantity:');
        $sheet->setCellValue('B' . $row, $stockEntry['quantity'] . ' ' . $stockEntry['unit_name']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Purchase Price (per unit):');
        $sheet->setCellValue('B' . $row, (float)($stockEntry['purchase_price'] ?? 0)); // Ensure numeric for Excel
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00'); // Format as currency
        $row++;
        $sheet->setCellValue('A' . $row, 'GST Rate:');
        $sheet->setCellValue('B' . $row, ($stockEntry['gst_rate_name'] ?? 'N/A') . ' (' . ($stockEntry['gst_rate_percentage'] ?? '0') . '%)');
        $row++;
        $sheet->setCellValue('A' . $row, 'Sub Total (before GST):');
        $sheet->setCellValue('B' . $row, (float)($stockEntry['total_amount_before_gst'] ?? 0));
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;
        $sheet->setCellValue('A' . $row, 'GST Amount:');
        $sheet->setCellValue('B' . $row, (float)($stockEntry['gst_amount'] ?? 0));
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;
        $sheet->setCellValue('A' . $row, 'Grand Total (incl. GST):');
        $sheet->setCellValue('B' . $row, (float)($stockEntry['grand_total'] ?? 0));
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;
        $sheet->setCellValue('A' . $row, 'Amount Paid:');
        $sheet->setCellValue('B' . $row, (float)($stockEntry['amount_paid'] ?? 0));
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;
        $sheet->setCellValue('A' . $row, 'Amount Pending:');
        $sheet->setCellValue('B' . $row, (float)($stockEntry['amount_pending'] ?? 0));
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;
        $sheet->setCellValue('A' . $row, 'Notes:');
        $sheet->setCellValue('B' . $row, $stockEntry['notes']);
        $row++;

        // Add a gap
        $row++;

        // Payment Transactions Header
        $sheet->setCellValue('A' . $row, 'Payment Transactions');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;

        // Payment Table Headers
        $sheet->setCellValue('A' . $row, 'S.No.');
        $sheet->setCellValue('B' . $row, 'Payment Date');
        $sheet->setCellValue('C' . $row, 'Amount');
        $sheet->setCellValue('D' . $row, 'Notes');
        $sheet->setCellValue('E' . $row, 'Recorded At');
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $row++;

        // Payment Data
        if (empty($stockPayments)) {
            $sheet->setCellValue('A' . $row, 'No payments recorded for this entry yet.');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        } else {
            $sno = 1;
            foreach ($stockPayments as $payment) {
                $sheet->setCellValue('A' . $row, $sno++);
                $sheet->setCellValue('B' . $row, $payment['payment_date']);
                $sheet->setCellValue('C' . $row, (float)$payment['payment_amount']);
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('D' . $row, $payment['notes']);
                $sheet->setCellValue('E' . $row, $payment['created_at']);
                $row++;
            }
        }

        // Auto-size columns for payment section as well
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        $filename = 'stock_in_details_' . $id . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportToPdf($id = null)
    {
        if ($id === null) {
            return redirect()->to('/stock-in')->with('error', 'No Stock In ID provided for PDF export.');
        }

        $builder = $this->stockInModel->builder();
        $builder->select('
            stock_in.*, 
            products.name as product_name, 
            units.name as unit_name, 
            vendors.agency_name as vendor_agency_name, 
            vendors.name as vendor_name,
            gst_rates.name as gst_rate_name,  
            (gst_rates.rate * 100) as gst_rate_percentage 
        ');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('vendors', 'vendors.id = stock_in.vendor_id', 'left');
        $builder->join('gst_rates', 'gst_rates.id = stock_in.gst_rate_id', 'left');
        $builder->where('stock_in.id', $id);

        $data['stock_entry'] = $builder->get()->getRowArray();

        if (empty($data['stock_entry'])) {
            return redirect()->to('/stock-in')->with('error', 'Stock In entry not found for PDF export.');
        }

        // Fetch related payments for the PDF template
        $data['stock_payments'] = $this->stockInPaymentModel->where('stock_in_id', $id)->orderBy('payment_date', 'asc')->findAll();

        // Render the view as HTML content for Dompdf
        $html = view('stock_in/pdf_template', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Enable loading remote assets if any (e.g., images)
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'stock_in_details_' . $id . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]); // true to force download, false to open in browser
        exit;
    }

    public function storePayment()
    {
        $rules = [
            'stock_in_id'    => 'required|integer',
            'payment_amount' => 'required|numeric|greater_than[0]',
            'payment_date'   => 'required|valid_date',
            'notes'          => 'permit_empty|string|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $stockInId = $this->request->getPost('stock_in_id');
        $paymentAmount = $this->request->getPost('payment_amount');
        $paymentDate = $this->request->getPost('payment_date');
        $notes = $this->request->getPost('notes');

        $data = [
            'stock_in_id'    => $stockInId,
            'payment_amount' => $paymentAmount,
            'payment_date'   => $paymentDate,
            'notes'          => $notes,
            'created_at'     => date('Y-m-d H:i:s') // Manually add timestamp as useTimestamps is false
        ];

        // Start transaction for payment as well, just in case payment update callbacks also modify stock_in
        $this->db->transBegin();
        try {
            if ($this->stockInPaymentModel->save($data)) {
                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    session()->setFlashdata('error', 'Failed to add payment due to database transaction error.');
                    return redirect()->back()->withInput();
                } else {
                    $this->db->transCommit();
                    session()->setFlashdata('success', 'Payment added successfully!');
                    return redirect()->to(base_url('stock-in/view/' . $stockInId));
                }
            } else {
                $this->db->transRollback(); // Rollback if save itself fails
                session()->setFlashdata('error', 'Failed to add payment. Please try again.');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::storePayment - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected system error occurred while adding payment: ' . $e->getMessage());
        }
    }

    public function addPayment($stockInId = null)
    {
        if ($stockInId === null) {
            return redirect()->to('/stock-in')->with('error', 'No Stock In ID provided for adding payment.');
        }

        // Validate the incoming payment data
        $rules = [
            'new_payment_amount' => 'required|numeric|greater_than[0]',
            'new_payment_date'   => 'required|valid_date',
            'new_payment_notes'  => 'permit_empty|string|max_length[255]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $paymentAmount = (float)$this->request->getPost('new_payment_amount');
        $paymentDate   = $this->request->getPost('new_payment_date');
        $paymentNotes  = $this->request->getPost('new_payment_notes');

        // Check if amount to be paid exceeds pending amount
        $stockInEntry = $this->stockInModel->find($stockInId);
        if (empty($stockInEntry)) {
            return redirect()->back()->with('error', 'Stock In entry not found.');
        }

        $amountPending = $stockInEntry['grand_total'] - $stockInEntry['amount_paid'];
        if ($paymentAmount > $amountPending + 0.01) { // Add a small tolerance for floating point
            return redirect()->back()->withInput()->with('error', 'Payment amount exceeds the remaining pending amount.');
        }

        $paymentData = [
            'stock_in_id'    => $stockInId,
            'payment_amount' => $paymentAmount,
            'payment_date'   => $paymentDate,
            'notes'          => $paymentNotes
        ];

        // Start transaction for addPayment too
        $this->db->transBegin();
        try {
            if ($this->stockInPaymentModel->save($paymentData)) {
                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    return redirect()->to('/stock-in/edit/' . $stockInId)->with('error', 'Failed to record payment due to database transaction error.');
                } else {
                    $this->db->transCommit();
                    return redirect()->to('/stock-in/edit/' . $stockInId)->with('success', 'Payment recorded successfully.');
                }
            } else {
                $this->db->transRollback(); // Rollback if save itself fails
                return redirect()->to('/stock-in/edit/' . $stockInId)->with('error', 'Failed to record payment. Please try again.');
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::addPayment - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->to('/stock-in/edit/' . $stockInId)->with('error', 'An unexpected system error occurred while adding payment: ' . $e->getMessage());
        }
    }

    public function editPayment($paymentId)
    {
        $payment = $this->stockInPaymentModel->find($paymentId);

        if (empty($payment)) {
            session()->setFlashdata('error', 'Payment entry not found.');
            return redirect()->back();
        }

        $data = [
            'payment' => $payment,
            'title'   => 'Edit Payment'
        ];

        return view('stock_in/payment_edit', $data);
    }

    public function updatePayment($paymentId)
    {
        $rules = [
            'payment_amount' => 'required|numeric|greater_than[0]',
            'payment_date'   => 'required|valid_date',
            'notes'          => 'permit_empty|string|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payment = $this->stockInPaymentModel->find($paymentId);
        if (empty($payment)) {
            session()->setFlashdata('error', 'Payment entry not found for update.');
            return redirect()->back();
        }

        $data = [
            'payment_amount' => $this->request->getPost('payment_amount'),
            'payment_date'   => $this->request->getPost('payment_date'),
            'notes'          => $this->request->getPost('notes'),
        ];

        // Start transaction for updatePayment
        $this->db->transBegin();
        try {
            if ($this->stockInPaymentModel->update($paymentId, $data)) {
                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    session()->setFlashdata('error', 'Failed to update payment due to database transaction error.');
                    return redirect()->back()->withInput();
                } else {
                    $this->db->transCommit();
                    session()->setFlashdata('success', 'Payment updated successfully!');
                    return redirect()->to(base_url('stock-in/view/' . $payment['stock_in_id']));
                }
            } else {
                $this->db->transRollback(); // Rollback if update itself fails
                session()->setFlashdata('error', 'Failed to update payment. Please try again.');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::updatePayment - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected system error occurred while updating payment: ' . $e->getMessage());
        }
    }

    public function deletePayment($paymentId)
    {
        $payment = $this->stockInPaymentModel->find($paymentId);
        if (empty($payment)) {
            session()->setFlashdata('error', 'Payment entry not found for deletion.');
            return redirect()->back();
        }

        $stockInId = $payment['stock_in_id']; // Get parent ID before deleting

        // Start transaction for deletePayment
        $this->db->transBegin();
        try {
            if ($this->stockInPaymentModel->delete($paymentId)) {
                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    session()->setFlashdata('error', 'Failed to delete payment due to database transaction error.');
                    return redirect()->back();
                } else {
                    $this->db->transCommit();
                    session()->setFlashdata('success', 'Payment deleted successfully!');
                    return redirect()->to(base_url('stock-in/view/' . $stockInId));
                }
            } else {
                $this->db->transRollback(); // Rollback if delete itself fails
                session()->setFlashdata('error', 'Failed to delete payment. Please try again.');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::deletePayment - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected system error occurred while deleting payment: ' . $e->getMessage());
        }
    }
}
