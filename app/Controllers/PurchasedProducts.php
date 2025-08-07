<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PurchasedProductModel;
use App\Models\UnitModel;
use CodeIgniter\HTTP\ResponseInterface;

class PurchasedProducts extends BaseController
{
    protected $purchasedProductModel;
    protected $unitModel;
    protected $validation;

    public function __construct()
    {
        $this->purchasedProductModel = new PurchasedProductModel();
        $this->unitModel = new UnitModel();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
    }

    /**
     * Display a list of all purchased products.
     */
    public function index(): string
    {
        // Fetch purchased products with unit name using join
        $builder = $this->purchasedProductModel->builder();
        $builder->select('purchased_products.*, units.name as unit_name');
        $builder->join('units', 'units.id = purchased_products.unit_id');
        $purchasedProducts = $builder->get()->getResultArray();

        $data = [
            'title'             => 'Manage Purchased Products',
            'purchasedProducts' => $purchasedProducts,
        ];
        return view('purchased_products/index', $data);
    }

    /**
     * Show the form for creating a new purchased product.
     */
    public function create(): string
    {
        $units = $this->unitModel->findAll();
        $data = [
            'title'      => 'Add New Purchased Product',
            'units'      => $units,
            'validation' => $this->validation,
        ];
        return view('purchased_products/create', $data);
    }

    /**
     * Store a newly created purchased product in the database.
     *
     * @return ResponseInterface
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[purchased_products.name]',
            'description'   => 'permit_empty|string|max_length[1000]',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'unit_id'       => $this->request->getPost('unit_id'),
            
        ];

        if ($this->purchasedProductModel->save($data)) {
            return redirect()->to('/purchased-products')->with('success', 'Purchased Product added successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to add Purchased Product.');
        }
    }

    /**
     * Show the form for editing the specified purchased product.
     *
     * @param int|null $id
     * @return ResponseInterface|string
     */
    public function edit(int $id = null): ResponseInterface|string
    {
        if ($id === null) {
            return redirect()->to(base_url('purchased-products'))->with('error', 'No Purchased Product ID provided.');
        }

        $purchasedProduct = $this->purchasedProductModel->find($id);

        if (!$purchasedProduct) {
            return redirect()->to(base_url('purchased-products'))->with('error', 'Purchased Product not found.');
        }

        $units = $this->unitModel->findAll();
        $data = [
            'title'            => 'Edit Purchased Product',
            'purchasedProduct' => $purchasedProduct,
            'units'            => $units,
            'validation'       => $this->validation,
        ];
        return view('purchased_products/edit', $data);
    }

    /**
     * Update the specified purchased product in the database.
     *
     * @param int|null $id
     * @return ResponseInterface
     */
    public function update(int $id = null): ResponseInterface
    {
        log_message('debug', 'PurchasedProducts::update - Method started.');
        log_message('debug', 'PurchasedProducts::update - Received ID: ' . var_export($id, true));

        if ($id === null) {
            log_message('error', 'PurchasedProducts::update - ID is null from URL segment. Redirecting.');
            return redirect()->to(base_url('purchased-products'))->with('error', 'No Purchased Product ID provided for update.');
        }

        $purchasedProduct = $this->purchasedProductModel->find($id);
        log_message('debug', 'PurchasedProducts::update - Fetched purchased product: ' . var_export($purchasedProduct, true));

        if (!$purchasedProduct) {
            log_message('error', 'PurchasedProducts::update - Purchased Product with ID ' . $id . ' not found. Redirecting.');
            return redirect()->to(base_url('purchased-products'))->with('error', 'Purchased Product not found.');
        }

        $newName = $this->request->getPost('name');
        log_message('debug', 'PurchasedProducts::update - New name from POST: ' . var_export($newName, true));

        // Define base validation rules, EXCLUDING 'is_unique' for name here
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]', // Basic rules for name
            'description'   => 'permit_empty|string|max_length[1000]',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
        ];
        log_message('debug', 'PurchasedProducts::update - Defined validation rules (excluding is_unique for name): ' . var_export($rules, true));


        // Prepare data for validation.
        // The 'id' is not a POST field, so it's not included in $validationData for validation.
        $validationData = [
            'name'        => $newName,
            'description' => $this->request->getPost('description'),
            'unit_id'     => $this->request->getPost('unit_id'),
        ];
        log_message('debug', 'PurchasedProducts::update - Validation data prepared: ' . var_export($validationData, true));


        // --- Manual Uniqueness Check for Name ---
        log_message('debug', 'PurchasedProducts::update - Starting manual uniqueness check.');
        // Only perform this check if the name has actually changed
        if ($newName !== $purchasedProduct['name']) {
            log_message('debug', 'PurchasedProducts::update - Name has changed from "' . $purchasedProduct['name'] . '" to "' . $newName . '". Performing uniqueness query.');

            // Check if the new name already exists for another product
            $existingProductWithName = $this->purchasedProductModel
                                            ->where('name', $newName)
                                            ->where('id !=', $id) // Exclude the current product being updated
                                            ->first();

            log_message('debug', 'PurchasedProducts::update - Result of uniqueness query: ' . var_export($existingProductWithName, true));

            if ($existingProductWithName) {
                // If a product with the new name exists and it's not the current one,
                // then the name is not unique.
                log_message('error', 'PurchasedProducts::update - Manual uniqueness check failed: Name already exists for another product.');
                return redirect()->back()->withInput()->with('errors', ['name' => 'The entered product name already exists.']);
            }
        } else {
            log_message('debug', 'PurchasedProducts::update - Name has not changed. Skipping uniqueness check.');
        }

        // Explicitly set the rules on the validator instance
        log_message('debug', 'PurchasedProducts::update - Setting validation rules on validator instance.');
        $this->validation->setRules($rules);

        // Run validation using the explicitly provided data
        log_message('debug', 'PurchasedProducts::update - Running validation with prepared data.');
        if (!$this->validation->run($validationData)) {
            log_message('error', 'PurchasedProducts::update - Validation failed. Errors: ' . json_encode($this->validation->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validation->getErrors());
        }

        // If validation passes (including manual uniqueness check), proceed with saving the data
        $data = [
            'id'            => $id, // Ensure ID is included for the model's update operation
            'name'          => $newName,
            'description'   => $this->request->getPost('description'),
            'unit_id'       => $this->request->getPost('unit_id'),
        ];
        log_message('debug', 'PurchasedProducts::update - Data prepared for saving: ' . var_export($data, true));


        log_message('debug', 'PurchasedProducts::update - Attempting to save data via model.');
        if ($this->purchasedProductModel->save($data)) {
            log_message('info', 'PurchasedProducts::update - Purchased Product updated successfully. Redirecting.');
            return redirect()->to(base_url('purchased-products'))->with('success', 'Purchased Product updated successfully.');
        } else {
            log_message('error', 'PurchasedProducts::update - Failed to save Purchased Product. Model errors: ' . json_encode($this->purchasedProductModel->errors()));
            return redirect()->back()->withInput()->with('error', 'Failed to update Purchased Product.');
        }
    }

    /**
     * Delete the specified purchased product from the database.
     *
     * @param int|null $id
     * @return ResponseInterface
     */
    public function delete(int $id = null): ResponseInterface
    {
        if ($id === null) {
            return redirect()->to(base_url('purchased-products'))->with('error', 'No Purchased Product ID provided for deletion.');
        }

        try {
            if ($this->purchasedProductModel->delete($id)) {
                return redirect()->to(base_url('purchased-products'))->with('success', 'Purchased Product deleted successfully.');
            } else {
                return redirect()->to(base_url('purchased-products'))->with('error', 'Failed to delete Purchased Product or it does not exist.');
            }
        } catch (\Exception $e) {
            log_message('error', 'PurchasedProducts::delete - Exception: ' . $e->getMessage());
            return redirect()->to(base_url('purchased-products'))->with('error', 'Failed to delete Purchased Product. It might be linked to existing stock-in records or other transactions.');
        }
    }

    /**
     * API Endpoint to get Available Stock for a Purchased Product.
     *
     * @param int $purchasedProductId The ID of the purchased product.
     * @return ResponseInterface JSON response with current_stock and unit_name.
     */
    public function getAvailableStock(int $purchasedProductId): ResponseInterface
    {
        $product = $this->purchasedProductModel->select('purchased_products.current_stock, units.name as unit_name')
            ->join('units', 'units.id = purchased_products.unit_id')
            ->find($purchasedProductId);

        $availableStock = $product['current_stock'] ?? 0;   
        $unitName = $product['unit_name'] ?? 'units';

        return $this->response->setJSON([
            'available_stock' => $availableStock,
            'unit_name'       => $unitName
        ]);
    }
}
