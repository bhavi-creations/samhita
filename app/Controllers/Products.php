<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\UnitModel;
use App\Models\StockInModel;  // <--- NEW: Import StockInModel
use App\Models\StockOutModel; // <--- NEW: Import StockOutModel

class Products extends BaseController
{
    protected $productModel;
    protected $unitModel;
    protected $stockInModel;   // <--- NEW: Declare StockInModel
    protected $stockOutModel;  // <--- NEW: Declare StockOutModel
    protected $db;             // <--- NEW: Declare DB connection for calculations

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->unitModel = new UnitModel();
        $this->stockInModel = new StockInModel();   // <--- NEW: Initialize StockInModel
        $this->stockOutModel = new StockOutModel();  // <--- NEW: Initialize StockOutModel
        $this->db = \Config\Database::connect();     // <--- NEW: Initialize DB connection
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
        return view('products/create', ['units' => $units]);
    }

    public function store()
    {
        // Basic validation for product creation
        $rules = [
            'name'        => 'required|min_length[3]|max_length[255]|is_unique[products.name]',
            'description' => 'permit_empty|string',
            'unit_id'     => 'required|integer|is_not_unique[units.id]',
            // Add other rules as needed, e.g., default_selling_price if it's required in form
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'unit_id' => $this->request->getPost('unit_id'),
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
        return view('products/edit', ['product' => $product, 'units' => $units]);
    }

    public function update($id)
    {
        // Basic validation for product update
        $rules = [
            'name'        => 'required|min_length[3]|max_length[255]|is_unique[products.name,id,' . $id . ']', // Allow same name for self-update
            'description' => 'permit_empty|string',
            'unit_id'     => 'required|integer|is_not_unique[units.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'unit_id' => $this->request->getPost('unit_id')
        ];

        $this->productModel->update($id, $data);

        return redirect()->to('/products')->with('success', 'Product updated successfully.');
    }

    public function delete($id)
    {
        // Consider checking for related stock_in/stock_out records before deleting a product
        // or ensure your foreign key constraints handle this (e.g., ON DELETE CASCADE)

        try {
            $this->productModel->delete($id);
            return redirect()->to('/products')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            // Catch database exceptions if product is linked to other tables with RESTRICT delete
            return redirect()->to('/products')->with('error', 'Failed to delete product. It might be linked to existing stock records.');
        }
    }

    // --- NEW: API Endpoint to get Available Stock for a Product ---
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