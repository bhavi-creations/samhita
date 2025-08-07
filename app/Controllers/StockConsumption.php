<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PurchasedProductModel; 
use App\Models\StockConsumptionModel;
use CodeIgniter\HTTP\ResponseInterface;

class StockConsumption extends BaseController
{
    /**
     * Loads the main stock consumption view with all required data.
     */
    public function index()
    {
        $purchasedProductModel = new PurchasedProductModel();
        $stockConsumptionModel = new StockConsumptionModel();

        $data = [
            'title' => 'Stock Consumption Records',
            'availablePurchasedStocks' => $purchasedProductModel->getProductsWithUnitDetails(),
            'consumptionEntries' => $stockConsumptionModel->getConsumptionEntriesWithDetails(),
        ];

        return view('stock_consumption/index', $data);
    }
    
    /**
     * Shows the form for adding a new consumption record.
     */
    public function create()
    {
        $purchasedProductModel = new PurchasedProductModel();
        
        $data = [
            'title' => 'Add New Stock Consumption',
            // Fetch all purchased products with their calculated available stock
            'products' => $purchasedProductModel->getProductsWithUnitDetails(),
            'validation' => \Config\Services::validation(),
        ];
        
        return view('stock_consumption/create', $data);
    }

    /**
     * Stores a new consumption record.
     */
    public function store()
    {
        // Define validation rules for the form
        $rules = [
            'product_id'        => 'required|integer',
            'quantity_consumed' => 'required|numeric|greater_than[0]',
            'date_consumed'     => 'required|valid_date',
            'used_by'           => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            // If validation fails, redirect back with input and errors
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $purchasedProductModel = new PurchasedProductModel();
        $stockConsumptionModel = new StockConsumptionModel();

        // Get all available stock data
        $availableStocks = $purchasedProductModel->getProductsWithUnitDetails();
        $product_id = $this->request->getPost('product_id');
        $availableStockData = null;
        
        // Find the specific product's stock data from the array
        foreach ($availableStocks as $stock) {
            if ($stock['id'] == $product_id) {
                $availableStockData = $stock;
                break;
            }
        }

        // Check if there is enough stock or if the product was not found.
        if (is_null($availableStockData) || $this->request->getPost('quantity_consumed') > $availableStockData['available_stock']) {
            return redirect()->back()->withInput()->with('error', 'Not enough stock available for this product.');
        }

        // Prepare the data for the new consumption record
        $data = [
            'product_id'        => $this->request->getPost('product_id'),
            'quantity_consumed' => $this->request->getPost('quantity_consumed'),
            'date_consumed'     => $this->request->getPost('date_consumed'),
            'used_by'           => $this->request->getPost('used_by'),
        ];
        
        // Save the new record
        $stockConsumptionModel->save($data);

        return redirect()->to(base_url('stock-consumption'))->with('success', 'Stock consumption record added successfully.');
    }

    /**
     * Shows the form for editing an existing consumption record.
     */
    public function edit(int $id)
    {
        $stockConsumptionModel = new StockConsumptionModel();
        $purchasedProductModel = new PurchasedProductModel();

        $data = [
            'title' => 'Edit Stock Consumption',
            'entry' => $stockConsumptionModel->find($id),
            // Fetch all purchased products with their calculated available stock
            'products' => $purchasedProductModel->getProductsWithUnitDetails(),
            'validation' => \Config\Services::validation(),
        ];
        
        // Handle case where entry is not found
        if (empty($data['entry'])) {
            return redirect()->to(base_url('stock-consumption'))->with('error', 'Consumption record not found.');
        }

        return view('stock_consumption/edit', $data);
    }

    /**
     * Updates an existing consumption record.
     */
    public function update(int $id)
    {
        // Define validation rules for the form
        $rules = [
            'product_id'        => 'required|integer',
            'quantity_consumed' => 'required|numeric|greater_than[0]',
            'date_consumed'     => 'required|valid_date',
            'used_by'           => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            // If validation fails, redirect back with input and errors
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $stockConsumptionModel = new StockConsumptionModel();
        
        // Prepare the data for the update
        $data = [
            'id'                => $id,
            'product_id'        => $this->request->getPost('product_id'),
            'quantity_consumed' => $this->request->getPost('quantity_consumed'),
            'date_consumed'     => $this->request->getPost('date_consumed'),
            'used_by'           => $this->request->getPost('used_by'),
        ];

        // Save the updated record
        $stockConsumptionModel->save($data);

        return redirect()->to(base_url('stock-consumption'))->with('success', 'Stock consumption record updated successfully.');
    }

    /**
     * Deletes a consumption record.
     */
    public function delete(int $id): ResponseInterface
    {
        $stockConsumptionModel = new StockConsumptionModel();
        
        // Delete the record
        $stockConsumptionModel->delete($id);

        return redirect()->to(base_url('stock-consumption'))->with('success', 'Stock consumption record deleted successfully.');
    }
}
