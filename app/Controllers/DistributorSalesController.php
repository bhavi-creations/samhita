<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DistributorSalesOrderModel;
use App\Models\DistributorSalesOrderItemModel;
use App\Models\DistributorPaymentModel;
use App\Models\DistributorModel;
use App\Models\ProductModel;
use App\Models\GstRateModel;
use App\Models\StockOutModel; 

// For PDF
use Dompdf\Dompdf;
use Dompdf\Options;

// For Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class DistributorSalesController extends BaseController
{
    protected $distributorSalesOrderModel;
    protected $distributorSalesOrderItemModel;
    protected $distributorPaymentModel;
    protected $distributorModel;
    protected $productModel;
    protected $gstRateModel;
    protected $stockOutModel; 
    protected $db; 

    public function __construct()
    {
        $this->distributorSalesOrderModel     = new DistributorSalesOrderModel();
        $this->distributorSalesOrderItemModel = new DistributorSalesOrderItemModel();
        $this->distributorPaymentModel        = new DistributorPaymentModel();
        $this->distributorModel               = new DistributorModel();
        $this->productModel                   = new ProductModel();
        $this->gstRateModel                   = new GstRateModel();
        $this->stockOutModel                  = new StockOutModel(); 
        $this->db = \Config\Database::connect(); 
        helper('number'); 
    }

    public function index()
    {
        $data = [
            'title'        => 'Distributor Sales Orders',
            'sales_orders' => $this->distributorSalesOrderModel
                ->select('distributor_sales_orders.*, distributors.agency_name')
                ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
                ->orderBy('invoice_date', 'DESC') // Sort by most recent invoice date first
                ->orderBy('distributor_sales_orders.id', 'DESC') // Then by ID (newest ID first for same date)
                ->findAll(),
        ];
        return view('distributorsales/index', $data);
    }

    public function create()
    {
        $data = [
            'title'           => 'Create New Distributor Sales Order',
            'distributors'    => $this->distributorModel->findAll(),
            'products'        => $this->productModel->findAll(),
            'gst_rates'       => $this->gstRateModel->findAll(),
            'paymentMethods'  => [
                'Cash' => 'Cash',
                'Credit' => 'Credit',
                'UPI' => 'UPI',
                'Bank Transfer' => 'Bank Transfer'
            ],
            'validation'      => \Config\Services::validation(),
            'default_discount_amount' => 0.00,
        ];
        return view('distributorsales/new', $data);
    }

    /**
     * Handles the creation of a new distributor sales order.
     * Decrements product stock and creates corresponding stock_out records.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'distributor_id'            => 'required|integer',
            'order_date'                => 'required|valid_date',
            'payment_type'              => 'required|max_length[50]',
            'invoice_number'            => 'permit_empty|max_length[100]',
            'notes'                     => 'permit_empty|max_length[500]',
            'products.*.product_id'     => 'required|integer',
            'products.*.quantity'       => 'required|integer|greater_than[0]',
            'products.*.gst_rate_id'    => 'required|integer',
            'initial_payment_amount'    => 'permit_empty|numeric|greater_than_equal_to[0]',
            'discount_amount'           => 'permit_empty|numeric|greater_than_equal_to[0]',
            'transaction_id'            => 'permit_empty|max_length[100]',
            'payment_notes'             => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'DistributorSales::store - Validation failed. Errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $distributorId = $this->request->getPost('distributor_id');
        $orderDate = $this->request->getPost('order_date');
        $paymentType = $this->request->getPost('payment_type');
        $notes = $this->request->getPost('notes');

        $postedProducts = $this->request->getPost('products');

        $totalAmountBeforeGst = 0;
        $totalGstAmount = 0;
        $salesOrderItemsData = [];

        // --- Stock Pre-Check for New Sales Order (Initial Quantity) ---
        foreach ($postedProducts as $itemKey => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['gst_rate_id'])) {
                log_message('error', 'Incomplete product item data received for key: ' . $itemKey);
                return redirect()->back()->withInput()->with('error', 'One or more product items are incomplete.');
            }

            $productId = $item['product_id'];
            $quantity = (int) $item['quantity'];
            $gstRateId = $item['gst_rate_id'];

            try {
                $product = $this->productModel->find($productId);
                if (!$product) { // This handles both null (not found) and false (DB error)
                    $dbError = $this->db->error();
                    if ($dbError['code'] !== 0) {
                         log_message('critical', 'DistributorSales::store - DB Error fetching product ' . $productId . ': ' . $dbError['message'] . ' (Code: ' . $dbError['code'] . ')');
                         throw new \Exception('Database error fetching product details.');
                    }
                    log_message('error', 'DistributorSales::store - Product not found for ID: ' . $productId);
                    return redirect()->back()->withInput()->with('error', 'Invalid product selected. Please refresh and try again.');
                }
            } catch (\Exception $e) {
                log_message('error', 'DistributorSales::store - Exception during product lookup: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while fetching product details: ' . $e->getMessage());
            }

            try {
                $gstRate = $this->gstRateModel->find($gstRateId);
                if (!$gstRate) { // This handles both null (not found) and false (DB error)
                    $dbError = $this->db->error();
                    if ($dbError['code'] !== 0) {
                        log_message('critical', 'DistributorSales::store - DB Error fetching GST rate ' . $gstRateId . ': ' . $dbError['message'] . ' (Code: ' . $dbError['code'] . ')');
                        throw new \Exception('Database error fetching GST rate details.');
                    }
                    log_message('error', 'DistributorSales::store - GST rate not found for ID: ' . $gstRateId);
                    return redirect()->back()->withInput()->with('error', 'Invalid GST rate selected. Please refresh and try again.');
                }
            } catch (\Exception $e) {
                log_message('error', 'DistributorSales::store - Exception during GST rate lookup: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while fetching GST rate details: ' . $e->getMessage());
            }

            // Check available stock (from products.current_stock)
            if ($quantity > $product['current_stock']) {
                log_message('warning', 'DistributorSales::store - Insufficient stock for product ID: ' . $productId . '. Required: ' . $quantity . ', Available: ' . $product['current_stock']);
                return redirect()->back()->withInput()->with('error', 'Not enough stock available for "' . esc($product['name']) . '". Requested: ' . $quantity . ', Available: ' . $product['current_stock'] . ' ' . esc($product['unit_name']) . '.');
            }

            $unitPriceAtSale = (float) $product['selling_price'];
            $gstRateAtSale = (float) $gstRate['rate'];

            $itemTotalBeforeGst = $quantity * $unitPriceAtSale;
            $itemGstAmount = ($itemTotalBeforeGst * $gstRateAtSale) / 100;
            $itemFinalTotal = $itemTotalBeforeGst + $itemGstAmount;

            $totalAmountBeforeGst += $itemTotalBeforeGst;
            $totalGstAmount += $itemGstAmount;

            $salesOrderItemsData[] = [
                'product_id'             => $productId,
                'gst_rate_id'            => $gstRateId,
                'quantity'               => $quantity,
                'unit_price_at_sale'     => $unitPriceAtSale,
                'gst_rate_at_sale'       => $gstRateAtSale,
                'item_total_before_gst'  => $itemTotalBeforeGst,
                'item_gst_amount'        => $itemGstAmount,
                'item_final_total'       => $itemFinalTotal,
            ];
        }

        $discountAmount = (float) $this->request->getPost('discount_amount');
        $grossTotal = $totalAmountBeforeGst + $totalGstAmount;
        $finalTotalAmount = $grossTotal - $discountAmount;

        if ($finalTotalAmount < 0) {
            $finalTotalAmount = 0;
        }

        $initialPaymentAmount = (float) $this->request->getPost('initial_payment_amount');
        $amountPaid = $initialPaymentAmount;
        $dueAmount = $finalTotalAmount - $amountPaid;
        $status = ($dueAmount <= 0) ? 'Paid' : (($amountPaid > 0) ? 'Partially Paid' : 'Pending');

        // --- Invoice Number Generation Logic ---
        $currentDatePart = date('Ymd', strtotime($orderDate));
        $prefix = 'INV-';

        $lastInvoice = $this->distributorSalesOrderModel->orderBy('id', 'DESC')->first();
        $invoiceSeq = 1;

        if ($lastInvoice && !empty($lastInvoice['invoice_number'])) {
            if (preg_match('/-' . date('Ymd', strtotime($lastInvoice['invoice_date'])) . '-(\d+)$/', $lastInvoice['invoice_number'], $matches)) {
                $lastSeq = (int) $matches[1];
                $invoiceSeq = $lastSeq + 1;
            } else {
                if (preg_match('/-(\d+)$/', $lastInvoice['invoice_number'], $matches)) {
                    $lastSeq = (int) $matches[1];
                    $invoiceSeq = $lastSeq + 1;
                }
                log_message('warning', 'DistributorSales::store - Last invoice number format might be inconsistent for sequence extraction: ' . $lastInvoice['invoice_number']);
            }
        }
        $invoiceNumber = $prefix . $currentDatePart . '-' . str_pad($invoiceSeq, 5, '0', STR_PAD_LEFT);
        // --- END Invoice Number Generation Logic ---

        $this->db->transStart(); 

        try {
            $salesOrderData = [
                'distributor_id'            => $distributorId,
                'invoice_number'            => $invoiceNumber,
                'invoice_date'              => $orderDate,
                'total_amount_before_gst'   => $totalAmountBeforeGst,
                'total_gst_amount'          => $totalGstAmount,
                'final_total_amount'        => $finalTotalAmount,
                'discount_amount'           => $discountAmount,
                'amount_paid'               => $amountPaid,
                'due_amount'                => $dueAmount,
                'status'                    => $status,
                'notes'                     => $notes,
            ];

            $salesOrderId = $this->distributorSalesOrderModel->insert($salesOrderData);

            if (!$salesOrderId) {
                $dbErrors = $this->distributorSalesOrderModel->errors();
                throw new \Exception('Failed to save sales order: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            foreach ($salesOrderItemsData as $item) {
                $item['distributor_sales_order_id'] = $salesOrderId;
                $insertedItemId = $this->distributorSalesOrderItemModel->insert($item, true); 

                if (!$insertedItemId) {
                    $dbErrors = $this->distributorSalesOrderItemModel->errors();
                    throw new \Exception('Failed to save sales order item: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }

                // Decrement products.current_stock for each item sold
                try {
                    $product = $this->productModel->find($item['product_id']);
                    if ($product) {
                        $newStock = (int)$product['current_stock'] - (int)$item['quantity'];
                        if (!$this->productModel->update($item['product_id'], ['current_stock' => $newStock])) {
                            $dbError = $this->db->error();
                            throw new \Exception('Failed to decrement product stock for product ID ' . $item['product_id'] . ': ' . ($dbError['message'] ?? 'Unknown DB error.'));
                        }
                    } else {
                        throw new \Exception('Product not found for stock decrement for ID: ' . $item['product_id']);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'DistributorSales::store - Exception during product stock update: ' . $e->getMessage());
                    throw $e; // Re-throw to trigger rollback
                }

                // Create stock_out record linked to the specific sales order item
                try {
                    if (!$this->stockOutModel->insert([
                        'product_id'        => $item['product_id'],
                        'quantity_out'      => $item['quantity'],
                        'transaction_type'  => 'distributor_sale',
                        'transaction_id'    => $salesOrderId,
                        'transaction_item_id' => $insertedItemId, 
                        'issued_date'       => $orderDate, 
                        'notes'             => 'Distributor Sale for Invoice ' . $invoiceNumber . ', Item ID: ' . $insertedItemId,
                    ])) {
                        $modelErrors = $this->stockOutModel->errors();
                        throw new \Exception('Failed to record stock out for product ID ' . $item['product_id'] . ': ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
                    }
                } catch (\Exception $e) {
                    log_message('error', 'DistributorSales::store - Exception during stock out record insert: ' . $e->getMessage());
                    throw $e; // Re-throw to trigger rollback
                }
            }

            if ($initialPaymentAmount > 0) {
                $paymentData = [
                    'distributor_sales_order_id' => $salesOrderId,
                    'payment_date'               => date('Y-m-d'),
                    'amount'                     => $initialPaymentAmount,
                    'payment_method'             => $paymentType,
                    'transaction_id'             => $this->request->getPost('transaction_id'),
                    'notes'                      => $this->request->getPost('payment_notes'),
                ];
                if (!$this->distributorPaymentModel->insert($paymentData)) {
                    $dbErrors = $this->distributorPaymentModel->errors();
                    throw new \Exception('Failed to save initial payment: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'DistributorSales::store - Transaction failed after completion check. DB Error: ' . json_encode($dbError));
                throw new \Exception('Transaction failed during sales order creation. Please check system logs.');
            }

            log_message('info', 'DistributorSales::store - Sales Order ' . $invoiceNumber . ' created successfully! ID: ' . $salesOrderId);
            return redirect()->to('/distributor-sales')->with('success', 'Sales Order ' . $invoiceNumber . ' created successfully!');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'DistributorSales::store - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'Error creating sales order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $salesOrder = $this->distributorSalesOrderModel
            ->select('distributor_sales_orders.*, distributors.agency_name, distributors.agency_address, distributors.agency_gst_number, distributors.owner_name, distributors.owner_phone')
            ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
            ->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $salesOrderItems = $this->distributorSalesOrderItemModel
            ->select('distributor_sales_order_items.*, products.name as product_name')
            ->join('products', 'products.id = distributor_sales_order_items.product_id')
            ->where('distributor_sales_order_id', $id)
            ->findAll();

        $payments = $this->distributorPaymentModel
            ->where('distributor_sales_order_id', $id)
            ->orderBy('payment_date', 'ASC')
            ->findAll();

        $data = [
            'title'             => 'Sales Order Details: ' . $salesOrder['invoice_number'],
            'sales_order'       => $salesOrder,
            'sales_order_items' => $salesOrderItems,
            'payments'          => $payments,
        ];
        return view('distributorsales/show', $data);
    }

    public function edit($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $salesOrderItems = $this->distributorSalesOrderItemModel
            ->where('distributor_sales_order_id', $id)
            ->findAll();

        $data = [
            'title'             => 'Edit Sales Order: ' . $salesOrder['invoice_number'],
            'sales_order'       => $salesOrder,
            'sales_order_items' => $salesOrderItems,
            'distributors'      => $this->distributorModel->findAll(),
            'products'          => $this->productModel->findAll(),
            'gst_rates'         => $this->gstRateModel->findAll(),
            'validation'        => \Config\Services::validation(),
        ];
        return view('distributorsales/edit', $data);
    }

    /**
     * Handles the update of an existing distributor sales order.
     * Manages product stock (increment/decrement) and updates/creates/deletes
     * corresponding stock_out records to reflect changes to sales order items.
     *
     * @param int $id The ID of the sales order to update.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id): \CodeIgniter\HTTP\ResponseInterface
    {
        log_message('debug', 'DistributorSales::update - Updating sales order with ID: ' . $id);

        try {
            $salesOrder = $this->distributorSalesOrderModel->find($id);
            if (!$salesOrder) { // Handles both null (not found) and false (DB error)
                $dbError = $this->db->error();
                if ($dbError['code'] !== 0) {
                    log_message('critical', 'DistributorSales::update - DB Error fetching sales order ' . $id . ': ' . $dbError['message'] . ' (Code: ' . $dbError['code'] . ')');
                    throw new \Exception('Database error fetching sales order for update.');
                }
                log_message('error', 'DistributorSales::update - Attempted to update non-existent sales order with ID: ' . $id);
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        } catch (\Exception $e) {
            log_message('error', 'DistributorSales::update - Exception during initial sales order lookup: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while verifying the sales order: ' . $e->getMessage());
        }

        $submittedInvoiceNumber = $this->request->getPost('invoice_number');
        $originalInvoiceNumber = $salesOrder['invoice_number'];

        // Construct unique rule string for invoice number, allowing self-update
        $uniqueRuleString = 'is_unique[distributor_sales_orders.invoice_number,id,' . $id . ']';
        
        $rules = [
            'distributor_id'        => 'required|integer',
            'order_date'            => 'required|valid_date',
            'invoice_number'        => 'required|string|min_length[3]|max_length[50]|' . $uniqueRuleString,
            'notes'                 => 'permit_empty|max_length[500]',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity'   => 'required|integer|greater_than[0]',
            'products.*.gst_rate_id' => 'required|integer',
            'discount_amount'       => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'DistributorSales::update - Validation failed for ID: ' . $id . '. Errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'Please correct the form errors below.');
        }

        $distributorId = $this->request->getPost('distributor_id');
        $orderDate = $this->request->getPost('order_date');
        $invoiceNumber = $this->request->getPost('invoice_number');
        $notes = $this->request->getPost('notes');
        $discountAmount = (float) $this->request->getPost('discount_amount');
        $postedProducts = $this->request->getPost('products');

        $totalAmountBeforeGst = 0;
        $totalGstAmount = 0;
        $salesOrderItemsToProcess = []; 

        // Fetch original sales order items for comparison, mapped by their ID
        $originalSalesOrderItems = $this->distributorSalesOrderItemModel
            ->where('distributor_sales_order_id', $id)
            ->findAll();
        $originalItemsMap = []; 
        foreach ($originalSalesOrderItems as $item) {
            $originalItemsMap[$item['id']] = $item;
        }

        $itemIdsToKeep = []; 

        // --- PRE-CHECK: Stock availability for net increases and prepare item data for processing ---
        foreach ($postedProducts as $itemKey => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['gst_rate_id'])) {
                log_message('error', 'DistributorSales::update - Incomplete product item data received for key during update: ' . $itemKey);
                return redirect()->back()->withInput()->with('error', 'One or more product items are incomplete. Please ensure all product fields are selected and quantities are entered.');
            }

            $productId = $item['product_id'];
            $newQuantity = (int) $item['quantity'];
            $gstRateId = $item['gst_rate_id'];
            $itemId = $item['id'] ?? null; 

            try {
                $product = $this->productModel->find($productId);
                if (!$product) {
                    $dbError = $this->db->error();
                    if ($dbError['code'] !== 0) {
                        log_message('critical', 'DistributorSales::update - DB Error fetching product ' . $productId . ': ' . $dbError['message'] . ' (Code: ' . $dbError['code'] . ')');
                        throw new \Exception('Database error fetching product details.');
                    }
                    log_message('error', 'DistributorSales::update - Product not found during stock pre-check for ID: ' . $productId . ' in update.');
                    return redirect()->back()->withInput()->with('error', 'Selected product (ID: ' . esc($productId) . ') not found. Please refresh and try again.');
                }
            } catch (\Exception $e) {
                log_message('error', 'DistributorSales::update - Exception during product lookup (pre-check): ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while fetching product details for pre-check: ' . $e->getMessage());
            }

            try {
                $gstRate = $this->gstRateModel->find($gstRateId);
                if (!$gstRate) {
                    $dbError = $this->db->error();
                    if ($dbError['code'] !== 0) {
                        log_message('critical', 'DistributorSales::update - DB Error fetching GST rate ' . $gstRateId . ': ' . $dbError['message'] . ' (Code: ' . $dbError['code'] . ')');
                        throw new \Exception('Database error fetching GST rate details.');
                    }
                    log_message('error', 'DistributorSales::update - GST rate not found for ID: ' . $gstRateId . ' during update.');
                    return redirect()->back()->withInput()->with('error', 'Invalid GST rate selected for an item. Please refresh and try again.');
                }
            } catch (\Exception $e) {
                log_message('error', 'DistributorSales::update - Exception during GST rate lookup (pre-check): ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while fetching GST rate details for pre-check: ' . $e->getMessage());
            }
            
            // Calculate item amounts (needed for later saving)
            $unitPriceAtSale = (float) $product['selling_price'];
            $gstRateAtSale = (float) $gstRate['rate'];
            $itemTotalBeforeGst = $newQuantity * $unitPriceAtSale;
            $itemGstAmount = ($itemTotalBeforeGst * $gstRateAtSale) / 100;
            $itemFinalTotal = $itemTotalBeforeGst + $itemGstAmount;

            // Accumulate totals for the main sales order
            $totalAmountBeforeGst += $itemTotalBeforeGst;
            $totalGstAmount += $itemGstAmount;

            // Prepare item data for update/insert operations
            $itemToSave = [
                'distributor_sales_order_id' => $id,
                'product_id'                 => $productId,
                'gst_rate_id'                => $gstRateId,
                'quantity'                   => $newQuantity,
                'unit_price_at_sale'         => $unitPriceAtSale,
                'gst_rate_at_sale'           => $gstRateAtSale,
                'item_total_before_gst'      => $itemTotalBeforeGst,
                'item_gst_amount'            => $itemGstAmount,
                'item_final_total'           => $itemFinalTotal,
            ];

            // If the item has an ID (meaning it's an existing item from the DB)
            if ($itemId && isset($originalItemsMap[$itemId])) {
                $itemToSave['id'] = $itemId; 
                $itemIdsToKeep[] = $itemId; 
            }
            $salesOrderItemsToProcess[] = $itemToSave;
        }

        $grossTotal = $totalAmountBeforeGst + $totalGstAmount;
        $finalTotalAmount = $grossTotal - $discountAmount;

        if ($finalTotalAmount < 0) {
            $finalTotalAmount = 0;
        }

        $newDueAmount = $finalTotalAmount - ($salesOrder['amount_paid'] ?? 0);
        $newStatus = ($newDueAmount <= 0) ? 'Paid' : (($salesOrder['amount_paid'] > 0) ? 'Partially Paid' : 'Pending');

        $this->db->transStart(); 

        try {
            // --- Phase 1: Revert all old stock changes and delete existing stock_out records for this sales order ---
            // Get all existing stock_out records for this sales order BEFORE deleting items
            $existingStockOuts = $this->stockOutModel
                                      ->where('transaction_id', $id)
                                      ->where('transaction_type', 'distributor_sale')
                                      ->findAll();

            foreach ($existingStockOuts as $oldStockOutRecord) {
                $originalProductId = $oldStockOutRecord['product_id'];
                $originalQuantity = (int)$oldStockOutRecord['quantity_out'];

                // Increment product stock (return to inventory)
                $product = $this->productModel->find($originalProductId);
                if ($product) {
                    $newStock = (int)$product['current_stock'] + $originalQuantity;
                    if (!$this->productModel->update($originalProductId, ['current_stock' => $newStock])) {
                        $dbError = $this->db->error();
                        throw new \Exception('Failed to return stock for product ID ' . $originalProductId . ' during sales order update revert: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                    }
                } else {
                    log_message('error', 'DistributorSales::update - Product not found for ID: ' . $originalProductId . ' during stock return in update phase 1.');
                }
            }

            // Now, delete ALL stock_out records related to this distributor sales order
            if (!$this->stockOutModel->where('transaction_id', $id)
                                      ->where('transaction_type', 'distributor_sale')
                                      ->delete()) {
                $modelErrors = $this->stockOutModel->errors();
                throw new \Exception('Failed to delete old stock out records for sales order ID ' . $id . ': ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
            }

            // Delete all existing sales order items from distributor_sales_order_items table
            // This is done before re-inserting the new set of items
            if (!$this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->delete()) {
                $dbErrors = $this->distributorSalesOrderItemModel->errors();
                throw new \Exception('Failed to clear old sales order items: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown DB error.'));
            }

            // 1. Update the main sales order record
            $updateSalesOrderData = [
                'distributor_id'            => $distributorId,
                'invoice_number'            => $invoiceNumber,
                'invoice_date'              => $orderDate,
                'total_amount_before_gst'   => $totalAmountBeforeGst,
                'total_gst_amount'          => $totalGstAmount,
                'final_total_amount'        => $finalTotalAmount,
                'discount_amount'           => $discountAmount,
                'due_amount'                => $newDueAmount,
                'status'                    => $newStatus,
                'notes'                     => $notes,
            ];

            if (!$this->distributorSalesOrderModel->update($id, $updateSalesOrderData)) {
                $dbErrors = $this->distributorSalesOrderModel->errors();
                $errorMessage = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.';
                throw new \Exception('Failed to update sales order in database: ' . $errorMessage);
            }

            // --- Phase 2: Insert new sales order items and create corresponding stock_out records ---
            foreach ($salesOrderItemsToProcess as $itemData) {
                $productId = $itemData['product_id'];
                $newQuantity = (int) $itemData['quantity'];
                
                // Check current stock for deduction
                $currentProduct = $this->productModel->find($productId);
                if (!$currentProduct) {
                    throw new \Exception('Product ' . $productId . ' not found for stock deduction.');
                }

                if ($newQuantity > (int)$currentProduct['current_stock']) {
                     throw new \Exception('Insufficient stock for product "' . esc($currentProduct['name']) . '". Needed: ' . $newQuantity . ', Available: ' . (int)$currentProduct['current_stock']);
                }

                // Insert the new sales order item into distributor_sales_order_items
                // Note: We unset 'id' if it exists from a re-submitted old item so insert generates a new ID.
                $tempItemData = $itemData;
                unset($tempItemData['id']); 
                $newSalesOrderItemId = $this->distributorSalesOrderItemModel->insert($tempItemData, true); 
                
                if (!$newSalesOrderItemId) {
                    $dbErrors = $this->distributorSalesOrderItemModel->errors();
                    $errorMessage = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.';
                    throw new \Exception('Failed to insert new sales order item for product ID ' . $productId . ': ' . $errorMessage);
                }

                // Decrement product stock
                $updatedStock = (int)$currentProduct['current_stock'] - $newQuantity;
                if (!$this->productModel->update($productId, ['current_stock' => $updatedStock])) {
                    $dbError = $this->db->error();
                    throw new \Exception('Failed to decrement stock for product ID ' . $productId . ': ' . ($dbError['message'] ?? 'Unknown DB error.'));
                }

                // Create a NEW Stock Out record for this item
                if (!$this->stockOutModel->insert([
                    'product_id'        => $productId,
                    'quantity_out'      => $newQuantity,
                    'transaction_type'  => 'distributor_sale', 
                    'transaction_id'    => $id, // Link to the main sales order ID
                    'transaction_item_id' => $newSalesOrderItemId, // Link to the NEW sales order item ID
                    'issued_date'       => $orderDate,
                    'notes'             => 'Distributor Sale for Invoice ' . $invoiceNumber . ', Item ID: ' . $newSalesOrderItemId,
                ])) {
                    $modelErrors = $this->stockOutModel->errors();
                    throw new \Exception('Failed to record stock out for product ID ' . $productId . ': ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
                }
            }

            $this->db->transComplete(); 

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'DistributorSales::update - Database transaction failed during sales order update. DB Error: ' . json_encode($dbError));
                throw new \Exception('Database transaction failed during sales order update. Please check logs for details.');
            }

            log_message('info', 'DistributorSales::update - Sales Order ' . $salesOrder['invoice_number'] . ' with ID ' . $id . ' updated successfully.');
            return redirect()->to(base_url('distributor-sales/show/' . $id))->with('success', 'Sales Order ' . $salesOrder['invoice_number'] . ' updated successfully!');
        } catch (\Exception $e) {
            $this->db->transRollback(); 
            log_message('error', 'DistributorSales::update - Caught Exception during sales order update (ID: ' . $id . '): ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while updating the sales order: ' . $e->getMessage());
        }
    }

    public function addPayment($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'        => 'Add Payment for Invoice: ' . $salesOrder['invoice_number'],
            'sales_order'  => $salesOrder,
            'paymentMethods' => [
                'Cash'          => 'Cash',
                'Credit Card'   => 'Credit Card',
                'UPI'           => 'UPI',
                'Bank Transfer' => 'Bank Transfer',
                'Cheque'        => 'Cheque',
            ],
            'validation'   => \Config\Services::validation(),
        ];
        return view('distributorsales/add_payment', $data);
    }

    public function savePayment()
    {
        $rules = [
            'sales_order_id' => 'required|integer',
            'payment_date'   => 'required|valid_date',
            'amount'         => 'required|numeric|greater_than[0]',
            'payment_method' => 'permit_empty|max_length[50]',
            'transaction_id' => 'permit_empty|max_length[100]',
            'notes'          => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $salesOrderId = $this->request->getPost('sales_order_id');
        $paymentAmount = (float) $this->request->getPost('amount');

        $salesOrder = $this->distributorSalesOrderModel->find($salesOrderId);

        if (!$salesOrder) {
            return redirect()->back()->with('error', 'Sales Order not found.');
        }

        if ($salesOrder['due_amount'] <= 0) {
            return redirect()->back()->with('error', 'This invoice is already fully paid.');
        }

        if ($paymentAmount > $salesOrder['due_amount']) {
            return redirect()->back()->with('error', 'Payment amount cannot exceed the due amount (₹' . number_format($salesOrder['due_amount'], 2) . ').');
        }

        $this->db->transStart();

        try {
            $paymentData = [
                'distributor_sales_order_id' => $salesOrderId,
                'payment_date'               => $this->request->getPost('payment_date'),
                'amount'                     => $paymentAmount,
                'payment_method'             => $this->request->getPost('payment_method'),
                'transaction_id'             => $this->request->getPost('transaction_id'),
                'notes'                      => $this->request->getPost('notes'),
            ];

            if (!$this->distributorPaymentModel->insert($paymentData)) {
                $dbErrors = $this->distributorPaymentModel->errors();
                throw new \Exception('Failed to save payment: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            $newAmountPaid = $salesOrder['amount_paid'] + $paymentAmount;
            $newDueAmount = $salesOrder['final_total_amount'] - $newAmountPaid;
            $newStatus = ($newDueAmount <= 0) ? 'Paid' : 'Partially Paid';

            $updateData = [
                'amount_paid' => $newAmountPaid,
                'due_amount'  => $newDueAmount,
                'status'      => $newStatus,
            ];

            if (!$this->distributorSalesOrderModel->update($salesOrderId, $updateData)) {
                $dbErrors = $this->distributorSalesOrderModel->errors();
                throw new \Exception('Failed to update sales order payment status: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'DistributorSales::savePayment - Transaction failed after completion check. DB Error: ' . json_encode($dbError));
                throw new \Exception('Transaction failed during payment save. Please check system logs.');
            }

            log_message('info', 'DistributorSales::savePayment - Payment recorded successfully for Sales Order ' . $salesOrderId);
            return redirect()->to('/distributor-sales/show/' . $salesOrderId)->with('success', 'Payment recorded successfully! Invoice status updated to ' . $newStatus . '.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'DistributorSales::savePayment - Error recording payment: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Handles the deletion of a distributor sales order.
     * Also returns product stock to inventory and deletes associated stock_out records.
     *
     * @param int $id The ID of the sales order to delete.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id): \CodeIgniter\HTTP\ResponseInterface // Fixed typo here: CodeIgneter -> CodeIgniter
    {
        $this->db->transStart();

        try {
            $salesOrder = $this->distributorSalesOrderModel->find($id);
            if (!$salesOrder) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Sales Order not found for ID: ' . $id);
            }

            // Fetch all items associated with this sales order BEFORE deleting them
            $salesOrderItems = $this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();

            // Delete associated payments
            if (!$this->distributorPaymentModel->where('distributor_sales_order_id', $id)->delete()) {
                $dbErrors = $this->distributorPaymentModel->errors();
                throw new \Exception('Failed to delete associated payments for sales order ID: ' . $id . '. Errors: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            // Delete associated sales order items
            if (!$this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->delete()) {
                $dbErrors = $this->distributorSalesOrderItemModel->errors();
                throw new \Exception('Failed to delete associated sales order items for sales order ID: ' . $id . '. Errors: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            // Delete the main sales order record
            if (!$this->distributorSalesOrderModel->delete($id)) {
                $dbErrors = $this->distributorSalesOrderModel->errors();
                throw new \Exception('Failed to delete sales order ID: ' . $id . '. Errors: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            // NEW: Adjust product stock for all items of the deleted sales order
            foreach ($salesOrderItems as $item) {
                $productId = $item['product_id'];
                $quantityReturned = (int)$item['quantity'];

                // Increment product stock (items are being returned to inventory)
                try {
                    $product = $this->productModel->find($productId);
                    if ($product) {
                        $newStock = (int)$product['current_stock'] + $quantityReturned;
                        if (!$this->productModel->update($productId, ['current_stock' => $newStock])) {
                            $dbError = $this->db->error();
                            throw new \Exception('Failed to increment stock for product ID ' . $productId . ' during sales order deletion: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                        }
                    } else {
                        log_message('error', 'DistributorSales::delete - Product (ID: ' . $productId . ') not found while attempting to return stock on sales order deletion.');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'DistributorSales::delete - Exception during product stock return for deletion: ' . $e->getMessage());
                    throw $e; 
                }
            }

            // NEW: Delete ALL corresponding stock_out records linked to this sales order ID
            if (!$this->stockOutModel->where('transaction_id', $id)
                                      ->where('transaction_type', 'distributor_sale')
                                      ->delete()) {
                $modelErrors = $this->stockOutModel->errors();
                throw new \Exception('Failed to delete associated stock out records for sales order ID ' . $id . ': ' . (!empty($modelErrors) ? implode(', ', $modelErrors) : 'Unknown error.'));
            }


            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'DistributorSales::delete - Transaction failed after completion check for sales order deletion. DB Error: ' . json_encode($dbError));
                throw new \Exception('Transaction failed during sales order deletion. Please check system logs.');
            }

            log_message('info', 'DistributorSales::delete - Sales Order ' . esc($salesOrder['invoice_number']) . ' with ID ' . $id . ' and all associated data deleted successfully.');
            return redirect()->to('/distributor-sales')->with('success', 'Sales Order ' . esc($salesOrder['invoice_number']) . ' and all associated data deleted successfully.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'DistributorSales::delete - Error deleting sales order (ID: ' . $id . '): ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->to('/distributor-sales')->with('error', 'Error deleting sales order: ' . $e->getMessage());
        }
    }

    public function exportIndexExcel()
    {
        $salesOrders = $this->distributorSalesOrderModel
            ->select('distributor_sales_orders.*, distributors.agency_name, distributors.owner_name')
            ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
            ->orderBy('invoice_date', 'DESC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers - ADD 'Discount Amount'
        $headers = ['ID', 'Invoice Number', 'Invoice Date', 'Distributor (Agency Name)', 'Distributor (Owner Name)', 'Total Before GST', 'Total GST', 'Final Total', 'Discount Amount', 'Amount Paid', 'Due Amount', 'Status', 'Notes', 'Created At', 'Updated At'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($salesOrders as $order) {
            $col = 'A';
            $sheet->setCellValue($col++ . $row, $order['id']);
            $sheet->setCellValue($col++ . $row, $order['invoice_number']);
            $sheet->setCellValue($col++ . $row, $order['invoice_date']);
            $sheet->setCellValue($col++ . $row, $order['agency_name']);
            $sheet->setCellValue($col++ . $row, $order['owner_name']);
            $sheet->setCellValue($col++ . $row, $order['total_amount_before_gst']);
            $sheet->setCellValue($col++ . $row, $order['total_gst_amount']);
            $sheet->setCellValue($col++ . $row, $order['final_total_amount']);
            $sheet->setCellValue($col++ . $row, $order['discount_amount']);
            $sheet->setCellValue($col++ . $row, $order['amount_paid']);
            $sheet->setCellValue($col++ . $row, $order['due_amount']);
            $sheet->setCellValue($col++ . $row, $order['status']);
            $sheet->setCellValue($col++ . $row, $order['notes']);
            $sheet->setCellValue($col++ . $row, $order['created_at']);
            $sheet->setCellValue($col++ . $row, $order['updated_at']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Distributor_Sales_Orders_Excel_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function exportIndexPdf()
    {
        $salesOrders = $this->distributorSalesOrderModel
            ->select('distributor_sales_orders.*, distributors.agency_name, distributors.owner_name')
            ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
            ->orderBy('invoice_date', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Distributor Sales Orders Report',
            'sales_orders' => $salesOrders,
            'currentDate'  => date('Y-m-d H:i:s')
        ];

        $html = view('distributorsales/index_pdf', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Distributor_Sales_Orders_Report_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => 1));
        exit;
    }

    public function exportInvoicePdf($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $salesOrderItems = $this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();
        $distributor = $this->distributorModel->find($salesOrder['distributor_id']);
        $payments = $this->distributorPaymentModel->where('distributor_sales_order_id', $id)->findAll();

        foreach ($salesOrderItems as &$item) {
            $product = $this->productModel->find($item['product_id']);
            $gstRate = $this->gstRateModel->find($item['gst_rate_id']);
            $item['product_name'] = $product['name'] ?? 'N/A';
            $item['product_unit_price'] = $product['selling_price'] ?? 0;
            $item['gst_rate_name'] = $gstRate['name'] ?? 'N/A';
            $item['gst_rate_percentage'] = $gstRate['rate'] ?? 0;

            $item['unit_price_at_sale'] = $item['unit_price_at_sale'] ?? $product['selling_price'];
            $item['gst_rate_at_sale'] = $item['gst_rate_at_sale'] ?? $gstRate['rate'];
        }
        unset($item);

        $data = [
            'title'             => 'Distributor Sales Invoice',
            'sales_order'       => $salesOrder,
            'sales_order_items' => $salesOrderItems,
            'distributor'       => $distributor,
            'payments'          => $payments,
            'currentDate'       => date('Y-m-d H:i:s')
        ];

        $html = view('distributorsales/invoice_pdf', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $invoiceNumber = $salesOrder['invoice_number'] ?? 'INV-' . $salesOrder['id'];
        $fileName = 'Distributor_Sales_Invoice_' . str_replace('/', '_', $invoiceNumber) . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => 1));
        exit;
    }

    public function exportInvoiceExcel($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $salesOrderItems = $this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();
        $distributor = $this->distributorModel->find($salesOrder['distributor_id']);
        $payments = $this->distributorPaymentModel->where('distributor_sales_order_id', $id)->findAll();

        foreach ($salesOrderItems as &$item) {
            $product = $this->productModel->find($item['product_id']);
            $gstRate = $this->gstRateModel->find($item['gst_rate_id']);
            $item['product_name'] = $product['name'] ?? 'N/A';
            $item['product_unit_price'] = $product['selling_price'] ?? 0;
            $item['gst_rate_name'] = $gstRate['name'] ?? 'N/A';
            $item['gst_rate_percentage'] = $gstRate['rate'] ?? 0;
        }
        unset($item);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice ' . $salesOrder['invoice_number']);

        $sheet->setCellValue('A1', 'Invoice: ' . $salesOrder['invoice_number']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue('A2', 'Date: ' . date('Y-m-d', strtotime($salesOrder['invoice_date'])));
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');

        $sheet->setCellValue('D1', 'Your Company Name');
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('D1:E1');
        $sheet->setCellValue('D2', 'Your Company Address');
        $sheet->mergeCells('D2:E2');
        $sheet->setCellValue('D3', 'GSTIN: Your Company GSTIN');
        $sheet->mergeCells('D3:E3');

        $current_row = 5;
        $sheet->setCellValue('A' . $current_row, 'Bill To:');
        $sheet->getStyle('A' . $current_row)->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($current_row + 1), 'Agency Name: ' . ($distributor['agency_name'] ?? 'N/A'));
        $sheet->setCellValue('A' . ($current_row + 2), 'Owner Name: ' . ($distributor['owner_name'] ?? 'N/A'));
        $sheet->setCellValue('A' . ($current_row + 3), 'Address: ' . ($distributor['agency_address'] ?? 'N/A'));
        $sheet->setCellValue('A' . ($current_row + 4), 'GSTIN: ' . ($distributor['agency_gst_number'] ?? 'N/A'));
        $sheet->setCellValue('A' . ($current_row + 5), 'Phone: ' . ($distributor['owner_phone'] ?? 'N/A'));

        $current_row += 7;

        $sheet->setCellValue('A' . $current_row, 'Invoice Items');
        $sheet->getStyle('A' . $current_row)->getFont()->setBold(true)->setSize(12);
        $current_row++;

        $headers_items = ['#', 'Product', 'Quantity', 'Unit Price (At Sale)', 'GST Rate (%)', 'Amount (Excl. GST)', 'GST Amount', 'Total (Incl. GST)'];
        $sheet->fromArray($headers_items, NULL, 'A' . $current_row);
        $sheet->getStyle('A' . $current_row . ':H' . $current_row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $current_row . ':H' . $current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');

        $current_row++;
        $item_no = 1;
        foreach ($salesOrderItems as $item) {
            $col = 'A';
            $sheet->setCellValue($col++ . $current_row, $item_no++);
            $sheet->setCellValue($col++ . $current_row, $item['product_name']);
            $sheet->setCellValue($col++ . $current_row, $item['quantity']);
            $sheet->setCellValue($col++ . $current_row, $item['unit_price_at_sale']);
            $sheet->setCellValue($col++ . $current_row, $item['gst_rate_at_sale']);
            $sheet->setCellValue($col++ . $current_row, $item['item_total_before_gst']);
            $sheet->setCellValue($col++ . $current_row, $item['item_gst_amount']);
            $sheet->setCellValue($col++ . $current_row, $item['item_final_total']);
            $current_row++;
        }

        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Subtotal (Excl. GST):');
        $sheet->setCellValue('G' . $current_row, $salesOrder['total_amount_before_gst']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true);
        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Total GST:');
        $sheet->setCellValue('G' . $current_row, $salesOrder['total_gst_amount']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true);
        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Gross Total:');
        $sheet->setCellValue('G' . $current_row, $salesOrder['total_amount_before_gst'] + $salesOrder['total_gst_amount']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true);
        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Discount Amount:');
        $sheet->setCellValue('G' . $current_row, $salesOrder['discount_amount']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true);
        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Final Total Amount:');
        $sheet->setCellValue('G' . $current_row, $salesOrder['final_total_amount']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true)->setSize(11);
        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Amount Paid:');
        $sheet->setCellValue('G' . $current_row, $salesOrder['amount_paid']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true);
        $current_row++;
        $sheet->setCellValue('F' . $current_row, 'Due Amount:');
        $sheet->setCellValue('G' . $current_row, $salesOrder['due_amount']);
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true)->getColor()->setARGB('FFD9534F');

        $current_row += 2;
        $amountInWords = convertNumberToWords($salesOrder['final_total_amount']) . ' Rupees Only.';
        $sheet->setCellValue('A' . $current_row, 'Amount in words: ' . $amountInWords);
        $sheet->getStyle('A' . $current_row)->getFont()->setItalic(true)->setBold(true);
        $sheet->mergeCells('A' . $current_row . ':H' . $current_row);

        $current_row += 2;
        $sheet->setCellValue('A' . $current_row, 'Payment History');
        $sheet->getStyle('A' . $current_row)->getFont()->setBold(true)->setSize(12);
        $current_row++;

        if (!empty($payments)) {
            $headers_payments = ['Payment Date', 'Amount', 'Method', 'Transaction ID', 'Notes'];
            $sheet->fromArray($headers_payments, NULL, 'A' . $current_row);
            $sheet->getStyle('A' . $current_row . ':E' . $current_row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $current_row . ':E' . $current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            $current_row++;
            foreach ($payments as $payment) {
                $col = 'A';
                $sheet->setCellValue($col++ . $current_row, date('Y-m-d', strtotime($payment['payment_date'])));
                $sheet->setCellValue($col++ . $current_row, $payment['amount']);
                $sheet->setCellValue($col++ . $current_row, $payment['payment_method'] ?? 'N/A');
                $sheet->setCellValue($col++ . $current_row, $payment['transaction_id'] ?? 'N/A');
                $sheet->setCellValue($col++ . $current_row, $payment['notes'] ?? 'N/A'); 
                $current_row++;
            }
        } else {
            $sheet->setCellValue('A' . $current_row, 'No payments recorded for this invoice yet.');
            $sheet->mergeCells('A' . $current_row . ':E' . $current_row);
            $current_row++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $currencyColumns = ['D', 'E', 'F', 'G', 'H'];
        foreach ($currencyColumns as $col) {
            for ($r = 10; $r < $current_row; $r++) {
                if ($r > 9 && $r < ($current_row - count($payments) - 3)) {
                    $sheet->getStyle($col . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                }
            }
        }
        $sheet->getStyle('G' . ($current_row - 1) . ':G' . ($current_row - 7))->getNumberFormat()->setFormatCode('#,##0.00');


        $writer = new Xlsx($spreadsheet);
        $invoiceNumber = $salesOrder['invoice_number'] ?? 'Invoice-' . $salesOrder['id'];
        $fileName = 'Distributor_Sales_Invoice_' . str_replace('/', '_', $invoiceNumber) . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
