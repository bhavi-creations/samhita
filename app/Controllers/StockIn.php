<?php

namespace App\Controllers;

use App\Models\StockInModel;
use App\Models\ProductModel;

class StockIn extends BaseController
{
    protected $stockInModel;
    protected $productModel;

    public function __construct()
    {
        $this->stockInModel = new StockInModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $builder = $this->stockInModel->builder();
        $builder->select('stock_in.*, products.name as product_name, units.name as unit_name');
        $builder->join('products', 'products.id = stock_in.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $data['stock_entries'] = $builder->get()->getResultArray();
        return view('stock_in/index', $data);
    }

    public function create()
    {
        $products = $this->productModel->findAll();
        return view('stock_in/create', ['products' => $products]);
    }

    public function store()
    {
        $this->stockInModel->save([
            'product_id'    => $this->request->getPost('product_id'),
            'quantity'      => $this->request->getPost('quantity'),
            'date_received' => $this->request->getPost('date_received'),
            'notes'         => $this->request->getPost('notes'),
        ]);

        return redirect()->to('/stock-in')->with('success', 'Stock added successfully.');
    }
}
