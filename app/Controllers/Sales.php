<?php

namespace App\Controllers;

use App\Models\SalesModel;
use App\Models\ProductModel;
use App\Models\MarketingPersonModel;
use App\Models\MarketingDistributionModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Dompdf\Dompdf;
use Dompdf\Options;

class Sales extends BaseController
{
    protected $salesModel;
    protected $productModel;
    protected $personModel;
    protected $marketingPersonModel;
    protected $marketingDistributionModel;
    protected $salePaymentModel;
    protected $paymentModel;
    protected $session;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->salesModel = new SalesModel();
        $this->productModel = new ProductModel();
        $this->personModel = new MarketingPersonModel();
        $this->marketingPersonModel = new MarketingPersonModel();
        $this->marketingDistributionModel = new MarketingDistributionModel();
        $this->salePaymentModel = new \App\Models\SalePaymentModel();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $builder = $this->salesModel->builder();
        $builder->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id,
                      sales.amount_received_from_person, sales.balance_from_person, sales.payment_status_from_person'); // Add new fields here
        $builder->join('products', 'products.id = sales.product_id');
        $builder->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id');


        if ($productId = $this->request->getGet('product_id')) {
            $builder->where('sales.product_id', $productId);
        }

        if ($personId = $this->request->getGet('marketing_person_id')) {
            $builder->where('sales.marketing_person_id', $personId);
        }

        if ($dateSold = $this->request->getGet('date_sold')) {
            $builder->where('sales.date_sold', $dateSold);
        }

        if ($customerName = $this->request->getGet('customer_name')) {
            $builder->like('sales.customer_name', $customerName);
        }

        $data['sales'] = $builder->get()->getResultArray();
        $data['products'] = $this->productModel->findAll();
        $data['marketing_persons'] = $this->personModel->findAll();

        return view('sales/index', $data);
    }

    private function getFilteredSales()
    {
        $builder = $this->salesModel->builder();

        $builder->select('
            sales.*,
            products.name as product_name,
            marketing_persons.name as person_name,
            marketing_persons.custom_id
        ');

        $builder->join('products', 'products.id = sales.product_id');
        $builder->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id');

        $productId = $this->request->getGet('product_id');
        $personId = $this->request->getGet('marketing_person_id');
        $dateSold = $this->request->getGet('date_sold');
        $customerName = $this->request->getGet('customer_name');

        if ($productId) {
            $builder->where('sales.product_id', $productId);
        }
        if ($personId) {
            $builder->where('sales.marketing_person_id', $personId);
        }
        if ($dateSold) {
            $builder->where('sales.date_sold', $dateSold);
        }
        if ($customerName) {
            $builder->like('sales.customer_name', $customerName);
        }

        return $builder->get()->getResultArray();
    }

    public function create()
    {
        return view('sales/create', [
            'products' => $this->productModel->findAll(),
            'marketing_persons' => $this->personModel->findAll()
        ]);
    }

    /**
     * AJAX endpoint to get product details (price, remaining stock).
     * Now fetches selling_price from stock_in table.
     */
    public function productDetails()
    {
        $productId = $this->request->getGet('product_id');
        $personId = $this->request->getGet('marketing_person_id');

        // Basic validation for AJAX request parameters
        if (empty($productId) || empty($personId)) {
            return $this->response->setJSON(['price_per_unit' => 0, 'remaining_qty' => 0]);
        }

        $db = \Config\Database::connect();

        // --- FETCH SELLING PRICE FROM stock_in ---
        // Get the latest selling price for the product from stock_in
        $priceQuery = $db->table('stock_in')
            ->select('selling_price')
            ->where('product_id', $productId)
            ->orderBy('id', 'DESC') // Assuming higher ID means more recent entry
            ->limit(1)
            ->get();
        $priceRow = $priceQuery->getRow();
        $pricePerUnit = $priceRow ? $priceRow->selling_price : 0;

        // Calculate remaining stock based on marketing_distribution and sales
        $issuedQuery = $db->table('marketing_distribution')
            ->selectSum('quantity_issued', 'total_issued') // Alias for clarity
            ->where('product_id', $productId)
            ->where('marketing_person_id', $personId)
            ->get();
        $totalIssued = (int)($issuedQuery->getRow()->total_issued ?? 0); // Use aliased column

        $soldQuery = $db->table('sales')
            ->selectSum('quantity_sold', 'total_sold') // Alias for clarity
            ->where('product_id', $productId)
            ->where('marketing_person_id', $personId)
            ->get();
        $totalSold = (int)($soldQuery->getRow()->total_sold ?? 0); // Use aliased column

        $remaining = $totalIssued - $totalSold;

        return $this->response->setJSON([
            'price_per_unit' => (float)$pricePerUnit,
            'remaining_qty' => (int)$remaining
        ]);
    }


    public function storeMultiple()
    {
        $productId = $this->request->getPost('product_id');
        $marketingPersonId = $this->request->getPost('marketing_person_id');
        $salesData = $this->request->getPost('sales');

        // 1. Basic check if any sales data was submitted
        if (empty($salesData)) {
            return redirect()->back()->with('error', 'No sales entries provided to save.');
        }

        $db = \Config\Database::connect();
        $db->transBegin(); // Start a database transaction for atomicity

        try {
            // 2. Fetch Initial Remaining Stock ONCE for the entire batch
            // Get total issued quantity for this product and person
            $issuedQuery = $db->table('marketing_distribution')
                ->selectSum('quantity_issued', 'total_issued')
                ->where('product_id', $productId)
                ->where('marketing_person_id', $marketingPersonId)
                ->get();
            $initialTotalIssued = (int)($issuedQuery->getRow()->total_issued ?? 0);

            // Get total sold quantity for this product and person from *already recorded* sales (in DB)
            $soldQuery = $db->table('sales')
                ->selectSum('quantity_sold', 'total_sold')
                ->where('product_id', $productId)
                ->where('marketing_person_id', $marketingPersonId)
                ->get();
            $totalSoldBeforeBatch = (int)($soldQuery->getRow()->total_sold ?? 0);

            // This is the actual remaining stock BEFORE processing any of the current form submissions
            // This variable will be decremented as sales in the current batch are validated
            $currentAvailableStock = $initialTotalIssued - $totalSoldBeforeBatch;

            // 3. Loop through each submitted sales entry
            foreach ($salesData as $index => $saleEntry) {
                // Ensure data types are correct and provide defaults if necessary
                $quantitySold = (int)($saleEntry['quantity_sold'] ?? 0);
                $pricePerUnit = (float)($saleEntry['price_per_unit'] ?? 0); // This should come from productDetails() on client-side
                $discount = (float)($saleEntry['discount'] ?? 0);
                $dateSold = $saleEntry['date_sold'] ?? date('Y-m-d');

                // Basic validation for quantity sold
                if ($quantitySold <= 0) {
                    throw new \Exception('Quantity sold must be a positive number for entry #' . ($index + 1) . '.');
                }

                // --- Stock Validation against the RUNNING $currentAvailableStock ---
                if ($quantitySold > $currentAvailableStock) {
                    throw new \Exception("Validation failed for entry #" . ($index + 1) . ": Cannot sell {$quantitySold} units. Only {$currentAvailableStock} units are remaining for the selected product and marketing person.");
                }

                // Calculate total for this specific sale entry, matching client-side logic
                $totalPrice = ($quantitySold * $pricePerUnit) - $discount;
                if ($totalPrice < 0) {
                    throw new \Exception("Total price cannot be negative for entry #" . ($index + 1) . ". Check quantity, price, and discount.");
                }

                // Prepare data for saving for this individual sale entry
                $dataToSave = [
                    'product_id' => $productId,
                    'marketing_person_id' => $marketingPersonId,
                    'quantity_sold' => $quantitySold,
                    'price_per_unit' => $pricePerUnit,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'date_sold' => $dateSold,
                    'customer_name' => $saleEntry['customer_name'] ?? null,
                    'customer_phone' => $saleEntry['customer_phone'] ?? null,
                    'customer_address' => $saleEntry['customer_address'] ?? null,
                    // --- NEW FIELDS INITIALIZATION ---
                    'amount_received_from_person' => 0.00, // Initially, no amount received
                    'balance_from_person' => $totalPrice,  // Initially, balance is the full total price
                    'payment_status_from_person' => 'Pending', // Initially, payment is pending
                    'last_remittance_date' => null, // No remittance yet
                ];

                // Save the sale entry using the model
                if (!$this->salesModel->insert($dataToSave)) {
                    // Get validation errors from the model if insert fails
                    $errorMessage = 'Failed to save sales entry #' . ($index + 1) . '. Error: ' . implode(', ', $this->salesModel->errors());
                    throw new \Exception($errorMessage);
                }

                // IMPORTANT: Decrement the running available stock for the next iteration in this batch
                $currentAvailableStock -= $quantitySold;
            }

            // 4. If loop completes without errors, commit the transaction
            $db->transCommit();
            return redirect()->to('/sales/create')->with('success', 'All sales entries saved successfully!');
        } catch (\Exception $e) {
            // 5. If any error occurred, rollback the transaction
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function view($id)
    {
        $request = \Config\Services::request();
        $filterProductId = $request->getGet('product_id'); // This is for filtering the 'All Sales of Person' table

        // Fetch the specific sale record, including product and marketing person names,
        // and all the new payment-related fields.
        $specificSale = $this->salesModel
            ->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id,
                     sales.amount_received_from_person, sales.balance_from_person, sales.payment_status_from_person, sales.last_remittance_date')
            ->join('products', 'products.id = sales.product_id')
            ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
            ->find($id);

        // If the specific sale isn't found, redirect with an error.
        if (empty($specificSale)) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale record not found.');
        }

        $marketingPersonId = $specificSale['marketing_person_id'];

        // --- Fetch all sales for this specific marketing person (with optional product filter) ---
        // This section provides a list of all sales made by the marketing person linked to the current sale.
        $salesQuery = $this->salesModel
            ->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id,
                      sales.amount_received_from_person, sales.balance_from_person, sales.payment_status_from_person') // Include new fields here for the list
            ->join('products', 'products.id = sales.product_id')
            ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
            ->where('sales.marketing_person_id', $marketingPersonId);

        // Apply product filter if specified in the URL for the 'All Sales of Person' table
        if ($filterProductId) {
            $salesQuery->where('sales.product_id', $filterProductId);
        }

        $allSalesOfPerson = $salesQuery
            ->orderBy('sales.date_sold', 'DESC')
            ->orderBy('sales.id', 'DESC')
            ->findAll();

        // --- Calculate Summary Totals for the Marketing Person ---
        // This calculates overall totals (issued, sold, remaining, discount, total price, remitted, balance due)
        // for the marketing person, optionally filtered by product.

        // 1. Summary from 'sales' table: quantities sold, discounts, total sale prices, and payment amounts
        $salesSummaryQuery = $this->db->table('sales')
            ->selectSum('quantity_sold', 'total_quantity_sold')
            ->selectSum('discount', 'total_discount')
            ->selectSum('total_price', 'overall_total_price')
            ->selectSum('amount_received_from_person', 'total_amount_remitted') // Sum of amounts remitted by the person
            ->selectSum('balance_from_person', 'total_balance_due') // Sum of outstanding balances
            ->where('marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $salesSummaryQuery->where('product_id', $filterProductId);
        }
        $salesSummaryResult = $salesSummaryQuery->get()->getRowArray();

        // 2. Total Quantity Issued from 'marketing_distribution' table
        $distributionSummaryQuery = $this->db->table('marketing_distribution')
            ->selectSum('quantity_issued', 'total_qty_issued')
            ->where('marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $distributionSummaryQuery->where('product_id', $filterProductId);
        }
        $distributionSummaryResult = $distributionSummaryQuery->get()->getRowArray();

        // Combine and calculate final summary
        $totalQtyIssued = $distributionSummaryResult['total_qty_issued'] ?? 0;
        $totalQtySold = $salesSummaryResult['total_quantity_sold'] ?? 0;
        $totalDiscount = $salesSummaryResult['total_discount'] ?? 0;
        $overallTotalPrice = $salesSummaryResult['overall_total_price'] ?? 0;
        $totalAmountRemitted = $salesSummaryResult['total_amount_remitted'] ?? 0;
        $totalBalanceDue = $salesSummaryResult['total_balance_due'] ?? 0;
        $totalRemaining = $totalQtyIssued - $totalQtySold;

        // Package summary data for the view
        $summary = [
            'total_qty_issued' => $totalQtyIssued,
            'total_quantity_sold' => $totalQtySold,
            'total_remaining' => $totalRemaining,
            'total_discount' => $totalDiscount,
            'overall_total_price' => $overallTotalPrice,
            'total_amount_remitted' => $totalAmountRemitted,
            'total_balance_due' => $totalBalanceDue,
        ];

        // Fetch all products to populate the filter dropdown
        $productsForFilter = $this->productModel->findAll();

        // Prepare data array to be passed to the view
        $data = [
            'specificSale' => $specificSale, // Details of the sale being viewed
            'marketingPersonName' => $specificSale['person_name'], // Name of the marketing person
            'marketingPersonCustomId' => $specificSale['custom_id'], // Custom ID of the marketing person
            'marketingPersonId' => $marketingPersonId, // ID of the marketing person
            'allSalesOfPerson' => $allSalesOfPerson, // List of all sales by this person (filtered)
            'specificSaleId' => $id, // The ID of the current sale for context
            'summary' => $summary, // The calculated summary totals
            'productsForFilter' => $productsForFilter, // Products for the filter dropdown
            'selectedProductId' => $filterProductId, // Currently selected product in the filter
            'title' => 'Sale Details for #' . $id // Page title
        ];

        // Load the view and pass the data
        return view('sales/view_details', $data);
    }

    // ... (rest of your Sales class) ...


    // ... (Your export methods also need the same table/column name corrections if they use marketing_distribution data) ...

    /**
     * Exports sales of a specific marketing person to Excel.
     * Optional product filter can be applied.
     * This needs PhpOffice/PhpSpreadsheet installed (composer require phpoffice/phpspreadsheet)
     * @param int $marketingPersonId
     */
    public function exportPersonSalesExcel($marketingPersonId)
    {
        $request = \Config\Services::request();
        $filterProductId = $request->getGet('product_id');

        $salesQuery = $this->salesModel
            ->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id')
            ->join('products', 'products.id = sales.product_id')
            ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
            ->where('sales.marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $salesQuery->where('sales.product_id', $filterProductId);
        }

        $salesData = $salesQuery->orderBy('sales.date_sold', 'DESC')->orderBy('sales.id', 'DESC')->findAll();

        // --- Fetch summary data for Excel (if you want to include it in the Excel) ---
        $db = \Config\Database::connect();
        $salesSummaryQuery = $db->table('sales')
            ->selectSum('quantity_sold', 'total_quantity_sold')
            ->selectSum('discount', 'total_discount')
            ->selectSum('total_price', 'overall_total_price')
            ->where('marketing_person_id', $marketingPersonId);
        if ($filterProductId) {
            $salesSummaryQuery->where('product_id', $filterProductId);
        }
        $salesSummaryResult = $salesSummaryQuery->get()->getRowArray();

        $distributionSummaryQuery = $db->table('marketing_distribution') // <--- CORRECTED TABLE NAME
            ->selectSum('quantity_issued', 'total_qty_issued') // <--- CORRECTED COLUMN NAME
            ->where('marketing_person_id', $marketingPersonId);
        if ($filterProductId) {
            $distributionSummaryQuery->where('product_id', $filterProductId);
        }
        $distributionSummaryResult = $distributionSummaryQuery->get()->getRowArray();

        $totalQtyIssued = $distributionSummaryResult['total_qty_issued'] ?? 0;
        $totalQtySold = $salesSummaryResult['total_quantity_sold'] ?? 0;
        $totalRemaining = $totalQtyIssued - $totalQtySold;
        $overallTotalPriceSummary = $salesSummaryResult['overall_total_price'] ?? 0;
        // --- End summary fetch for Excel ---


        // Load Spreadsheet library
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Headers for the list
        $sheet->setCellValue('A1', 'S.No.');
        $sheet->setCellValue('B1', 'Sale Date');
        $sheet->setCellValue('C1', 'Product');
        $sheet->setCellValue('D1', 'Quantity Sold');
        $sheet->setCellValue('E1', 'Price/Unit');
        $sheet->setCellValue('F1', 'Discount');
        $sheet->setCellValue('G1', 'Total Price');
        $sheet->setCellValue('H1', 'Customer Name');
        $sheet->setCellValue('I1', 'Customer Phone');
        $sheet->setCellValue('J1', 'Customer Address');
        $sheet->setCellValue('K1', 'Marketing Person');
        $sheet->setCellValue('L1', 'Marketing Person ID');

        $row = 2; // Start data from row 2
        $s_no = 1;
        foreach ($salesData as $sale) {
            $sheet->setCellValue('A' . $row, $s_no++);
            $sheet->setCellValue('B' . $row, $sale['date_sold']);
            $sheet->setCellValue('C' . $row, $sale['product_name']);
            $sheet->setCellValue('D' . $row, $sale['quantity_sold']);
            $sheet->setCellValue('E' . $row, $sale['price_per_unit']);
            $sheet->setCellValue('F' . $row, $sale['discount']);
            $sheet->setCellValue('G' . $row, $sale['total_price']);
            $sheet->setCellValue('H' . $row, $sale['customer_name']);
            $sheet->setCellValue('I' . $row, $sale['customer_phone']);
            $sheet->setCellValue('J' . $row, $sale['customer_address']);
            $sheet->setCellValue('K' . $row, $sale['person_name']);
            $sheet->setCellValue('L' . $row, $sale['custom_id']);
            $row++;
        }

        // Add a summary row at the end if there's data
        if (!empty($salesData)) {
            $row++; // Add a blank row for separation
            $sheet->setCellValue('C' . $row, 'TOTAL');
            $sheet->setCellValue('D' . $row, $totalQtySold);
            // No specific column for total price/unit in summary.
            // No specific column for total discount in summary table, but it's calculated.
            $sheet->setCellValue('G' . $row, $overallTotalPriceSummary);
            // You might want to add total_qty_issued and total_remaining here too
            // Example: $sheet->setCellValue('B' . $row, 'Total Qty Issued: ' . $totalQtyIssued);
            // $sheet->setCellValue('C' . $row, 'Total Remaining: ' . $totalRemaining);
        }

        // Set auto size for columns
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Sales_by_' . url_title($salesData[0]['person_name'] ?? 'MarketingPerson', '-', true) . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit();
    }


    /**
     * Exports sales of a specific marketing person to PDF.
     * Optional product filter can be applied.
     * This needs dompdf/dompdf installed (composer require dompdf/dompdf)
     * @param int $marketingPersonId
     */
    public function exportPersonSalesPDF($marketingPersonId)
    {
        $request = \Config\Services::request();
        $filterProductId = $request->getGet('product_id');

        $salesQuery = $this->salesModel
            ->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id')
            ->join('products', 'products.id = sales.product_id')
            ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
            ->where('sales.marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $salesQuery->where('sales.product_id', $filterProductId);
        }

        $salesData = $salesQuery->orderBy('sales.date_sold', 'DESC')->orderBy('sales.id', 'DESC')->findAll();

        // --- Fetch summary data for PDF ---
        $db = \Config\Database::connect();
        $salesSummaryQuery = $db->table('sales')
            ->selectSum('quantity_sold', 'total_quantity_sold')
            ->selectSum('discount', 'total_discount')
            ->selectSum('total_price', 'overall_total_price')
            ->where('marketing_person_id', $marketingPersonId);
        if ($filterProductId) {
            $salesSummaryQuery->where('product_id', $filterProductId);
        }
        $salesSummaryResult = $salesSummaryQuery->get()->getRowArray();

        $distributionSummaryQuery = $db->table('marketing_distribution') // <--- CORRECTED TABLE NAME
            ->selectSum('quantity_issued', 'total_qty_issued') // <--- CORRECTED COLUMN NAME
            ->where('marketing_person_id', $marketingPersonId);
        if ($filterProductId) {
            $distributionSummaryQuery->where('product_id', $filterProductId);
        }
        $distributionSummaryResult = $distributionSummaryQuery->get()->getRowArray();

        $summaryPdf = [
            'total_qty_issued' => $distributionSummaryResult['total_qty_issued'] ?? 0,
            'total_quantity_sold' => $salesSummaryResult['total_quantity_sold'] ?? 0,
            'total_remaining' => ($distributionSummaryResult['total_qty_issued'] ?? 0) - ($salesSummaryResult['total_quantity_sold'] ?? 0),
            'overall_total_price' => $salesSummaryResult['overall_total_price'] ?? 0,
        ];
        // --- End summary fetch for PDF ---


        $marketingPersonName = $salesData[0]['person_name'] ?? 'Unknown Person';
        $marketingPersonCustomId = $salesData[0]['custom_id'] ?? 'N/A';
        $filterProductName = '';
        if ($filterProductId && !empty($this->productModel->find($filterProductId)['name'])) {
            $filterProductName = ' for Product: ' . $this->productModel->find($filterProductId)['name'];
        }

        $html = view('sales/pdf_template_person_sales', [
            'salesData' => $salesData,
            'marketingPersonName' => $marketingPersonName,
            'marketingPersonCustomId' => $marketingPersonCustomId,
            'filterProductName' => $filterProductName,
            'summary' => $summaryPdf, // Pass summary data to PDF template
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'Sales_by_' . url_title($marketingPersonName, '-', true) . $filterProductName . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, array("Attachment" => true));
        exit();
    }



    public function edit($id = null)
    {
        if ($id === null) {
            // No ID provided, redirect with error
            return redirect()->to(base_url('sales'))->with('error', 'Sale ID not provided.');
        }

        $sale = $this->salesModel->find($id);

        if (!$sale) {
            // Sale not found, redirect with error
            return redirect()->to(base_url('sales'))->with('error', 'Sale entry not found.');
        }

        $data = [
            'title' => 'Edit Sale Entry',
            'sale' => $sale, // The specific sale entry record to edit
            'products' => $this->productModel->findAll(),
            'marketing_persons' => $this->marketingPersonModel->findAll() // Ensure this variable name matches your view's expectation
        ];

        return view('sales/edit', $data);
    }


    public function update($id = null)
    {
        if ($id === null) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale ID not provided for update.');
        }

        // Fetch the original sale record to get existing amounts and quantities
        $originalSale = $this->salesModel->find($id);
        if (!$originalSale) {
            return redirect()->to(base_url('sales'))->with('error', 'Original sale entry not found for update.');
        }

        // Define validation rules for a single sale entry
        $rules = [
            'product_id'        => 'required|integer',
            'marketing_person_id' => 'required|integer',
            'date_sold'         => 'required|valid_date',
            'quantity_sold'     => 'required|integer|greater_than[0]',
            'price_per_unit'    => 'required|numeric|greater_than_equal_to[0]',
            'discount'          => 'permit_empty|numeric|greater_than_equal_to[0]', // Ensure 'discount' matches form field
            'customer_name'     => 'required|max_length[255]',
            'customer_phone'    => 'required|regex_match[/^[0-9]{10}$/]',
            'customer_address'  => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            // If validation fails, redirect back with input and errors
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get validated data from the form submission
        $postData = $this->request->getPost();

        // --- Step 1: Calculate the NEW total_price ---
        $quantity = (float)$postData['quantity_sold'];
        $price = (float)$postData['price_per_unit'];
        $discount = (float)($postData['discount'] ?? 0);

        $newTotalPrice = ($quantity * $price) - $discount;
        if ($newTotalPrice < 0) $newTotalPrice = 0; // Prevent negative totals

        // --- Step 2: Retrieve the existing amount_received_from_person ---
        // This is crucial: we use the amount already paid for this sale.
        $existingAmountReceived = (float)($originalSale['amount_received_from_person'] ?? 0.00);

        // --- Step 3: Calculate the NEW balance_from_person ---
        $newBalanceFromPerson = $newTotalPrice - $existingAmountReceived;
        if ($newBalanceFromPerson < 0) $newBalanceFromPerson = 0; // Prevent negative balance if overpaid

        // --- Step 4: Determine the payment_status_from_person based on the new balance ---
        $newPaymentStatus = 'Pending'; // Default
        if ($newBalanceFromPerson <= 0.01) { // Allowing a small floating point tolerance
            $newPaymentStatus = 'Paid';
        } elseif ($existingAmountReceived > 0) {
            $newPaymentStatus = 'Partial';
        }


        // Data array for update - Now includes new total_price AND new balance_from_person
        $dataToUpdate = [
            'product_id'        => $postData['product_id'],
            'marketing_person_id' => $postData['marketing_person_id'],
            'date_sold'         => $postData['date_sold'],
            'quantity_sold'     => $quantity,
            'price_per_unit'    => $price,
            'discount'          => $discount,
            'total_price'       => $newTotalPrice, // <--- Updated total price
            'amount_received_from_person' => $existingAmountReceived, // Retain existing received amount
            'balance_from_person' => $newBalanceFromPerson,         // <--- NEW: Calculated balance
            'payment_status_from_person' => $newPaymentStatus,      // <--- NEW: Updated status based on balance
            'customer_name'     => $postData['customer_name'],
            'customer_phone'    => $postData['customer_phone'],
            'customer_address'  => $postData['customer_address'],
            // 'updated_at' will be automatically managed by the model if you have $useTimestamps = true
        ];

        // --- Important Stock Validation for Update ---
        $newQuantitySold = $quantity;

        $currentProductId = $postData['product_id'];
        $currentMarketingPersonId = $postData['marketing_person_id'];

        $totalIssuedForSelected = (int)$this->db->table('marketing_distribution')
            ->selectSum('quantity_issued', 'total_issued')
            ->where('product_id', $currentProductId)
            ->where('marketing_person_id', $currentMarketingPersonId)
            ->get()
            ->getRow()
            ->total_issued ?? 0;

        $salesExcludingCurrent = (int)$this->db->table('sales')
            ->selectSum('quantity_sold', 'total_sold')
            ->where('product_id', $currentProductId)
            ->where('marketing_person_id', $currentMarketingPersonId)
            ->where('id !=', $id)
            ->get()
            ->getRow()
            ->total_sold ?? 0;

        $availableStockForUpdate = $totalIssuedForSelected - $salesExcludingCurrent;

        if ($newQuantitySold > $availableStockForUpdate) {
            return redirect()->back()->withInput()->with('error', 'The new quantity sold exceeds the available stock for this product and marketing person. Available: ' . $availableStockForUpdate);
        }
        // --- End Stock Validation ---


        // Attempt to update the sale record
        if ($this->salesModel->update($id, $dataToUpdate)) {
            return redirect()->to(base_url('sales'))->with('success', 'Sale entry updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update sale entry. No changes were made or an error occurred.');
        }
    }

    public function delete($id)
    {
        $this->salesModel->delete($id);
        return redirect()->to('/sales')->with('success', 'Sale deleted.');
    }

    public function exportExcel()
    {
        $sales = $this->getFilteredSales();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $sheet->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Product')
            ->setCellValue('C1', 'Marketing Person')
            ->setCellValue('D1', 'Quantity Sold')
            ->setCellValue('E1', 'Price Per Unit')
            ->setCellValue('F1', 'Discount')
            ->setCellValue('G1', 'Total Price')
            ->setCellValue('H1', 'Date Sold')
            ->setCellValue('I1', 'Customer Name')
            ->setCellValue('J1', 'Customer Phone')
            ->setCellValue('K1', 'Customer Address');

        $rowNum = 2;
        foreach ($sales as $sale) {
            $sheet->setCellValue('A' . $rowNum, $sale['id']);
            $sheet->setCellValue('B' . $rowNum, $sale['product_name']);
            $sheet->setCellValue('C' . $rowNum, $sale['person_name']);
            $sheet->setCellValue('D' . $rowNum, $sale['quantity_sold']);
            $sheet->setCellValue('E' . $rowNum, $sale['price_per_unit']);
            $sheet->setCellValue('F' . $rowNum, $sale['discount']);
            $sheet->setCellValue('G' . $rowNum, $sale['total_price']);
            $sheet->setCellValue('H' . $rowNum, $sale['date_sold']);
            $sheet->setCellValue('I' . $rowNum, $sale['customer_name']);
            $sheet->setCellValue('J' . $rowNum, $sale['customer_phone']);
            $sheet->setCellValue('K' . $rowNum, $sale['customer_address']);

            $rowNum++;
        }

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sales_export.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    public function exportPDF()
    {
        $sales = $this->getFilteredSales();

        $html = view('sales/export_pdf', ['sales' => $sales]); // Remember to update export_pdf view too

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream('sales_export.pdf', ["Attachment" => true]);
        exit;
    }




    public function remitPayment($saleId)
    {
        $sale = $this->salesModel
            ->select('sales.*, products.name as product_name, marketing_persons.name as person_name')
            ->join('products', 'products.id = sales.product_id')
            ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
            ->find($saleId);

        if (!$sale) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale record not found for remittance.');
        }

        // Check if the sale is already fully paid
        if ($sale['payment_status_from_person'] == 'Paid') {
            return redirect()->to(base_url('sales/view/' . $saleId))->with('info', 'This sale has already been fully paid by the marketing person.');
        }

        $data = [
            'sale' => $sale,
            'title' => 'Remit Payment for Sale #' . $sale['id']
        ];

        return view('sales/remit_payment', $data);
    }

    /**
     * Processes the remittance payment for a specific sale.
     * Updates amount_received_from_person, balance_from_person, and payment_status_from_person.
     * @param int $saleId The ID of the sale to update.
     */
    public function processRemittance($saleId)
    {
        $sale = $this->salesModel->find($saleId);

        if (!$sale) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale record not found.');
        }

        // Basic validation for the amount
        $rules = [
            'amount_paid_now' => 'required|numeric|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $amountPaidNow = (float)$this->request->getPost('amount_paid_now');

        $db = \Config\Database::connect();
        $db->transBegin(); // Start transaction for atomicity

        try {
            $currentAmountReceived = (float)$sale['amount_received_from_person'];
            $currentBalance = (float)$sale['balance_from_person'];
            $totalSalePrice = (float)$sale['total_price'];

            // Ensure amount paid doesn't exceed the balance due
            if ($amountPaidNow > $currentBalance) {
                throw new \Exception("Amount to remit ({$amountPaidNow}) cannot exceed the current balance due ({$currentBalance}).");
            }

            $newAmountReceived = $currentAmountReceived + $amountPaidNow;
            $newBalance = $totalSalePrice - $newAmountReceived; // Recalculate from total_price to avoid floating point inaccuracies

            $paymentStatus = 'Partial';
            if (abs($newBalance) < 0.01) { // Use a small epsilon for floating point comparison
                $paymentStatus = 'Paid';
                $newBalance = 0.00; // Ensure balance is exactly zero if fully paid
            } elseif ($newAmountReceived <= 0) {
                $paymentStatus = 'Pending'; // Should not happen if amountPaidNow > 0
            }

            $dataToUpdate = [
                'amount_received_from_person' => $newAmountReceived,
                'balance_from_person' => $newBalance,
                'payment_status_from_person' => $paymentStatus,
                'last_remittance_date' => date('Y-m-d') // Record the date of this remittance
            ];

            if (!$this->salesModel->update($saleId, $dataToUpdate)) {
                $errorMessage = 'Failed to update sale payment details. Error: ' . implode(', ', $this->salesModel->errors());
                throw new \Exception($errorMessage);
            }

            $db->transCommit();
            return redirect()->to(base_url('sales/view/' . $saleId))->with('success', "Remittance of " . number_format($amountPaidNow, 2) . " received successfully. Sale status: {$paymentStatus}.");
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    public function getRemainingStock()
    {
        $productId = $this->request->getGet('product_id');
        $marketingPersonId = $this->request->getGet('marketing_person_id');

        if (!$productId || !$marketingPersonId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product ID and Marketing Person ID are required.',
                'remaining_qty' => 0,
                'price_per_unit' => 0 // Default to 0
            ]);
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product not found.',
                'remaining_qty' => 0,
                'price_per_unit' => 0 // Default to 0
            ]);
        }

        // Calculate total quantity issued to this person for this product
        $totalIssued = (int)$this->db->table('marketing_distribution')
            ->selectSum('quantity_issued', 'total_issued')
            ->where('product_id', $productId)
            ->where('marketing_person_id', $marketingPersonId)
            ->get()
            ->getRow()
            ->total_issued ?? 0;

        // Calculate total quantity sold by this person for this product
        $totalSold = (int)$this->db->table('sales')
            ->selectSum('quantity_sold', 'total_sold')
            ->where('product_id', $productId)
            ->where('marketing_person_id', $marketingPersonId)
            ->get()
            ->getRow()
            ->total_sold ?? 0;

        $remainingQty = $totalIssued - $totalSold;

        return $this->response->setJSON([
            'status' => 'success',
            'remaining_qty' => $remainingQty,
            'price_per_unit' => (float)($product['selling_price'] ?? 0.00) // *** This is the crucial line ***
        ]);
    }


    public function recordSalePaymentForm($saleId = null)
    {
        if ($saleId === null) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale ID not provided for payment.');
        }

        $sale = $this->salesModel->getSaleDetails($saleId);
        if (!$sale) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale entry not found.');
        }

        if ($sale['payment_status_from_person'] == 'Paid') {
            return redirect()->to(base_url('sales/view/' . $sale['marketing_person_id']))->with('info', 'This sale is already fully paid.');
        }

        $data = [
            'title'        => 'Record Payment for Sale',
            'sale'         => $sale,
            'validation'   => \Config\Services::validation(),
        ];

        return view('sales/record_sale_payment_form', $data);
    }

    public function recordSalePayment()
    {
        $rules = [
            'sale_id'       => 'required|integer',
            'payment_date'  => 'required|valid_date',
            'amount_paid'   => 'required|numeric|greater_than[0]',
            'payment_method' => 'permit_empty|max_length[50]',
            'remarks'       => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();
        $saleId = $postData['sale_id'];

        $sale = $this->salesModel->find($saleId);
        if (!$sale) {
            return redirect()->back()->withInput()->with('error', 'Sale not found.');
        }

        // Prevent overpayment on a single transaction (optional, but good practice)
        $amountToPay = (float)$postData['amount_paid'];
        $currentBalance = (float)$sale['balance_from_person'];

        if ($amountToPay > $currentBalance && $currentBalance > 0.01) { // Allow slight tolerance
            return redirect()->back()->withInput()->with('error', 'Amount paid cannot exceed the outstanding balance of ₹' . number_format($currentBalance, 2));
        }

        // --- Save the individual payment transaction ---
        $paymentData = [
            'sale_id'        => $saleId,
            'payment_date'   => $postData['payment_date'],
            'amount_paid'    => $amountToPay,
            'payment_method' => $postData['payment_method'],
            'remarks'        => $postData['remarks'],
        ];

        if (!$this->salePaymentModel->save($paymentData)) {
            return redirect()->back()->withInput()->with('error', 'Failed to record payment transaction.');
        }

        // --- Update the main sales record ---
        // Sum all payments for this sale to get the new total amount received
        $totalAmountReceivedForSale = $this->salePaymentModel
            ->selectSum('amount_paid', 'total_paid')
            ->where('sale_id', $saleId)
            ->get()
            ->getRow()
            ->total_paid ?? 0.00;

        $newBalance = (float)$sale['total_price'] - $totalAmountReceivedForSale;
        if ($newBalance < 0) $newBalance = 0; // Ensure balance doesn't go negative

        $newPaymentStatus = 'Pending';
        if ($newBalance <= 0.01) {
            $newPaymentStatus = 'Paid';
        } elseif ($totalAmountReceivedForSale > 0) {
            $newPaymentStatus = 'Partial';
        }

        $saleDataToUpdate = [
            'amount_received_from_person' => $totalAmountReceivedForSale,
            'balance_from_person'         => $newBalance,
            'payment_status_from_person'  => $newPaymentStatus,
            'last_remittance_date'        => $postData['payment_date'], // Update with latest payment date
        ];

        if ($this->salesModel->update($saleId, $saleDataToUpdate)) {
            return redirect()->to(base_url('sales/payment-history/' . $saleId))->with('success', 'Payment recorded and sale status updated!');
        } else {
            // This case should ideally not happen if save($paymentData) succeeded,
            // but good to have a fallback.
            return redirect()->back()->withInput()->with('error', 'Payment recorded, but failed to update main sale details.');
        }
    }

    public function viewSalePaymentHistory($saleId = null)
    {
        if ($saleId === null) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale ID not provided for payment history.');
        }

        $sale = $this->salesModel->getSaleDetails($saleId);
        if (!$sale) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale not found.');
        }

        $payments = $this->salePaymentModel
            ->where('sale_id', $saleId)
            ->orderBy('payment_date', 'ASC')
            ->orderBy('created_at', 'ASC') // For payments on same day
            ->findAll();

        $data = [
            'title'      => 'Payment History for Sale #' . $saleId,
            'sale'       => $sale,
            'payments'   => $payments,
        ];

        return view('sales/sale_payment_history', $data);
    }
    public function exportSalePaymentsExcel($saleId = null)
    {
        if ($saleId === null) {
            return redirect()->to(base_url('sales'))->with('error', 'No sale ID provided for export.');
        }

        $sale = $this->salesModel->getSaleDetails($saleId); // Using your SalesModel
        if (!$sale) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale not found for export.');
        }

        $payments = $this->salePaymentModel->where('sale_id', $saleId)->findAll(); // <--- USE YOUR CORRECT MODEL HERE

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ... (rest of your Excel export logic) ...
        $sheet->setCellValue('A1', 'Sale ID:'); // Example line
        // ...

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Sale_' . $saleId . '_Payment_History_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    public function exportSalePaymentsPDF($saleId = null)
    {
        if ($saleId === null) {
            return redirect()->to(base_url('sales'))->with('error', 'No sale ID provided for export.');
        }

        $sale = $this->salesModel->getSaleDetails($saleId); // Using your SalesModel
        if (!$sale) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale not found for export.');
        }

        $payments = $this->salePaymentModel->where('sale_id', $saleId)->findAll(); // <--- USE YOUR CORRECT MODEL HERE

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $data = [
            'sale' => $sale,
            'payments' => $payments,
        ];
        $html = view('sales/sale_payment_history_pdf_template', $data); // Using your confirmed template path

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Sale_' . $saleId . '_Payment_History_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, ['Attachment' => 1]);
        exit();
    }
}
