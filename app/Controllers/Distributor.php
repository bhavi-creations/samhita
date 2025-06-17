<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DistributorModel;
use CodeIgniter\HTTP\ResponseInterface; // Import ResponseInterface

class Distributor extends BaseController
{
    protected $distributorModel;
    protected $session;
    protected $validation; // Declared here, initialized in constructor

    public function __construct()
    {
        $this->distributorModel = new DistributorModel();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation(); // Correctly initialized
        helper(['form', 'url']); // Ensure form and URL helpers are loaded
    }

    /**
     * Displays a list of all distributors.
     */
    public function index(): string
    {
        $data = [
            'title'        => 'Distributors List',
            'distributors' => $this->distributorModel->orderBy('agency_name', 'ASC')->findAll(),
        ];
        return view('distributors/index', $data);
    }

    /**
     * Displays the form to add a new distributor.
     * This method will now also be used for editing by passing existing data.
     */
    public function add(): string
    {
        $data = [
            'title'         => 'Add New Distributor',
            'validation'    => $this->validation, // Pass validation service to view for sticky errors
            'statusOptions' => ['Active', 'Inactive', 'On Hold'],
            'distributor'   => null, // No distributor data for adding
        ];
        return view('distributors/add_form', $data);
    }

    /**
     * Handles the submission of the add new distributor form.
     */
    public function store(): ResponseInterface
    {
        $postData = $this->request->getPost();
        log_message('debug', 'Distributor::store - Received Post Data: ' . json_encode($postData));

        // --- CRUCIAL: Explicitly validate using the model's validate method ---
        if (!$this->distributorModel->validate($postData)) {
            $errors = $this->distributorModel->errors();
            log_message('error', 'Distributor::store - Validation failed. Errors: ' . json_encode($errors));
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // If validation passes, attempt to save
        if (!$this->distributorModel->save($postData)) {
            // This 'save' failing might indicate a database error after validation
            $dbErrors = $this->distributorModel->errors(); // Get any database-related errors if save() failed
            log_message('error', 'Distributor::store - Model save() failed after validation. Database errors: ' . json_encode($dbErrors));
            return redirect()->back()->withInput()->with('errors', $dbErrors);
        }

        log_message('debug', 'Distributor::store - New distributor added successfully.');
        return redirect()->to(base_url('distributors'))->with('success', 'Distributor added successfully!');
    }

    /**
     * Displays the details of a single distributor.
     *
     * @param int $id The ID of the distributor to view.
     */
    public function view(int $id): string | ResponseInterface
    {
        $distributor = $this->distributorModel->find($id);

        if (!$distributor) {
            return redirect()->to(base_url('distributors'))->with('error', 'Distributor not found.');
        }

        $data = [
            'title'       => 'Distributor Details',
            'distributor' => $distributor,
        ];

        return view('distributors/view_details', $data);
    }

    /**
     * Displays the form to edit an existing distributor.
     *
     * @param int $id The ID of the distributor to edit.
     */
    public function edit(int $id): string | ResponseInterface
    {
        $distributor = $this->distributorModel->find($id);

        if (!$distributor) {
            return redirect()->to(base_url('distributors'))->with('error', 'Distributor not found for editing.');
        }

        $data = [
            'title'         => 'Edit Distributor',
            // Pass the validation service to the view so it can be used for showing sticky errors.
            'validation'    => \Config\Services::validation(),
            'statusOptions' => ['Active', 'Inactive', 'On Hold'],
            'distributor'   => $distributor, // Pass existing distributor data to the form
        ];

        return view('distributors/add_form', $data); // Reuse the add_form
    }

    /**
     * Handles the submission of the edit distributor form.
     *
     * @param int $id The ID of the distributor being updated.
     */
    public function update(int $id): ResponseInterface
    {
        $postData = $this->request->getPost();
        log_message('debug', 'Distributor::update - Received Post Data for ID: ' . $id . ' -> ' . json_encode($postData));

        // Important: Add the ID to the postData so the model knows to update, not insert
        // The save() method of the model relies on the 'id' key being present to perform an update.
        $postData['id'] = $id;

        // --- CRUCIAL: Explicitly validate using the model's validate method ---
        if (!$this->distributorModel->validate($postData)) {
            $errors = $this->distributorModel->errors();
            log_message('error', 'Distributor::update - Validation failed. Errors: ' . json_encode($errors));
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // If validation passes, attempt to save
        // The save() method handles both insert and update based on the presence of 'id' in data
        if (!$this->distributorModel->save($postData)) {
            // This 'save' failing might indicate a database error after validation (e.g., integrity constraint)
            $dbErrors = $this->distributorModel->errors(); // Get any database-related errors if save() failed
            log_message('error', 'Distributor::update - Model save() failed after validation. Database errors: ' . json_encode($dbErrors));
            return redirect()->back()->withInput()->with('errors', $dbErrors);
        }

        log_message('debug', 'Distributor::update - Distributor ID ' . $id . ' updated successfully.');
        return redirect()->to(base_url('distributors'))->with('success', 'Distributor updated successfully!');
    }

    /**
     * Handles the deletion of a distributor.
     *
     * @param int $id The ID of the distributor to delete.
     */
    public function delete(int $id): ResponseInterface
    {
        $distributor = $this->distributorModel->find($id);

        if (!$distributor) {
            return redirect()->to(base_url('distributors'))->with('error', 'Distributor not found for deletion.');
        }

        if ($this->distributorModel->delete($id)) {
            log_message('debug', 'Distributor::delete - Distributor ID ' . $id . ' deleted successfully.');
            return redirect()->to(base_url('distributors'))->with('success', 'Distributor deleted successfully!');
        } else {
            $errors = $this->distributorModel->errors();
            log_message('error', 'Distributor::delete - Failed to delete distributor ID ' . $id . '. Errors: ' . json_encode($errors));
            return redirect()->to(base_url('distributors'))->with('error', 'Failed to delete distributor.');
        }
    }
}