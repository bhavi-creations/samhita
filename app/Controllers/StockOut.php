<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StockOutModel;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\MarketingPersonModel;
use App\Models\MarketingDistributionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;

class StockOut extends BaseController
{
    protected $stockOutModel;
    protected $productModel;
    protected $salesModel;
    protected $marketingPersonModel;
    protected $marketingDistributionModel;
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

        // Build the query
        $builder = $this->stockOutModel->builder();
        $builder->select('stock_out.*, products.name as product_name');
        $builder->join('products', 'products.id = stock_out.product_id');

        // Apply filters
        if (!empty($product_id)) {
            $builder->where('stock_out.product_id', $product_id);
        }
        if (!empty($transaction_type)) {
            $builder->where('stock_out.transaction_type', $transaction_type);
        }
        if (!empty($issued_date_start)) {
            $builder->where('stock_out.issued_date >=', $issued_date_start);
        }
        if (!empty($issued_date_end)) {
            $builder->where('stock_out.issued_date <=', $issued_date_end);
        }

        $stockOutRecords = $builder->orderBy('stock_out.issued_date DESC, stock_out.id DESC')->get()->getResultArray();

        // Fetch all products and transaction types for filter dropdowns
        $products = $this->productModel->select('id, name')->findAll();
        $allTransactionTypes = $this->stockOutModel->distinct()->select('transaction_type')->findAll();

        // Prepare a map for product names (if not already joined in primary query)
        $productMap = array_column($products, 'name', 'id');

        // Enrich stockOutRecords with related sale/person details for 'Sale' and 'marketing_distribution' types
        $enrichedRecords = [];
        foreach ($stockOutRecords as $record) {
            $record['related_transaction_details'] = null; // Initialize
            $record['product_name'] = $productMap[$record['product_id']] ?? 'N/A'; // Ensure product name is set

            if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
                // Fetch sale and marketing person details if transaction_type is Sale
                $sale = $this->salesModel->select('sales.*, marketing_persons.name as marketing_person_name, marketing_persons.custom_id as marketing_person_custom_id')
                    ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($sale) {
                    $record['related_transaction_details'] = [
                        'type' => 'Sale',
                        'sale_id' => $sale['id'],
                        'customer_name' => $sale['customer_name'],
                        'marketing_person' => $sale['marketing_person_name'] . ' (' . $sale['marketing_person_custom_id'] . ')',
                        'sale_date' => $sale['date_sold'],
                        // Add more sale details if needed
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
                        'type'                          => 'Marketing Distribution',
                        'distribution_id'               => $distribution['id'],
                        'marketing_person_id'           => $distribution['marketing_person_id'],
                        'marketing_person_name'         => $distribution['marketing_person_name'],
                        'marketing_person_custom_id'    => $distribution['marketing_person_custom_id'],
                        'marketing_person_phone'        => $distribution['marketing_person_phone'],
                        'quantity_issued_in_dist'       => $distribution['quantity_issued'],
                        'date_issued_in_dist'           => $distribution['date_issued'],
                        'notes_in_dist'                 => $distribution['notes'],
                        'marketing_person_display'      => $distribution['marketing_person_name'] . ' (ID: ' . $distribution['marketing_person_custom_id'] . ', Phone: ' . $distribution['marketing_person_phone'] . ')'
                    ];
                }
            }
            $enrichedRecords[] = $record;
        }

        $data = [
            'title'                 => 'Stock Out Records',
            'stockOutRecords'       => $enrichedRecords,
            'products'              => $products, // For filter dropdown
            'transactionTypes'      => array_column($allTransactionTypes, 'transaction_type'), // For filter dropdown
            'selectedProductId'       => $product_id,
            'selectedTransactionType' => $transaction_type,
            'selectedIssuedDateStart' => $issued_date_start,
            'selectedIssuedDateEnd'   => $issued_date_end,
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
            // 'Sale' is explicitly removed from this list for new entries as per your request
            'transaction_types' => ['Damage', 'Sample', 'Internal Use',  'Other'],
        ];

        // *** IMPORTANT: This needs to point to your actual view file, which you confirmed is 'issue_form.php' ***
        return view('stock_out/issue_form', $data);
    }

    /**
     * Handles the submission of the stock out issue form.
     */
     public function store(): ResponseInterface
    {
        // Removed dd($this->request->getPost());

        $rules = [
            'product_id'        => 'required|integer|is_not_unique[products.id]',
            'quantity_out'      => 'required|integer|greater_than[0]',
            // 'marketing_distribution' removed from in_list validation
            'transaction_type'  => 'required|in_list[Damage,Sample,Internal Use,Other]',
            'issued_date'       => 'required|valid_date',
            'notes'             => 'permit_empty|string|max_length[500]',
            'transaction_id'    => 'permit_empty|integer', // Always permit_empty now
        ];

        // The conditional block for 'marketing_distribution' is REMOVED
        // because it's no longer an option from the form.
        // If you add another transaction type later that needs 'transaction_id' to be required,
        // you would add a similar if/else if block here.

        if (!$this->validate($rules)) {
            log_message('error', 'StockOut::store - Validation failed. Errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $productId = $this->request->getPost('product_id');
        $quantityOut = $this->request->getPost('quantity_out');
        $transactionType = $this->request->getPost('transaction_type'); // Get transaction type
        $transactionId = $this->request->getPost('transaction_id');

        // Ensure transaction_id is null if it's empty, regardless of transaction type,
        // as it's now always permit_empty from the form.
        if (empty($transactionId)) {
            $transactionId = null;
        }

        // Check current product stock BEFORE transaction begins
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
            'transaction_id'    => $transactionId, // Will be null or integer
            'issued_date'       => $this->request->getPost('issued_date'),
            'notes'             => $this->request->getPost('notes'),
        ];

        $this->db->transBegin(); // Start database transaction
        log_message('debug', 'StockOut::store - Database transaction begun.');

        try {
            // Attempt to insert stock out record
            $stockOutInsertResult = $this->stockOutModel->insert($stockOutData);
            log_message('debug', 'StockOut::store - Stock Out Insert attempt. Result: ' . ($stockOutInsertResult ? 'Success (ID: ' . $stockOutInsertResult . ')' : 'Failed'));

            if (!$stockOutInsertResult) { // If insert fails, explicitly rollback and report
                $this->db->transRollback();
                $dbError = $this->db->error(); // Get database error details
                log_message('error', 'StockOut::store - Failed to insert stock out record. DB Error: ' . json_encode($dbError) . ', Data: ' . json_encode($stockOutData));
                return redirect()->back()->withInput()->with('error', 'Failed to record stock out. Database insert failed: ' . $dbError['message']);
            }

            // Attempt to update product stock
            $newStock = $product['current_stock'] - $quantityOut;
            $productUpdateResult = $this->productModel->update($productId, ['current_stock' => $newStock]);
            log_message('debug', 'StockOut::store - Product Stock Update attempt. Result: ' . ($productUpdateResult ? 'Success' : 'Failed') . ', New Stock: ' . $newStock);

            if (!$productUpdateResult) { // If product update fails, explicitly rollback and report
                $this->db->transRollback();
                $dbError = $this->db->error(); // Get database error details
                log_message('error', 'StockOut::store - Failed to update product stock. Product ID: ' . $productId . ', New Stock: ' . $newStock . ', DB Error: ' . json_encode($dbError));
                return redirect()->back()->withInput()->with('error', 'Failed to record stock out. Database update for product stock failed: ' . $dbError['message']);
            }

            if ($this->db->transStatus() === false) {
                // This block catches any unhandled transaction failures before commit
                $this->db->transRollback();
                $dbError = $this->db->error(); // Get any lingering database error details
                log_message('error', 'StockOut::store - Transaction final status is FALSE (before commit). DB Error: ' . json_encode($dbError) . ', Data: ' . json_encode($stockOutData));
                return redirect()->back()->withInput()->with('error', 'Failed to record stock out due to an internal transaction error. Please check system logs for details.');
            } else {
                // If all operations successful, commit the transaction
                $this->db->transCommit();
                log_message('debug', 'StockOut::store - Transaction Committed Successfully.');
                return redirect()->to(base_url('stock-out'))->with('success', 'Stock Out recorded successfully!');
            }
        } catch (\Exception $e) {
            // Catch any unexpected PHP exceptions that occur during the process
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

        // Initialize related_transaction_details
        $record['related_transaction_details'] = null;

        if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
            $sale = $this->salesModel
                ->select('sales.*, customers.name as customer_name, marketing_persons.name as marketing_person_name, marketing_persons.phone as marketing_person_phone')
                ->join('customers', 'customers.id = sales.customer_id', 'left')
                ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                ->find($record['transaction_id']);

            if ($sale) {
                $record['related_transaction_details'] = [
                    'type'                        => 'Sale',
                    'sale_id'                     => $sale['id'],
                    'customer_name'               => $sale['customer_name'],
                    'marketing_person'            => ($sale['marketing_person_name'] ? $sale['marketing_person_name'] . ' (' . $sale['marketing_person_phone'] . ')' : 'N/A'),
                    'sale_date'                   => $sale['sale_date'],
                    'quantity_sold_in_sale'       => $sale['quantity_sold'],
                    'price_per_unit_in_sale'      => $sale['price_per_unit'],
                    'total_price_in_sale'         => $sale['total_price'],
                    'payment_status_from_person'  => $sale['payment_status'],
                    'amount_received_from_person' => $sale['amount_received'],
                    'balance_from_person'         => $sale['balance'],
                ];
            }
        } elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['transaction_id'])) {
            $distribution = $this->marketingDistributionModel
                ->select('marketing_distribution.*, marketing_persons.name as marketing_person_name, marketing_persons.custom_id as marketing_person_custom_id, marketing_persons.phone as marketing_person_phone')
                ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id', 'left')
                ->find($record['transaction_id']);

            if ($distribution) {
                $record['related_transaction_details'] = [
                    'type'                          => 'Marketing Distribution',
                    'distribution_id'               => $distribution['id'],
                    'marketing_person_db_id'        => $distribution['marketing_person_id'],
                    'marketing_person_name'         => $distribution['marketing_person_name'],
                    'marketing_person_custom_id'    => $distribution['marketing_person_custom_id'],
                    'marketing_person_phone'        => $distribution['marketing_person_phone'],
                    'quantity_issued_in_dist'       => $distribution['quantity_issued'],
                    'date_issued_in_dist'           => $distribution['date_issued'],
                    'notes_in_dist'                 => $distribution['notes'],
                    'marketing_person_display'      => $distribution['marketing_person_name'] . ' (ID: ' . $distribution['marketing_person_custom_id'] . ', Phone: ' . $distribution['marketing_person_phone'] . ')'
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

        $builder = $this->stockOutModel->builder();
        $builder->select('stock_out.id, products.name as product_name, stock_out.quantity_out, stock_out.transaction_type, stock_out.transaction_id, stock_out.issued_date, stock_out.notes, stock_out.created_at');
        $builder->join('products', 'products.id = stock_out.product_id');

        // Apply filters
        if (!empty($product_id)) {
            $builder->where('stock_out.product_id', $product_id);
        }
        if (!empty($transaction_type)) {
            $builder->where('stock_out.transaction_type', $transaction_type);
        }
        if (!empty($issued_date_start)) {
            $builder->where('stock_out.issued_date >=', $issued_date_start);
        }
        if (!empty($issued_date_end)) {
            $builder->where('stock_out.issued_date <=', $issued_date_end);
        }
        $recordsToExport = $builder->orderBy('stock_out.issued_date DESC, stock_out.id DESC')->get()->getResultArray();

        // If 'Sale' type, fetch sale details for export
        foreach ($recordsToExport as &$record) {
            if ($record['transaction_type'] === 'Sale' && !empty($record['transaction_id'])) {
                $sale = $this->salesModel->select('customer_name, marketing_persons.name as marketing_person_name')
                    ->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($sale) {
                    $record['customer_name'] = $sale['customer_name'] ?? '';
                    $record['marketing_person_name_for_export'] = $sale['marketing_person_name'] ?? '';
                }
            } elseif ($record['transaction_type'] === 'marketing_distribution' && !empty($record['transaction_id'])) {
                $distribution = $this->marketingDistributionModel
                    ->select('marketing_persons.name as marketing_person_name, marketing_persons.custom_id')
                    ->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id', 'left')
                    ->find($record['transaction_id']);
                if ($distribution) {
                    $record['customer_name'] = 'N/A'; // No customer for direct distribution
                    $record['marketing_person_name_for_export'] = $distribution['marketing_person_name'] . ' (ID: ' . $distribution['custom_id'] . ')';
                }
            } else {
                $record['customer_name'] = '';
                $record['marketing_person_name_for_export'] = '';
            }
        }
        unset($record); // Unset the reference

        $filename = 'stock_out_records_' . date('YmdHis') . '.csv'; // Using CSV as a simple example
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add CSV Headers
        fputcsv($output, ['S.No.', 'Product Name', 'Quantity Out', 'Transaction Type', 'Transaction ID', 'Issued Date', 'Notes', 'Recorded At', 'Customer Name', 'Marketing Person']);

        // Add Data
        $sno = 1;
        foreach ($recordsToExport as $row) {
            fputcsv($output, [
                $sno++,
                $row['product_name'],
                $row['quantity_out'],
                $row['transaction_type'],
                $row['transaction_id'],
                $row['issued_date'],
                $row['notes'],
                $row['created_at'],
                $row['customer_name'],
                $row['marketing_person_name_for_export']
            ]);
        }
        fclose($output);

        exit(); // Important to stop further CodeIgniter execution
    }

    /**
     * Export Stock Out records to PDF.
     */
    public function exportPdf(): ResponseInterface
    {
        // Fetch the same data you display in the index view
        $data = [
            'title'   => 'Stock Out List PDF',
            'stockOuts' => $this->stockOutModel
                ->select('stock_out.*, products.name as product_name')
                ->join('products', 'products.id = stock_out.product_id')
                ->orderBy('issued_date', 'DESC')
                ->findAll()
        ];

        // Load the PDF template view to capture its HTML content
        $html = view('stock_out/pdf_template', $data);

        // Instantiate Dompdf
        $dompdf = new Dompdf();

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