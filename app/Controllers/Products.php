<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\UnitModel;

class Products extends BaseController
{
    protected $productModel;
    protected $unitModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->unitModel = new UnitModel();
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
        return view('products/create', ['units' => $units]);
    }

    public function store()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'unit_id' => $this->request->getPost('unit_id')
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
        return view('products/edit', ['product' => $product, 'units' => $units]);
    }

    public function update($id)
    {
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
        $this->productModel->delete($id);
        return redirect()->to('/products')->with('success', 'Product deleted successfully.');
    }
}
