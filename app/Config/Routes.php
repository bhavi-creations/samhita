<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\MarketingDistribution; 
use App\Controllers\DistributorSalesController; 
// --- NEW IMPORTS START ---
use App\Controllers\SellingProducts; 
use App\Controllers\PurchasedProducts; 
use App\Controllers\PurchasedConsumption; 
// --- NEW IMPORTS END ---


/**
 * @var RouteCollection $routes
 */

$routes->setAutoRoute(true);

// --- Public Routes (Accessible without login) ---
$routes->GET('/', 'Home::index');

// Authentication Routes
$routes->GET('login', 'Auth::login', ['as' => 'login']);
$routes->POST('login/attempt', 'Auth::attemptLogin');
$routes->GET('logout', 'Auth::logout', ['as' => 'logout']);

// --- Protected Routes (Require Authentication) ---
$routes->group('/', ['filter' => 'auth'], function ($routes) {

    // DASHBOARD
    $routes->GET('dashboard', 'Dashboard::index');

    // UNITS
    $routes->GET('units', 'Units::index');
    $routes->GET('units/create', 'Units::create');
    $routes->POST('units/store', 'Units::store');
    $routes->GET('units/edit/(:num)', 'Units::edit/$1');
    $routes->POST('units/update/(:num)', 'Units::update/$1');
    $routes->POST('units/delete/(:num)', 'Units::delete/$1');

    // --- CHANGE START: SELLING PRODUCTS (formerly PRODUCTS) ---
    $routes->group('selling-products', function ($routes) {
        // Show the list of products
        $routes->GET('/', 'SellingProducts::index');
        $routes->GET('create', 'SellingProducts::create');
        $routes->POST('store', 'SellingProducts::store');
        $routes->match(['GET', 'PUT'], 'update/(:num)', 'SellingProducts::update/$1');
        $routes->GET('edit/(:num)', 'SellingProducts::edit/$1');
        $routes->POST('delete/(:num)', 'SellingProducts::delete/$1');

        $routes->GET('stock-overview', 'SellingProducts::stockOverview');
        $routes->GET('get-available-stock/(:num)', 'SellingProducts::getAvailableStock/$1');
    });
    // --- CHANGE END: SELLING PRODUCTS ---

    // --- NEW GROUP START: PURCHASED PRODUCTS ---
    $routes->group('purchased-products', function ($routes) {
        $routes->GET('/', 'PurchasedProducts::index');
        $routes->GET('create', 'PurchasedProducts::create');
        $routes->POST('store', 'PurchasedProducts::store');
        $routes->GET('edit/(:num)', 'PurchasedProducts::edit/$1');
        $routes->PUT('update/(:num)', 'PurchasedProducts::update/$1');
        $routes->DELETE('delete/(:num)', 'PurchasedProducts::delete/$1');
    });
    // --- NEW GROUP END: PURCHASED PRODUCTS ---

    // --- NEW GROUP START: PURCHASED CONSUMPTION (Replaces old stock-consumption) ---
    $routes->group('purchased-consumption', function ($routes) {
        $routes->GET('/', 'PurchasedConsumption::index');
        $routes->POST('consume', 'PurchasedConsumption::consume');
    });
    // --- NEW GROUP END: PURCHASED CONSUMPTION ---


    // STOCK OUT
    $routes->group('stock-out', function ($routes) {
        $routes->GET('/', 'StockOut::index');
        $routes->GET('issue', 'StockOut::issue');
        $routes->POST('store', 'StockOut::store');
        $routes->GET('view/(:num)', 'StockOut::view/$1');
        $routes->GET('export-excel', 'StockOut::exportExcel');
        $routes->GET('export-pdf', 'StockOut::exportPdf');
    });

    // --- CHANGE START: STOCK IN (Updated view method name) ---
    $routes->group('stock-in', function ($routes) {
        $routes->GET('/', 'StockIn::index');
        $routes->GET('create', 'StockIn::create');
        $routes->POST('store', 'StockIn::store');
        $routes->GET('view/(:num)', 'StockIn::view/$1'); // Changed from 'view' to 'show'
        $routes->GET('edit/(:num)', 'StockIn::edit/$1');
        $routes->PUT('update/(:num)', 'StockIn::update/$1');
        $routes->DELETE('delete/(:num)', 'StockIn::delete/$1');
        $routes->GET('export-excel/(:num)', 'StockIn::exportToExcel/$1');
        $routes->GET('export-pdf/(:num)', 'StockIn::exportToPdf/$1');
        $routes->POST('payment/store', 'StockIn::storePayment'); // Assuming this is for adding payment
        $routes->GET('payment/edit/(:num)', 'StockIn::editPayment/$1');
        $routes->PUT('payment/update/(:num)', 'StockIn::updatePayment/$1');
        $routes->DELETE('payment/delete/(:num)', 'StockIn::deletePayment/$1');
        $routes->POST('add-payment/(:num)', 'StockIn::addPayment/$1'); // This route seems redundant with payment/store, review usage
    });


    // DEPRECATED - This group is now replaced by the purchased-consumption group
    $routes->group('stock-consumption', function ($routes) {
        $routes->GET('/', 'StockConsumption::index');
        $routes->GET('create', 'StockConsumption::create');
        $routes->POST('store', 'StockConsumption::store');
        $routes->GET('edit/(:num)', 'StockConsumption::edit/$1');
        $routes->POST('update/(:num)', 'StockConsumption::update/$1');
        $routes->DELETE('delete/(:num)', 'StockConsumption::delete/$1');
    });


    $routes->GET('api/selling-products/available-stock/(:num)', 'SellingProducts::getAvailableStock/$1');
    // --- NEW API ROUTE FOR PURCHASED PRODUCTS ---
    $routes->GET('api/purchased-products/details/(:num)', 'PurchasedProducts::getPurchasedProductDetails/$1');
    // --- CHANGE END: API route ---

    

    // MARKETING PERSONS
    $routes->group('marketing-persons', function ($routes) {
        $routes->GET('/', 'MarketingPersons::index');
        $routes->GET('create', 'MarketingPersons::create');
        $routes->POST('store', 'MarketingPersons::store');
        $routes->GET('edit/(:num)', 'MarketingPersons::edit/$1');
        $routes->PUT('update/(:num)', 'MarketingPersons::update/$1');
        $routes->DELETE('delete/(:num)', 'MarketingPersons::delete/$1');
        $routes->GET('view/(:num)', 'MarketingPersons::view/$1');
        $routes->GET('export-all-excel', 'MarketingPersons::exportAllExcel');
        $routes->GET('export-all-pdf', 'MarketingPersons::exportAllPdf');
        $routes->GET('export-excel/(:num)', 'MarketingPersons::exportExcel/$1');
        $routes->GET('export-pdf/(:num)', 'MarketingPersons::exportPdf/$1');
    });

    // SALES
    $routes->group('sales', function ($routes) {
        $routes->GET('/', 'Sales::index');
        $routes->GET('create', 'Sales::create');
        $routes->POST('store-multiple', 'Sales::storeMultiple');
        $routes->GET('view/(:num)', 'Sales::view/$1');
        $routes->GET('edit/(:num)', 'Sales::edit/$1');
        $routes->PUT('update/(:num)', 'Sales::update/$1');
        $routes->DELETE('delete/(:num)', 'Sales::delete/$1');
        $routes->GET('remitPayment/(:num)', 'Sales::remitPayment/$1');
        $routes->POST('processRemittance/(:num)', 'Sales::processRemittance/$1');
        $routes->GET('product-details', 'Sales::productDetails');
        $routes->GET('export-person-sales-excel/(:num)', 'Sales::exportPersonSalesExcel/$1');
        $routes->GET('export-person-sales-pdf/(:num)', 'Sales::exportPersonSalesPDF/$1');
        $routes->GET('export-excel', 'Sales::exportExcel');
        $routes->GET('export-pdf', 'Sales::exportPDF');
        $routes->GET('get-remaining-stock', 'Sales::getRemainingStock');
        $routes->GET('record-sale-payment-form/(:num)', 'Sales::recordSalePaymentForm/$1');
        $routes->POST('record-sale-payment', 'Sales::recordSalePayment');
        $routes->GET('payment-history/(:num)', 'Sales::viewSalePaymentHistory/$1');
        $routes->GET('export-sale-payments-excel/(:num)', 'Sales::exportSalePaymentsExcel/$1');
        $routes->GET('export-sale-payments-pdf/(:num)', 'Sales::exportSalePaymentsPDF/$1');
    });

    // REPORTS
    $routes->GET('reports/person-stock', 'Reports::marketingPersonStock');

    // GST RATES MANAGEMENT
    $routes->group('gst-rates', function ($routes) {
        $routes->GET('/', 'GstRates::index');
        $routes->GET('create', 'GstRates::create');
        $routes->POST('store', 'GstRates::store');
        $routes->GET('edit/(:num)', 'GstRates::edit/$1');
        $routes->PUT('update/(:num)', 'GstRates::update/$1');
        $routes->GET('delete/(:num)', 'GstRates::delete/$1');
    });

    // VENDORS
    $routes->group('vendors', function ($routes) {
        $routes->GET('/', 'Vendors::index');
        $routes->GET('create', 'Vendors::create');
        $routes->POST('store', 'Vendors::store');
        $routes->GET('edit/(:num)', 'Vendors::edit/$1');
        $routes->POST('update/(:num)', 'Vendors::update/$1');
        $routes->GET('delete/(:num)', 'Vendors::delete/$1');
        $routes->GET('vendorReport', 'Vendors::vendorReport');
        $routes->GET('vendorReportExport', 'Vendors::vendorReportExport');
        $routes->GET('reports/vendor-report-pdf', 'Vendors::vendorReportPDF');
    });

    // DISTRIBUTOR
    $routes->group('distributors', function ($routes) {
        $routes->GET('/', 'Distributor::index');
        $routes->GET('add', 'Distributor::add');
        $routes->POST('store', 'Distributor::store');
        $routes->GET('view/(:num)', 'Distributor::view/$1');
        $routes->GET('edit/(:num)', 'Distributor::edit/$1');
        $routes->POST('update/(:num)', 'Distributor::update/$1');
        $routes->GET('delete/(:num)', 'Distributor::delete/$1');
        $routes->GET('exportExcel', 'Distributor::exportExcel');
        $routes->GET('exportPdf', 'Distributor::exportPdf');
        $routes->GET('exportSingleExcel/(:num)', 'Distributor::exportSingleExcel/$1');
        $routes->GET('exportSinglePdf/(:num)', 'Distributor::exportSinglePdf/$1');
    });

// DISTRIBUTOR SALES
$routes->group('distributor-sales', function ($routes) {
    // Main Pages
    $routes->GET('/', 'DistributorSalesController::index', ['as' => 'distributor-sales-list']);
    $routes->GET('create', 'DistributorSalesController::create', ['as' => 'distributor-sales-create']);
    $routes->POST('save', 'DistributorSalesController::save', ['as' => 'distributor-sales-save']);
    $routes->GET('view/(:num)', 'DistributorSalesController::view/$1', ['as' => 'distributor-sales-view']);
    $routes->GET('edit/(:num)', 'DistributorSalesController::edit/$1', ['as' => 'distributor-sales-edit']);
    $routes->PUT('update/(:num)', 'DistributorSalesController::update/$1', ['as' => 'distributor-sales-update']);
    $routes->DELETE('delete/(:num)', 'DistributorSalesController::delete/$1', ['as' => 'distributor-sales-delete']);
    $routes->GET('sold-stock-overview', 'DistributorSalesController::soldStockOverview', ['as' => 'distributor-sales-sold-stock-overview']);

    // Payments
    $routes->GET('add-payment/(:num)', 'DistributorSalesController::addPayment/$1', ['as' => 'distributor-sales-add-payment']);
    $routes->POST('save-payment', 'DistributorSalesController::savePayment', ['as' => 'distributor-sales-save-payment']);

    // Export Methods
    $routes->GET('export/invoice-excel/(:num)', 'DistributorSalesController::exportExcel/$1', ['as' => 'distributor-sales-export-excel']);
    $routes->GET('export/invoice-pdf/(:num)', 'DistributorSalesController::exportPdf/$1', ['as' => 'distributor-sales-export-pdf']);
});

   // --- NEW ROUTE START: MARKETING SALES REPORT ---
    // This route will handle the display and filtering of sales by marketing person.
    // It maps to the index method of your new MarketingSalesController.
    // The full URL will be: http://your-app-url/marketing-sales
    $routes->get('marketing-sales', 'MarketingSalesController::index', ['as' => 'marketing-sales-report']);
    // --- NEW ROUTE END ---


    $routes->group('/', function ($routes) {
        $routes->GET('company-settings', 'CompanySettingsController::index');
        $routes->POST('company-settings/upload-image', 'CompanySettingsController::uploadImage');
        $routes->POST('company-settings/delete-image/(:any)', 'CompanySettingsController::deleteImage/$1');
    });

});
