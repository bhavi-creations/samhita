<?php

namespace App\Controllers;

use App\Models\SalesModel;
use App\Models\ProductModel;
use App\Models\MarketingPersonModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;


class Sales extends BaseController
{
    protected $salesModel, $productModel, $personModel;

    public function __construct()
    {
        $this->salesModel = new SalesModel();
        $this->productModel = new ProductModel();
        $this->personModel = new MarketingPersonModel();
    }

    public function index()
    {
        $builder = $this->salesModel->builder();
        $builder->select('sales.*, products.name as product_name, units.name as unit_name, marketing_persons.name as person_name, marketing_persons.custom_id');
        $builder->join('products', 'products.id = sales.product_id');
        $builder->join('units', 'units.id = products.unit_id');
        $builder->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id');

        if ($productId = $this->request->getGet('product_id')) {
            $builder->where('sales.product_id', $productId);
        }

        if ($personId = $this->request->getGet('marketing_person_id')) {
            $builder->where('sales.marketing_person_id', $personId);
        }

        if ($dateSold = $this->request->getGet('date_sold')) {
            $builder->where('sales.date_sold', $dateSold);
        }

        $data['sales'] = $builder->get()->getResultArray();
        $data['products'] = $this->productModel->findAll();
        $data['marketing_persons'] = $this->personModel->findAll();

        return view('sales/index', $data);
    }


    private function getFilteredSales()
    {
        $builder = $this->salesModel->builder();

        $builder->select('
            sales.*,
            products.name as product_name,
            marketing_persons.name as person_name,
            marketing_persons.custom_id
        ');

        $builder->join('products', 'products.id = sales.product_id');
        $builder->join('marketing_persons', 'marketing_persons.id = sales.marketing_person_id');

        $productId = $this->request->getGet('product_id');
        $personId = $this->request->getGet('marketing_person_id');
        $dateSold = $this->request->getGet('date_sold');

        if ($productId) {
            $builder->where('sales.product_id', $productId);
        }
        if ($personId) {
            $builder->where('sales.marketing_person_id', $personId);
        }
        if ($dateSold) {
            $builder->where('sales.date_sold', $dateSold);
        }

        return $builder->get()->getResultArray();
    }

    public function create()
    {
        return view('sales/create', [
            'products' => $this->productModel->findAll(),
            'marketing_persons' => $this->personModel->findAll()
        ]);
    }

    public function store()
    {
        $productId = $this->request->getPost('product_id');
        $personId = $this->request->getPost('marketing_person_id');
        $quantitySold = (int)$this->request->getPost('quantity_sold');

        // ✅ 1. Get total quantity issued
        $db = \Config\Database::connect();

        $issuedQuery = $db->table('marketing_distribution')
            ->selectSum('quantity_issued')
            ->where('product_id', $productId)
            ->where('marketing_person_id', $personId)
            ->get();
        $issuedRow = $issuedQuery->getRow();
        $totalIssued = (int)($issuedRow->quantity_issued ?? 0);

        // ✅ 2. Get total quantity already sold
        $soldQuery = $db->table('sales')
            ->selectSum('quantity_sold')
            ->where('product_id', $productId)
            ->where('marketing_person_id', $personId)
            ->get();
        $soldRow = $soldQuery->getRow();
        $totalSold = (int)($soldRow->quantity_sold ?? 0);

        $remaining = $totalIssued - $totalSold;

        // ✅ 3. Check if new quantity exceeds limit
        if ($quantitySold > $remaining) {
            return redirect()->back()->withInput()->with('error', 'Cannot sell more than the remaining stock. Remaining: ' . $remaining);
        }

        // ✅ 4. Insert sale
        $this->salesModel->save([
            'product_id' => $productId,
            'marketing_person_id' => $personId,
            'quantity_sold' => $quantitySold,
            'price_per_unit' => $this->request->getPost('price_per_unit'),
            'date_sold' => $this->request->getPost('date_sold'),
        ]);

        return redirect()->to('/sales')->with('success', 'Sale added successfully.');
    }


    public function edit($id)
    {
        $sale = $this->salesModel->find($id);
        if (!$sale) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Sale not found');

        return view('sales/edit', [
            'sale' => $sale,
            'products' => $this->productModel->findAll(),
            'marketing_persons' => $this->personModel->findAll()
        ]);
    }

    public function update($id)
    {
        $this->salesModel->update($id, [
            'product_id' => $this->request->getPost('product_id'),
            'marketing_person_id' => $this->request->getPost('marketing_person_id'),
            'quantity_sold' => $this->request->getPost('quantity_sold'),
            'price_per_unit' => $this->request->getPost('price_per_unit'),
            'date_sold' => $this->request->getPost('date_sold'),
        ]);

        return redirect()->to('/sales')->with('success', 'Sale updated.');
    }

    public function delete($id)
    {
        $this->salesModel->delete($id);
        return redirect()->to('/sales')->with('success', 'Sale deleted.');
    }

    public function exportExcel()
    {
        $sales = $this->getFilteredSales();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $sheet->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Product')
            ->setCellValue('C1', 'Marketing Person')
            ->setCellValue('D1', 'Quantity Sold')
            ->setCellValue('E1', 'Price Per Unit')
            ->setCellValue('F1', 'Date Sold')
            ->setCellValue('G1', 'Total Price');

        $rowNum = 2;
        foreach ($sales as $sale) {
            $totalPrice = $sale['quantity_sold'] * $sale['price_per_unit'];

            $sheet->setCellValue('A' . $rowNum, $sale['id']);
            $sheet->setCellValue('B' . $rowNum, $sale['product_name']);
            $sheet->setCellValue('C' . $rowNum, $sale['person_name']);
            $sheet->setCellValue('D' . $rowNum, $sale['quantity_sold']);
            $sheet->setCellValue('E' . $rowNum, $sale['price_per_unit']);
            $sheet->setCellValue('F' . $rowNum, $sale['date_sold']);
            $sheet->setCellValue('G' . $rowNum, $totalPrice);

            $rowNum++;
        }

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sales_export.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    public function exportPDF()
    {
        $sales = $this->getFilteredSales();

        $html = view('sales/export_pdf', ['sales' => $sales]);

        $options = new Options();
        $options->set('isRemoteEnabled', true); // enable loading remote images if any

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream('sales_export.pdf', ["Attachment" => true]);
        exit;
    }


    public function getRemainingStock()
    {
        $productId = $this->request->getGet('product_id');
        $personId = $this->request->getGet('marketing_person_id');

        $db = \Config\Database::connect();

        $issuedQuery = $db->table('marketing_distribution')
            ->selectSum('quantity_issued')
            ->where('product_id', $productId)
            ->where('marketing_person_id', $personId)
            ->get();
        $issued = (int)($issuedQuery->getRow()->quantity_issued ?? 0);

        $soldQuery = $db->table('sales')
            ->selectSum('quantity_sold')
            ->where('product_id', $productId)
            ->where('marketing_person_id', $personId)
            ->get();
        $sold = (int)($soldQuery->getRow()->quantity_sold ?? 0);

        $remaining = $issued - $sold;

        return $this->response->setJSON(['remaining' => $remaining]);
    }
}
