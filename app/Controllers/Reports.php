<?php


namespace App\Controllers;

use App\Models\MarketingPersonModel;
use App\Models\ProductModel;
use CodeIgniter\Database\BaseConnection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class Reports extends BaseController
{
    public function marketingPersonStock()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('marketing_distribution md');
        $builder->select("
        mp.custom_id AS person_id,
        mp.name AS person_name,
        p.name AS product_name,
        IFNULL(SUM(md.quantity_issued), 0) AS total_issued,

        -- Subquery for total sold
        (
            SELECT IFNULL(SUM(s.quantity_sold), 0)
            FROM sales s
            WHERE s.product_id = md.product_id
              AND s.marketing_person_id = md.marketing_person_id
              " . ($this->request->getGet('from_date') ? "AND s.date_sold >= '" . $this->request->getGet('from_date') . "'" : "") . "
              " . ($this->request->getGet('to_date') ? "AND s.date_sold <= '" . $this->request->getGet('to_date') . "'" : "") . "
        ) AS total_sold,

        -- Subquery for total value
        (
            SELECT IFNULL(SUM(s.quantity_sold * s.price_per_unit), 0)
            FROM sales s
            WHERE s.product_id = md.product_id
              AND s.marketing_person_id = md.marketing_person_id
              " . ($this->request->getGet('from_date') ? "AND s.date_sold >= '" . $this->request->getGet('from_date') . "'" : "") . "
              " . ($this->request->getGet('to_date') ? "AND s.date_sold <= '" . $this->request->getGet('to_date') . "'" : "") . "
        ) AS total_value_sold,

        -- Subquery for latest sale date
        (
            SELECT MAX(s.date_sold)
            FROM sales s
            WHERE s.product_id = md.product_id
              AND s.marketing_person_id = md.marketing_person_id
              " . ($this->request->getGet('from_date') ? "AND s.date_sold >= '" . $this->request->getGet('from_date') . "'" : "") . "
              " . ($this->request->getGet('to_date') ? "AND s.date_sold <= '" . $this->request->getGet('to_date') . "'" : "") . "
        ) AS latest_sale_date
    ");

        $builder->join('marketing_persons mp', 'mp.id = md.marketing_person_id');
        $builder->join('products p', 'p.id = md.product_id');

        // Filters
        $person_id = $this->request->getGet('marketing_person_id');
        $product_id = $this->request->getGet('product_id');
        $from_date = $this->request->getGet('from_date');
        $to_date = $this->request->getGet('to_date');

        if ($person_id) {
            $builder->where('md.marketing_person_id', $person_id);
        }
        if ($product_id) {
            $builder->where('md.product_id', $product_id);
        }

        $builder->groupBy(['md.marketing_person_id', 'md.product_id']);
        $builder->orderBy('mp.name');

        $report = $builder->get()->getResultArray();

        // Totals
        $total_issued = $total_sold = $total_value = 0;
        foreach ($report as $r) {
            $total_issued += $r['total_issued'];
            $total_sold += $r['total_sold'];
            $total_value += $r['total_value_sold'];
        }

        // View data
        $data = [
            'report' => $report,
            'total_issued' => $total_issued,
            'total_sold' => $total_sold,
            'total_value' => $total_value,
            'persons' => $db->table('marketing_persons')->get()->getResultArray(),
            'products' => $db->table('products')->get()->getResultArray(),
            'selected_person' => $person_id,
            'selected_product' => $product_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ];

        $exportType = $this->request->getGet('export');
        if ($exportType === 'excel') {
            return $this->exportPersonStockExcel($report, $data);
        } elseif ($exportType === 'pdf') {
            return $this->exportPersonStockPdf($report, $data);
        }


        return view('reports/person_stock', $data);
    }



    private function exportPersonStockExcel($report, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->fromArray([
            'S.No',
            'Person ID',
            'Name',
            'Product',
            'Qty Issued',
            'Qty Sold',
            'Remaining',
            'Total Value',
            'Latest Sale Date'
        ], NULL, 'A1');

        // Data
        $rowIndex = 2;
        $i = 1;
        foreach ($report as $row) {
            $sheet->fromArray([
                $i++,
                $row['person_id'],
                $row['person_name'],
                $row['product_name'],
                $row['total_issued'],
                $row['total_sold'],
                $row['total_issued'] - $row['total_sold'],
                $row['total_value_sold'],
                $row['latest_sale_date']
            ], NULL, 'A' . $rowIndex++);
        }

        // Totals
        $sheet->fromArray([
            '',
            '',
            '',
            'TOTAL',
            $data['total_issued'],
            $data['total_sold'],
            $data['total_issued'] - $data['total_sold'],
            $data['total_value'],
            ''
        ], NULL, 'A' . $rowIndex);

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = 'marketing_person_stock_report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save("php://output");
        exit;
    }


    private function exportPersonStockPdf($report, $data)
    {
        $html = view('reports/export_person_stock_pdf', [
            'report' => $report,
            'total_issued' => $data['total_issued'],
            'total_sold' => $data['total_sold'],
            'total_value' => $data['total_value']
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('marketing_person_stock_report_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
        exit;
    }
}
