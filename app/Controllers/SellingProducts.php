<?php

namespace App\Controllers;

use App\Controllers\BaseController;
// --- CHANGE START ---
// Use SellingProductModel instead of ProductModel
use App\Models\SellingProductModel;
// --- CHANGE END ---
use App\Models\UnitModel;
// --- REMOVED: StockInModel and StockInPaymentModel are not directly managed by SellingProductsController ---
// use App\Models\StockInModel;
// use App\Models\StockInPaymentModel;
// --- END REMOVED ---
use App\Models\StockOutModel; // Still needed for stock-out related to sales
use CodeIgniter\HTTP\ResponseInterface;

// --- CHANGE START ---
// Renamed class from Products to SellingProducts
class SellingProducts extends BaseController
// --- CHANGE END ---
{
    // --- CHANGE START ---
    // Renamed property from productModel to sellingProductModel
    protected $sellingProductModel;
    // --- END CHANGE ---
    protected $unitModel;
    // --- REMOVED: StockInModel and StockInPaymentModel are not directly managed by SellingProductsController ---
    // protected $stockInModel;
    // protected $stockInPaymentModel;
    // --- END REMOVED ---
    protected $stockOutModel;
    protected $db;
    protected $session;
    protected $validation;

    public function __construct()
    {
        // --- CHANGE START ---
        // Instantiate SellingProductModel
        $this->sellingProductModel = new SellingProductModel();
        // --- END CHANGE ---
        $this->unitModel = new UnitModel();
        // --- REMOVED: StockInModel and StockInPaymentModel are not directly managed by SellingProductsController ---
        // $this->stockInModel = new StockInModel();
        // $this->stockInPaymentModel = new StockInPaymentModel();
        // --- END REMOVED ---
        $this->stockOutModel = new StockOutModel();
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
    }

    /**
     * Displays a list of all selling products.
     */
    public function index(): string
    {
        // Fetch products with unit name using join
        // --- CHANGE START ---
        // Use sellingProductModel and join with 'selling_products' table
        $builder = $this->sellingProductModel->builder();
        $builder->select('selling_products.*, units.name as unit_name');
        $builder->join('units', 'units.id = selling_products.unit_id');
        $products = $builder->get()->getResultArray();
        // --- CHANGE END ---

        $data = [
            'title'    => 'Selling Products List',
            'products' => $products,
        ];
        return view('selling_products/index', $data); // Adjust view path if necessary
    }

    /**
     * Shows the form for creating a new selling product.
     */
    public function create(): string
    {
        $data = [
            'title'      => 'Add New Selling Product',
            'units'      => $this->unitModel->findAll(),
            'validation' => $this->validation,
        ];
        return view('selling_products/create', $data); // Adjust view path if necessary
    }

    /**
     * Stores a newly created selling product in the database.
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[selling_products.name]', // Table name updated
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'selling_price' => 'required|numeric|greater_than_equal_to[0]',
            'farmer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'current_stock' => 'required|numeric|greater_than_equal_to[0]', // Initial stock
            'notes'         => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'selling_price' => $this->request->getPost('selling_price'),
            'farmer_price'  => $this->request->getPost('farmer_price'),
            'current_stock' => $this->request->getPost('current_stock'),
            'notes'         => $this->request->getPost('notes'),
        ];

        try {
            // --- CHANGE START ---
            // Use sellingProductModel to save
            if ($this->sellingProductModel->save($data)) {
                return redirect()->to(base_url('selling-products'))->with('success', 'Selling Product added successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to add selling product.');
            }
            // --- CHANGE END ---
        } catch (\ReflectionException $e) {
            log_message('error', 'SellingProducts::store - ReflectionException: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Shows the form for editing an existing selling product.
     *
     * @param int|null $id
     */
    public function edit(int $id = null): ResponseInterface|string
    {
        if ($id === null) {
            return redirect()->to(base_url('selling-products'))->with('error', 'No product ID provided for edit.');
        }

        // --- CHANGE START ---
        // Use sellingProductModel to find the product
        $product = $this->sellingProductModel->find($id);
        // --- CHANGE END ---

        if (empty($product)) {
            return redirect()->to(base_url('selling-products'))->with('error', 'Selling Product not found.');
        }

        $data = [
            'title'      => 'Edit Selling Product',
            'product'    => $product,
            'units'      => $this->unitModel->findAll(),
            'validation' => $this->validation,
        ];
        return view('selling_products/edit', $data); // Adjust view path if necessary
    }

    /**
     * Updates an existing selling product in the database.
     *
     * @param int|null $id
     */
    public function update(int $id = null): ResponseInterface
    {
        if ($id === null) {
            return redirect()->to(base_url('selling-products'))->with('error', 'No product ID provided for update.');
        }

        // --- CHANGE START ---
        // Use sellingProductModel to find the product
        $product = $this->sellingProductModel->find($id);
        // --- END CHANGE ---

        if (empty($product)) {
            return redirect()->to(base_url('selling-products'))->with('error', 'Selling Product not found for update.');
        }

        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[selling_products.name,id,' . $id . ']', // Table name updated
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'selling_price' => 'required|numeric|greater_than_equal_to[0]',
            'farmer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'current_stock' => 'required|numeric|greater_than_equal_to[0]',
            'notes'         => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'            => $id,
            'name'          => $this->request->getPost('name'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'selling_price' => $this->request->getPost('selling_price'),
            'farmer_price'  => $this->request->getPost('farmer_price'),
            'current_stock' => $this->request->getPost('current_stock'),
            'notes'         => $this->request->getPost('notes'),
        ];

        try {
            // --- CHANGE START ---
            // Use sellingProductModel to save
            if ($this->sellingProductModel->save($data)) {
                return redirect()->to(base_url('selling-products'))->with('success', 'Selling Product updated successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to update selling product.');
            }
            // --- CHANGE END ---
        } catch (\ReflectionException $e) {
            log_message('error', 'SellingProducts::update - ReflectionException: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Deletes a selling product from the database.
     *
     * @param int|null $id
     */
    public function delete(int $id = null): ResponseInterface
    {
        if ($id === null) {
            return redirect()->to(base_url('selling-products'))->with('error', 'No product ID provided for deletion.');
        }

        // Check if the product is involved in any stock_out records
        // --- CHANGE START ---
        // Updated to use 'selling_product_id' assuming the foreign key name change in stock_out table
        $stockOutCount = $this->stockOutModel->where('selling_product_id', $id)->countAllResults();
        // --- CHANGE END ---
        if ($stockOutCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete this product as it is associated with existing stock out records. Please remove associated stock out records first.');
        }

        try {
            // --- CHANGE START ---
            // Use sellingProductModel to delete
            if ($this->sellingProductModel->delete($id)) {
                return redirect()->to(base_url('selling-products'))->with('success', 'Selling Product deleted successfully!');
            } else {
                // This else block might be hit if the ID doesn't exist or a DB error occurs
                $dbError = $this->db->error();
                $errorMessage = 'Failed to delete selling product. ' . ($dbError['message'] ?? 'Unknown database error.');
                log_message('error', 'SellingProducts::delete - ' . $errorMessage . ' Product ID: ' . $id);
                return redirect()->to(base_url('selling-products'))->with('error', $errorMessage);
            }
            // --- CHANGE END ---
        } catch (\Exception $e) {
            log_message('error', 'SellingProducts::delete - Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred during deletion: ' . $e->getMessage());
        }
    }

    /**
     * Displays an overview of available stock for selling products.
     */
    public function stockOverview(): string
    {
        // Fetch products with unit name and order by name
        // --- CHANGE START ---
        // Use sellingProductModel and join with 'selling_products' table
        $builder = $this->sellingProductModel->builder();
        $builder->select('selling_products.*, units.name as unit_name');
        $builder->join('units', 'units.id = selling_products.unit_id');
        $builder->orderBy('selling_products.name', 'ASC');
        $products = $builder->get()->getResultArray();
        // --- CHANGE END ---

        $data = [
            'title'    => 'Available Selling Stock Overview with Prices',
            'products' => $products,
        ];

        return view('selling_products/stock_overview', $data); // Adjust view path if necessary
    }

    /**
     * Returns the available stock for a product directly from the selling_products.current_stock column.
     *
     * @param int $productId The ID of the product.
     * @return ResponseInterface JSON response with available_stock and unit_name.
     */
    public function getAvailableStock(int $productId): ResponseInterface
    {
        // Fetch the product directly, including its current_stock and unit name
        // --- CHANGE START ---
        // Use sellingProductModel and select from 'selling_products' table
        $product = $this->sellingProductModel->select('selling_products.current_stock, selling_products.selling_price, selling_products.farmer_price, units.name as unit_name')
            ->join('units', 'units.id = selling_products.unit_id')
            ->find($productId);
        // --- CHANGE END ---

        $availableStock = $product['current_stock'] ?? 0;
        $unitName = $product['unit_name'] ?? 'units';
        $sellingPrice = $product['selling_price'] ?? 0;
        $farmerPrice = $product['farmer_price'] ?? 0;

        // Return as JSON
        return $this->response->setJSON([
            'available_stock' => $availableStock,
            'unit_name'       => $unitName,
            'selling_price'   => $sellingPrice,
            'farmer_price'    => $farmerPrice,
        ]);
    }
}
