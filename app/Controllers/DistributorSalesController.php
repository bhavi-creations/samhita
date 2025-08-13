<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\DistributorSalesOrderModel;
use App\Models\DistributorSalesOrderItemModel;
use App\Models\DistributorPaymentModel;
use App\Models\DistributorModel;
use App\Models\SellingProductModel;
use App\Models\GstRateModel;
// use App\Models\StockOutModel;
use App\Models\CompanySettingModel;
use App\Models\MarketingPersonModel;
use App\Models\UnitModel;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
// For PDF
use Dompdf\Dompdf;
use Dompdf\Options;
// For Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use CodeIgniter\I18n\Time;
use Exception;
use CodeIgniter\Database\Exceptions\DatabaseException;

class DistributorSalesController extends BaseController
{
    use ResponseTrait;

    protected $distributorSalesOrderModel;
    protected $distributorSalesOrderItemModel;
    protected $distributorPaymentModel;
    protected $distributorModel;
    protected $sellingProductModel;
    protected $gstRateModel;
    // protected $stockOutModel;
    protected $companySettingModel;
    protected $marketingPersonModel;
    protected $db;

    public function __construct()
    {
        $this->distributorSalesOrderModel = new DistributorSalesOrderModel();
        $this->distributorSalesOrderItemModel = new DistributorSalesOrderItemModel();
        $this->distributorPaymentModel = new DistributorPaymentModel();
        $this->distributorModel = new DistributorModel();
        $this->sellingProductModel = new SellingProductModel();
        $this->gstRateModel = new GstRateModel();
        // $this->stockOutModel = new StockOutModel();
        $this->companySettingModel = new CompanySettingModel();
        $this->marketingPersonModel = new MarketingPersonModel();
        $this->db = \Config\Database::connect();
        helper('number');
    }

    public function index()
    {
        $salesOrders = [];
        $errorMessage = '';

        try {
            $salesOrders = $this->distributorSalesOrderModel
                ->select('distributor_sales_orders.*, distributors.agency_name')
                ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id', 'left')
                ->orderBy('invoice_date', 'DESC')
                ->orderBy('distributor_sales_orders.id', 'DESC')
                ->findAll();
        } catch (DatabaseException $e) {
            $errorMessage = 'A database error occurred while fetching sales orders. Details: ' . $e->getMessage();
            log_message('error', $errorMessage);
        } catch (Exception $e) {
            $errorMessage = 'An unexpected error occurred: ' . $e->getMessage();
            log_message('error', $errorMessage);
        }

        $data = [
            'title' => 'Distributor Sales Orders',
            'sales_orders' => $salesOrders,
            'error_message' => $errorMessage,
        ];
        return view('distributorsales/index', $data);
    }


    public function delete(int $id)
    {
        if ($id <= 0) {
            session()->setFlashdata('error', 'Invalid sales order ID.');
            return redirect()->to(base_url('distributor-sales'));
        }

        // Begin a database transaction
        $this->db->transBegin();

        try {
            // Find all items associated with the sales order using the correct field name
            $orderItems = $this->distributorSalesOrderItemModel
                ->where('distributor_sales_order_id', $id)
                ->findAll();

            if (!empty($orderItems)) {
                // Restore the stock for each product
                foreach ($orderItems as $item) {
                    $product = $this->sellingProductModel->find($item['product_id']);
                    if ($product) {
                        $newStock = (int)$product['current_stock'] + (int)$item['quantity'];
                        $this->sellingProductModel->update($item['product_id'], ['current_stock' => $newStock]);
                    }
                }
            }

            // Delete the sales order items using the correct field name
            $this->distributorSalesOrderItemModel
                ->where('distributor_sales_order_id', $id)
                ->delete();

            // Delete the sales order itself
            $this->distributorSalesOrderModel->delete($id);

            // If all operations were successful, commit the transaction
            $this->db->transCommit();
            session()->setFlashdata('success', 'Sales order and associated stock successfully restored.');
        } catch (Exception $e) {
            // If any error occurred, roll back the transaction
            $this->db->transRollback();
            session()->setFlashdata('error', 'Could not delete the sales order. An error occurred: ' . $e->getMessage());
        }

        return redirect()->to(base_url('distributor-sales'));
    }



    public function create()
    {
        // Fetch all the data needed for the form from the respective models
        $data = [
            'title' => 'New Distributor Sales Order',
            'distributors' => $this->distributorModel->findAll(),
            'products' => $this->sellingProductModel->findAll(),
            'gstRates' => $this->gstRateModel->findAll(),
            'marketingPersons' => $this->marketingPersonModel->findAll(),
        ];
        return view('distributorsales/create', $data);
    }


    public function save()
    {
        // Start a database transaction.
        $this->db->transBegin();

        try {
            $postData = $this->request->getPost();

            // Updated validation rules for better robustness
            $validationRules = [
                'distributor_id' => 'required|numeric',
                'marketing_person_id' => 'required|numeric',
                'product_id.*' => 'required|numeric',
                'quantity.*' => 'required|numeric|greater_than[0]',
                'pricing_tier' => 'in_list[dealer,farmer]',
                'overall_gst_rate_ids' => 'required',
                // Permit empty values for optional fields, but validate them if they are present.
                'initial_payment_amount' => 'permit_empty|numeric',
                'overall_discount' => 'permit_empty|numeric',
            ];

            if (!$this->validate($validationRules)) {
                $this->db->transRollback();
                log_message('error', 'Form Validation Failed: ' . json_encode($this->validator->getErrors()));
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $pricingTier = $postData['pricing_tier'] ?? 'dealer';
            $subTotal = 0;
            $salesItems = [];
            $product_ids = $postData['product_id'];
            $quantities = $postData['quantity'];

            if (!is_array($product_ids) || empty($product_ids)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Sales order must contain at least one product item.');
            }

            for ($i = 0; $i < count($product_ids); $i++) {
                $product = $this->sellingProductModel->find($product_ids[$i]);

                if (!$product) {
                    $this->db->transRollback();
                    return redirect()->back()->with('error', 'One or more products not found.');
                }

                $rate = ($pricingTier == 'dealer') ? $product['dealer_price'] : $product['farmer_price'];
                $amount = (float)$quantities[$i] * (float)$rate;
                $subTotal += $amount;

                $salesItems[] = [
                    'product_id' => $product_ids[$i],
                    'quantity' => $quantities[$i],
                    'unit_price_at_sale' => $rate,
                    'item_total' => $amount,
                    'gst_rate_id' => null,
                ];
            }

            $discount_amount = (float)($postData['overall_discount'] ?? 0);
            $amount_paid = (float)($postData['initial_payment_amount'] ?? 0);

            $gstRateIds = $postData['overall_gst_rate_ids'];
            $totalGstAmount = 0;
            $overallGstPercentageAtSale = 0;

            if (!empty($gstRateIds)) {
                $gstRates = $this->gstRateModel->whereIn('id', $gstRateIds)->findAll();
                $overallGstPercentageAtSale = array_sum(array_column($gstRates, 'rate'));
            }

            $total_amount_before_gst = $subTotal - $discount_amount;
            $totalGstAmount = $total_amount_before_gst * ($overallGstPercentageAtSale / 100);
            $final_total_amount = $total_amount_before_gst + $totalGstAmount;

            $due_amount = $final_total_amount - $amount_paid;

            // --- START OF CORRECTION: Logic for continuous auto-incrementing invoice number ---
            // Get the current date to be used in the new invoice number.
            $today = date('Ymd');
            $prefix = 'INV-' . $today;

            // Find the last invoice record in the entire table, regardless of the date.
            // Ordering by ID DESC is a reliable way to get the most recent record.
            $lastInvoice = $this->distributorSalesOrderModel
                ->select('invoice_number')
                ->orderBy('id', 'DESC')
                ->first();

            $nextNumber = 1;
            if ($lastInvoice) {
                // Extract the numeric part from the last invoice number string.
                // The format is INV-YYYYMMDDNNNN, so we get the last 4 characters.
                $lastNumber = (int)substr($lastInvoice['invoice_number'], -4);
                // Increment the number for the new invoice.
                $nextNumber = $lastNumber + 1;
            }
            // Combine the current date prefix with the new, padded sequence number.
            $newInvoiceNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            // --- END OF CORRECTION ---

            $status = 'Pending';
            if ($amount_paid > 0 && $due_amount > 0) {
                $status = 'Partially Paid';
            } elseif ($due_amount <= 0) {
                $status = 'Paid';
            }

            $salesData = [
                'distributor_id' => $postData['distributor_id'],
                'marketing_person_id' => $postData['marketing_person_id'],
                'pricing_tier' => $pricingTier,
                'invoice_number' => $newInvoiceNumber,
                'invoice_date' => Time::now()->toDateString(),
                'sub_total' => $subTotal,
                'total_amount_before_gst' => $total_amount_before_gst,
                'total_gst_amount' => $totalGstAmount,
                'discount_amount' => $discount_amount,
                'final_total_amount' => $final_total_amount,
                'amount_paid' => $amount_paid,
                'due_amount' => $due_amount,
                'status' => $status,
                'overall_gst_rate_ids' => json_encode($gstRateIds),
                'overall_gst_percentage_at_sale' => $overallGstPercentageAtSale,
                'notes' => $postData['notes'] ?? '',
            ];

            log_message('debug', 'Data for sales order insertion: ' . json_encode($salesData));

            $salesOrderId = $this->distributorSalesOrderModel->insert($salesData);

            if (!$salesOrderId) {
                throw new DatabaseException('Failed to insert the main sales order.');
            }

            $itemsToInsert = [];
            foreach ($salesItems as $item) {
                $itemsToInsert[] = array_merge($item, ['distributor_sales_order_id' => $salesOrderId]);
            }
            $this->distributorSalesOrderItemModel->insertBatch($itemsToInsert);

            foreach ($itemsToInsert as $item) {
                $this->sellingProductModel->set('current_stock', 'current_stock - ' . $item['quantity'], false)->where('id', $item['product_id'])->update();
            }

            if ($amount_paid > 0) {
                $paymentData = [
                    'distributor_sales_order_id' => $salesOrderId,
                    'payment_date' => Time::now()->toDateString(),
                    'amount' => $amount_paid,
                    'payment_method' => $postData['payment_type'] ?? 'Unknown',
                    'transaction_id' => $postData['transaction_id'] ?? null,
                    'notes' => 'Initial payment for sales order ' . $newInvoiceNumber,
                ];

                $this->distributorPaymentModel->insert($paymentData);
            }

            $this->db->transCommit();

            session()->setFlashdata('success', 'Sales order created successfully!');
            return redirect()->to(site_url('distributor-sales/view/' . $salesOrderId));
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Database Transaction Failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'A database error occurred. The sales order could not be created.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Exception in DistributorSalesController->save(): ' . $e->getMessage());
            log_message('error', 'Stack Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function view($id = null)
    {
        // 1. Ensure an ID is provided
        if ($id === null) {
            return redirect()->to(site_url('distributor-sales'))->with('error', 'No sales order ID provided.');
        }

        // 2. Load all necessary models
        $salesModel = new DistributorSalesOrderModel();
        $distributorModel = new DistributorModel();
        $marketingPersonModel = new MarketingPersonModel();
        $salesItemModel = new DistributorSalesOrderItemModel();
        $paymentModel = new DistributorPaymentModel();
        $sellingProductModel = new SellingProductModel();
        $unitModel = new UnitModel();
        $gstRateModel = new GstRateModel();

        // 3. Fetch the sales order
        $salesOrder = $salesModel->find($id);

        if (!$salesOrder) {
            return redirect()->to(site_url('distributor-sales'))->with('error', 'Sales order not found.');
        }

        // 4. Fetch all related data for the view
        $distributor = $distributorModel->find($salesOrder['distributor_id']);
        $marketingPerson = $marketingPersonModel->find($salesOrder['marketing_person_id']);
        $salesOrderItems = $salesItemModel->where('distributor_sales_order_id', $id)->findAll();
        $payments = $paymentModel->where('distributor_sales_order_id', $id)->findAll();

        // 5. Loop through sales order items to fetch product details
        foreach ($salesOrderItems as $key => $item) {
            $product = $sellingProductModel->find($item['product_id']);
            if ($product) {
                $salesOrderItems[$key]['product_name'] = $product['name'];
                $unit = $unitModel->find($product['unit_id'] ?? null);
                $salesOrderItems[$key]['unit_name'] = $unit['name'] ?? 'N/A';
                $salesOrderItems[$key]['unit_price_at_sale'] = $item['unit_price_at_sale'];
                $salesOrderItems[$key]['quantity'] = $item['quantity'];
                $salesOrderItems[$key]['item_total'] = $item['item_total'];
            }
        }

        // --- CORRECTED LOGIC: Use the array directly as it's already decoded ---
        $gst_rates_details = [];
        $gstIds = $salesOrder['overall_gst_rate_ids'] ?? [];

        // Check if we have valid IDs before querying the database
        if (!empty($gstIds) && is_array($gstIds)) {
            // Fetch the GST details from the `gst_rates` table
            $gst_rates_details = $gstRateModel->whereIn('id', $gstIds)->findAll();
        }
        // --- END OF CORRECTED LOGIC ---

        // 6. Prepare the data array to send to the view
        $data = [
            'sales_order' => $salesOrder,
            'distributor' => $distributor,
            'marketing_person' => $marketingPerson,
            'sales_order_items' => $salesOrderItems,
            'payments' => $payments,
            'gst_rates_details' => $gst_rates_details, // Pass the GST data
            'title' => 'Sales Order Details',
        ];

        // 7. Return the view with the data
        return view('distributorsales/view', $data);
    }


    public function edit(int $id): string
    {
        // Load necessary models
        $salesOrderModel = new DistributorSalesOrderModel();
        $distributorModel = new DistributorModel();
        $marketingPersonModel = new MarketingPersonModel();
        $sellingProductModel = new SellingProductModel();
        $salesOrderItemModel = new DistributorSalesOrderItemModel();
        $unitModel = new UnitModel();
        $gstRateModel = new GstRateModel();

        // Fetch the sales order and associated items
        $sales_order = $salesOrderModel->find($id);
        if (!$sales_order) {
            return redirect()->to('distributor-sales')->with('error', 'Sales order not found.');
        }

        // Fetch related data for dropdowns
        $distributors = $distributorModel->findAll();
        $marketing_persons = $marketingPersonModel->findAll();
        $products = $sellingProductModel->findAll();
        $units = $unitModel->findAll();
        $gst_rates = $gstRateModel->findAll();

        // Fix for "Undefined array key" in the view when rendering the product dropdown.
        // We'll ensure every product has a 'gst_rate_id' key.
        foreach ($products as &$product_option) {
            $product_option['gst_rate_id'] = $product_option['gst_rate_id'] ?? null;
        }

        // Fetch sales order items and enrich them with product details
        $sales_order_items = $salesOrderItemModel->where('distributor_sales_order_id', $id)->findAll();
        foreach ($sales_order_items as &$item) {
            $product = $sellingProductModel->find($item['product_id']);
            if ($product) {
                $item['product_name'] = $product['name'];
                // Fix for "Undefined array key" error
                $item['gst_rate_id'] = $product['gst_rate_id'] ?? null;
                $unit = $unitModel->find($product['unit_id']);
                $item['unit_name'] = $unit ? $unit['name'] : '';
            }
        }

        $data = [
            'sales_order' => $sales_order,
            'distributors' => $distributors,
            'marketing_persons' => $marketing_persons,
            'products' => $products,
            'sales_order_items' => $sales_order_items,
            'units' => $units,
            'gst_rates' => $gst_rates,
        ];

        return view('distributorsales/edit', $data);
    }


    public function update(int $id)
    {
        // Load models
        $salesOrderModel = new DistributorSalesOrderModel();
        $salesOrderItemModel = new DistributorSalesOrderItemModel();
        $sellingProductModel = new SellingProductModel();
        $gstRateModel = new GstRateModel();

        // Fetch the existing sales order to get old data
        $oldSalesOrder = $salesOrderModel->find($id);
        if (!$oldSalesOrder) {
            return redirect()->to('distributor-sales')->with('error', 'Sales order not found.');
        }

        // Define validation rules
        $rules = [
            'invoice_date' => 'required|valid_date',
            'status' => 'required|in_list[Pending,Partially Paid,Paid,Cancelled]',
            'distributor_id' => 'required|integer|is_not_unique[distributors.id]',
            'marketing_person_id' => 'required|integer|is_not_unique[marketing_persons.id]',
            'items.*.product_id' => 'required|integer|is_not_unique[selling_products.id]',
            'items.*.quantity' => 'required|numeric|greater_than[0]',
            'items.*.unit_price_at_sale' => 'required|numeric|greater_than_equal_to[0]',
        ];

        // Process form data
        if (!$this->validate($rules)) {
            // If validation fails, redirect back with errors and old input
            return redirect()->back()->withInput()->with('error', 'Please correct the errors in the form.');
        }

        // Calculate new totals based on the submitted items
        $items = $this->request->getPost('items');
        $totalAmountBeforeGst = 0;
        $totalGstAmount = 0;
        $overallGstRateIds = [];

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price_at_sale'];
            $totalAmountBeforeGst += $itemTotal;

            // Fetch the product to get its GST rate ID
            $product = $sellingProductModel->find($item['product_id']);
            if ($product && !empty($product['gst_rate_id'])) {
                $gstRate = $gstRateModel->find($product['gst_rate_id']);
                if ($gstRate) {
                    $itemGstAmount = ($itemTotal * $gstRate['rate']) / 100;
                    $totalGstAmount += $itemGstAmount;
                    if (!in_array($product['gst_rate_id'], $overallGstRateIds)) {
                        $overallGstRateIds[] = $product['gst_rate_id'];
                    }
                }
            }
        }

        $finalTotalAmount = $totalAmountBeforeGst + $totalGstAmount;
        $dueAmount = $finalTotalAmount - $oldSalesOrder['amount_paid'];

        // Prepare data for the sales order table
        $salesOrderData = [
            'invoice_date' => $this->request->getPost('invoice_date'),
            'status' => $this->request->getPost('status'),
            'distributor_id' => $this->request->getPost('distributor_id'),
            'marketing_person_id' => $this->request->getPost('marketing_person_id'),
            'total_amount_before_gst' => $totalAmountBeforeGst,
            'total_gst_amount' => $totalGstAmount,
            'final_total_amount' => $finalTotalAmount,
            'due_amount' => $dueAmount,
            'overall_gst_rate_ids' => $overallGstRateIds,
        ];

        // Update the main sales order record
        $salesOrderModel->update($id, $salesOrderData);

        // Delete old items and insert new ones
        $salesOrderItemModel->where('distributor_sales_order_id', $id)->delete();
        foreach ($items as $item) {
            $salesOrderItemModel->insert([
                'distributor_sales_order_id' => $id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price_at_sale' => $item['unit_price_at_sale'],
                'item_total' => $item['quantity'] * $item['unit_price_at_sale']
            ]);
        }

        // Redirect with a success message
        return redirect()->to('distributor-sales/view/' . $id)->with('success', 'Sales order updated successfully.');
    }

    public function addPayment($id = null)
    {
        // Check if an ID was provided.
        if ($id === null) {
            return redirect()->back()->with('error', 'Sales Order ID is missing.');
        }

        // Fetch the sales order details.
        $salesOrder = $this->distributorSalesOrderModel->find($id);

        if (!$salesOrder) {
            return redirect()->back()->with('error', 'Sales Order not found.');
        }

        // Fetch related distributor and marketing person details.
        $distributor = $this->distributorModel->find($salesOrder['distributor_id']);
        $marketingPerson = $this->marketingPersonModel->find($salesOrder['marketing_person_id']);

        // Define a list of payment methods to populate the dropdown.
        $paymentMethods = [
            '' => 'Select Payment Method (Optional)',
            'Cash' => 'Cash',
            'Bank Transfer' => 'Bank Transfer',
            'Card' => 'Card',
            'Online Payment' => 'Online Payment',
            'Cheque' => 'Cheque',
        ];

        $data = [
            'sales_order' => $salesOrder,
            'distributor' => $distributor,
            'marketing_person' => $marketingPerson,
            'due_amount' => $salesOrder['due_amount'],
            'paymentMethods' => $paymentMethods,
            'title' => 'Add New Payment',
        ];

        return view('distributorsales/add_payment', $data);
    }


    public function savePayment()
    {
        // Start a database transaction.
        $this->db->transBegin();

        try {
            $postData = $this->request->getPost();

            $validationRules = [
                'sales_order_id' => 'required|numeric',
                'amount' => 'required|numeric|greater_than[0]', // Ensure this matches the input field name
                'payment_method' => 'permit_empty|max_length[255]',
                'transaction_id' => 'permit_empty|max_length[255]',
            ];

            if (!$this->validate($validationRules)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('validation', $this->validator);
            }

            $salesOrderId = $postData['sales_order_id'];
            $paymentAmount = (float)($postData['amount']); // This must match the validation rule name

            // Get the current sales order details.
            $salesOrder = $this->distributorSalesOrderModel->find($salesOrderId);

            if (!$salesOrder) {
                $this->db->transRollback();
                return redirect()->back()->with('error', 'Sales Order not found.');
            }

            // Calculate new amounts.
            $newAmountPaid = $salesOrder['amount_paid'] + $paymentAmount;
            $newDueAmount = $salesOrder['due_amount'] - $paymentAmount;

            // Determine the new status.
            $newStatus = 'Partially Paid';
            if ($newDueAmount <= 0) {
                $newStatus = 'Paid';
            } elseif ($newAmountPaid == 0) {
                $newStatus = 'Pending';
            }

            // Update the sales order table.
            $this->distributorSalesOrderModel->update($salesOrderId, [
                'amount_paid' => $newAmountPaid,
                'due_amount' => $newDueAmount,
                'status' => $newStatus,
            ]);

            // Save the new payment record.
            $paymentData = [
                'distributor_sales_order_id' => $salesOrderId,
                'payment_date' => $postData['payment_date'] ?? Time::now()->toDateString(),
                'amount' => $paymentAmount,
                'payment_method' => $postData['payment_method'] ?? null,
                'transaction_id' => $postData['transaction_id'] ?? null,
                'notes' => $postData['notes'] ?? 'Payment for invoice ' . $salesOrder['invoice_number'],
            ];

            $this->distributorPaymentModel->insert($paymentData);

            $this->db->transCommit();
            session()->setFlashdata('success', 'Payment added successfully! The sales order status has been updated.');
            return redirect()->to(site_url('distributor-sales/view/' . $salesOrderId));
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Database Transaction Failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'A database error occurred. The payment could not be saved.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Exception in DistributorSalesController->savePayment(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }


    public function exportPdf(int $id)
    {
        // Load models with the correct names
        $salesModel = new DistributorSalesOrderModel();
        $distributorModel = new DistributorModel();
        $salesItemModel = new DistributorSalesOrderItemModel();
        $paymentModel = new DistributorPaymentModel();
        $sellingProductModel = new SellingProductModel();
        $unitModel = new UnitModel();
        $gstRateModel = new GstRateModel();


        // Fetch sales order details using the find() method
        $salesOrder = $salesModel->find($id);

        if (!$salesOrder) {
            return $this->response->setStatusCode(404)->setBody('Invoice not found.');
        }

        // Fetch related data
        $distributor = $distributorModel->find($salesOrder['distributor_id']);
        $salesOrderItems = $salesItemModel->where('distributor_sales_order_id', $id)->findAll();
        $payments = $paymentModel->where('distributor_sales_order_id', $id)->findAll();

        // Enrich sales order items with product and unit names, and calculate totals
        foreach ($salesOrderItems as $key => $item) {
            $product = $sellingProductModel->find($item['product_id']);
            if ($product) {
                // Accessing product_name as an array key
                $salesOrderItems[$key]['product_name'] = $product['name'];

                // Now, use the unit_id from the product to find the unit name
                $unit = $unitModel->find($product['unit_id'] ?? null);
                if ($unit) {
                    // Accessing unit_name as an array key
                    $salesOrderItems[$key]['unit_name'] = $unit['name'];
                }

                // Add the GST rate from the product
                $salesOrderItems[$key]['gst_rate_at_sale'] = $product['gst_rate'] ?? 0;

                // --- NEW LOGIC: Calculate item totals for the PDF view
                $quantity = (float)$item['quantity'];
                $unitPrice = (float)$item['unit_price_at_sale'];
                $gstRate = (float)$salesOrderItems[$key]['gst_rate_at_sale'];

                $salesOrderItems[$key]['item_total_before_gst'] = $quantity * $unitPrice;
                $salesOrderItems[$key]['item_gst_amount'] = ($salesOrderItems[$key]['item_total_before_gst'] * $gstRate) / 100;
                $salesOrderItems[$key]['item_final_total'] = $salesOrderItems[$key]['item_total_before_gst'] + $salesOrderItems[$key]['item_gst_amount'];
                // --- END OF NEW LOGIC ---
            }
        }

        // Fetch only the filenames from the database
        $companyLogoFilename = $this->companySettingModel->getSetting('company_logo');
        $companyStampFilename = $this->companySettingModel->getSetting('company_stamp');
        $companySignatureFilename = $this->companySettingModel->getSetting('company_signature');

        // **CRUCIAL CHANGE:** Convert images to Base64 strings for embedding in PDF
        $company_logo_data = null;
        $company_stamp_data = null;
        $company_signature_data = null;

        $logoPath = $companyLogoFilename ? ROOTPATH . 'public/uploads/company_images/' . $companyLogoFilename : null;
        if ($logoPath && file_exists($logoPath)) {
            $company_logo_data = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $stampPath = $companyStampFilename ? ROOTPATH . 'public/uploads/company_images/' . $companyStampFilename : null;
        if ($stampPath && file_exists($stampPath)) {
            $company_stamp_data = 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath));
        }

        $signaturePath = $companySignatureFilename ? ROOTPATH . 'public/uploads/company_images/' . $companySignatureFilename : null;
        if ($signaturePath && file_exists($signaturePath)) {
            $company_signature_data = 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath));
        }


        $gst_rates_details = [];
        $gstIds = $salesOrder['overall_gst_rate_ids'] ?? [];
        // Check if we have valid IDs before querying the database
        if (!empty($gstIds) && is_array($gstIds)) {
            // Fetch the GST details from the `gst_rates` table
            $gst_rates_details = $gstRateModel->whereIn('id', $gstIds)->findAll();
        }
        // Prepare data for the view
        $data = [
            'sales_order' => $salesOrder,
            'distributor' => $distributor,
            'sales_order_items' => $salesOrderItems,
            'gst_rates_details' => $gst_rates_details, // Pass the GST data
            'payments' => $payments,


            'company_logo_data' => $company_logo_data,
            'company_stamp_data' => $company_stamp_data,
            'company_signature_data' => $company_signature_data,
        ];

        // Instantiate and configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Load the HTML content from the view file
        $html = view('distributorsales/invoice_pdf', $data);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        // The second parameter 'D' forces a download, 'I' forces an inline display
        $dompdf->stream('Invoice_' . $salesOrder['invoice_number'] . '.pdf', ['Attachment' => 1]);
    }

    public function exportExcel(int $id)
    {
        // Load models with the correct names
        $salesModel = new DistributorSalesOrderModel();
        $distributorModel = new DistributorModel();
        $salesItemModel = new DistributorSalesOrderItemModel();
        $paymentModel = new DistributorPaymentModel();
        $sellingProductModel = new SellingProductModel();
        $unitModel = new UnitModel();
        $gstRateModel = new GstRateModel();

        // Fetch sales order details using the find() method
        $salesOrder = $salesModel->find($id);

        if (!$salesOrder) {
            // Return a 404 response if the invoice is not found
            return $this->response->setStatusCode(404)->setBody('Invoice not found.');
        }

        // Fetch related data
        $distributor = $distributorModel->find($salesOrder['distributor_id']);
        $salesOrderItems = $salesItemModel->where('distributor_sales_order_id', $id)->findAll();
        $payments = $paymentModel->where('distributor_sales_order_id', $id)->findAll();

        // Enrich sales order items with product and unit names
        foreach ($salesOrderItems as $key => $item) {
            $product = $sellingProductModel->find($item['product_id']);
            if ($product) {
                $salesOrderItems[$key]['product_name'] = $product['name'];
                $unit = $unitModel->find($product['unit_id'] ?? null);
                if ($unit) {
                    $salesOrderItems[$key]['unit_name'] = $unit['name'];
                }

                // Add the GST rate from the product
                $salesOrderItems[$key]['gst_rate_at_sale'] = $product['gst_rate'] ?? 0;
            }
        }

        // Fetch GST rates details
        $gst_rates_details = [];
        $gstIds = $salesOrder['overall_gst_rate_ids'] ?? [];
        if (!empty($gstIds) && is_array($gstIds)) {
            $gst_rates_details = $gstRateModel->whereIn('id', $gstIds)->findAll();
        }

        // --- EXCEL GENERATION LOGIC ---

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice-' . $salesOrder['invoice_number']);
        $currentRow = 1;

        // --- A. INVOICE HEADER AND COMPANY INFO ---
        $sheet->setCellValue('A' . $currentRow, 'SAMHITA SOIL SOLUTIONS');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, '2-46-26/21, Venkat Nager, Kakinada-533003');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'GSTIN: 37AQFPB2946M1ZN | Phone: 9848549349, 9491822559');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow += 2;

        $sheet->setCellValue('A' . $currentRow, 'INVOICE: ' . $salesOrder['invoice_number']);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Date: ' . date('d-M-Y', strtotime($salesOrder['invoice_date'])));
        $currentRow += 2;

        // --- B. DISTRIBUTOR DETAILS ---
        $sheet->setCellValue('A' . $currentRow, 'Bill To:');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Agency Name: ' . ($distributor['agency_name'] ?? 'N/A'));
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Owner Name: ' . ($distributor['owner_name'] ?? 'N/A'));
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Address: ' . ($distributor['agency_address'] ?? 'N/A'));
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'GSTIN: ' . ($distributor['agency_gst_number'] ?? 'N/A'));
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Phone: ' . ($distributor['owner_phone'] ?? 'N/A'));
        $currentRow += 2;

        // --- C. ITEM TABLE ---
        $sheet->setCellValue('A' . $currentRow, 'S.No');
        $sheet->setCellValue('B' . $currentRow, 'Product');
        $sheet->setCellValue('C' . $currentRow, 'Quantity');
        $sheet->setCellValue('D' . $currentRow, 'Unit Price');
        $sheet->setCellValue('E' . $currentRow, 'Amount (Excl. GST)');
        $sheet->setCellValue('F' . $currentRow, 'GST Rate (%)');
        $sheet->setCellValue('G' . $currentRow, 'GST Amount');
        $sheet->setCellValue('H' . $currentRow, 'Total Amount');

        $headerStyle = $sheet->getStyle('A' . $currentRow . ':H' . $currentRow);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
        $headerStyle->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $currentRow++;

        $i = 1;
        foreach ($salesOrderItems as $item) {
            $quantity = (float)$item['quantity'];
            $unitPrice = (float)$item['unit_price_at_sale'];
            $gstRate = (float)$item['gst_rate_at_sale'];
            $amountExclGst = $quantity * $unitPrice;
            $gstAmount = ($amountExclGst * $gstRate) / 100;
            $finalTotal = $amountExclGst + $gstAmount;

            $sheet->setCellValue('A' . $currentRow, $i++);
            $sheet->setCellValue('B' . $currentRow, $item['product_name']);
            $sheet->setCellValue('C' . $currentRow, $quantity . ' ' . ($item['unit_name'] ?? ''));
            $sheet->setCellValue('D' . $currentRow, $unitPrice);
            $sheet->setCellValue('E' . $currentRow, $amountExclGst);
            $sheet->setCellValue('F' . $currentRow, $gstRate . '%');
            $sheet->setCellValue('G' . $currentRow, $gstAmount);
            $sheet->setCellValue('H' . $currentRow, $finalTotal);
            $currentRow++;
        }

        $currentRow += 2;

        // --- D. TOTALS TABLE ---
        $sheet->setCellValue('F' . $currentRow, 'Subtotal:');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['sub_total']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('F' . $currentRow, 'Discount:');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['discount_amount']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('F' . $currentRow, 'Total Amount (Before GST):');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['total_amount_before_gst']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('F' . $currentRow, 'Total GST Amount:');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['total_gst_amount']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('F' . $currentRow, 'Grand Total:');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['final_total_amount']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
        $currentRow++;

        // Add the amount in words. Note: This requires a `convertNumberToWords` function to be defined.
        $amountInWords = 'Amount in words: ' . (function_exists('convertNumberToWords') ? convertNumberToWords($salesOrder['final_total_amount']) : 'Function not found') . ' Rupees Only.';
        $sheet->setCellValue('A' . $currentRow, $amountInWords);
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true)->setBold(true);
        $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
        $currentRow++;

        $sheet->setCellValue('F' . $currentRow, 'Amount Paid:');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['amount_paid']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('F' . $currentRow, 'Amount Due:');
        $sheet->setCellValue('G' . $currentRow, $salesOrder['due_amount']);
        $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true)->getColor()->setARGB('FFD9534F');
        $currentRow += 2;

        // --- E. PAYMENT HISTORY ---
        $sheet->setCellValue('A' . $currentRow, 'Payment History');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12);
        $currentRow++;

        if (!empty($payments)) {
            $headers_payments = ['Payment Date', 'Amount', 'Method', 'Transaction ID', 'Notes'];
            $sheet->fromArray($headers_payments, NULL, 'A' . $currentRow);
            $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            $currentRow++;
            foreach ($payments as $payment) {
                $col = 'A';
                $sheet->setCellValue($col++ . $currentRow, date('Y-m-d', strtotime($payment['payment_date'])));
                $sheet->setCellValue($col++ . $currentRow, $payment['amount']);
                $sheet->setCellValue($col++ . $currentRow, $payment['payment_method'] ?? 'N/A');
                $sheet->setCellValue($col++ . $currentRow, $payment['transaction_id'] ?? 'N/A');
                $sheet->setCellValue($col++ . $currentRow, $payment['notes'] ?? 'N/A');
                $currentRow++;
            }
        } else {
            $sheet->setCellValue('A' . $currentRow, 'No payments recorded for this invoice yet.');
            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
            $currentRow++;
        }

        // --- F. FINAL TOUCHES ---
        // Auto-size all columns for better readability
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Apply number formatting to currency columns
        $currencyColumns = ['D', 'E', 'G', 'H'];
        foreach ($currencyColumns as $col) {
            $sheet->getStyle($col . '13:' . $col . ($currentRow - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $sheet->getStyle('G' . ($currentRow - 1) . ':G' . ($currentRow - 7))->getNumberFormat()->setFormatCode('#,##0.00');


        // --- G. OUTPUT THE EXCEL FILE ---
        // Set HTTP headers for a file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Invoice_' . $salesOrder['invoice_number'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

   public function soldStockOverview()
    {
        // Use the query builder to get the total quantity sold for each product
        $sold_stock = $this->db->table('distributor_sales_order_items')
            // Select the product name, the unit name from the 'units' table, and the total sold quantity
            ->select('selling_products.name as product_name, units.name as unit, SUM(distributor_sales_order_items.quantity) as total_sold_quantity')
            // Join with the selling_products table
            ->join('selling_products', 'selling_products.id = distributor_sales_order_items.product_id')
            // Add a new join to the units table using the foreign key
            ->join('units', 'units.id = selling_products.unit_id')
            // Group by both the product name and the unit name for accurate aggregation
            ->groupBy('selling_products.name, units.name')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Stock Sold Overview',
            'sold_stock' => $sold_stock,
        ];

        return view('distributorsales/sold_stock_overview', $data);
    }
}
