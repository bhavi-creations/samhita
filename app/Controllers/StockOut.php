<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StockOutModel;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\MarketingPersonModel;
use App\Models\MarketingDistributionModel;
use App\Models\DistributorSalesOrderModel;
use App\Models\DistributorModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class StockOut extends BaseController
{
    protected $stockOutModel;
    protected $productModel;
    protected $salesModel;
    protected $marketingPersonModel;
    protected $marketingDistributionModel;
    protected $distributorSalesOrderModel;
    protected $distributorModel;
    protected $session;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->stockOutModel = new StockOutModel();
        $this->productModel = new ProductModel();
        $this->salesModel = new SalesModel();
        $this->marketingPersonModel = new MarketingPersonModel();
        $this->marketingDistributionModel = new MarketingDistributionModel();
        $this->distributorSalesOrderModel = new DistributorSalesOrderModel();
        $this->distributorModel = new DistributorModel();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    /**
     * Displays a list of all stock out transactions with filters.
     */
    public function index(): string
    {
        $product_id = $this->request->getGet('product_id');
        $transaction_type = $this->request->getGet('transaction_type');
        $issued_date_start = $this->request->getGet('issued_date_start');
        $issued_date_end = $this->request->getGet('issued_date_end');

        // Define a mapping for user-friendly transaction types
        $transactionTypeMap = [
            'Sale'                 => 'Sale',
            'distributor_sale'     => 'Distributor Sale',
            'marketing_distribution' => 'Marketing Distribution',
            'Damage'               => 'Damage',
            'Sample'               => 'Sample',
            'Internal Use'         => 'Internal Use',
            'Other'                => 'Other',
        ];

        // Build the query
        $builder = $this->stockOutModel->builder();
        $builder->select('stock_out.*, products.name as product_name');
        $builder->join('products', 'products.id = stock_out.product_id');

        // Apply filters
        if (!empty($product_id)) {
            $builder->where('stock_out.product_id', $product_id);
        }
        if (!empty($transaction_type)) {
            // Use the original database value for filtering
            $builder->where('stock_out.transaction_type', array_search($transaction_type, $transactionTypeMap) ?: $transaction_type);
        }
        if (!empty($issued_date_start)) {
            $builder->where('stock_out.issued_date >=', $issued_date_start);
        }
        if (!empty($issued_date_end)) {
            $builder->where('stock_out.issued_date <=', $issued_date_end);
        }

        $stockOutRecords = $builder->orderBy('stock_out.issued_date DESC, stock_out.id DESC')->get()->getResultArray();

        // Fetch all products and distinct transaction types from DB for filter dropdown
        $products = $this->productModel->select('id, name')->findAll();
        $allDbTransactionTypes = $this->stockOutModel->distinct()->select('transaction_type')->findAll();
        $allDbTransactionTypes = array_column($allDbTransactionTypes, 'transaction_type');

        // Prepare transaction types for the filter dropdown, using user-friendly names
        $filterTransactionTypes = [];
        foreach ($allDbTransactionTypes as $dbType) {
            $filterTransactionTypes[$dbType] = $transactionTypeMap[$dbType] ?? $dbType;
        }

        // Prepare a map for product names (if not already joined in primary query)
        $productMap = array_column($products, 'name', 'id');

        // Enrich stockOutRecords with related sale/person/distributor details
        $enrichedRecords = [];
        foreach ($stockOutRecords as $record) {
            $record['related_transaction_details'] = null; // Initialize
            $record['product_name'] = $productMap[$record['product_id']] ?? 'N/A'; // Ensure product name is set
            $record['display_transaction_type'] = $transactionTypeMap[$record['transaction_type']] ?? $record['transaction_type']; // Add display type

            if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
                // Fetch sale and marketing person details if transaction_type is Sale
                $sale = $this->salesModel->select('sales.*, marketing_persons.name as marketing_person_name, marketing_persons.custom_id as marketing_person_custom_id')
                    ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($sale) {
                    $record['related_transaction_details'] = [
                        'type' => 'Sale',
                        'sale_id' => $sale['id'],
                        'marketing_person_id' => $sale['marketing_person_id'] ?? null,
                        'marketing_person_name' => $sale['marketing_person_name'] ?? 'N/A',
                        'marketing_person_custom_id' => $sale['marketing_person_custom_id'] ?? 'N/A',
                        'sale_date' => $sale['date_sold'],
                    ];
                }
            } elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['transaction_id'])) {
                // Fetch marketing distribution and marketing person details
                $distribution = $this->marketingDistributionModel
                    ->select('marketing_distribution.*, marketing_persons.name as marketing_person_name, marketing_persons.custom_id as marketing_person_custom_id, marketing_persons.phone as marketing_person_phone')
                    ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id', 'left')
                    ->find($record['transaction_id']);

                if ($distribution) {
                    $record['related_transaction_details'] = [
                        'type'                       => 'Marketing Distribution',
                        'distribution_id'            => $distribution['id'],
                        'marketing_person_db_id'     => $distribution['marketing_person_id'],
                        'marketing_person_name'      => $distribution['marketing_person_name'],
                        'marketing_person_custom_id' => $distribution['marketing_person_custom_id'],
                        'marketing_person_phone'     => $distribution['marketing_person_phone'],
                        'quantity_issued_in_dist'    => $distribution['quantity_issued'],
                        'date_issued_in_dist'        => $distribution['date_issued'],
                        'notes_in_dist'              => $distribution['notes'],
                    ];
                }
            } elseif ($record['transaction_type'] === 'distributor_sale' && !empty($record['transaction_id'])) {
                // Fetch distributor sales order and distributor details
                $distributorSale = $this->distributorSalesOrderModel
                    ->select('distributor_sales_orders.*, distributors.agency_name, distributors.owner_name, distributors.id as distributor_db_id')
                    ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id', 'left')
                    ->find($record['transaction_id']);

                if ($distributorSale) {
                    $record['related_transaction_details'] = [
                        'type'            => 'Distributor Sale',
                        'sales_order_id'  => $distributorSale['id'],
                        'distributor_id'  => $distributorSale['distributor_db_id'],
                        'agency_name'     => $distributorSale['agency_name'],
                        'owner_name'      => $distributorSale['owner_name'],
                        'invoice_number'  => $distributorSale['invoice_number'],
                        'invoice_date'    => $distributorSale['invoice_date'],
                    ];
                }
            }
            $enrichedRecords[] = $record;
        }

        $data = [
            'title'                   => 'Stock Out Records',
            'stockOutRecords'         => $enrichedRecords,
            'products'                => $products, // For filter dropdown
            'transactionTypes'        => $filterTransactionTypes, // Use mapped types for dropdown
            'selectedProductId'       => $product_id,
            'selectedTransactionType' => $transaction_type, // This will be the user-friendly name if selected
            'selectedIssuedDateStart' => $issued_date_start,
            'selectedIssuedDateEnd'   => $issued_date_end,
            'request'                 => $this->request,
        ];

        return view('stock_out/index', $data);
    }

    /**
     * Displays the form to issue new stock out.
     * This method renders the issue_form.php view.
     */
    public function issue(): string
    {
        $products = $this->productModel->select('id, name, selling_price')->findAll();

        $data = [
            'title'             => 'Issue Stock Out',
            'products'          => $products,
            'validation'        => $this->validation,
            'transaction_types' => ['Damage', 'Sample', 'Internal Use', 'Other'],
        ];

        return view('stock_out/issue_form', $data);
    }

    /**
     * Handles the submission of the stock out issue form.
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'product_id'        => 'required|integer|is_not_unique[products.id]',
            'quantity_out'      => 'required|integer|greater_than[0]',
            'transaction_type'  => 'required|in_list[Damage,Sample,Internal Use,Other]',
            'issued_date'       => 'required|valid_date',
            'notes'             => 'permit_empty|string|max_length[500]',
            'transaction_id'    => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'StockOut::store - Validation failed. Errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $productId = $this->request->getPost('product_id');
        $quantityOut = (int)$this->request->getPost('quantity_out');
        $transactionType = $this->request->getPost('transaction_type');
        $transactionId = $this->request->getPost('transaction_id');

        if (empty($transactionId)) {
            $transactionId = null;
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            log_message('error', 'StockOut::store - Product not found for ID: ' . $productId);
            return redirect()->back()->withInput()->with('error', 'Selected product not found. Please choose a valid product.');
        }
        if ($product['current_stock'] < $quantityOut) {
            log_message('error', 'StockOut::store - Insufficient stock for product ID ' . $productId . '. Attempted: ' . $quantityOut . ', Available: ' . $product['current_stock']);
            return redirect()->back()->withInput()->with('error', 'Insufficient stock for this product. Available: ' . $product['current_stock']);
        }

        $stockOutData = [
            'product_id'        => $productId,
            'quantity_out'      => $quantityOut,
            'transaction_type'  => $transactionType,
            'transaction_id'    => $transactionId,
            'issued_date'       => $this->request->getPost('issued_date'),
            'notes'             => $this->request->getPost('notes'),
        ];

        $this->db->transBegin();
        log_message('debug', 'StockOut::store - Database transaction begun.');

        try {
            $stockOutInsertResult = $this->stockOutModel->insert($stockOutData);
            log_message('debug', 'StockOut::store - Stock Out Insert attempt. Result: ' . ($stockOutInsertResult ? 'Success (ID: ' . $stockOutInsertResult . ')' : 'Failed'));

            if (!$stockOutInsertResult) {
                $this->db->transRollback();
                $dbError = $this->db->error();
                log_message('error', 'StockOut::store - Failed to insert stock out record. DB Error: ' . json_encode($dbError) . ', Data: ' . json_encode($stockOutData));
                return redirect()->back()->withInput()->with('error', 'Failed to record stock out. Database insert failed: ' . $dbError['message']);
            }

            $newStock = $product['current_stock'] - $quantityOut;
            $productUpdateResult = $this->productModel->update($productId, ['current_stock' => $newStock]);
            log_message('debug', 'StockOut::store - Product Stock Update attempt. Result: ' . ($productUpdateResult ? 'Success' : 'Failed') . ', New Stock: ' . $newStock);

            if (!$productUpdateResult) {
                $this->db->transRollback();
                $dbError = $this->db->error();
                log_message('error', 'StockOut::store - Failed to update product stock. Product ID: ' . $productId . ', New Stock: ' . $newStock . ', DB Error: ' . json_encode($dbError));
                return redirect()->back()->withInput()->with('error', 'Failed to record stock out. Database update for product stock failed: ' . $dbError['message']);
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                $dbError = $this->db->error();
                log_message('error', 'StockOut::store - Transaction final status is FALSE (before commit). DB Error: ' . json_encode($dbError) . ', Data: ' . json_encode($stockOutData));
                return redirect()->back()->withInput()->with('error', 'Failed to record stock out due to an internal transaction error. Please check system logs for details.');
            } else {
                $this->db->transCommit();
                log_message('debug', 'StockOut::store - Transaction Committed Successfully.');
                return redirect()->to(base_url('stock-out'))->with('success', 'Stock Out recorded successfully!');
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'StockOut::store - Caught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'An unexpected system error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Displays details of a single stock out transaction.
     *
     * @param int $id The ID of the stock out record.
     */
    public function view(int $id): ResponseInterface|string
    {
        $record = $this->stockOutModel
            ->select('stock_out.*, products.name as product_name')
            ->join('products', 'products.id = stock_out.product_id')
            ->find($id);

        if (!$record) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Stock Out record not found: ' . $id);
        }

        // Define a mapping for user-friendly transaction types for this view
        $transactionTypeMap = [
            'Sale'                 => 'Sale',
            'distributor_sale'     => 'Distributor Sale',
            'marketing_distribution' => 'Marketing Distribution',
            'Damage'               => 'Damage',
            'Sample'               => 'Sample',
            'Internal Use'         => 'Internal Use',
            'Other'                => 'Other',
        ];
        $record['display_transaction_type'] = $transactionTypeMap[$record['transaction_type']] ?? $record['transaction_type'];


        // Initialize related_transaction_details
        $record['related_transaction_details'] = null;

        if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
            $sale = $this->salesModel
                ->select('sales.*, marketing_persons.name as marketing_person_name, marketing_persons.phone as marketing_person_phone, marketing_persons.id as marketing_person_db_id')
                ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                ->find($record['transaction_id']);

            if ($sale) {
                $record['related_transaction_details'] = [
                    'type'                       => 'Sale',
                    'sale_id'                    => $sale['id'],
                    'marketing_person'           => ($sale['marketing_person_name'] ? $sale['marketing_person_name'] . ' (' . $sale['marketing_person_phone'] . ')' : 'N/A'),
                    'marketing_person_db_id'     => $sale['marketing_person_db_id'],
                    'sale_date'                  => $sale['sale_date'],
                    'quantity_sold_in_sale'      => $sale['quantity_sold'],
                    'price_per_unit_in_sale'     => $sale['price_per_unit'],
                    'total_price_in_sale'        => $sale['total_price'],
                    'payment_status_from_person' => $sale['payment_status'],
                    'amount_received_from_person' => $sale['amount_received'],
                    'balance_from_person'        => $sale['balance'],
                ];
            }
        } elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['transaction_id'])) {
            $distribution = $this->marketingDistributionModel
                ->select('marketing_distribution.*, marketing_persons.name as marketing_person_name, marketing_persons.custom_id as marketing_person_custom_id, marketing_persons.phone as marketing_person_phone, marketing_persons.id as marketing_person_db_id')
                ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id', 'left')
                ->find($record['transaction_id']);

            if ($distribution) {
                $record['related_transaction_details'] = [
                    'type'                       => 'Marketing Distribution',
                    'distribution_id'            => $distribution['id'],
                    'marketing_person_db_id'     => $distribution['marketing_person_id'],
                    'marketing_person_name'      => $distribution['marketing_person_name'],
                    'marketing_person_custom_id' => $distribution['marketing_person_custom_id'],
                    'marketing_person_phone'     => $distribution['marketing_person_phone'],
                    'quantity_issued_in_dist'    => $distribution['quantity_issued'],
                    'date_issued_in_dist'        => $distribution['date_issued'],
                    'notes_in_dist'              => $distribution['notes'],
                ];
            }
        } elseif ($record['transaction_type'] === 'distributor_sale' && !empty($record['transaction_id'])) {
            $distributorSale = $this->distributorSalesOrderModel
                ->select('distributor_sales_orders.*, distributors.agency_name, distributors.owner_name, distributors.id as distributor_db_id')
                ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id', 'left')
                ->find($record['transaction_id']);

            if ($distributorSale) {
                $record['related_transaction_details'] = [
                    'type'            => 'Distributor Sale',
                    'sales_order_id'  => $distributorSale['id'],
                    'distributor_db_id' => $distributorSale['distributor_db_id'],
                    'agency_name'     => $distributorSale['agency_name'],
                    'owner_name'      => $distributorSale['owner_name'],
                    'invoice_number'  => $distributorSale['invoice_number'],
                    'invoice_date'    => $distributorSale['invoice_date'],
                ];
            }
        }

        $data = [
            'title'  => 'Stock Out Detail',
            'record' => $record,
        ];

        return view('stock_out/view_detail', $data);
    }

    /**
     * Export Stock Out records to Excel.
     */
    public function exportExcel(): ResponseInterface
    {
        // Get filter parameters, apply them as in the index() method
        $product_id = $this->request->getGet('product_id');
        $transaction_type = $this->request->getGet('transaction_type');
        $issued_date_start = $this->request->getGet('issued_date_start');
        $issued_date_end = $this->request->getGet('issued_date_end');

        // Define a mapping for user-friendly transaction types for export
        $transactionTypeMap = [
            'Sale'                 => 'Sale',
            'distributor_sale'     => 'Distributor Sale',
            'marketing_distribution' => 'Marketing Distribution',
            'Damage'               => 'Damage',
            'Sample'               => 'Sample',
            'Internal Use'         => 'Internal Use',
            'Other'                => 'Other',
        ];

        $builder = $this->stockOutModel->builder();
        $builder->select('stock_out.id, products.name as product_name, stock_out.quantity_out, stock_out.transaction_type, stock_out.transaction_id, stock_out.issued_date, stock_out.notes, stock_out.created_at');
        $builder->join('products', 'products.id = stock_out.product_id');

        // Apply filters
        if (!empty($product_id)) {
            $builder->where('stock_out.product_id', $product_id);
        }
        if (!empty($transaction_type)) {
             // Use the original database value for filtering during export as well
            $builder->where('stock_out.transaction_type', array_search($transaction_type, $transactionTypeMap) ?: $transaction_type);
        }
        if (!empty($issued_date_start)) {
            $builder->where('stock_out.issued_date >=', $issued_date_start);
        }
        if (!empty($issued_date_end)) {
            $builder->where('stock_out.issued_date <=', $issued_date_end);
        }
        $recordsToExport = $builder->orderBy('stock_out.issued_date DESC, stock_out.id DESC')->get()->getResultArray();

        // Enrich records with related details for export
        foreach ($recordsToExport as &$record) {
            $record['distributed_to_name'] = '';
            $record['distributed_to_type'] = '';
            $record['display_transaction_type'] = $transactionTypeMap[$record['transaction_type']] ?? $record['transaction_type']; // Add display type for export

            if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
                $sale = $this->salesModel->select('marketing_persons.name as marketing_person_name')
                    ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($sale) {
                    $record['distributed_to_name'] = 'Marketing Person: ' . ($sale['marketing_person_name'] ?? 'N/A');
                    $record['distributed_to_type'] = 'Marketing Person (Sale)';
                }
            } elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['transaction_id'])) {
                $distribution = $this->marketingDistributionModel
                    ->select('marketing_persons.name as marketing_person_name, marketing_persons.custom_id')
                    ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($distribution) {
                    $record['distributed_to_name'] = ($distribution['marketing_person_name'] ?? 'N/A') . ' (ID: ' . ($distribution['custom_id'] ?? 'N/A') . ')';
                    $record['distributed_to_type'] = 'Marketing Person (Distribution)';
                }
            } elseif ($record['transaction_type'] === 'distributor_sale' && !empty($record['transaction_id'])) {
                 $distributorSale = $this->distributorSalesOrderModel
                    ->select('distributors.agency_name, distributors.owner_name')
                    ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id', 'left')
                    ->find($record['transaction_id']);
                if ($distributorSale) {
                    $record['distributed_to_name'] = ($distributorSale['agency_name'] ?? 'N/A') . ' (Owner: ' . ($distributorSale['owner_name'] ?? 'N/A') . ')';
                    $record['distributed_to_type'] = 'Distributor';
                }
            } else {
                $record['distributed_to_name'] = 'N/A';
                $record['distributed_to_type'] = $record['transaction_type'];
            }
        }
        unset($record); // Unset the reference

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add CSV Headers - Updated to include 'Distributed To' and 'Distributed To Type'
        $headers = ['S.No.', 'Product Name', 'Quantity Out', 'Transaction Type', 'Transaction ID', 'Issued Date', 'Notes', 'Recorded At', 'Distributed To', 'Distributed To Type'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Add Data
        $sno = 1;
        $row = 2;
        foreach ($recordsToExport as $dataRow) {
            $col = 'A';
            $sheet->setCellValue($col++ . $row, $sno++);
            $sheet->setCellValue($col++ . $row, $dataRow['product_name']);
            $sheet->setCellValue($col++ . $row, $dataRow['quantity_out']);
            $sheet->setCellValue($col++ . $row, $dataRow['display_transaction_type']); // Use display type
            $sheet->setCellValue($col++ . $row, $dataRow['transaction_id']);
            $sheet->setCellValue($col++ . $row, $dataRow['issued_date']);
            $sheet->setCellValue($col++ . $row, $dataRow['notes']);
            $sheet->setCellValue($col++ . $row, $dataRow['created_at']);
            $sheet->setCellValue($col++ . $row, $dataRow['distributed_to_name']);
            $sheet->setCellValue($col++ . $row, $dataRow['distributed_to_type']);
            $row++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'stock_out_records_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    /**
     * Export Stock Out records to PDF.
     */
    public function exportPdf(): ResponseInterface
    {
        // Get filter parameters, apply them as in the index() method
        $product_id = $this->request->getGet('product_id');
        $transaction_type = $this->request->getGet('transaction_type');
        $issued_date_start = $this->request->getGet('issued_date_start');
        $issued_date_end = $this->request->getGet('issued_date_end');

        // Define a mapping for user-friendly transaction types for PDF
        $transactionTypeMap = [
            'Sale'                 => 'Sale',
            'distributor_sale'     => 'Distributor Sale',
            'marketing_distribution' => 'Marketing Distribution',
            'Damage'               => 'Damage',
            'Sample'               => 'Sample',
            'Internal Use'         => 'Internal Use',
            'Other'                => 'Other',
        ];

        $builder = $this->stockOutModel->builder();
        $builder->select('stock_out.*, products.name as product_name');
        $builder->join('products', 'products.id = stock_out.product_id');

        if (!empty($product_id)) {
            $builder->where('stock_out.product_id', $product_id);
        }
        if (!empty($transaction_type)) {
            // Use the original database value for filtering
            $builder->where('stock_out.transaction_type', array_search($transaction_type, $transactionTypeMap) ?: $transaction_type);
        }
        if (!empty($issued_date_start)) {
            $builder->where('stock_out.issued_date >=', $issued_date_start);
        }
        if (!empty($issued_date_end)) {
            $builder->where('stock_out.issued_date <=', $issued_date_end);
        }

        $stockOutRecords = $builder->orderBy('stock_out.issued_date DESC, stock_out.id DESC')->get()->getResultArray();

        // Enrich records with related details for PDF
        $enrichedRecords = [];
        foreach ($stockOutRecords as $record) {
            $record['distributed_to_display'] = 'N/A'; // Default
            $record['related_link'] = null; // Default
            $record['display_transaction_type'] = $transactionTypeMap[$record['transaction_type']] ?? $record['transaction_type']; // Add display type for PDF

            if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
                $sale = $this->salesModel
                    ->select('marketing_persons.name as marketing_person_name, marketing_persons.id as marketing_person_id')
                    ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($sale) {
                    $record['distributed_to_display'] = 'Marketing Person: ' . ($sale['marketing_person_name'] ?? 'N/A');
                    $record['related_link'] = base_url('marketing-persons/show/' . $sale['marketing_person_id']); // Link to marketing person
                }
            } elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['transaction_id'])) {
                $distribution = $this->marketingDistributionModel
                    ->select('marketing_persons.name as marketing_person_name, marketing_persons.custom_id, marketing_persons.id as marketing_person_id')
                    ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($distribution) {
                    $record['distributed_to_display'] = ($distribution['marketing_person_name'] ?? 'N/A') . ' (ID: ' . ($distribution['custom_id'] ?? 'N/A') . ')';
                    $record['related_link'] = base_url('marketing-persons/show/' . $distribution['marketing_person_id']); // Link to marketing person
                }
            } elseif ($record['transaction_type'] === 'distributor_sale' && !empty($record['transaction_id'])) {
                $distributorSale = $this->distributorSalesOrderModel
                    ->select('distributors.agency_name, distributors.owner_name, distributors.id as distributor_id')
                    ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id', 'left')
                    ->find($record['transaction_id']);
                if ($distributorSale) {
                    $record['distributed_to_display'] = ($distributorSale['agency_name'] ?? 'N/A') . ' (Owner: ' . ($distributorSale['owner_name'] ?? 'N/A') . ')';
                    $record['related_link'] = base_url('distributors/show/' . $distributorSale['distributor_id']); // Link to distributor
                }
            }
            $enrichedRecords[] = $record;
        }


        $data = [
            'title'     => 'Stock Out List PDF',
            'stockOuts' => $enrichedRecords,
            'currentDate' => date('Y-m-d H:i:s')
        ];

        // Load the PDF template view to capture its HTML content
        $html = view('stock_out/pdf_template', $data);

        // Instantiate Dompdf
        $dompdf = new Dompdf();

        // Enable HTML5 parsing and remote URLs for images/CSS if needed
        $options = $dompdf->getOptions();
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);
        $dompdf->setOptions($options);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF (inline or download)
        $filename = 'stock_out_list_' . date('Ymd_His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
