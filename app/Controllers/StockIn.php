<?php

namespace App\Controllers;

use App\Models\StockInModel;
use App\Models\ProductModel;
use App\Models\VendorModel;  // Make sure VendorModel is used
use App\Models\GstRateModel; // ADD THIS LINE
use App\Models\StockInPaymentModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;


class StockIn extends BaseController
{
    protected $stockInModel;
    protected $productModel;
    protected $vendorModel;  // ADD THIS LINE (if not already present and used for vendorModel)
    protected $gstRateModel; // ADD THIS LINE
    protected $stockInPaymentModel;

    public function __construct()
    {
        $this->stockInModel = new StockInModel();
        $this->productModel = new ProductModel();
        $this->vendorModel  = new VendorModel(); // INITIALIZE VendorModel
        $this->gstRateModel = new GstRateModel(); // INITIALIZE GstRateModel
        $this->stockInPaymentModel = new StockInPaymentModel();
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
        $vendors  = $this->vendorModel->findAll(); // Using the class property
        $gstRates = $this->gstRateModel->findAll(); // FETCH GST RATES

        return view('stock_in/create', [
            'products' => $products,
            'vendors'  => $vendors,
            'gstRates' => $gstRates // PASS GST RATES TO THE VIEW
        ]);
    }

    public function store()
    {
        $rules = [
            'product_id'            => 'required|numeric',
            'quantity'              => 'required|numeric|greater_than[0]',
            'purchase_price'        => 'required|numeric|greater_than_equal_to[0]',
            'gst_rate_id'           => 'required|numeric',
            'date_received'         => 'required|valid_date',
            'total_amount_hidden'   => 'required|numeric|greater_than_equal_to[0]',
            'gst_amount'            => 'required|numeric|greater_than_equal_to[0]',
            'grand_total_hidden'    => 'required|numeric|greater_than_equal_to[0]',
            'amount_paid_initial'   => 'permit_empty|numeric|greater_than_equal_to[0]', // New validation for initial payment
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataToSave = [
            'product_id'            => $this->request->getPost('product_id'),
            'quantity'              => $this->request->getPost('quantity'),
            'vendor_id'             => $this->request->getPost('vendor_id'),
            'purchase_price'        => $this->request->getPost('purchase_price'),
            // 'selling_price'         => $this->request->getPost('selling_price'),
            'date_received'         => $this->request->getPost('date_received'),
            'notes'                 => $this->request->getPost('notes'),
            'gst_rate_id'           => $this->request->getPost('gst_rate_id'),
            'total_amount_before_gst' => $this->request->getPost('total_amount_hidden'),
            'gst_amount'            => $this->request->getPost('gst_amount'),
            'grand_total'           => $this->request->getPost('grand_total_hidden'),
            // REMOVE 'amount_paid' and 'amount_pending' from here
            // 'amount_paid' field in stock_in will be updated by StockInPaymentModel callback
            // 'amount_pending' field in stock_in will be updated by StockInPaymentModel callback
        ];

        $dataToSave['current_quantity'] = $dataToSave['quantity'];
        // dd($dataToSave);
        // Save the main stock_in entry first
        $stockInId = $this->stockInModel->insert($dataToSave, true); // `true` returns the inserted ID

        if ($stockInId) {
            $initialAmountPaid = (float)$this->request->getPost('amount_paid_initial');

            // If an initial payment is provided, record it
            if ($initialAmountPaid > 0) {
                $paymentData = [
                    'stock_in_id'    => $stockInId,
                    'payment_amount' => $initialAmountPaid,
                    'payment_date'   => date('Y-m-d'), // Today's date for initial payment
                    'notes'          => 'Initial payment upon stock-in',
                    'created_at'     => date('Y-m-d H:i:s')
                ];

                log_message('debug', 'PHP Default Timezone (from date_default_timezone_get()): ' . date_default_timezone_get());
                log_message('debug', 'Current time from date(): ' . date('Y-m-d H:i:s'));


                $this->stockInPaymentModel->save($paymentData);
                // The StockInPaymentModel callback will automatically update amount_paid and amount_pending in stock_in
            }

            return redirect()->to('/stock-in')->with('success', 'Stock added successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to add stock. Please try again.');
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

        return view('stock_in/view', $data); // We will create this view next
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
            'stock_entry_payments' => $stockEntryPayments, // <<< ADD THIS LINE
            'products'             => $products,
            'vendors'              => $vendors,
            'gstRates'             => $gstRates
        ]);
    }

    // In app/Controllers/StockIn.php

    // ... (existing methods)

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

        if ($this->stockInPaymentModel->save($data)) {
            session()->setFlashdata('success', 'Payment added successfully!');
            // Redirect back to the view page of the parent stock-in entry
            return redirect()->to(base_url('stock-in/view/' . $stockInId));
        } else {
            session()->setFlashdata('error', 'Failed to add payment. Please try again.');
            return redirect()->back()->withInput();
        }
    }
    // --- ADD THE addPayment METHOD BELOW THIS LINE ---
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

        if ($this->stockInPaymentModel->save($paymentData)) {
            return redirect()->to('/stock-in/edit/' . $stockInId)->with('success', 'Payment recorded successfully.');
        } else {
            return redirect()->to('/stock-in/edit/' . $stockInId)->with('error', 'Failed to record payment. Please try again.');
        }
    }

    // In app/Controllers/StockIn.php

    public function update($id)
    {
        $rules = [
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric|greater_than[0]',
            'purchase_price' => 'required|numeric|greater_than[0]',
            'gst_rate_id' => 'required|integer',
            'date_received' => 'required|valid_date',
            'notes' => 'permit_empty|string|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get the hidden grand_total from the form (calculated by JavaScript)
        $grandTotalFromForm = $this->request->getPost('grand_total_hidden');
        $totalAmountBeforeGstFromForm = $this->request->getPost('total_amount_hidden');
        $gstAmountFromForm = $this->request->getPost('gst_amount_hidden');


        $data = [
            'product_id'              => $this->request->getPost('product_id'),
            'quantity'                => $this->request->getPost('quantity'),
            'vendor_id'               => $this->request->getPost('vendor_id'),
            'purchase_price'          => $this->request->getPost('purchase_price'),
            'gst_rate_id'             => $this->request->getPost('gst_rate_id'),
            'date_received'           => $this->request->getPost('date_received'),
            'notes'                   => $this->request->getPost('notes'),
            // These calculated values are passed from the frontend hidden fields
            'total_amount_before_gst' => $totalAmountBeforeGstFromForm,
            'gst_amount'              => $gstAmountFromForm,
            'grand_total'             => $grandTotalFromForm, // <<< Make sure this is included
            // amount_paid and amount_pending are NOT updated here, they are managed by payments
        ];

        if ($this->stockInModel->update($id, $data)) {
            session()->setFlashdata('success', 'Stock In entry updated successfully!');
            return redirect()->to(base_url('stock-in/view/' . $id)); // Redirect back to the view page
        } else {
            session()->setFlashdata('error', 'Failed to update Stock In entry. Please try again.');
            return redirect()->back()->withInput();
        }
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('/stock-in')->with('error', 'No Stock In ID provided for deletion.');
        }

        // Optional: Check if the record exists before attempting to delete
        $stockEntry = $this->stockInModel->find($id);
        if (empty($stockEntry)) {
            return redirect()->to('/stock-in')->with('error', 'Stock In entry not found for deletion.');
        }

        if ($this->stockInModel->delete($id)) {
            return redirect()->to('/stock-in')->with('success', 'Stock In entry deleted successfully.');
        } else {
            return redirect()->to('/stock-in')->with('error', 'Failed to delete Stock In entry. Please try again.');
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

    public function editPayment($paymentId)
    {
        $payment = $this->stockInPaymentModel->find($paymentId);

        if (empty($payment)) {
            session()->setFlashdata('error', 'Payment entry not found.');
            return redirect()->back(); // Redirect back to the stock-in view page
        }

        $data = [
            'payment' => $payment,
            'title'   => 'Edit Payment'
        ];

        return view('stock_in/payment_edit', $data); // Create this view next
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
            // Do NOT update created_at here. If you need updated_at, you'd add it to StockInPaymentModel.
        ];

        if ($this->stockInPaymentModel->update($paymentId, $data)) {
            session()->setFlashdata('success', 'Payment updated successfully!');
            // Redirect back to the view page of the parent stock-in entry
            return redirect()->to(base_url('stock-in/view/' . $payment['stock_in_id']));
        } else {
            session()->setFlashdata('error', 'Failed to update payment. Please try again.');
            return redirect()->back()->withInput();
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

        if ($this->stockInPaymentModel->delete($paymentId)) {
            session()->setFlashdata('success', 'Payment deleted successfully!');
            // Redirect back to the view page of the parent stock-in entry
            return redirect()->to(base_url('stock-in/view/' . $stockInId));
        } else {
            session()->setFlashdata('error', 'Failed to delete payment. Please try again.');
            return redirect()->back();
        }
    }
}
