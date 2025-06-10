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
