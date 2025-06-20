<?php

use App\Controllers\MarketingDistribution;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->setAutoRoute(true); // <-- This must be true
$routes->get('/', 'Home::index');

// Add these lines if needed:

// $routes->get('welcome', 'Welcome::index');
// $routes->get('welcome/test/(:segment)', 'Welcome::test/$1');
// $routes->get('blog', 'Blog::index');






$routes->get('dashboard', 'Dashboard::index');






// UNITS 

$routes->get('units', 'Units::index');
$routes->get('units/create', 'Units::create');
$routes->post('units/store', 'Units::store');
$routes->get('units/edit/(:num)', 'Units::edit/$1');
$routes->post('units/update/(:num)', 'Units::update/$1');
$routes->post('units/delete/(:num)', 'Units::delete/$1');



// In app/Config/Routes.php
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


// --- Stock Out Routes ---
$routes->group('stock-out', function ($routes) {
    // List Stock Out Records (with filters)
    $routes->get('/', 'StockOut::index');
    // Display form for issuing new stock out
    $routes->get('issue', 'StockOut::issue');
    // Handle submission for storing new stock out
    $routes->post('store', 'StockOut::store');
    // Display details of a single stock out record
    $routes->get('view/(:num)', 'StockOut::view/$1'); // :num ensures it's a number ID
    // Export Stock Out records to Excel (CSV for now)
    $routes->get('export-excel', 'StockOut::exportExcel');
    // Export Stock Out records to PDF
    $routes->get('export-pdf', 'StockOut::exportPdf');
});
// STOCK_IN 



$routes->group('stock-in', function ($routes) {
    $routes->get('/', 'StockIn::index'); // Display all stock in entries
    $routes->get('create', 'StockIn::create'); // Show the form to add new stock
    $routes->post('store', 'StockIn::store'); // Process the form submission for new stock

    // Routes for View, Edit, and Delete actions
    $routes->get('view/(:num)', 'StockIn::view/$1');    // Show details of a specific entry
    $routes->get('edit/(:num)', 'StockIn::edit/$1');    // Show the form to edit an entry
    $routes->post('update/(:num)', 'StockIn::update/$1'); // Process the form submission for updating an entry (can be PUT, but POST is common for forms)
    $routes->delete('delete/(:num)', 'StockIn::delete/$1'); // Handle deletion of an entry


    $routes->get('export-excel/(:num)', 'StockIn::exportToExcel/$1');
    $routes->get('export-pdf/(:num)', 'StockIn::exportToPdf/$1');


    $routes->post('payment/store', 'StockIn::storePayment'); // For adding new payments
    $routes->get('payment/edit/(:num)', 'StockIn::editPayment/$1'); // For displaying edit payment form
    $routes->put('payment/update/(:num)', 'StockIn::updatePayment/$1'); // For updating payment
    $routes->delete('payment/delete/(:num)', 'StockIn::deletePayment/$1');
    $routes->post('add-payment/(:num)', 'StockIn::addPayment/$1');
});





$routes->get('api/products/available-stock/(:num)', 'Products::getAvailableStock/$1');
$routes->put('marketing-distribution/update/(:num)', 'MarketingDistribution::update/$1');

// Marketing Distribution


$routes->get('marketing-distribution', 'MarketingDistribution::index');
$routes->get('marketing-distribution/create', 'MarketingDistribution::create');
$routes->post('marketing-distribution/store', 'MarketingDistribution::store');
$routes->get('marketing-distribution/edit/(:num)', 'MarketingDistribution::edit/$1');
// $routes->post('marketing-distribution/update/(:num)', 'MarketingDistribution::update/$1');
$routes->put('marketing-distribution/update/(:num)', 'MarketingDistribution::update/$1');
$routes->get('marketing-distribution/delete/(:num)', 'MarketingDistribution::delete/$1');





// Marketing Persons


// In app/Config/Routes.php

$routes->group('marketing-persons', function ($routes) {
    $routes->get('/', 'MarketingPersons::index');
    $routes->get('create', 'MarketingPersons::create');
    $routes->post('store', 'MarketingPersons::store');
    $routes->get('edit/(:num)', 'MarketingPersons::edit/$1');
    $routes->put('update/(:num)', 'MarketingPersons::update/$1'); // Use PUT for update
    $routes->delete('delete/(:num)', 'MarketingPersons::delete/$1'); // Use DELETE for delete

    // New routes for Marketing Persons
    $routes->get('view/(:num)', 'MarketingPersons::view/$1'); // New view route
    $routes->get('export-all-excel', 'MarketingPersons::exportAllExcel'); // Export all Excel
    $routes->get('export-all-pdf', 'MarketingPersons::exportAllPdf');     // Export all PDF
    $routes->get('export-excel/(:num)', 'MarketingPersons::exportExcel/$1'); // Export single Excel
    $routes->get('export-pdf/(:num)', 'MarketingPersons::exportPdf/$1');     // Export single PDF
});


// ... other routes you might have ...

// SALES ROUTES




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
    // $routes->get('product-details/(:num)', 'Sales::getProductDetails/$1'); // Remove or comment this if getRemainingStock replaces it
});


// REPORTS 

$routes->get('reports/person-stock', 'Reports::marketingPersonStock');


// GST Rates Management

$routes->group('gst-rates', function ($routes) {
    $routes->get('/', 'GstRates::index');
    $routes->get('create', 'GstRates::create');
    $routes->post('store', 'GstRates::store');
    $routes->get('edit/(:num)', 'GstRates::edit/$1');
    $routes->put('update/(:num)', 'GstRates::update/$1'); // Using POST for update, will use _method PUT in form
    $routes->get('delete/(:num)', 'GstRates::delete/$1'); // Simple GET delete for now
});


// Vendoers 

$routes->get('vendors', 'Vendors::index');
$routes->get('vendors/create', 'Vendors::create');
$routes->post('vendors/store', 'Vendors::store');
$routes->get('vendors/edit/(:num)', 'Vendors::edit/$1');
$routes->post('vendors/update/(:num)', 'Vendors::update/$1');
$routes->get('vendors/delete/(:num)', 'Vendors::delete/$1');
$routes->get('vendors/vendorReport', 'Vendors::vendorReport');
$routes->get('vendors/vendorReportExport', 'Vendors::vendorReportExport');
$routes->get('reports/vendor-report-pdf', 'Vendors::vendorReportPDF');




// Distributor


$routes->group('distributors', function ($routes) {
    $routes->get('/', 'Distributor::index'); // List all distributors
    $routes->get('add', 'Distributor::add'); // Show add form
    $routes->post('store', 'Distributor::store'); // Handle add form submission
    $routes->get('view/(:num)', 'Distributor::view/$1'); // View details of a specific distributor by ID
    $routes->get('edit/(:num)', 'Distributor::edit/$1'); // Show edit form for a specific distributor by ID
    $routes->post('update/(:num)', 'Distributor::update/$1'); // Handle edit form submission for a specific distributor by ID
    $routes->get('delete/(:num)', 'Distributor::delete/$1'); // Delete a specific distributor by ID
    $routes->get('exportExcel', 'Distributor::exportExcel');
    $routes->get('exportPdf', 'Distributor::exportPdf');

    $routes->get('exportSingleExcel/(:num)', 'Distributor::exportSingleExcel/$1');
    $routes->get('exportSinglePdf/(:num)', 'Distributor::exportSinglePdf/$1');
});




// Distributor sales



// app/Config/Routes.php

$routes->group('distributor-sales', function ($routes) {
    // ... existing routes ...

    // List all sales orders
    $routes->get('/', 'DistributorSalesController::index', ['as' => 'distributor-sales']);

    // Show form to create a new sales order
    $routes->get('new', 'DistributorSalesController::create', ['as' => 'distributor-sales-new']);

    // Handle form submission for creating a new sales order
    $routes->post('save', 'DistributorSalesController::store', ['as' => 'distributor-sales-store']);

    // Show details of a specific sales order
    $routes->get('show/(:num)', 'DistributorSalesController::show/$1', ['as' => 'distributor-sales-show']);

    // Show form to add a payment for a sales order
    $routes->get('add-payment/(:num)', 'DistributorSalesController::addPayment/$1', ['as' => 'distributor-sales-add-payment']);

    // Handle payment form submission
    $routes->post('save-payment', 'DistributorSalesController::savePayment', ['as' => 'distributor-sales-save-payment']);

    $routes->get('edit/(:num)', 'DistributorSalesController::edit/$1', ['as' => 'distributor-sales-edit']);
    $routes->put('update/(:num)', 'DistributorSalesController::update/$1', ['as' => 'distributor-sales-update']);

    // Existing DELETE route
    $routes->delete('delete/(:num)', 'DistributorSalesController::delete/$1', ['as' => 'distributor-sales-delete']);

    // --- ADD THESE NEW ROUTES FOR EXPORT ---
    $routes->get('export/pdf-index', 'DistributorSalesController::exportIndexPdf', ['as' => 'distributor-sales-export-pdf-index']);
    $routes->get('export/excel-index', 'DistributorSalesController::exportIndexExcel', ['as' => 'distributor-sales-export-excel-index']);
    // --- END NEW ROUTES ---

       // Export individual invoice to PDF
    $routes->get('export/invoice-pdf/(:num)', 'DistributorSalesController::exportInvoicePdf/$1', ['as' => 'distributor-sales-export-invoice-pdf']);

    // --- NEW ROUTE FOR EXCEL EXPORT OF SINGLE INVOICE ---
    $routes->get('export/invoice-excel/(:num)', 'DistributorSalesController::exportInvoiceExcel/$1', ['as' => 'distributor-sales-export-invoice-excel']);
    // --- END NEW ROUTE ---
});
