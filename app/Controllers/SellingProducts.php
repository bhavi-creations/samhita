<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SellingProductModel;
use App\Models\UnitModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;
use Exception; // Added to enable error handling

class SellingProducts extends BaseController
{
    // Properties for dependency injection
    protected SellingProductModel $sellingProductModel;
    protected UnitModel $unitModel;
    
    public function __construct()
    {
        // Instantiate models for use in the controller
        $this->sellingProductModel = new SellingProductModel();
        $this->unitModel = new UnitModel();

        // Load the form and URL helpers
        helper(['form', 'url']);
    }

    /**
     * Displays a list of all selling products with their unit names.
     * This method now includes a try...catch block to handle database errors gracefully.
     */
    public function index(): string
    {
        $products = [];
        $errorMessage = '';

        try {
            // Fetch products with unit name using a LEFT join.
            // Using a LEFT join is more robust because it will still return products
            // even if their associated unit is missing, preventing a fatal error.
            $products = $this->sellingProductModel
                ->select('selling_products.*, units.name as unit_name')
                ->join('units', 'units.id = selling_products.unit_id', 'left') // Switched to a 'left' join
                ->findAll();
        } catch (Exception $e) {
            // Catch any database or other exceptions that might occur.
            $errorMessage = 'A database error occurred: ' . $e->getMessage() . '. Please check your table and column names.';
            log_message('error', $errorMessage);
        }

        $data = [
            'title' => 'Selling Products List',
            'products' => $products,
            'error_message' => $errorMessage // Pass the error message to the view
        ];

        return view('selling_products/index', $data);
    }

    /**
     * Shows the form for creating a new selling product.
     */
    public function create(): string
    {
        $data = [
            'title' => 'Add New Selling Product',
            'units' => $this->unitModel->findAll(),
            'validation' => \Config\Services::validation(),
        ];
        
        return view('selling_products/create', $data);
    }

    /**
     * Stores a newly created selling product in the database.
     */
    public function store(): ResponseInterface
    {
        // Get validation rules from the private method
        if (!$this->validate($this->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();

        try {
            if ($this->sellingProductModel->save($data)) {
                return redirect()->to(base_url('selling-products'))->with('success', 'Selling Product added successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to add selling product.');
            }
        } catch (ReflectionException $e) {
            log_message('error', 'SellingProducts::store - ReflectionException: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Shows the form for editing an existing selling product.
     *
     * @param int|null $id The ID of the product to edit.
     */
    public function edit(?int $id = null): ResponseInterface|string
    {
        // Ensure an ID is provided and the product exists.
        $product = $this->sellingProductModel->find($id);

        if (empty($product)) {
            return redirect()->to(base_url('selling-products'))->with('error', 'Selling Product not found.');
        }

        $data = [
            'title' => 'Edit Selling Product',
            'product' => $product,
            'units' => $this->unitModel->findAll(),
            'validation' => \Config\Services::validation(),
        ];

        return view('selling_products/edit', $data);
    }

    /**
     * Updates an existing selling product in the database.
     *
     * @param int|null $id The ID of the product to update.
     */
   
    public function update(?int $id = null): ResponseInterface
    {
        // First, check if the product exists.
        $product = $this->sellingProductModel->find($id);
        if (empty($product)) {
            return redirect()->to(base_url('selling-products'))->with('error', 'Selling Product not found for update.');
        }

        // Define the validation rules. We use an array for better readability.
        $rules = [
            'name'          => [
                'label'  => 'Product Name',
                // This is the corrected 'is_unique' rule. It tells the rule to
                // ignore the record with the current ID.
                'rules'  => 'required|is_unique[selling_products.name,id,' . $id . ']',
                'errors' => [
                    'is_unique' => 'The product name must be unique.'
                ]
            ],
            'description'   => 'permit_empty|max_length[255]',
            'unit_id'       => 'required|integer',
            'dealer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'farmer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'current_stock' => 'required|integer|greater_than_equal_to[0]',
        ];
        
        // This is a cleaner way to handle validation without a separate getValidationRules() method
        // If your getValidationRules() method exists and returns a similar array, you can
        // continue to use it and modify the 'name' rule as shown above.

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['id'] = $id;

        try {
            if ($this->sellingProductModel->save($data)) {
                return redirect()->to(base_url('selling-products'))->with('success', 'Selling Product updated successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to update selling product.');
            }
        } catch (ReflectionException $e) {
            log_message('error', 'SellingProducts::update - ReflectionException: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Soft deletes a selling product.
     *
     * @param int|null $id The ID of the product to delete.
     */
    public function delete(?int $id = null): ResponseInterface
    {
        if ($id === null) {
            return redirect()->to(base_url('selling-products'))->with('error', 'No product ID provided for deletion.');
        }

        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Invalid request method. Deletion requires a POST.');
        }

        $deleted = $this->sellingProductModel->delete($id);

        if ($deleted) {
            return redirect()->to(base_url('selling-products'))->with('success', 'Product has been soft deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Could not delete the product. It may have already been deleted or does not exist.');
        }
    }

    /**
     * Displays an overview of all available stock.
     */
    public function stockOverview(): string
    {
        $products = $this->sellingProductModel
            ->select('selling_products.*, units.name as unit_name')
            ->join('units', 'units.id = selling_products.unit_id')
            ->orderBy('selling_products.name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Available Selling Stock Overview with Prices',
            'products' => $products,
        ];

        return view('selling_products/stock_overview', $data);
    }

    /**
     * Returns the available stock for a product as a JSON response.
     *
     * @param int $productId The ID of the product.
     */
    public function getAvailableStock(int $productId): ResponseInterface
    {
        $product = $this->sellingProductModel
            ->select('selling_products.current_stock, selling_products.dealer_price, selling_products.farmer_price, units.name as unit_name')
            ->join('units', 'units.id = selling_products.unit_id')
            ->find($productId);

        $availableStock = $product['current_stock'] ?? 0;
        $unitName = $product['unit_name'] ?? 'units';
        $dealerPrice = $product['dealer_price'] ?? 0;
        $farmerPrice = $product['farmer_price'] ?? 0;

        return $this->response->setJSON([
            'available_stock' => $availableStock,
            'unit_name' => $unitName,
            'dealer_price' => $dealerPrice,
            'farmer_price' => $farmerPrice,
        ]);
    }
    
    /**
     * Defines the validation rules for product data.
     *
     * @return array The validation rules.
     */
    private function getValidationRules(): array
    {
        return [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[selling_products.name]',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'dealer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'farmer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'current_stock' => 'required|numeric|greater_than_equal_to[0]',
            'description'   => 'permit_empty|max_length[1000]',
        ];
    }
}
