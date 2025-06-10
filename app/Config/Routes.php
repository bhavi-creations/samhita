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
$routes->get('products', 'Products::index');
$routes->get('stock/incoming', 'Stock::incoming');
$routes->get('stock/outgoing', 'Stock::outgoing');





// UNITS 

$routes->get('units', 'Units::index');
$routes->get('units/create', 'Units::create');
$routes->post('units/store', 'Units::store');
$routes->get('units/edit/(:num)', 'Units::edit/$1');
$routes->post('units/update/(:num)', 'Units::update/$1');
$routes->post('units/delete/(:num)', 'Units::delete/$1');





// PRODUCTS 

$routes->get('products', 'Products::index');
$routes->get('products/create', 'Products::create');
$routes->post('products/store', 'Products::store');
$routes->get('products/edit/(:segment)', 'Products::edit/$1');
$routes->post('products/update/(:segment)', 'Products::update/$1');
$routes->get('products/delete/(:segment)', 'Products::delete/$1');



// STOCK_IN 

$routes->get('stock-in', 'StockIn::index');
$routes->get('stock-in/create', 'StockIn::create');
$routes->post('stock-in/store', 'StockIn::store');




// Marketing Distribution


$routes->get('marketing-distribution', 'MarketingDistribution::index');
$routes->get('marketing-distribution/create', 'MarketingDistribution::create');
$routes->post('marketing-distribution/store', 'MarketingDistribution::store');
$routes->get('marketing-distribution/edit/(:num)', 'MarketingDistribution::edit/$1');
$routes->post('marketing-distribution/update/(:num)', 'MarketingDistribution::update/$1');
$routes->get('marketing-distribution/delete/(:num)', 'MarketingDistribution::delete/$1');





// Marketing Persons


$routes->get('marketing-persons', 'MarketingPersons::index');
$routes->get('marketing-persons/create', 'MarketingPersons::create');
$routes->post('marketing-persons/store', 'MarketingPersons::store');
$routes->get('marketing-persons/edit/(:num)', 'MarketingPersons::edit/$1');
$routes->post('marketing-persons/update/(:num)', 'MarketingPersons::update/$1');
$routes->get('marketing-persons/delete/(:num)', 'MarketingPersons::delete/$1');




// ... other routes you might have ...

// SALES ROUTES
$routes->get('sales', 'Sales::index');
$routes->get('sales/create', 'Sales::create');
// $routes->post('sales/store', 'Sales::store'); // REMOVE this if you're not using a Sales::store() method anymore
$routes->post('sales/store-multiple', 'Sales::storeMultiple');
$routes->get('sales/edit/(:num)', 'Sales::edit/$1');
$routes->get('sales/view/(:num)', 'Sales::view/$1');
$routes->post('sales/update/(:num)', 'Sales::update/$1');
$routes->get('sales/delete/(:num)', 'Sales::delete/$1');
// $routes->get('sales/remaining-stock', 'Sales::getRemainingStock'); // REMOVE this if you're not using a Sales::getRemainingStock() method anymore
$routes->get('sales/product-details', 'Sales::productDetails');
// Export Routes
$routes->get('sales/export-person-sales-excel/(:num)', 'Sales::exportPersonSalesExcel/$1');
$routes->get('sales/export-person-sales-pdf/(:num)', 'Sales::exportPersonSalesPDF/$1');

$routes->get('sales/export-excel', 'Sales::exportExcel');
$routes->get('sales/export-pdf', 'Sales::exportPDF');

// ... rest of your routes ...

// REPORTS 

$routes->get('reports/person-stock', 'Reports::marketingPersonStock');


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
