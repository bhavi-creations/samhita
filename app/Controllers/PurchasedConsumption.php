<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PurchasedProductModel;
use App\Models\StockConsumptionModel;
use App\Models\UnitModel;
use CodeIgniter\HTTP\ResponseInterface;

class PurchasedConsumption extends BaseController
{
    protected $purchasedProductModel;
    protected $stockConsumptionModel;
    protected $unitModel;
    protected $validation;

    public function __construct()
    {
        $this->purchasedProductModel = new PurchasedProductModel();
        $this->stockConsumptionModel = new StockConsumptionModel();
        $this->unitModel = new UnitModel(); // Add the UnitModel
        $this->validation = \Config\Services::validation();
    }

    /**
     * Display a list of all purchased products (available stock) and the stock consumption history.
     */
    public function index(): string
    {
        // 1. Fetch data for the 'Available Purchased Stock' table
        // This fetches the product name, current stock, and unit name
        $availableStocks = $this->purchasedProductModel->select('purchased_products.name as product_name, purchased_products.current_stock as available_stock, units.name as unit_name')
            ->join('units', 'units.id = purchased_products.unit_id')
            ->findAll();

        // 2. Fetch data for the 'Stock Consumption Records' table
        // This fetches consumption records and joins with other tables to get product name and unit
        $consumptionEntries = $this->stockConsumptionModel->select('stock_consumption.*, purchased_products.name as product_name, units.name as unit_name')
            ->join('purchased_products', 'purchased_products.id = stock_consumption.product_id')
            ->join('units', 'units.id = purchased_products.unit_id')
            ->findAll();

        // 3. Prepare data to be passed to the view
        $data = [
            'title' => 'Stock Consumption Management',
            'availablePurchasedStocks' => $availableStocks,
            'consumptionEntries' => $consumptionEntries,
        ];

        // 4. Load the view and pass the data
        return view('stock_consumption/index', $data);
    }

    /**
     * Handles the form submission to consume stock.
     */
    public function consume(): ResponseInterface
    {
        // Define validation rules to ensure data is correct and a product exists
        $rules = [
            'product_id' => 'required|integer|is_not_unique[purchased_products.id]',
            'quantity_consumed' => 'required|decimal|greater_than[0]',
            'used_by' => 'permit_empty|string|max_length[255]',
            'notes' => 'permit_empty|string|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get the validated data from the request
        $productId = $this->request->getPost('product_id');
        $quantityConsumed = $this->request->getPost('quantity_consumed');
        $usedBy = $this->request->getPost('used_by');
        $notes = $this->request->getPost('notes');

        // Attempt to deduct stock from the purchased products table
        $success = $this->purchasedProductModel->updateCurrentStock($productId, $quantityConsumed);

        if ($success) {
            // If stock deduction is successful, log the transaction in the consumption table
            $consumptionData = [
                'product_id' => $productId,
                'quantity_consumed' => $quantityConsumed,
                'date_consumed' => date('Y-m-d H:i:s'),
                'used_by' => $usedBy,
                'notes' => $notes,
            ];

            if ($this->stockConsumptionModel->insert($consumptionData)) {
                return redirect()->to(base_url('purchased-consumption'))->with('success', 'Stock consumed and logged successfully.');
            } else {
                // IMPORTANT: In a real system, you'd need to reverse the stock deduction here
                log_message('error', 'Failed to log consumption for product ' . $productId);
                return redirect()->back()->withInput()->with('error', 'Failed to log consumption. Please try again.');
            }
        } else {
            // If the stock deduction failed (e.g., not enough stock)
            return redirect()->back()->withInput()->with('error', 'Not enough stock available for this product.');
        }
    }
}
