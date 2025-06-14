<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\UnitModel;
use App\Models\StockInModel;
use App\Models\StockOutModel;
use CodeIgniter\HTTP\ResponseInterface; // Added for type hinting on new methods

class Products extends BaseController
{
    protected $productModel;
    protected $unitModel;
    protected $stockInModel;
    protected $stockOutModel;
    protected $db;
    protected $session; // Declare session property
    protected $validation; // Declare validation property

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->unitModel = new UnitModel();
        $this->stockInModel = new StockInModel();
        $this->stockOutModel = new StockOutModel();
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session(); // Initialize session service
        $this->validation = \Config\Services::validation(); // Initialize validation service
        helper(['form', 'url']); // Load form and URL helpers
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
        // Pass all units for dropdown
        $units = $this->unitModel->findAll();
        // Pass validation service for form errors
        $data['validation'] = \Config\Services::validation();
        // Pass units to the view
        $data['units'] = $units;
        return view('products/create', $data);
    }

    public function store()
    {
        // Basic validation for product creation
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[products.name]',
            'description'   => 'permit_empty|string',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'selling_price' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal[10,2]', // Added rule for selling_price
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'selling_price' => $this->request->getPost('selling_price'), // Include selling_price from the form
            // 'default_selling_price' => $this->request->getPost('default_selling_price'), // Uncomment if added to form
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
        // Pass validation service for form errors
        $data['validation'] = \Config\Services::validation();
        $data['product'] = $product;
        $data['units'] = $units;
        return view('products/edit', $data);
    }

    public function update($id)
    {
        // Basic validation for product update
        $rules = [
            'name'          => 'required|min_length[3]|max_length[255]|is_unique[products.name,id,' . $id . ']', // Allow same name for self-update
            'description'   => 'permit_empty|string',
            'unit_id'       => 'required|integer|is_not_unique[units.id]',
            'selling_price' => 'permit_empty|numeric|greater_than_equal_to[0]|decimal[10,2]', // Added rule for selling_price
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'unit_id'       => $this->request->getPost('unit_id'),
            'selling_price' => $this->request->getPost('selling_price'), // Include selling_price from the form
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
            'id' => $id, // Important: Include ID for update operation
            'selling_price' => $this->request->getPost('selling_price'),
            // No need to manually set 'updated_at' here, the model handles it via useTimestamps
        ];

        if ($this->productModel->save($data)) {
            return redirect()->to(base_url('products/manage-prices'))->with('success', 'Product selling price updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update product selling price.');
        }
    }

    // --- Available Stock Overview with Prices Module (NEW) ---

    /**
     * Display an overview of all products showing available stock and selling price.
     */
    public function stockOverview(): string
    {
        // 1. Fetch all products along with their unit names
        $builder = $this->productModel->builder();
        $builder->select('products.id, products.name, products.selling_price, units.name as unit_name');
        $builder->join('units', 'units.id = products.unit_id');
        $products = $builder->get()->getResultArray();

        // 2. Fetch total stock-in for all products efficiently
        // Using getResultArray() to fetch all at once then map
        $stockIns = $this->db->table('stock_in')
                             ->select('product_id, SUM(quantity) as total_in')
                             ->groupBy('product_id')
                             ->get()
                             ->getResultArray();

        // 3. Fetch total stock-out for all products efficiently
        // Using getResultArray() to fetch all at once then map
        $stockOuts = $this->db->table('stock_out')
                              ->select('product_id, SUM(quantity_out) as total_out')
                              ->groupBy('product_id')
                              ->get()
                              ->getResultArray();

        // 4. Map stock data by product_id for efficient lookup
        $stockInMap = array_column($stockIns, 'total_in', 'product_id');
        $stockOutMap = array_column($stockOuts, 'total_out', 'product_id');

        // 5. Calculate available stock for each product and add to the product array
        foreach ($products as &$product) { // Using & to modify the array elements directly
            $productId = $product['id'];
            $totalIn = $stockInMap[$productId] ?? 0;
            $totalOut = $stockOutMap[$productId] ?? 0;
            $product['available_stock'] = $totalIn - $totalOut;
        }
        unset($product); // Break the reference to the last element

        $data = [
            'title'    => 'Available Stock Overview with Prices',
            'products' => $products,
        ];

        return view('products/stock_overview', $data);
    }

    // --- API Endpoint to get Available Stock for a Product (Existing) ---
    public function getAvailableStock(int $productId)
    {
        // Calculate available stock using stock_in and stock_out tables
        $stockIn = $this->db->table('stock_in')
            ->selectSum('quantity')
            ->where('product_id', $productId)
            ->get()
            ->getRow()->quantity ?? 0;

        $stockOut = $this->db->table('stock_out')
            ->selectSum('quantity_out')
            ->where('product_id', $productId)
            ->get()
            ->getRow()->quantity_out ?? 0;

        $availableStock = $stockIn - $stockOut;

        // Fetch the unit name for the product
        $product = $this->productModel->select('units.name as unit_name')
            ->join('units', 'units.id = products.unit_id')
            ->find($productId);

        $unitName = $product['unit_name'] ?? 'units'; // Default to 'units' if not found

        // Return as JSON
        return $this->response->setJSON([
            'available_stock' => $availableStock,
            'unit_name'       => $unitName
        ]);
    }
}