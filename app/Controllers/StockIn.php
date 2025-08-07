<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\StockInModel;
use App\Models\StockInProductModel;
use App\Models\StockInPaymentModel;
use App\Models\StockInGstModel;
use App\Models\PurchasedProductModel;
use App\Models\VendorModel;
use App\Models\GstRateModel;
use App\Models\UnitModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\AvailablePurchasedStockModel;
use App\Models\StockConsumptionModel; // Assuming you have a model for the stock_consumption table


class StockIn extends BaseController
{
    use ResponseTrait;

    protected $purchasedProductModel;
    protected $vendorModel;
    protected $gstRateModel;
    protected $stockInModel;
    protected $stockInProductModel;
    protected $stockInGstModel;
    protected $stockInPaymentModel;
    protected $availablePurchasedStockModel;
    protected $unitModel;
    protected $db;
    protected $validation;
    protected $stockConsumptionModel;

    public function __construct()
    {
        $this->purchasedProductModel = new PurchasedProductModel();
        $this->vendorModel = new VendorModel();
        $this->gstRateModel = new GstRateModel();
        $this->stockInModel = new StockInModel();
        $this->stockInProductModel = new StockInProductModel();
        $this->stockInGstModel = new StockInGstModel();
        $this->stockInPaymentModel = new StockInPaymentModel();
        $this->stockConsumptionModel = new StockConsumptionModel();
        $this->availablePurchasedStockModel = new AvailablePurchasedStockModel();
        $this->unitModel = new UnitModel();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
    }


    public function index()
    {
        $data = [
            // Call the new method to get the combined data
            'stockInEntries' => $this->stockInModel->getStockInEntriesWithVendors(),
            'title' => 'Stock In Entries',
        ];

        return view('stock_in/index', $data);
    }


      public function create()
    {
        // Fetch all vendors from the database
        $vendors = $this->vendorModel->findAll();

        // Fetch all GST rates from the database
        $gstRates = $this->gstRateModel->findAll();

        // Fetch all units from the database
        $units = $this->unitModel->findAll();

        // Fetch all purchased products with their unit names
        // Correctly using the purchasedProductModel and joining with the units table
        $products = $this->purchasedProductModel
            ->select('purchased_products.*, units.name as unit_name')
            ->join('units', 'units.id = purchased_products.unit_id', 'left')
            ->findAll();

        // Define the payment methods
        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'upi' => 'UPI',
            'cheque' => 'Cheque',
        ];

        $data = [
            'title' => 'Add New Stock In Entry',
            'vendors' => $vendors,
            'gstRates' => $gstRates,
            'units' => $units,
            'products' => $products, // <-- Passed the fetched products to the view
            'paymentMethods' => $paymentMethods,
        ];

        // This is necessary if you're using `session()->getFlashdata('errors')`
        if (session()->getFlashdata('errors')) {
            $data['errors'] = session()->getFlashdata('errors');
        }

        // If validation fails, we need to repopulate the form with old input.
        // This includes old products and GST rates.
        if (old('product_name')) {
            $old_products = [];
            foreach (old('product_name') as $index => $name) {
                $old_products[] = [
                    'product_name' => $name,
                    'unit_id' => old('unit_id')[$index],
                    'quantity' => old('quantity')[$index],
                    'unit_price' => old('unit_price')[$index],
                    'taxable_amount' => old('taxable_amount')[$index],
                    'product_gst_rate_id' => old('product_gst_rate_ids')[$index] ?? null,
                    'product_gst_amount' => old('product_gst_amounts')[$index] ?? null,
                    'total_price' => old('total_prices')[$index],
                ];
            }
            $data['old_products'] = $old_products;
        }

        if (old('overall_gst_rate_ids')) {
            $data['old_overall_gst_rate_ids'] = old('overall_gst_rate_ids');
            $data['old_overall_gst_amounts'] = old('overall_gst_amounts');
        }

        return view('stock_in/create', $data);
    }

    public function store(): ResponseInterface
    {
        // Define validation rules for the main stock-in entry and nested arrays
        $rules = [
            'vendor_id' => 'required|integer|is_not_unique[vendors.id]',
            'date_received' => 'required|valid_date',
            'notes' => 'permit_empty|max_length[500]',
            'discount_amount' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal',
            'initial_payment_amount' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal',
            'payment_type' => 'permit_empty|in_list[cash,bank_transfer,upi,card,cheque,other]',
            'transaction_id' => 'permit_empty|string|max_length[255]',
            'payment_notes' => 'permit_empty|max_length[500]',
            'total_amount_before_gst' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'gst_amount' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'grand_total' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'amount_pending' => 'required|numeric|decimal',
            'products' => 'required',
            'products.*.product_id' => 'required|integer|is_not_unique[purchased_products.id]',
            'products.*.quantity' => 'required|numeric|greater_than[0]|decimal',
            'products.*.purchase_price' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'overall_gst_rate_ids' => 'required',
            'overall_gst_rate_ids.*' => 'required|integer|is_not_unique[gst_rates.id]',
        ];

        $messages = [
            // ... (Your existing custom validation messages) ...
            'products' => ['required' => 'At least one product item must be added.'],
            'products.*.product_id' => [
                'required' => 'A product must be selected for each item.',
                'is_not_unique' => 'Selected product does not exist.',
                'integer' => 'Invalid product ID.'
            ],
            'products.*.quantity' => [
                'required' => 'Quantity is required for each product.',
                'numeric' => 'Quantity must be a number.',
                'greater_than' => 'Quantity must be greater than zero.',
                'decimal' => 'Quantity must be a valid decimal number.',
            ],
            'products.*.purchase_price' => [
                'required' => 'Purchase price is required for each product.',
                'numeric' => 'Purchase price must be a number.',
                'greater_than_equal_to' => 'Purchase price cannot be negative.',
                'decimal' => 'Purchase price must be a valid decimal number.',
            ],
            'overall_gst_rate_ids' => ['required' => 'At least one overall GST rate must be selected.'],
            'overall_gst_rate_ids.*' => [
                'required' => 'Each selected GST rate must be valid.',
                'integer' => 'Invalid GST rate ID.',
                'is_not_unique' => 'Selected GST rate does not exist.',
            ],
            'total_amount_before_gst' => [
                'required' => 'Sub Total (before GST) is required.',
                'numeric' => 'Sub Total (before GST) must be a number.',
                'greater_than_equal_to' => 'Sub Total (before GST) cannot be negative.',
                'decimal' => 'Sub Total (before GST) must be a valid decimal number.',
            ],
            'gst_amount' => [
                'required' => 'GST Amount is required.',
                'numeric' => 'GST Amount must be a number.',
                'greater_than_equal_to' => 'GST Amount cannot be negative.',
                'decimal' => 'GST Amount must be a valid decimal number.',
            ],
            'grand_total' => [
                'required' => 'Grand Total (before discount) is required.',
                'numeric' => 'Grand Total (before discount) must be a number.',
                'greater_than_equal_to' => 'Grand Total (before discount) cannot be negative.',
                'decimal' => 'Grand Total (before discount) must be a valid decimal number.',
            ],
            'amount_pending' => [
                'required' => 'Amount Pending is required.',
                'numeric' => 'Amount Pending must be a number.',
                'decimal' => 'Amount Pending must be a valid decimal number.',
            ],
            'payment_type' => ['in_list' => 'Invalid payment method selected.'],
            'vendor_id' => [
                'required' => 'The Vendor field is required.',
                'integer' => 'Invalid Vendor ID.',
                'is_not_unique' => 'Selected vendor does not exist.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            log_message('error', 'StockIn::store - Validation failed. Errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->transStart();

        try {
            // Gather all post data
            $vendorId = $this->request->getPost('vendor_id');
            $dateReceived = $this->request->getPost('date_received');
            $notes = $this->request->getPost('notes');
            $discountAmount = (float) $this->request->getPost('discount_amount');
            $initialPaymentAmount = (float) $this->request->getPost('initial_payment_amount');
            $paymentType = $this->request->getPost('payment_type');
            $transactionId = $this->request->getPost('transaction_id');
            $paymentNotes = $this->request->getPost('payment_notes');
            $totalAmountBeforeGst = (float) $this->request->getPost('total_amount_before_gst');
            $gstAmount = (float) $this->request->getPost('gst_amount');
            $grandTotalBeforeDiscount = (float) $this->request->getPost('grand_total');
            $amountPending = (float) $this->request->getPost('amount_pending');
            $finalGrandTotal = $grandTotalBeforeDiscount - $discountAmount;
            $productItems = $this->request->getPost('products');
            $overallGstRateIds = $this->request->getPost('overall_gst_rate_ids');

            // 1. Insert into the main stock_in table
            $stockInData = [
                'vendor_id' => !empty($vendorId) ? $vendorId : null,
                'date_received' => $dateReceived,
                'notes' => $notes,
                'discount_amount' => $discountAmount,
                'initial_payment_amount' => $initialPaymentAmount,
                'balance_amount' => $amountPending,
                'payment_type' => $paymentType,
                'transaction_id' => $transactionId,
                'payment_notes' => $paymentNotes,
                'total_amount_before_gst' => $totalAmountBeforeGst,
                'gst_amount' => $gstAmount,
                'grand_total' => $grandTotalBeforeDiscount,
                'final_grand_total' => $finalGrandTotal,
            ];
            $stockInId = $this->stockInModel->insert($stockInData);
            if (!$stockInId) {
                $dbErrors = $this->stockInModel->errors();
                throw new \Exception('Failed to add Stock In entry: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            // 2. Loop and insert into the stock_in_products table and update product stock
            foreach ($productItems as $item) {
                $stockInProductData = [
                    'stock_in_id' => $stockInId,
                    'product_id' => $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'purchase_price' => (float) $item['purchase_price'],
                    'item_total' => (float) $item['quantity'] * (float) $item['purchase_price'],
                ];
                if (!$this->stockInProductModel->insert($stockInProductData)) {
                    $dbErrors = $this->stockInProductModel->errors();
                    throw new \Exception('Failed to add Stock In product item: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }

                // *** NEW LOGIC: Update or Insert into available_purchased_stock ***
                $productId = $item['product_id'];
                $quantity = (float) $item['quantity'];

                $existingStock = $this->availablePurchasedStockModel->where('product_id', $productId)->first();

                if ($existingStock) {
                    $newBalance = (float)$existingStock['balance'] + $quantity;
                    $this->availablePurchasedStockModel->update($existingStock['id'], ['balance' => $newBalance]);
                } else {
                    $this->availablePurchasedStockModel->insert([
                        'product_id' => $productId,
                        'balance'    => $quantity,
                    ]);
                }
            }

            // 3. Loop and insert into the stock_in_gsts table
            foreach ($overallGstRateIds as $gstRateId) {
                $stockInGstData = [
                    'stock_in_id' => $stockInId,
                    'gst_rate_id' => $gstRateId
                ];
                if (!$this->stockInGstModel->insert($stockInGstData)) {
                    $dbErrors = $this->stockInGstModel->errors();
                    throw new \Exception('Failed to add overall GST rate: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }
            }

            // 4. Record initial payment if any
            if ($initialPaymentAmount > 0) {
                $paymentData = [
                    'stock_in_id' => $stockInId,
                    'payment_amount' => $initialPaymentAmount,
                    'payment_type' => $paymentType,
                    'transaction_id' => $transactionId,
                    'notes' => $paymentNotes,
                    'payment_date' => $dateReceived,
                ];
                if (!$this->stockInPaymentModel->insert($paymentData)) {
                    $dbErrors = $this->stockInPaymentModel->errors();
                    throw new \Exception('Failed to record initial payment: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'StockIn::store - Transaction failed after completion check. DB Error: ' . json_encode($dbError));
                throw new \Exception('Transaction failed during stock in creation. Please check system logs.');
            }

            return redirect()->to('/stock-in')->with('success', 'Stock In entry added successfully!');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::store - Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'Error adding Stock In entry: ' . $e->getMessage());
        }
    }






    public function view(int $id): string
    {
        $stockInEntry = $this->stockInModel
            ->select('stock_in.*, vendors.agency_name as vendor_name')
            ->join('vendors', 'vendors.id = stock_in.vendor_id', 'left')
            ->find($id);

        if (!$stockInEntry) {
            throw PageNotFoundException::forPageNotFound();
        }

        $stockInEntry['products'] = $this->stockInProductModel
            ->select('stock_in_products.*, purchased_products.name as product_name, purchased_products.unit_id, units.name as unit_name')
            ->join('purchased_products', 'purchased_products.id = stock_in_products.product_id')
            ->join('units', 'units.id = purchased_products.unit_id')
            ->where('stock_in_id', $id)
            ->findAll();

        $stockInEntry['payments'] = $this->stockInPaymentModel
            ->where('stock_in_id', $id)
            ->orderBy('payment_date', 'asc')
            ->findAll();

        $stockInEntry['gst_rates'] = $this->stockInGstModel
            ->select('gst_rates.*')
            ->join('gst_rates', 'gst_rates.id = stock_in_gst.gst_rate_id')
            ->where('stock_in_gst.stock_in_id', $id)
            ->findAll();

        $data = [
            'title' => 'View Stock In Entry',
            'stockInEntry' => $stockInEntry
        ];

        return view('stock_in/view', $data);
    }

    /**
     * Show the form for editing an existing stock-in entry.
     *
     * @param int $id The ID of the stock-in entry
     * @return string
     */
    public function edit(int $id): string
    {
        $stockInEntry = $this->stockInModel
            ->find($id);

        if (!$stockInEntry) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Fetch products and GST rates associated with this stock-in entry
        $stockInEntry['products'] = $this->stockInProductModel
            ->where('stock_in_id', $id)
            ->findAll();

        $stockInEntry['gst_rates'] = $this->stockInGstModel
            ->where('stock_in_id', $id)
            ->findAll();

        // Fetch all vendors, products, and GST rates for the form dropdowns
        $products = $this->purchasedProductModel
            ->select('purchased_products.id, purchased_products.name, purchased_products.current_stock, units.name as unit_name')
            ->join('units', 'units.id = purchased_products.unit_id')
            ->findAll();

        $vendors = $this->vendorModel->findAll();
        $gstRates = $this->gstRateModel->findAll();
        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'upi' => 'UPI',
            'card' => 'Card',
            'cheque' => 'Cheque',
            'other' => 'Other'
        ];

        $data = [
            'title' => 'Edit Stock In Entry',
            'stockInEntry' => $stockInEntry,
            'vendors' => $vendors,
            'products' => $products,
            'gstRates' => $gstRates,
            'paymentMethods' => $paymentMethods,
            'validation' => $this->validation
        ];

        return view('stock_in/edit', $data);
    }

    /**
     * Handle form submission for updating an existing stock-in entry.
     *
     * @param int $id The ID of the stock-in entry
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        // Define validation rules
        $rules = [
            'vendor_id' => 'required|integer|is_not_unique[vendors.id]',
            'date_received' => 'required|valid_date',
            'notes' => 'permit_empty|max_length[500]',
            'discount_amount' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal',
            'total_amount_before_gst' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'gst_amount' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'grand_total' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'amount_pending' => 'required|numeric|decimal',
            'products' => 'required',
            'products.*.product_id' => 'required|integer|is_not_unique[purchased_products.id]',
            'products.*.quantity' => 'required|numeric|greater_than[0]|decimal',
            'products.*.purchase_price' => 'required|numeric|greater_than_equal_to[0]|decimal',
            'overall_gst_rate_ids' => 'required',
            'overall_gst_rate_ids.*' => 'required|integer|is_not_unique[gst_rates.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->transStart();

        try {
            // Step 1: Get the old products and reverse the stock
            $oldProducts = $this->stockInProductModel->where('stock_in_id', $id)->findAll();
            foreach ($oldProducts as $oldProduct) {
                $purchasedProduct = $this->purchasedProductModel->find($oldProduct['product_id']);
                if ($purchasedProduct) {
                    $newStock = (float)$purchasedProduct['current_stock'] - (float)$oldProduct['quantity'];
                    if ($newStock < 0) {
                        throw new \Exception("Cannot update stock. Insufficient stock to reverse for product ID: {$oldProduct['product_id']}.");
                    }
                    if (!$this->purchasedProductModel->update($oldProduct['product_id'], ['current_stock' => $newStock])) {
                        $dbError = $this->db->error();
                        throw new \Exception('Failed to reverse purchased product stock: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                    }
                }
            }

            // Step 2: Delete old related records
            $this->stockInProductModel->where('stock_in_id', $id)->delete();
            $this->stockInGstModel->where('stock_in_id', $id)->delete();
            // Note: We don't delete payments here, as they are a historical record. We can add a method to add a new payment, but not edit/delete old ones.

            // Step 3: Update the main stock-in entry
            $vendorId = $this->request->getPost('vendor_id');
            $stockInData = [
                'vendor_id' => !empty($vendorId) ? $vendorId : null,
                'date_received' => $this->request->getPost('date_received'),
                'notes' => $this->request->getPost('notes'),
                'discount_amount' => (float) $this->request->getPost('discount_amount'),
                'balance_amount' => (float) $this->request->getPost('amount_pending'),
                'total_amount_before_gst' => (float) $this->request->getPost('total_amount_before_gst'),
                'gst_amount' => (float) $this->request->getPost('gst_amount'),
                'grand_total' => (float) $this->request->getPost('grand_total'),
                'final_grand_total' => (float) $this->request->getPost('grand_total') - (float) $this->request->getPost('discount_amount'),
            ];
            if (!$this->stockInModel->update($id, $stockInData)) {
                $dbErrors = $this->stockInModel->errors();
                throw new \Exception('Failed to update Stock In entry: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            // Step 4: Insert new related records and update stock
            $productItems = $this->request->getPost('products');
            foreach ($productItems as $item) {
                $stockInProductData = [
                    'stock_in_id' => $id,
                    'product_id' => $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'purchase_price' => (float) $item['purchase_price'],
                    'item_total' => (float) $item['quantity'] * (float) $item['purchase_price'],
                ];
                if (!$this->stockInProductModel->insert($stockInProductData)) {
                    $dbErrors = $this->stockInProductModel->errors();
                    throw new \Exception('Failed to add new Stock In product item: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }

                $purchasedProduct = $this->purchasedProductModel->find($item['product_id']);
                if ($purchasedProduct) {
                    $newStock = (float)$purchasedProduct['current_stock'] + (float)$item['quantity'];
                    if (!$this->purchasedProductModel->update($item['product_id'], ['current_stock' => $newStock])) {
                        $dbError = $this->db->error();
                        throw new \Exception('Failed to update purchased product stock: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                    }
                } else {
                    throw new \Exception('Purchased Product not found for stock update.');
                }
            }

            $overallGstRateIds = $this->request->getPost('overall_gst_rate_ids');
            foreach ($overallGstRateIds as $gstRateId) {
                $stockInGstData = [
                    'stock_in_id' => $id,
                    'gst_rate_id' => $gstRateId
                ];
                if (!$this->stockInGstModel->insert($stockInGstData)) {
                    $dbErrors = $this->stockInGstModel->errors();
                    throw new \Exception('Failed to add new overall GST rate: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'StockIn::update - Transaction failed after completion check. DB Error: ' . json_encode($dbError));
                throw new \Exception('Transaction failed during stock in update. Please check system logs.');
            }

            return redirect()->to('/stock-in')->with('success', 'Stock In entry updated successfully!');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::update - Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'Error updating Stock In entry: ' . $e->getMessage());
        }
    }

    /**
     * Delete a Stock In entry.
     *
     * @param int $id The ID of the stock-in entry
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        $stockInEntry = $this->stockInModel->find($id);

        if (!$stockInEntry) {
            return redirect()->to('/stock-in')->with('error', 'Stock In entry not found.');
        }

        $this->db->transStart();

        try {
            // Step 1: Get products and reverse the stock
            $products = $this->stockInProductModel->where('stock_in_id', $id)->findAll();
            foreach ($products as $product) {
                $purchasedProduct = $this->purchasedProductModel->find($product['product_id']);
                if ($purchasedProduct) {
                    $newStock = (float)$purchasedProduct['current_stock'] - (float)$product['quantity'];
                    if ($newStock < 0) {
                        throw new \Exception("Cannot delete entry. Insufficient stock to reverse for product ID: {$product['product_id']}.");
                    }
                    if (!$this->purchasedProductModel->update($product['product_id'], ['current_stock' => $newStock])) {
                        $dbError = $this->db->error();
                        throw new \Exception('Failed to reverse purchased product stock: ' . ($dbError['message'] ?? 'Unknown DB error.'));
                    }
                }
            }

            // Step 2: Delete related records first
            $this->stockInPaymentModel->where('stock_in_id', $id)->delete();
            $this->stockInGstModel->where('stock_in_id', $id)->delete();
            $this->stockInProductModel->where('stock_in_id', $id)->delete();

            // Step 3: Delete the main entry
            if (!$this->stockInModel->delete($id)) {
                $dbErrors = $this->stockInModel->errors();
                throw new \Exception('Failed to delete Stock In entry: ' . (!empty($dbErrors) ? implode(', ', $dbErrors) : 'Unknown database error.'));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                log_message('error', 'StockIn::delete - Transaction failed after completion check. DB Error: ' . json_encode($dbError));
                throw new \Exception('Transaction failed during stock in deletion. Please check system logs.');
            }

            return redirect()->to('/stock-in')->with('success', 'Stock In entry deleted successfully!');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockIn::delete - Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'Error deleting Stock In entry: ' . $e->getMessage());
        }
    }



    public function addPayment($stockInId)
    {
        // Load the models
        $stockInModel = new \App\Models\StockInModel();
        // CORRECTION: Use the correct model name 'StockInPaymentModel'
        // and use the class property for consistency.
        // $paymentModel = new \App\Models\StockInPaymentModel();

        // Check if the stock-in entry exists
        $stockInEntry = $stockInModel->find($stockInId);
        if (!$stockInEntry) {
            return redirect()->to(base_url('stock-in'))->with('error', 'Stock In Entry not found.');
        }

        // Check if there is a remaining balance
        if ($stockInEntry['balance_amount'] <= 0) {
            return redirect()->back()->with('error', 'This entry has no remaining balance.');
        }

        // Define validation rules for the payment form
        $rules = [
            'payment_date' => 'required|valid_date',
            'payment_amount' => 'required|numeric|greater_than[0]',
            'payment_type' => 'required|in_list[cash,bank_transfer,card,upi]',
            'notes' => 'permit_empty',
        ];

        // Get the form data
        $postData = $this->request->getPost();

        if ($this->validate($rules)) {
            $paymentAmount = $postData['payment_amount'];

            // Check if the payment amount is not greater than the balance
            if ($paymentAmount > $stockInEntry['balance_amount']) {
                return redirect()->back()->with('error', 'Payment amount cannot exceed the remaining balance.');
            }

            // Prepare the payment data
            $paymentData = [
                'stock_in_id' => $stockInId,
                'payment_date' => $postData['payment_date'],
                'payment_amount' => $paymentAmount,
                'payment_type' => $postData['payment_type'],
                'notes' => $postData['notes'],
            ];

            // Save the new payment record using the class property
            if ($this->stockInPaymentModel->insert($paymentData)) {
                // Update the stock-in entry's financial summary
                $updatedAmountPaid = $stockInEntry['initial_amount_paid'] + $paymentAmount;
                $updatedBalance = $stockInEntry['balance_amount'] - $paymentAmount;

                $stockInModel->update($stockInId, [
                    'initial_amount_paid' => $updatedAmountPaid,
                    'balance_amount' => $updatedBalance
                ]);

                return redirect()->back()->with('success', 'Payment added successfully and balance updated.');
            } else {
                return redirect()->back()->with('error', 'Failed to save payment.');
            }
        } else {
            // Validation failed, send back errors
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
    }
}
