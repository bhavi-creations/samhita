<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DistributorSalesOrderModel;
use App\Models\DistributorSalesOrderItemModel;
use App\Models\DistributorPaymentModel;
use App\Models\DistributorModel;
use App\Models\ProductModel;
use App\Models\GstRateModel;
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

    public function __construct()
    {
        $this->distributorSalesOrderModel     = new DistributorSalesOrderModel();
        $this->distributorSalesOrderItemModel = new DistributorSalesOrderItemModel();
        $this->distributorPaymentModel        = new DistributorPaymentModel();
        $this->distributorModel               = new DistributorModel();
        $this->productModel                   = new ProductModel();
        $this->gstRateModel                   = new GstRateModel();
        helper('number');
    }

    public function index()
    {
        $data = [
            'title'        => 'Distributor Sales Orders',
            'sales_orders' => $this->distributorSalesOrderModel
                ->select('distributor_sales_orders.*, distributors.agency_name')
                ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
                ->orderBy('invoice_date', 'DESC')
                ->findAll(),
        ];
        return view('distributorsales/index', $data);
    }

    public function create()
    {
        $data = [
            'title'          => 'Create New Distributor Sales Order',
            'distributors'   => $this->distributorModel->findAll(),
            'products'       => $this->productModel->findAll(),
            'gst_rates'      => $this->gstRateModel->findAll(),
            'paymentMethods' => [
                'Cash' => 'Cash',
                'Credit' => 'Credit',
                'UPI' => 'UPI',
                'Bank Transfer' => 'Bank Transfer'
            ],
            'validation'     => \Config\Services::validation(),
            'default_discount_amount' => 0.00, // <--- Added for create form
        ];
        return view('distributorsales/new', $data);
    }

    public function store()
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

        foreach ($postedProducts as $itemKey => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['gst_rate_id'])) {
                log_message('error', 'Incomplete product item data received for key: ' . $itemKey);
                return redirect()->back()->withInput()->with('error', 'One or more product items are incomplete.');
            }

            $productId = $item['product_id'];
            $quantity = (int) $item['quantity'];
            $gstRateId = $item['gst_rate_id'];

            $product = $this->productModel->find($productId);
            $gstRate = $this->gstRateModel->find($gstRateId);

            if (!$product) {
                log_message('error', 'Product not found for ID: ' . $productId);
                return redirect()->back()->withInput()->with('error', 'Invalid product selected. Please refresh and try again.');
            }

            if (!$gstRate) {
                log_message('error', 'GST rate not found for ID: ' . $gstRateId);
                return redirect()->back()->withInput()->with('error', 'Invalid GST rate selected. Please refresh and try again.');
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

        // --- START OF CORRECTED INVOICE NUMBER GENERATION LOGIC ---
        // Get the current date part for the invoice number
        $currentDatePart = date('Ymd', strtotime($orderDate));
        $prefix = 'INV-'; // Standard prefix

        // Get the highest sequence number from *all* existing invoices
        // Order by ID (assuming auto-incrementing ID implies chronological order of creation)
        $lastInvoice = $this->distributorSalesOrderModel->orderBy('id', 'DESC')->first();
        $invoiceSeq = 1; // Default starting sequence

        if ($lastInvoice && !empty($lastInvoice['invoice_number'])) {
            // Regex to extract the numeric part AFTER the date (e.g., '0003' from 'INV-20250620-0003')
            if (preg_match('/-' . date('Ymd', strtotime($lastInvoice['invoice_date'])) . '-(\d+)$/', $lastInvoice['invoice_number'], $matches)) {
                $lastSeq = (int) $matches[1];
                $invoiceSeq = $lastSeq + 1;
            } else {
                // Fallback: If the last invoice number doesn't match the expected date format,
                // try to find the highest number after "INV-" or "INV-YYYYMMDD-" if possible.
                // This handles cases where older formats might exist or if the date part changes.
                // For simplicity and robustness with your given pattern, we'll try to find any number
                // after the last hyphen, assuming it's the sequence.
                if (preg_match('/-(\d+)$/', $lastInvoice['invoice_number'], $matches)) {
                    $lastSeq = (int) $matches[1];
                    $invoiceSeq = $lastSeq + 1;
                }
                log_message('warning', 'Last invoice number format might be inconsistent for sequence extraction: ' . $lastInvoice['invoice_number']);
            }
        }

        // Construct the new invoice number using the CURRENT DATE and the GLOBAL SEQUENCE
        $invoiceNumber = $prefix . $currentDatePart . '-' . str_pad($invoiceSeq, 5, '0', STR_PAD_LEFT);
        // --- END OF CORRECTED INVOICE NUMBER GENERATION LOGIC ---

        $this->distributorSalesOrderModel->transStart();

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
                throw new \Exception('Failed to save sales order: ' . implode(', ', $this->distributorSalesOrderModel->errors()));
            }

            foreach ($salesOrderItemsData as $item) {
                $item['distributor_sales_order_id'] = $salesOrderId;
                if (!$this->distributorSalesOrderItemModel->insert($item)) {
                    throw new \Exception('Failed to save sales order item: ' . implode(', ', $this->distributorSalesOrderItemModel->errors()));
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
                    throw new \Exception('Failed to save initial payment: ' . implode(', ', $this->distributorPaymentModel->errors()));
                }
            }

            $this->distributorSalesOrderModel->transComplete();

            if ($this->distributorSalesOrderModel->transStatus() === false) {
                throw new \Exception('Transaction failed after completion check.');
            }

            return redirect()->to('/distributor-sales')->with('success', 'Sales Order ' . $invoiceNumber . ' created successfully!');
        } catch (\Exception $e) {
            $this->distributorSalesOrderModel->transRollback();
            log_message('error', 'Error creating sales order: ' . $e->getMessage());
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
            // 'discount' is now implicitly part of sales_order['discount_amount']
        ];
        return view('distributorsales/show', $data);
    }

    public function edit($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Fetch sales order items for this order
        $salesOrderItems = $this->distributorSalesOrderItemModel
            ->where('distributor_sales_order_id', $id)
            ->findAll();

        $data = [
            'title'             => 'Edit Sales Order: ' . $salesOrder['invoice_number'],
            'sales_order'       => $salesOrder,
            'sales_order_items' => $salesOrderItems, // Pass items to the view
            'distributors'      => $this->distributorModel->findAll(),
            'products'          => $this->productModel->findAll(),
            'gst_rates'         => $this->gstRateModel->findAll(),
            'validation'        => \Config\Services::validation(),
            // No need to explicitly pass discount here, it's part of $salesOrder['discount_amount']
        ];
        return view('distributorsales/edit', $data);
    }

    public function update($id)
    {
        // --- DEBUGGING LOGS START ---
        log_message('debug', 'Updating sales order with ID: ' . $id);

        $uniqueRuleString = 'is_unique[distributor_sales_orders.invoice_number,id,' . $id . ']';
        log_message('debug', 'Constructed invoice_number unique rule: ' . $uniqueRuleString);

        $salesOrder = $this->distributorSalesOrderModel->find($id);
        if (!$salesOrder) {
            log_message('error', 'Attempted to update non-existent sales order with ID: ' . $id);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $submittedInvoiceNumber = $this->request->getPost('invoice_number');
        $originalInvoiceNumber = $salesOrder['invoice_number'];

        log_message('debug', 'Submitted Invoice Number: "' . $submittedInvoiceNumber . '"');
        log_message('debug', 'Original Invoice Number: "' . $originalInvoiceNumber . '"');
        log_message('debug', 'Are submitted and original identical? ' . (($submittedInvoiceNumber === $originalInvoiceNumber) ? 'YES' : 'NO'));
        log_message('debug', 'Length of Submitted Invoice Number: ' . strlen($submittedInvoiceNumber));
        log_message('debug', 'Length of Original Invoice Number: ' . strlen($originalInvoiceNumber));
        // --- DEBUGGING LOGS END ---


        $rules = [
            'distributor_id'        => 'required|integer',
            'order_date'            => 'required|valid_date',
            'invoice_number'        => 'required|string|min_length[3]|max_length[50]|' . $uniqueRuleString,
            'notes'                 => 'permit_empty|max_length[500]',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity'   => 'required|integer|greater_than[0]',
            'products.*.gst_rate_id' => 'required|integer',
            'discount_amount'       => 'permit_empty|numeric|greater_than_equal_to[0]', // <--- Updated field name
        ];

        if (!$this->validate($rules)) {
            // Log validation errors for internal debugging
            log_message('error', 'Sales order update validation failed for ID: ' . $id);
            foreach ($this->validator->getErrors() as $field => $error) {
                log_message('error', 'Validation Error - Field: ' . $field . ', Error: ' . $error);
            }
            // Return to form with input data and validation errors
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'Please correct the form errors below.');
        }

        // Retrieve and sanitize posted data
        $distributorId = $this->request->getPost('distributor_id');
        $orderDate = $this->request->getPost('order_date');
        $invoiceNumber = $this->request->getPost('invoice_number');
        $notes = $this->request->getPost('notes');
        $discountAmount = (float) $this->request->getPost('discount_amount'); // <--- Retrieve discount amount

        $postedProducts = $this->request->getPost('products');

        $totalAmountBeforeGst = 0;
        $totalGstAmount = 0;
        $salesOrderItemsToProcess = []; // Data for items to be updated or inserted

        // Collect existing item IDs for the current sales order to identify what to delete later
        $existingItemIds = $this->distributorSalesOrderItemModel
            ->where('distributor_sales_order_id', $id)
            ->findColumn('id'); // Get only the IDs for efficient deletion

        // Map existing items by their product_id for quick lookup
        $existingItemsByProductId = [];
        $tempExistingItems = $this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();
        foreach ($tempExistingItems as $item) {
            $existingItemsByProductId[$item['product_id']] = $item;
        }

        $itemIdsToKeep = []; // To track which existing item IDs are still present in the form

        // Process each product item from the form
        foreach ($postedProducts as $itemKey => $item) {
            // Basic check for essential product item data
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['gst_rate_id'])) {
                log_message('error', 'Incomplete product item data received for key during update: ' . $itemKey);
                return redirect()->back()->withInput()->with('error', 'One or more product items are incomplete. Please ensure all product fields are selected and quantities are entered.');
            }

            $productId = $item['product_id'];
            $quantity = (int) $item['quantity'];
            $gstRateId = $item['gst_rate_id'];

            // Fetch product and GST rate details from database
            $product = $this->productModel->find($productId);
            $gstRate = $this->gstRateModel->find($gstRateId);

            // Validate fetched product and GST rate
            if (!$product) {
                log_message('error', 'Product not found for ID: ' . $productId . ' during update.');
                return redirect()->back()->withInput()->with('error', 'Invalid product selected for an item. Please refresh and try again.');
            }
            if (!$gstRate) {
                log_message('error', 'GST rate not found for ID: ' . $gstRateId . ' during update.');
                return redirect()->back()->withInput()->with('error', 'Invalid GST rate selected for an item. Please refresh and try again.');
            }

            // Calculate item amounts
            $unitPriceAtSale = (float) $product['selling_price'];
            $gstRateAtSale = (float) $gstRate['rate'];

            $itemTotalBeforeGst = $quantity * $unitPriceAtSale;
            $itemGstAmount = ($itemTotalBeforeGst * $gstRateAtSale) / 100;
            $itemFinalTotal = $itemTotalBeforeGst + $itemGstAmount;

            // Accumulate totals for the main sales order
            $totalAmountBeforeGst += $itemTotalBeforeGst;
            $totalGstAmount += $itemGstAmount;

            // Prepare item data for update/insert operations
            $itemData = [
                'distributor_sales_order_id' => $id, // Link to the current sales order
                'product_id'             => $productId,
                'gst_rate_id'            => $gstRateId,
                'quantity'               => $quantity,
                'unit_price_at_sale'     => $unitPriceAtSale,
                'gst_rate_at_sale'       => $gstRateAtSale,
                'item_total_before_gst'  => $itemTotalBeforeGst,
                'item_gst_amount'        => $itemGstAmount,
                'item_final_total'       => $itemFinalTotal,
            ];

            // If the product ID already exists among the sales order's items,
            // include its existing ID to indicate an update operation and mark it to be kept.
            if (isset($existingItemsByProductId[$productId])) {
                $existingItem = $existingItemsByProductId[$productId];
                $itemData['id'] = $existingItem['id']; // Set ID for update operation
                $itemIdsToKeep[] = $existingItem['id'];
            }
            $salesOrderItemsToProcess[] = $itemData;
        }

        // Calculate final totals for the main sales order, factoring in discount
        $grossTotal = $totalAmountBeforeGst + $totalGstAmount;
        $finalTotalAmount = $grossTotal - $discountAmount; // <--- Apply discount in update

        // Ensure finalTotalAmount doesn't go below zero
        if ($finalTotalAmount < 0) {
            $finalTotalAmount = 0;
        }

        // Recalculate due_amount and status based on current amount_paid and new final_total_amount
        // `amount_paid` is retrieved from the original sales order record.
        $newDueAmount = $finalTotalAmount - ($salesOrder['amount_paid'] ?? 0); // Use original amount paid
        $newStatus = ($newDueAmount <= 0) ? 'Paid' : (($salesOrder['amount_paid'] > 0) ? 'Partially Paid' : 'Pending');

        // Start database transaction for atomicity
        $this->distributorSalesOrderModel->transStart();

        try {
            // Prepare main sales order data for update
            $updateSalesOrderData = [
                'distributor_id'            => $distributorId,
                'invoice_number'            => $invoiceNumber,
                'invoice_date'              => $orderDate,
                'total_amount_before_gst'   => $totalAmountBeforeGst,
                'total_gst_amount'          => $totalGstAmount,
                'final_total_amount'        => $finalTotalAmount,
                'discount_amount'           => $discountAmount, // <--- Save updated discount amount
                'due_amount'                => $newDueAmount,
                'status'                    => $newStatus,
                'notes'                     => $notes,
            ];

            // Update the main sales order record
            if (!$this->distributorSalesOrderModel->update($id, $updateSalesOrderData)) {
                $dbErrors = $this->distributorSalesOrderModel->errors();
                $errorMessage = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.';
                throw new \Exception('Failed to update sales order in database: ' . $errorMessage);
            }

            // Identify items to be removed (those that were in the DB but not in the submitted form)
            $itemIdsToRemove = array_diff($existingItemIds, $itemIdsToKeep);

            // Delete sales order items that are no longer in the form
            if (!empty($itemIdsToRemove)) {
                if (!$this->distributorSalesOrderItemModel->delete($itemIdsToRemove)) {
                    $dbErrors = $this->distributorSalesOrderItemModel->errors();
                    $errorMessage = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.';
                    throw new \Exception('Failed to delete sales order items: ' . $errorMessage);
                }
            }

            // Update existing or insert new sales order items
            foreach ($salesOrderItemsToProcess as $itemData) {
                if (isset($itemData['id'])) {
                    // Item has an ID, so it's an existing item to be updated
                    $itemId = $itemData['id'];
                    unset($itemData['id']); // Remove ID from data array as it's for the update method signature
                    if (!$this->distributorSalesOrderItemModel->update($itemId, $itemData)) {
                        $dbErrors = $this->distributorSalesOrderItemModel->errors();
                        $errorMessage = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.';
                        throw new \Exception('Failed to update sales order item for product ID ' . $itemData['product_id'] . ': ' . $errorMessage);
                    }
                } else {
                    // Item does not have an ID, so it's a new item to be inserted
                    if (!$this->distributorSalesOrderItemModel->insert($itemData)) {
                        $dbErrors = $this->distributorSalesOrderItemModel->errors();
                        $errorMessage = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.';
                        throw new \Exception('Failed to insert new sales order item for product ID ' . $itemData['product_id'] . ': ' . $errorMessage);
                    }
                }
            }

            // Complete the transaction (commit changes)
            $this->distributorSalesOrderModel->transComplete();

            // Additional check for transaction status (important if auto-commit is off or for deferred errors)
            if ($this->distributorSalesOrderModel->transStatus() === false) {
                throw new \Exception('Database transaction failed during sales order update. Check logs for details.');
            }

            // Redirect to show page with success message
            return redirect()->to(base_url('distributor-sales/show/' . $id))->with('success', 'Sales Order ' . $salesOrder['invoice_number'] . ' updated successfully!');
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->distributorSalesOrderModel->transRollback();
            // Log the detailed error
            log_message('error', 'Error updating sales order (ID: ' . $id . '): ' . $e->getMessage());
            // Redirect back with error message and old input
            return redirect()->back()->withInput()->with('error', 'Error updating sales order: ' . $e->getMessage());
        }
    }

    public function addPayment($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Add Payment for Invoice: ' . $salesOrder['invoice_number'],
            'sales_order'    => $salesOrder,
            'paymentMethods' => [ // <--- THIS IS REQUIRED HERE FOR add_payment.php VIEW
                'Cash'          => 'Cash',
                'Credit Card'   => 'Credit Card', // Consider if "Credit" in create and "Credit Card" here should be consistent
                'UPI'           => 'UPI',
                'Bank Transfer' => 'Bank Transfer',
                'Cheque'        => 'Cheque', // Add if needed, ensure consistency
            ],
            'validation'     => \Config\Services::validation(),
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

        // Allow partial payment, but not overpayment
        if ($paymentAmount > $salesOrder['due_amount']) {
            return redirect()->back()->with('error', 'Payment amount cannot exceed the due amount (₹' . number_format($salesOrder['due_amount'], 2) . ').');
        }

        $this->distributorSalesOrderModel->transStart();

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
                throw new \Exception('Failed to save payment: ' . implode(', ', $this->distributorPaymentModel->errors()));
            }

            $newAmountPaid = $salesOrder['amount_paid'] + $paymentAmount;
            // The final_total_amount already includes the discount, so no change needed here.
            $newDueAmount = $salesOrder['final_total_amount'] - $newAmountPaid;
            $newStatus = ($newDueAmount <= 0) ? 'Paid' : 'Partially Paid';

            $updateData = [
                'amount_paid' => $newAmountPaid,
                'due_amount'  => $newDueAmount,
                'status'      => $newStatus,
            ];

            if (!$this->distributorSalesOrderModel->update($salesOrderId, $updateData)) {
                throw new \Exception('Failed to update sales order payment status: ' . implode(', ', $this->distributorSalesOrderModel->errors()));
            }

            $this->distributorSalesOrderModel->transComplete();

            if ($this->distributorSalesOrderModel->transStatus() === false) {
                throw new \Exception('Transaction failed after completion check.');
            }

            return redirect()->to('/distributor-sales/show/' . $salesOrderId)->with('success', 'Payment recorded successfully! Invoice status updated to ' . $newStatus . '.');
        } catch (\Exception $e) {
            $this->distributorSalesOrderModel->transRollback();
            log_message('error', 'Error recording payment: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $this->distributorSalesOrderModel->transStart();

        try {
            $salesOrder = $this->distributorSalesOrderModel->find($id);
            if (!$salesOrder) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Sales Order not found for ID: ' . $id);
            }

            if (!$this->distributorPaymentModel->where('distributor_sales_order_id', $id)->delete()) {
                throw new \Exception('Failed to delete associated payments for sales order ID: ' . $id . '. Errors: ' . implode(', ', $this->distributorPaymentModel->errors()));
            }

            if (!$this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->delete()) {
                throw new \Exception('Failed to delete associated sales order items for sales order ID: ' . $id . '. Errors: ' . implode(', ', $this->distributorSalesOrderItemModel->errors()));
            }

            if (!$this->distributorSalesOrderModel->delete($id)) {
                throw new \Exception('Failed to delete sales order ID: ' . $id . '. Errors: ' . implode(', ', $this->distributorSalesOrderModel->errors()));
            }

            $this->distributorSalesOrderModel->transComplete();

            if ($this->distributorSalesOrderModel->transStatus() === false) {
                throw new \Exception('Transaction failed after completion check for sales order deletion.');
            }

            return redirect()->to('/distributor-sales')->with('success', 'Sales Order ' . esc($salesOrder['invoice_number']) . ' and all associated data deleted successfully.');
        } catch (\Exception $e) {
            $this->distributorSalesOrderModel->transRollback();
            log_message('error', 'Error deleting sales order (ID: ' . $id . '): ' . $e->getMessage());
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
            $sheet->setCellValue($col++ . $row, $order['discount_amount']); // <--- Add discount amount
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
            'sales_orders' => $salesOrders, // <-- Ensure your existing index_pdf.php uses this variable name
            'currentDate'  => date('Y-m-d H:i:s')
        ];

        // Use your existing view for PDF content
        $html = view('distributorsales/index_pdf', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Distributor_Sales_Orders_Report_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => 1)); // 1 = download, 0 = preview
        exit;
    }

    public function exportInvoicePdf($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Fetch related data for the invoice
        $salesOrderItems = $this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();
        $distributor = $this->distributorModel->find($salesOrder['distributor_id']);
        $payments = $this->distributorPaymentModel->where('distributor_sales_order_id', $id)->findAll();

        // Enrich sales order items with product and GST details for the invoice
        foreach ($salesOrderItems as &$item) {
            $product = $this->productModel->find($item['product_id']);
            $gstRate = $this->gstRateModel->find($item['gst_rate_id']);
            $item['product_name'] = $product['name'] ?? 'N/A';
            $item['product_unit_price'] = $product['selling_price'] ?? 0; // The original unit price from product master
            $item['gst_rate_name'] = $gstRate['name'] ?? 'N/A';
            $item['gst_rate_percentage'] = $gstRate['rate'] ?? 0;

            // Ensure 'unit_price_at_sale' and 'gst_rate_at_sale' are present
            // If they are directly from the sales_order_items table, this is redundant but safe.
            $item['unit_price_at_sale'] = $item['unit_price_at_sale'] ?? $product['selling_price'];
            $item['gst_rate_at_sale'] = $item['gst_rate_at_sale'] ?? $gstRate['rate'];
        }
        unset($item); // Break the reference

        $data = [
            'title'             => 'Distributor Sales Invoice',
            'sales_order'       => $salesOrder,
            'sales_order_items' => $salesOrderItems,
            'distributor'       => $distributor, // Pass distributor separately
            'payments'          => $payments,
            'currentDate'       => date('Y-m-d H:i:s')
        ];

        // Load your existing invoice view for PDF content
        $html = view('distributorsales/invoice_pdf', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans'); // Set default font for Rupee symbol
        // $options->set('tempDir', APPPATH . 'writable/temp_dompdf'); // Uncomment and create this directory if needed

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $invoiceNumber = $salesOrder['invoice_number'] ?? 'INV-' . $salesOrder['id'];
        $fileName = 'Distributor_Sales_Invoice_' . str_replace('/', '_', $invoiceNumber) . '.pdf'; // Sanitize for filename
        $dompdf->stream($fileName, array("Attachment" => 1)); // 0 = preview in browser, 1 = download
        exit;
    }

    // --- NEW METHOD FOR EXPORTING SINGLE INVOICE TO EXCEL ---
    public function exportInvoiceExcel($id)
    {
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $salesOrderItems = $this->distributorSalesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();
        $distributor = $this->distributorModel->find($salesOrder['distributor_id']);
        $payments = $this->distributorPaymentModel->where('distributor_sales_order_id', $id)->findAll();

        // Enrich sales order items with product and GST details for Excel
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

        // --- Invoice Header & Summary ---
        $sheet->setCellValue('A1', 'Invoice: ' . $salesOrder['invoice_number']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue('A2', 'Date: ' . date('Y-m-d', strtotime($salesOrder['invoice_date'])));
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');

        $sheet->setCellValue('D1', 'Your Company Name'); // Placeholder, replace with actual company name
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

        $current_row += 7; // Leave some space

        // --- Invoice Items ---
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

        // --- Totals ---
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
        $sheet->getStyle('G' . $current_row)->getFont()->setBold(true)->getColor()->setARGB('FFD9534F'); // Red for due amount

        // --- Amount in Words ---
        $current_row += 2;
        $amountInWords = convertNumberToWords($salesOrder['final_total_amount']) . ' Rupees Only.';
        $sheet->setCellValue('A' . $current_row, 'Amount in words: ' . $amountInWords);
        $sheet->getStyle('A' . $current_row)->getFont()->setItalic(true)->setBold(true);
        $sheet->mergeCells('A' . $current_row . ':H' . $current_row);

        // --- Payment History ---
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

        // Auto-size columns for better readability
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set number formats for currency columns
        $currencyColumns = ['D', 'E', 'F', 'G', 'H']; // Adjust based on your columns for item table
        foreach ($currencyColumns as $col) {
            for ($r = 10; $r < $current_row; $r++) { // Adjust start row for actual data
                if ($r > 9 && $r < ($current_row - count($payments) - 3)) { // Apply to item table rows
                    $sheet->getStyle($col . $r)->getNumberFormat()->setFormatCode('#,##0.00');
                }
            }
        }
        // Apply currency format to total cells
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
