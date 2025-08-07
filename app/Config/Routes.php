<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\MarketingDistribution; // Ensure this is present if needed for explicit use within routes
use App\Controllers\DistributorSalesController; // Ensure this is present if needed for explicit use within routes
// --- NEW IMPORTS START ---
use App\Controllers\SellingProducts; // New import for SellingProductsController
use App\Controllers\PurchasedProducts; // New import for PurchasedProductsController
use App\Controllers\PurchasedConsumption; // New import for PurchasedConsumptionController
// --- NEW IMPORTS END ---


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

    // --- CHANGE START: SELLING PRODUCTS (formerly PRODUCTS) ---
    $routes->group('selling-products', function ($routes) {
        $routes->get('/', 'SellingProducts::index');
        $routes->get('create', 'SellingProducts::create');
        $routes->post('store', 'SellingProducts::store');
        $routes->get('edit/(:segment)', 'SellingProducts::edit/$1');
        $routes->post('update/(:segment)', 'SellingProducts::update/$1'); // Changed from put to post for consistency
        $routes->get('delete/(:segment)', 'SellingProducts::delete/$1'); // Changed from delete to get for consistency
        $routes->get('stock-overview', 'SellingProducts::stockOverview');
        // Product Price Management Routes (assuming these are for selling products)
        $routes->get('manage-prices', 'SellingProducts::managePrices'); // Displays the list
        $routes->get('edit-price/(:num)', 'SellingProducts::editPrice/$1'); // Displays the edit form
        $routes->post('update-price/(:num)', 'SellingProducts::updatePrice/$1'); // Handles form submission
    });
    // --- CHANGE END: SELLING PRODUCTS ---

    // --- NEW GROUP START: PURCHASED PRODUCTS ---
    // The index route here is no longer needed as PurchasedConsumption::index handles the view
    $routes->group('purchased-products', function ($routes) {
        $routes->get('/', 'PurchasedProducts::index');
        $routes->get('create', 'PurchasedProducts::create');
        $routes->post('store', 'PurchasedProducts::store');
        $routes->get('edit/(:num)', 'PurchasedProducts::edit/$1');
        $routes->put('update/(:num)', 'PurchasedProducts::update/$1');  // Changed from put to post for consistency
        $routes->get('delete/(:num)', 'PurchasedProducts::delete/$1'); // Changed from delete to get for consistency
    });
    // --- NEW GROUP END: PURCHASED PRODUCTS ---

    // --- NEW GROUP START: PURCHASED CONSUMPTION (Replaces old stock-consumption) ---
    $routes->group('purchased-consumption', function ($routes) {
        $routes->get('/', 'PurchasedConsumption::index');
        $routes->post('consume', 'PurchasedConsumption::consume');
    });
    // --- NEW GROUP END: PURCHASED CONSUMPTION ---


    // STOCK OUT
    $routes->group('stock-out', function ($routes) {
        $routes->get('/', 'StockOut::index');
        $routes->get('issue', 'StockOut::issue');
        $routes->post('store', 'StockOut::store');
        $routes->get('view/(:num)', 'StockOut::view/$1');
        $routes->get('export-excel', 'StockOut::exportExcel');
        $routes->get('export-pdf', 'StockOut::exportPdf');
    });

    // --- CHANGE START: STOCK IN (Updated view method name) ---
    $routes->group('stock-in', function ($routes) {
        $routes->get('/', 'StockIn::index');
        $routes->get('create', 'StockIn::create');
        $routes->post('store', 'StockIn::store');
        $routes->get('view/(:num)', 'StockIn::view/$1'); // Changed from 'view' to 'show'
        $routes->get('edit/(:num)', 'StockIn::edit/$1');
        $routes->put('update/(:num)', 'StockIn::update/$1');
        $routes->delete('delete/(:num)', 'StockIn::delete/$1');
        $routes->get('export-excel/(:num)', 'StockIn::exportToExcel/$1');
        $routes->get('export-pdf/(:num)', 'StockIn::exportToPdf/$1');
        $routes->post('payment/store', 'StockIn::storePayment'); // Assuming this is for adding payment
        $routes->get('payment/edit/(:num)', 'StockIn::editPayment/$1');
        $routes->put('payment/update/(:num)', 'StockIn::updatePayment/$1');
        $routes->delete('payment/delete/(:num)', 'StockIn::deletePayment/$1');
        $routes->post('add-payment/(:num)', 'StockIn::addPayment/$1'); // This route seems redundant with payment/store, review usage
    });


    // DEPRECATED - This group is now replaced by the purchased-consumption group
    $routes->group('stock-consumption', function ($routes) {
        $routes->get('/', 'StockConsumption::index');
        $routes->get('create', 'StockConsumption::create');
        $routes->post('store', 'StockConsumption::store');
        $routes->get('edit/(:num)', 'StockConsumption::edit/$1');
        $routes->post('update/(:num)', 'StockConsumption::update/$1');
        $routes->delete('delete/(:num)', 'StockConsumption::delete/$1');
    });

    
    $routes->get('api/selling-products/available-stock/(:num)', 'SellingProducts::getAvailableStock/$1');
    // --- NEW API ROUTE FOR PURCHASED PRODUCTS ---
    $routes->get('api/purchased-products/details/(:num)', 'PurchasedProducts::getPurchasedProductDetails/$1');
    // --- CHANGE END: API route ---

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
        $routes->get('export/invoice-pdf/(:num)/(:any)', 'DistributorSalesController::exportInvoicePdf/$1/$2', ['as' => 'distributor-sales-export-invoice-pdf-mode']);

        $routes->get('export/invoice-excel/(:num)', 'DistributorSalesController::exportInvoiceExcel/$1', ['as' => 'distributor-sales-export-invoice-excel']);
    });

    // In app/Config/Routes.php
    $routes->group('/', function ($routes) {

        $routes->get('company-settings', 'CompanySettingsController::index');
        $routes->post('company-settings/upload-image', 'CompanySettingsController::uploadImage');
        $routes->post('company-settings/delete-image/(:any)', 'CompanySettingsController::deleteImage/$1');
    });
});
