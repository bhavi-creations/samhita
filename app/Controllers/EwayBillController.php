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
use App\Models\CompanySettingModel;
use App\Models\MarketingPersonModel;
use App\Models\UnitModel;
use App\Models\EwayBillModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Dompdf\Dompdf;

class EwayBillController extends BaseController
{
    use ResponseTrait;

    /**
     * Displays a list of all e-way bills.
     */


    public function index()
    {
        $data['title'] = 'E-way Bills';
        $ewayBillModel = new EwayBillModel();

        // Fetch all E-way bills, joining with sales orders and ordering by the latest generated date
        $data['eway_bills'] = $ewayBillModel
            ->select('eway_bills.*, distributor_sales_orders.invoice_number')
            ->join('distributor_sales_orders', 'distributor_sales_orders.id = eway_bills.distributor_sales_order_id')
            ->orderBy('eway_bills.generated_at', 'DESC') // Added this line to sort by the latest generated bills
            ->findAll();

        return view('e_way_bills/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create New E-way Bill';
        $salesOrderModel = new DistributorSalesOrderModel();

        // Updated query to join with the distributors table and select the agency name
        $data['sales_orders'] = $salesOrderModel
            ->select('distributor_sales_orders.*, distributors.agency_name')
            ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
            ->orderBy('distributor_sales_orders.id', 'DESC')
            ->findAll();

        return view('e_way_bills/create', $data);
    }

    public function store()
    {
        // Add validation rules for the new fields
        $rules = [
            'distributor_sales_order_id' => 'required|is_natural_no_zero',
            'vehicle_number' => 'required|max_length[15]',
            'place_of_dispatch' => 'required|max_length[255]',
            'place_of_delivery' => 'required|max_length[255]',
            'reason_for_transportation' => 'required|max_length[255]',
            // New fields validation
            'bill_generated_by' => 'required|max_length[255]',
            'transaction_type' => 'required|max_length[50]',
            'driver_name' => 'required|max_length[255]',
            'distance' => 'required|integer', // Added validation for distance
            'transport_mode' => 'required|max_length[50]', // Added validation for transport_mode
            // 'multiple_veh_info' and 'cewb_no' are optional and do not require a rule
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = $this->request->getPost();

            $ewayBillModel = new EwayBillModel();
            $salesOrderModel = new DistributorSalesOrderModel();
            $distributorModel = new DistributorModel();

            // 1. Fetch sales order and distributor data
            $salesOrder = $salesOrderModel->find($data['distributor_sales_order_id']);
            if (!$salesOrder) {
                throw new \Exception("Sales Order not found.");
            }
            $distributor = $distributorModel->find($salesOrder['distributor_id']);

            // 2. Generate E-way bill number
            $lastEwayBill = $ewayBillModel->orderBy('id', 'DESC')->first();
            $nextCounter = 1;
            if ($lastEwayBill) {
                // Extract the counter from the last E-way bill number
                $lastNumber = substr($lastEwayBill['eway_bill_no'], -4);
                $nextCounter = (int)$lastNumber + 1;
            }
            $generatedDate = date('Ymd');
            $data['eway_bill_no'] = 'EWAY-' . $generatedDate . str_pad($nextCounter, 4, '0', STR_PAD_LEFT);
            $data['generated_at'] = date('Y-m-d H:i:s');
            $data['valid_until'] = date('Y-m-d H:i:s', strtotime('+7 days'));

            // 3. Store E-way bill with all new data, including the new fields
            $ewayBillModel->insert($data);

            return redirect()->to('/e-way-bills')->with('success', 'E-way bill ' . $data['eway_bill_no'] . ' created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', 'Database Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        $ewayBillModel = new EwayBillModel();
        $salesOrderModel = new DistributorSalesOrderModel();
        $distributorModel = new DistributorModel();
        $salesOrderItemModel = new DistributorSalesOrderItemModel();
        $sellingProductModel = new SellingProductModel();

        // Find the e-way bill or show 404
        $ewayBill = $ewayBillModel->find($id);
        if (!$ewayBill) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Fetch related sales order and distributor details
        $salesOrder = $salesOrderModel->find($ewayBill['distributor_sales_order_id']);
        $distributor = $distributorModel->find($salesOrder['distributor_id']);

        // Fetch products associated with the sales order
        $salesOrderItems = $salesOrderItemModel
            ->select('distributor_sales_order_items.*, selling_products.name')
            ->join('selling_products', 'selling_products.id = distributor_sales_order_items.product_id')
            ->where('distributor_sales_order_id', $salesOrder['id'])
            ->findAll();

        $data['eway_bill'] = $ewayBill;
        $data['sales_order'] = $salesOrder;
        $data['distributor'] = $distributor;
        $data['sales_order_items'] = $salesOrderItems;
        $data['title'] = 'View E-way Bill: ' . $ewayBill['eway_bill_no'];

        return view('e_way_bills/view', $data);
    }
    public function edit($id = null)
    {
        // Instantiate the models
        $ewayBillModel = new EwayBillModel();
        $distributorSalesOrderModel = new DistributorSalesOrderModel();
        $distributorSalesOrderItemModel = new DistributorSalesOrderItemModel();

        // Fetch the specific E-Way Bill data
        $eway_bill = $ewayBillModel->find($id);

        if (!$eway_bill) {
            return redirect()->to('/e-way-bills')->with('error', 'E-Way Bill not found.');
        }

        // Fetch all sales orders with their associated distributor names for the dropdown
        $sales_orders = $distributorSalesOrderModel
            ->select('distributor_sales_orders.*, distributors.agency_name')
            ->orderBy('distributor_sales_orders.id', 'DESC')
            ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id')
            ->findAll();

        // Fetch the items for the sales order currently associated with the e-way bill
        $sales_order_items = $distributorSalesOrderItemModel
            ->select('distributor_sales_order_items.*, selling_products.name')
            ->join('selling_products', 'selling_products.id = distributor_sales_order_items.product_id')
            ->where('distributor_sales_order_id', $eway_bill['distributor_sales_order_id'])
            ->findAll();

        $data = [
            'title' => 'Edit E-Way Bill',
            'eway_bill' => $eway_bill,
            'sales_orders' => $sales_orders,
            'sales_order_items' => $sales_order_items,
        ];

        return view('e_way_bills/edit', $data);
    }



    public function update($id)
    {
        // Define validation rules for all fields
        $rules = [
            'distributor_sales_order_id' => 'required|is_natural_no_zero',
            'bill_generated_by'          => 'required|max_length[255]',
            'place_of_dispatch'          => 'required|max_length[255]',
            'place_of_delivery'          => 'required|max_length[255]',
            'driver_name'                => 'required|max_length[255]',
            'vehicle_number'             => 'required|max_length[15]',
            'transaction_type'           => 'required|max_length[50]',
            'reason_for_transportation'  => 'required|max_length[255]',
            'distance'                   => 'required|integer',
            'transport_mode'             => 'required|max_length[50]',
            // 'multiple_veh_info' and 'cewb_no' are optional and do not require a rule
        ];

        // Check if the request passes validation
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $ewayBillModel = new EwayBillModel();

            // Find the existing e-way bill record
            $existingEwayBill = $ewayBillModel->find($id);

            if (!$existingEwayBill) {
                return redirect()->to('/e-way-bills')->with('error', 'E-Way Bill not found.');
            }

            // Get the validated data from the form
            $data = $this->request->getPost();

            // Update the record in the database
            $ewayBillModel->update($id, $data);

            return redirect()->to('/e-way-bills')->with('success', 'E-way bill updated successfully.');
        } catch (ValidationException $e) {
            // This catch block is for completeness, but the above validation handles most cases.
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }


    public function delete($id)
    {
        try {
            $ewayBillModel = new EwayBillModel();
            $ewayBill = $ewayBillModel->find($id);

            if (!$ewayBill) {
                return redirect()->to('/e-way-bills')->with('error', 'E-way bill not found.');
            }

            $ewayBillModel->delete($id);

            return redirect()->to('/e-way-bills')->with('success', 'E-way bill deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->to('/e-way-bills')->with('error', 'An error occurred while trying to delete the e-way bill: ' . $e->getMessage());
        }
    }



    public function getSalesOrderItems($salesOrderId)
    {
        $distributorSalesOrderItemModel = new DistributorSalesOrderItemModel();

        $sales_order_items = $distributorSalesOrderItemModel
            ->select('distributor_sales_order_items.*, selling_products.name')
            ->join('selling_products', 'selling_products.id = distributor_sales_order_items.product_id')
            ->where('distributor_sales_order_id', $salesOrderId)
            ->findAll();

        return $this->respond($sales_order_items);
    }

    /**
     * Generates and downloads a PDF of the e-way bill.
     */
    public function downloadPdf($id)
    {
        $ewayBillModel = new EwayBillModel();
        $salesOrderModel = new DistributorSalesOrderModel();
        $distributorModel = new DistributorModel();
        $salesOrderItemModel = new DistributorSalesOrderItemModel();

        $ewayBill = $ewayBillModel->find($id);
        if (!$ewayBill) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $salesOrder = $salesOrderModel->find($ewayBill['distributor_sales_order_id']);
        $distributor = $distributorModel->find($salesOrder['distributor_id']);
        $salesOrderItems = $salesOrderItemModel
            ->select('distributor_sales_order_items.*, selling_products.name')
            ->join('selling_products', 'selling_products.id = distributor_sales_order_items.product_id')
            ->where('distributor_sales_order_id', $salesOrder['id'])
            ->findAll();

        $data = [
            'eway_bill' => $ewayBill,
            'sales_order' => $salesOrder,
            'distributor' => $distributor,
            'sales_order_items' => $salesOrderItems,
        ];

        // Instantiate and use the Dompdf library
        $dompdf = new Dompdf();
        $html = view('e_way_bills/pdf_layout', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream the file to the browser for download
        $filename = 'EWAY_BILL_' . $ewayBill['eway_bill_no'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
        exit();
    }
}
