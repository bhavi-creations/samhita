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
    protected $marketingDistributionModel;

    public function __construct()
    {
        $this->salesModel = new SalesModel();
        $this->productModel = new ProductModel();
        $this->personModel = new MarketingPersonModel();
        $this->marketingDistributionModel = new MarketingDistributionModel();
    }

    public function index()
    {
        $builder = $this->salesModel->builder();
        $builder->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id');
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

    // --- UPDATED storeMultiple() FUNCTION ---
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
        $filterProductId = $request->getGet('product_id');

        $specificSale = $this->salesModel
                             ->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id')
                             ->join('products', 'products.id = sales.product_id')
                             ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
                             ->find($id);

        if (empty($specificSale)) {
            return redirect()->to(base_url('sales'))->with('error', 'Sale record not found.');
        }

        $marketingPersonId = $specificSale['marketing_person_id'];

        // --- Fetch all sales for this specific marketing person (with optional product filter) ---
        $salesQuery = $this->salesModel
                           ->select('sales.*, products.name as product_name, marketing_persons.name as person_name, marketing_persons.custom_id')
                           ->join('products', 'products.id = sales.product_id')
                           ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id')
                           ->where('sales.marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $salesQuery->where('sales.product_id', $filterProductId);
        }

        $allSalesOfPerson = $salesQuery
                                ->orderBy('sales.date_sold', 'DESC')
                                ->orderBy('sales.id', 'DESC')
                                ->findAll();

        // --- Calculate Summary Totals (Qty Issued, Qty Sold, Remaining, Discount, Total Price) ---
        $db = \Config\Database::connect();

        // 1. Total Quantity Sold, Discount, and Overall Total Price from 'sales' table
        $salesSummaryQuery = $db->table('sales')
                                ->selectSum('quantity_sold', 'total_quantity_sold')
                                ->selectSum('discount', 'total_discount')
                                ->selectSum('total_price', 'overall_total_price')
                                ->where('marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $salesSummaryQuery->where('product_id', $filterProductId);
        }
        $salesSummaryResult = $salesSummaryQuery->get()->getRowArray();

        // 2. Total Quantity Issued from 'marketing_distribution' table (CORRECTED TABLE NAME)
        $distributionSummaryQuery = $db->table('marketing_distribution') // <--- CORRECTED TABLE NAME
                                       ->selectSum('quantity_issued', 'total_qty_issued') // <--- CORRECTED COLUMN NAME
                                       ->where('marketing_person_id', $marketingPersonId);

        if ($filterProductId) {
            $distributionSummaryQuery->where('product_id', $filterProductId);
        }
        $distributionSummaryResult = $distributionSummaryQuery->get()->getRowArray();


        // Combine and calculate final summary
        $totalQtyIssued = $distributionSummaryResult['total_qty_issued'] ?? 0;
        $totalQtySold = $salesSummaryResult['total_quantity_sold'] ?? 0;
        $totalDiscount = $salesSummaryResult['total_discount'] ?? 0;
        $overallTotalPrice = $salesSummaryResult['overall_price'] ?? 0; // Ensure 'overall_price' is correct or use 'overall_total_price'
        $totalRemaining = $totalQtyIssued - $totalQtySold;

        $summary = [
            'total_qty_issued' => $totalQtyIssued,
            'total_quantity_sold' => $totalQtySold,
            'total_remaining' => $totalRemaining,
            'total_discount' => $totalDiscount,
            'overall_total_price' => $overallTotalPrice, // Use the correct alias from selectSum
        ];

        // ... (rest of the view($id) method, fetching productsForFilter and preparing $data) ...

        $productsForFilter = $this->productModel->findAll();

        $data = [
            'marketingPersonName' => $specificSale['person_name'],
            'marketingPersonCustomId' => $specificSale['custom_id'],
            'marketingPersonId' => $marketingPersonId,
            'allSalesOfPerson' => $allSalesOfPerson,
            'specificSaleId' => $id,
            'summary' => $summary,
            'productsForFilter' => $productsForFilter,
            'selectedProductId' => $filterProductId
        ];

        return view('sales/view_details', $data);
    }

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



    public function edit($id)
    {
        $sale = $this->salesModel->find($id);
        if (!$sale) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Sale not found');
        }

        return view('sales/edit', [
            'sale' => $sale,
            'products' => $this->productModel->findAll(),
            'marketing_persons' => $this->personModel->findAll()
        ]);
    }

    public function update($id)
    {
        $data = [
            'product_id' => $this->request->getPost('product_id'),
            'marketing_person_id' => $this->request->getPost('marketing_person_id'),
            'quantity_sold' => $this->request->getPost('quantity_sold'),
            'price_per_unit' => $this->request->getPost('price_per_unit'),
            'discount' => $this->request->getPost('discount'),
            'date_sold' => $this->request->getPost('date_sold'),
            'customer_name' => $this->request->getPost('customer_name'),
            'customer_phone' => $this->request->getPost('customer_phone'),
            'customer_address' => $this->request->getPost('customer_address'),
        ];
        $quantitySold = (float)$data['quantity_sold'];
        $pricePerUnit = (float)$data['price_per_unit'];
        $discount = (float)$data['discount'];
        $data['total_price'] = ($quantitySold * $pricePerUnit) - $discount; // Recalculate total price

        $this->salesModel->update($id, $data);

        return redirect()->to('/sales')->with('success', 'Sale updated.');
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
}
