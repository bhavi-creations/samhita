<?php

namespace App\Controllers;

use App\Models\MarketingDistributionModel;
use App\Models\ProductModel;
use App\Models\MarketingPersonModel;

class MarketingDistribution extends BaseController
{
    protected $distributionModel;
    protected $productModel;
    protected $marketingPersonModel;

    public function __construct()
    {
        $this->distributionModel = new MarketingDistributionModel();
        $this->productModel = new ProductModel();
        $this->marketingPersonModel = new MarketingPersonModel();
    }

   public function index()
{
     
    $builder = $this->distributionModel->builder();

    $builder->select('
        marketing_distribution.*,
        products.name as product_name,
        units.name as unit_name,
        marketing_persons.name as person_name,
        marketing_persons.custom_id,
        COALESCE(stock_in.total_stock, 0) as total_stock,
        COALESCE(total_issued.total_issued, 0) as total_issued
    ');

    $builder->join('products', 'products.id = marketing_distribution.product_id');
    $builder->join('units', 'units.id = products.unit_id');
    $builder->join('marketing_persons', 'marketing_persons.id = marketing_distribution.marketing_person_id');

    // Join stock_in (grouped)
    $builder->join('(SELECT product_id, SUM(quantity) as total_stock FROM stock_in GROUP BY product_id) as stock_in', 'stock_in.product_id = products.id', 'left');

    // Join distribution (grouped) for issued count
    $builder->join('(SELECT product_id, SUM(quantity_issued) as total_issued FROM marketing_distribution GROUP BY product_id) as total_issued', 'total_issued.product_id = products.id', 'left');

    // Optional filters
    $productId = $this->request->getGet('product_id');
    $personId = $this->request->getGet('marketing_person_id');
    $dateIssued = $this->request->getGet('date_issued');

    if ($productId) {
        $builder->where('marketing_distribution.product_id', $productId);
    }
    if ($personId) {
        $builder->where('marketing_distribution.marketing_person_id', $personId);
    }
    if ($dateIssued) {
        $builder->where('marketing_distribution.date_issued', $dateIssued);
    }

    $data['distributions'] = $builder->get()->getResultArray();
    $data['products'] = $this->productModel->findAll();
    $data['marketing_persons'] = $this->marketingPersonModel->findAll();

    return view('marketing_distribution/index', $data);
}

    public function create()
    {
        $data['products'] = $this->productModel->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();
        return view('marketing_distribution/create', $data);
    }

    public function store()
    {
        $this->distributionModel->save([
            'product_id' => $this->request->getPost('product_id'),
            'marketing_person_id' => $this->request->getPost('marketing_person_id'),
            'quantity_issued' => $this->request->getPost('quantity_issued'),
            'date_issued' => $this->request->getPost('date_issued'),
        ]);

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record added successfully.');
    }

    public function edit($id)
    {
        $distribution = $this->distributionModel->find($id);
        if (!$distribution) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Record not found');
        }
        $data['distribution'] = $distribution;
        $data['products'] = $this->productModel->findAll();
        $data['marketing_persons'] = $this->marketingPersonModel->findAll();
        return view('marketing_distribution/edit', $data);
    }

    public function update($id)
    {
        $this->distributionModel->update($id, [
            'product_id' => $this->request->getPost('product_id'),
            'marketing_person_id' => $this->request->getPost('marketing_person_id'),
            'quantity_issued' => $this->request->getPost('quantity_issued'),
            'date_issued' => $this->request->getPost('date_issued'),
        ]);

        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record updated successfully.');
    }

    public function delete($id)
    {
        $this->distributionModel->delete($id);
        return redirect()->to('/marketing-distribution')->with('success', 'Distribution record deleted successfully.');
    }
}
