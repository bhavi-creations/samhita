<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DistributorSalesOrderModel;
use App\Models\MarketingPersonModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class MarketingSalesController extends BaseController
{
    // These properties will hold instances of the models
    protected $marketingPersonModel;
    protected $distributorSalesOrderModel;

    /**
     * Constructor to load the necessary models.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do not forget to call the parent's initController
        parent::initController($request, $response, $logger);

        $this->marketingPersonModel = new MarketingPersonModel();
        $this->distributorSalesOrderModel = new DistributorSalesOrderModel();
    }

    /**
     * Displays a list of sales orders, filtered by marketing person.
     */
    public function index()
    {
        // Fetch all marketing persons to populate the filter dropdown
        $marketingPersons = $this->marketingPersonModel->findAll();

        // Get the marketing person ID from the URL query string
        $marketingPersonId = $this->request->getGet('marketing_person_id');
        $selectedPersonName = 'All Marketing Persons';

        // Initialize the query builder for sales orders
        $salesQuery = $this->distributorSalesOrderModel->select(
            // --- CORRECTION: Use the 'status' column from the database and alias it as 'payment_status' ---
            'distributor_sales_orders.id, distributor_sales_orders.invoice_number, distributor_sales_orders.invoice_date, ' .
            'distributor_sales_orders.final_total_amount as grand_total, distributor_sales_orders.status as payment_status, ' .
            'distributor_sales_orders.distributor_id, distributor_sales_orders.marketing_person_id, ' .
            'distributors.owner_name, distributors.agency_name, ' .
            'marketing_persons.name as marketing_person_name'
            // --- END OF CORRECTION ---
        )
        ->join('distributors', 'distributors.id = distributor_sales_orders.distributor_id', 'left')
        ->join('marketing_persons', 'marketing_persons.id = distributor_sales_orders.marketing_person_id', 'left');

        // Check if a specific marketing person is selected for filtering
        if ($marketingPersonId) {
            $salesQuery->where('distributor_sales_orders.marketing_person_id', $marketingPersonId);
            $selectedPerson = $this->marketingPersonModel->find($marketingPersonId);
            if ($selectedPerson) {
                $selectedPersonName = $selectedPerson['name'];
            }
        }

        // Fetch the sales orders based on the applied filter
        $salesOrders = $salesQuery->orderBy('invoice_date', 'DESC')->findAll();

        // Prepare the data to be sent to the view
        $data = [
            'marketingPersons'   => $marketingPersons,
            'salesOrders'        => $salesOrders,
            'selectedPersonId'   => $marketingPersonId,
            'selectedPersonName' => $selectedPersonName,
            'title'              => 'Marketing Sales Report'
        ];

        // Load the view and pass the data
        return view('marketing_sales/index', $data);
    }
}
