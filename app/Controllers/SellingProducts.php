<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SellingProductModel;
use App\Models\UnitModel;
use App\Models\StockOutModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class SellingProducts extends BaseController
{
    protected $sellingProductModel;
    protected $unitModel;
    protected $stockOutModel;
    protected $db;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->sellingProductModel = new SellingProductModel();
        $this->unitModel = new UnitModel();
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
        $builder = $this->sellingProductModel->builder();
        $builder->select('selling_products.*, units.name as unit_name');
        $builder->join('units', 'units.id = selling_products.unit_id');
        $products = $builder->get()->getResultArray();

        $data = [
            'title'    => 'Selling Products List',
            'products' => $products,
        ];
        return view('selling_products/index', $data);
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
        return view('selling_products/create', $data);
    }

    /**
     * Stores a newly created selling product in the database.
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[selling_products.name]',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'dealer_price'  => 'required|numeric|greater_than_equal_to[0]', // Corrected field name
            'farmer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'current_stock' => 'required|numeric|greater_than_equal_to[0]',
            'description'   => 'permit_empty|max_length[1000]', // Changed 'notes' to 'description' based on DB schema
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'dealer_price'  => $this->request->getPost('dealer_price'), // Corrected field name
            'farmer_price'  => $this->request->getPost('farmer_price'),
            'current_stock' => $this->request->getPost('current_stock'),
            'description'   => $this->request->getPost('description'), // Changed 'notes' to 'description'
        ];

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
     * @param int|null $id
     */
    public function edit(int $id = null): ResponseInterface|string
    {
        if ($id === null) {
            return redirect()->to(base_url('selling-products'))->with('error', 'No product ID provided for edit.');
        }

        $product = $this->sellingProductModel->find($id);

        if (empty($product)) {
            return redirect()->to(base_url('selling-products'))->with('error', 'Selling Product not found.');
        }

        $data = [
            'title'      => 'Edit Selling Product',
            'product'    => $product,
            'units'      => $this->unitModel->findAll(),
            'validation' => $this->validation,
        ];
        return view('selling_products/edit', $data);
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

        $product = $this->sellingProductModel->find($id);

        if (empty($product)) {
            return redirect()->to(base_url('selling-products'))->with('error', 'Selling Product not found for update.');
        }

        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[selling_products.name,id,' . $id . ']',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'dealer_price'  => 'required|numeric|greater_than_equal_to[0]', // Corrected field name
            'farmer_price'  => 'required|numeric|greater_than_equal_to[0]',
            'current_stock' => 'required|numeric|greater_than_equal_to[0]',
            'description'   => 'permit_empty|max_length[1000]', // Changed 'notes' to 'description' based on DB schema
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'            => $id,
            'name'          => $this->request->getPost('name'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'dealer_price'  => $this->request->getPost('dealer_price'), // Corrected field name
            'farmer_price'  => $this->request->getPost('farmer_price'),
            'current_stock' => $this->request->getPost('current_stock'),
            'description'   => $this->request->getPost('description'), // Changed 'notes' to 'description'
        ];

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
        $stockOutCount = $this->stockOutModel->where('selling_product_id', $id)->countAllResults();
        if ($stockOutCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete this product as it is associated with existing stock out records. Please remove associated stock out records first.');
        }

        try {
            if ($this->sellingProductModel->delete($id)) {
                return redirect()->to(base_url('selling-products'))->with('success', 'Selling Product deleted successfully!');
            } else {
                $dbError = $this->db->error();
                $errorMessage = 'Failed to delete selling product. ' . ($dbError['message'] ?? 'Unknown database error.');
                log_message('error', 'SellingProducts::delete - ' . $errorMessage . ' Product ID: ' . $id);
                return redirect()->to(base_url('selling-products'))->with('error', $errorMessage);
            }
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
        $builder = $this->sellingProductModel->builder();
        $builder->select('selling_products.*, units.name as unit_name');
        $builder->join('units', 'units.id = selling_products.unit_id');
        $builder->orderBy('selling_products.name', 'ASC');
        $products = $builder->get()->getResultArray();

        $data = [
            'title'    => 'Available Selling Stock Overview with Prices',
            'products' => $products,
        ];

        return view('selling_products/stock_overview', $data);
    }

    /**
     * Returns the available stock for a product directly from the selling_products.current_stock column.
     *
     * @param int $productId The ID of the product.
     * @return ResponseInterface JSON response with available_stock and unit_name.
     */
    public function getAvailableStock(int $productId): ResponseInterface
    {
        $product = $this->sellingProductModel->select('selling_products.current_stock, selling_products.dealer_price, selling_products.farmer_price, units.name as unit_name')
            ->join('units', 'units.id = selling_products.unit_id')
            ->find($productId);

        $availableStock = $product['current_stock'] ?? 0;
        $unitName = $product['unit_name'] ?? 'units';
        $dealerPrice = $product['dealer_price'] ?? 0; // Corrected field name
        $farmerPrice = $product['farmer_price'] ?? 0;

        return $this->response->setJSON([
            'available_stock' => $availableStock,
            'unit_name'       => $unitName,
            'dealer_price'    => $dealerPrice, // Corrected field name
            'farmer_price'    => $farmerPrice,
        ]);
    }
}
