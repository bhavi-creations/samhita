<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\MarketingDistribution; // Ensure this is present if needed for explicit use within routes
use App\Controllers\DistributorSalesController; // Ensure this is present if needed for explicit use within routes


/**
 * @var RouteCollection $routes
 */

$routes->setAutoRoute(true); // Keep this at the very top for auto-routing functionality

// --- Public Routes (Accessible without login) ---
$routes->get('/', 'Home::index');

// Authentication Routes
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login/attempt', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout', ['as' => 'logout']);

// --- Protected Routes (Require Authentication) ---
// All routes inside this group will be protected by the 'auth' filter
$routes->group('/', ['filter' => 'auth'], function ($routes) {

    // DASHBOARD
    $routes->get('dashboard', 'Dashboard::index');

    // UNITS
    $routes->get('units', 'Units::index');
    $routes->get('units/create', 'Units::create');
    $routes->post('units/store', 'Units::store');
    $routes->get('units/edit/(:num)', 'Units::edit/$1');
    $routes->post('units/update/(:num)', 'Units::update/$1');
    $routes->post('units/delete/(:num)', 'Units::delete/$1');

    // PRODUCTS
    $routes->group('products', function ($routes) {
        $routes->get('/', 'Products::index');
        $routes->get('create', 'Products::create');
        $routes->post('store', 'Products::store');
        $routes->get('edit/(:segment)', 'Products::edit/$1');
        $routes->post('update/(:segment)', 'Products::update/$1');
        $routes->get('delete/(:segment)', 'Products::delete/$1');
        $routes->get('stock-overview', 'Products::stockOverview');
        // Product Price Management Routes
        $routes->get('manage-prices', 'Products::managePrices'); // Displays the list
        $routes->get('edit-price/(:num)', 'Products::editPrice/$1'); // Displays the edit form
        $routes->post('update-price/(:num)', 'Products::updatePrice/$1'); // Handles form submission
    });

    // STOCK OUT
    $routes->group('stock-out', function ($routes) {
        $routes->get('/', 'StockOut::index');
        $routes->get('issue', 'StockOut::issue');
        $routes->post('store', 'StockOut::store');
        $routes->get('view/(:num)', 'StockOut::view/$1');
        $routes->get('export-excel', 'StockOut::exportExcel');
        $routes->get('export-pdf', 'StockOut::exportPdf');
    });

    // STOCK IN
    $routes->group('stock-in', function ($routes) {
        $routes->get('/', 'StockIn::index');
        $routes->get('create', 'StockIn::create');
        $routes->post('store', 'StockIn::store');
        $routes->get('view/(:num)', 'StockIn::view/$1');
        $routes->get('edit/(:num)', 'StockIn::edit/$1');
        $routes->post('update/(:num)', 'StockIn::update/$1');
        $routes->delete('delete/(:num)', 'StockIn::delete/$1'); // Corrected to use DELETE method
        $routes->get('export-excel/(:num)', 'StockIn::exportToExcel/$1');
        $routes->get('export-pdf/(:num)', 'StockIn::exportToPdf/$1');
        $routes->post('payment/store', 'StockIn::storePayment');
        $routes->get('payment/edit/(:num)', 'StockIn::editPayment/$1');
        $routes->put('payment/update/(:num)', 'StockIn::updatePayment/$1');
        $routes->delete('payment/delete/(:num)', 'StockIn::deletePayment/$1');
        $routes->post('add-payment/(:num)', 'StockIn::addPayment/$1');
    });

    // API ROUTES (if they need authentication, otherwise move them out of this group)
    $routes->get('api/products/available-stock/(:num)', 'Products::getAvailableStock/$1');

    // MARKETING DISTRIBUTION
    $routes->group('marketing-distribution', function ($routes) {
        $routes->get('/', 'MarketingDistribution::index');
        $routes->get('create', 'MarketingDistribution::create');
        $routes->post('store', 'MarketingDistribution::store');
        $routes->get('edit/(:num)', 'MarketingDistribution::edit/$1');
        $routes->put('update/(:num)', 'MarketingDistribution::update/$1');
        $routes->get('delete/(:num)', 'MarketingDistribution::delete/$1');
    });

    // MARKETING PERSONS
    $routes->group('marketing-persons', function ($routes) {
        $routes->get('/', 'MarketingPersons::index');
        $routes->get('create', 'MarketingPersons::create');
        $routes->post('store', 'MarketingPersons::store');
        $routes->get('edit/(:num)', 'MarketingPersons::edit/$1');
        $routes->put('update/(:num)', 'MarketingPersons::update/$1');
        $routes->delete('delete/(:num)', 'MarketingPersons::delete/$1');
        $routes->get('view/(:num)', 'MarketingPersons::view/$1');
        $routes->get('export-all-excel', 'MarketingPersons::exportAllExcel');
        $routes->get('export-all-pdf', 'MarketingPersons::exportAllPdf');
        $routes->get('export-excel/(:num)', 'MarketingPersons::exportExcel/$1');
        $routes->get('export-pdf/(:num)', 'MarketingPersons::exportPdf/$1');
    });

    // SALES
    $routes->group('sales', function ($routes) {
        $routes->get('/', 'Sales::index');
        $routes->get('create', 'Sales::create');
        $routes->post('store-multiple', 'Sales::storeMultiple');
        $routes->get('view/(:num)', 'Sales::view/$1');
        $routes->get('edit/(:num)', 'Sales::edit/$1');
        $routes->put('update/(:num)', 'Sales::update/$1');
        $routes->get('delete/(:num)', 'Sales::delete/$1');
        $routes->get('remitPayment/(:num)', 'Sales::remitPayment/$1');
        $routes->post('processRemittance/(:num)', 'Sales::processRemittance/$1');
        $routes->get('product-details', 'Sales::productDetails');
        $routes->get('export-person-sales-excel/(:num)', 'Sales::exportPersonSalesExcel/$1');
        $routes->get('export-person-sales-pdf/(:num)', 'Sales::exportPersonSalesPDF/$1');
        $routes->get('export-excel', 'Sales::exportExcel');
        $routes->get('export-pdf', 'Sales::exportPDF');
        $routes->get('get-remaining-stock', 'Sales::getRemainingStock');
        $routes->get('record-sale-payment-form/(:num)', 'Sales::recordSalePaymentForm/$1');
        $routes->post('record-sale-payment', 'Sales::recordSalePayment');
        $routes->get('payment-history/(:num)', 'Sales::viewSalePaymentHistory/$1');
        $routes->get('export-sale-payments-excel/(:num)', 'Sales::exportSalePaymentsExcel/$1');
        $routes->get('export-sale-payments-pdf/(:num)', 'Sales::exportSalePaymentsPDF/$1');
    });

    // REPORTS
    $routes->get('reports/person-stock', 'Reports::marketingPersonStock');

    // GST RATES MANAGEMENT
    $routes->group('gst-rates', function ($routes) {
        $routes->get('/', 'GstRates::index');
        $routes->get('create', 'GstRates::create');
        $routes->post('store', 'GstRates::store');
        $routes->get('edit/(:num)', 'GstRates::edit/$1');
        $routes->put('update/(:num)', 'GstRates::update/$1');
        $routes->get('delete/(:num)', 'GstRates::delete/$1');
    });

    // VENDORS
    $routes->group('vendors', function ($routes) { // Grouped for better organization and filter application
        $routes->get('/', 'Vendors::index');
        $routes->get('create', 'Vendors::create');
        $routes->post('store', 'Vendors::store');
        $routes->get('edit/(:num)', 'Vendors::edit/$1');
        $routes->post('update/(:num)', 'Vendors::update/$1');
        $routes->get('delete/(:num)', 'Vendors::delete/$1');
        $routes->get('vendorReport', 'Vendors::vendorReport');
        $routes->get('vendorReportExport', 'Vendors::vendorReportExport');
        $routes->get('reports/vendor-report-pdf', 'Vendors::vendorReportPDF');
    });

    // DISTRIBUTOR
    $routes->group('distributors', function ($routes) {
        $routes->get('/', 'Distributor::index');
        $routes->get('add', 'Distributor::add');
        $routes->post('store', 'Distributor::store');
        $routes->get('view/(:num)', 'Distributor::view/$1');
        $routes->get('edit/(:num)', 'Distributor::edit/$1');
        $routes->post('update/(:num)', 'Distributor::update/$1');
        $routes->get('delete/(:num)', 'Distributor::delete/$1');
        $routes->get('exportExcel', 'Distributor::exportExcel');
        $routes->get('exportPdf', 'Distributor::exportPdf');
        $routes->get('exportSingleExcel/(:num)', 'Distributor::exportSingleExcel/$1');
        $routes->get('exportSinglePdf/(:num)', 'Distributor::exportSinglePdf/$1');
    });

    // DISTRIBUTOR SALES
    $routes->group('distributor-sales', function ($routes) {
        $routes->get('/', 'DistributorSalesController::index', ['as' => 'distributor-sales']);
        $routes->get('new', 'DistributorSalesController::create', ['as' => 'distributor-sales-new']);
        $routes->post('save', 'DistributorSalesController::store', ['as' => 'distributor-sales-store']);
        $routes->get('show/(:num)', 'DistributorSalesController::show/$1', ['as' => 'distributor-sales-show']);
        $routes->get('add-payment/(:num)', 'DistributorSalesController::addPayment/$1', ['as' => 'distributor-sales-add-payment']);
        $routes->post('save-payment', 'DistributorSalesController::savePayment', ['as' => 'distributor-sales-save-payment']);
        $routes->get('edit/(:num)', 'DistributorSalesController::edit/$1', ['as' => 'distributor-sales-edit']);
        $routes->put('update/(:num)', 'DistributorSalesController::update/$1', ['as' => 'distributor-sales-update']);
        $routes->delete('delete/(:num)', 'DistributorSalesController::delete/$1', ['as' => 'distributor-sales-delete']);
        $routes->get('export/pdf-index', 'DistributorSalesController::exportIndexPdf', ['as' => 'distributor-sales-export-pdf-index']);
        $routes->get('export/excel-index', 'DistributorSalesController::exportIndexExcel', ['as' => 'distributor-sales-export-excel-index']);
        $routes->get('export/invoice-pdf/(:num)', 'DistributorSalesController::exportInvoicePdf/$1', ['as' => 'distributor-sales-export-invoice-pdf']);
        $routes->get('export/invoice-excel/(:num)', 'DistributorSalesController::exportInvoiceExcel/$1', ['as' => 'distributor-sales-export-invoice-excel']);
    });
});
