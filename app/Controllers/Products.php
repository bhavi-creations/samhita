<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\UnitModel;
use App\Models\StockInModel;
use App\Models\StockOutModel;
use App\Models\StockInPaymentModel; // Added initialization if used for stockIn store
use CodeIgniter\HTTP\ResponseInterface;

class Products extends BaseController
{
    protected $productModel;
    protected $unitModel;
    protected $stockInModel;
    protected $stockOutModel;
    protected $db;
    protected $session;
    protected $validation;
    protected $stockInPaymentModel; // Declared for use in constructor if applicable

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->unitModel = new UnitModel();
        $this->stockInModel = new StockInModel();
        $this->stockOutModel = new StockOutModel();
        $this->stockInPaymentModel = new StockInPaymentModel(); // Initialize if you use it, otherwise remove
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Fetch products with unit name using join
        $builder = $this->productModel->builder();
        $builder->select('products.*, units.name as unit_name');
        $builder->join('units', 'units.id = products.unit_id');
        $products = $builder->get()->getResultArray();

        return view('products/index', ['products' => $products]);
    }

    public function create()
    {
        $units = $this->unitModel->findAll();
        $data['validation'] = \Config\Services::validation();
        $data['units'] = $units;
        return view('products/create', $data);
    }

    public function store()
    {
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[products.name]',
            'description'   => 'permit_empty|string',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'selling_price' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal[10,2]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'selling_price' => $this->request->getPost('selling_price'),
        ];

        $this->productModel->save($data);

        return redirect()->to('/products')->with('success', 'Product added successfully.');
    }

    public function edit($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }
        $units = $this->unitModel->findAll();
        $data['validation'] = \Config\Services::validation();
        $data['product'] = $product;
        $data['units'] = $units;
        return view('products/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[products.name,id,' . $id . ']',
            'description'   => 'permit_empty|string',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'selling_price' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal[10,2]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'selling_price' => $this->request->getPost('selling_price'),
        ];

        $this->productModel->update($id, $data);

        return redirect()->to('/products')->with('success', 'Product updated successfully.');
    }

    public function delete($id)
    {
        try {
            $this->productModel->delete($id);
            return redirect()->to('/products')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->to('/products')->with('error', 'Failed to delete product. It might be linked to existing stock records.');
        }
    }

    // --- Product Price Management Methods ---

    /**
     * Display a list of products with their current selling prices.
     */
    public function managePrices(): string
    {
        $products = $this->productModel->findAll(); // Fetches all products

        $data = [
            'title'    => 'Manage Product Selling Prices',
            'products' => $products,
        ];

        return view('products/manage_prices', $data);
    }

    /**
     * Display a form to edit the selling price of a specific product.
     *
     * @param int|null $id Product ID
     */
    public function editPrice(int $id = null): ResponseInterface|string
    {
        if ($id === null) {
            return redirect()->to(base_url('products/manage-prices'))->with('error', 'No product ID provided.');
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to(base_url('products/manage-prices'))->with('error', 'Product not found.');
        }

        $data = [
            'title'      => 'Edit Product Selling Price',
            'product'    => $product,
            'validation' => $this->validation,
        ];

        return view('products/edit_price', $data);
    }

    /**
     * Handle the form submission to update the selling price.
     *
     * @param int|null $id Product ID
     */
    public function updatePrice(int $id = null): ResponseInterface
    {
        if ($id === null) {
            return redirect()->to(base_url('products/manage-prices'))->with('error', 'No product ID provided for update.');
        }

        $rules = [
            'selling_price' => 'required|numeric|greater_than_equal_to[0]|decimal[10,2]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'selling_price' => $this->request->getPost('selling_price'),
        ];

        if ($this->productModel->save($data)) {
            return redirect()->to(base_url('products/manage-prices'))->with('success', 'Product selling price updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update product selling price.');
        }
    }

    // --- Available Stock Overview with Prices Module (MODIFIED TO READ FROM current_stock) ---

    /**
     * Display an overview of all products showing available stock and selling price.
     * This method now reads 'current_stock' directly from the products table.
     */
    public function stockOverview(): string
    {
        // Fetch all products along with their unit names and the current_stock
        $builder = $this->productModel->builder();
        // SELECT products.id, products.name, products.selling_price, products.current_stock AS available_stock, units.name AS unit_name
        $builder->select('products.id, products.name, products.selling_price, products.current_stock as available_stock, units.name as unit_name');
        $builder->join('units', 'units.id = products.unit_id');
        $products = $builder->get()->getResultArray();

        // Removed: Logic for summing stock_in and stock_out tables, as current_stock is now the source of truth.

        $data = [
            'title'    => 'Available Stock Overview with Prices',
            'products' => $products,
        ];

        return view('products/stock_overview', $data);
    }

    // --- API Endpoint to get Available Stock for a Product (MODIFIED TO READ FROM current_stock) ---
    /**
     * Returns the available stock for a product directly from the products.current_stock column.
     *
     * @param int $productId The ID of the product.
     * @return ResponseInterface JSON response with available_stock and unit_name.
     */
    public function getAvailableStock(int $productId): ResponseInterface
    {
        // Fetch the product directly, including its current_stock and unit name
        $product = $this->productModel->select('products.current_stock, units.name as unit_name')
            ->join('units', 'units.id = products.unit_id')
            ->find($productId);

        $availableStock = $product['current_stock'] ?? 0;
        $unitName = $product['unit_name'] ?? 'units';

        // Return as JSON
        return $this->response->setJSON([
            'available_stock' => $availableStock,
            'unit_name'       => $unitName
        ]);
    }
}
