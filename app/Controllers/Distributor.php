<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DistributorModel; // Make sure to import your new DistributorModel
use CodeIgniter\HTTP\ResponseInterface;

class Distributor extends BaseController
{
    protected $distributorModel;
    protected $session;
    protected $validation;

    public function __construct()
    {
        // Load the Distributor Model
        $this->distributorModel = new DistributorModel();
        // Load CodeIgniter services
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        // Load necessary helpers
        helper(['form', 'url']);
    }

    /**
     * Displays a list of all distributors.
     * This will be the main landing page for the distributors module.
     */
    public function index(): string
    {
        $data = [
            'title'        => 'Distributors List',
            'distributors' => $this->distributorModel->orderBy('agency_name', 'ASC')->findAll(),
        ];

        // This view file (app/Views/distributors/index.php) will be created next
        return view('distributors/index', $data);
    }

    /**
     * Displays the form to add a new distributor.
     */
    public function add(): string
    {
        $data = [
            'title'         => 'Add New Distributor',
            'validation'    => $this->validation, // Pass validation service to the view for error display
            'statusOptions' => ['Active', 'Inactive', 'On Hold'], // Options for the status dropdown
        ];

        // This view file (app/Views/distributors/add_form.php) will be created next
        return view('distributors/add_form', $data);
    }

    /**
     * Handles the submission of the add new distributor form.
     */
    public function store(): ResponseInterface
    {
        // Get all post data
        $postData = $this->request->getPost();

        // Validate the input using rules defined in the DistributorModel
        // The Model's validation rules will be used here automatically
        if (!$this->distributorModel->save($postData)) { // save() handles both insert and update and validates automatically
            log_message('error', 'Distributor::store - Validation failed. Errors: ' . json_encode($this->distributorModel->errors()));
            return redirect()->back()->withInput()->with('errors', $this->distributorModel->errors());
        }

        // If validation passes, the data has already been inserted by $this->distributorModel->save($postData)
        // Set a success flash message and redirect
        return redirect()->to(base_url('distributors'))->with('success', 'Distributor added successfully!');
    }

    // You can add more methods here later, like 'edit', 'update', 'delete', 'view_detail' etc.
}