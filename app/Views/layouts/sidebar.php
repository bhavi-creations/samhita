<aside class="app-sidebar side_bg  shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="<?= base_url('/') ?>" class="brand-link">
            <img src="<?= base_url('assets/img/credit/samhita logo.jpg') ?>" alt="Samhita Logo" class="brand-image  " />
            <span class="brand-text  ">Samhita Soil Solutions </span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">






                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>




                <?php
                // Determine if any page under 'distributor-sales' or 'distributors' is active
                $isDistributorSalesActive = url_is('distributor-sales*') || url_is('distributors*');
                ?>
                <li class="nav-item <?= $isDistributorSalesActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isDistributorSalesActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-truck-loading"></i>
                        <p>
                            Distributor Sales
                            <i class="nav-arrow fas fa-chevron-right"></i> <!-- Changed from bi bi-chevron-right -->
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('distributors') ?>" class="nav-link <?= uri_string() == 'distributors' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Distributors</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('distributor-sales') ?>" class="nav-link <?= uri_string() == 'distributor-sales' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-truck-flatbed"></i>

                                <p>All Sales Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('distributor-sales/new') ?>" class="nav-link <?= uri_string() == 'distributor-sales/new' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-truck-flatbed"></i>

                                <p>Create New Order</p>
                            </a>
                        </li>
                    </ul>
                </li>


                <?php
                // Determine if any page under 'vendors' is active
                $isVendorsActive = url_is('vendors') || url_is('vendors/*');
                ?>
                <li class="nav-item <?= $isVendorsActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isVendorsActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-handshake"></i> <!-- Icon for the Vendors parent menu -->
                        <p>
                            Vendors & Reports
                            <i class="nav-arrow fas fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('vendors') ?>" class="nav-link <?= uri_string() == 'vendors' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Vendors</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('vendors/vendorReport') ?>" class="nav-link <?= uri_string() == 'vendors/vendorReport' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Vendor Report</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php
                // Determine if any page under 'gst-rates', 'units', or 'products' is active
                $isProductManagementActive = url_is('gst-rates*') || url_is('units*') || url_is('products*');
                ?>
                <li class="nav-item <?= $isProductManagementActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isProductManagementActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-boxes"></i> <!-- Icon for the Product Management parent menu -->
                        <p>
                            Product Management
                            <i class="nav-arrow fas fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-header">UNITS & GST</li> <!-- Moved inside the treeview -->

                        <li class="nav-item">
                            <a href="<?= base_url('gst-rates') ?>" class="nav-link <?= uri_string() == 'gst-rates' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-percent"></i> <!-- Changed from bi bi-percent to fas fa-percent -->
                                <p>GST Rates</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('units') ?>" class="nav-link <?= uri_string() == 'units' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-cubes"></i>
                                <p>Units</p>
                            </a>
                        </li>
                        <li class="nav-header">Product Details</li> <!-- Moved inside the treeview -->
                        <li class="nav-item">
                            <a href="<?= base_url('products') ?>" class="nav-link <?= uri_string() == 'products' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-box"></i>
                                <p>Products List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('products/manage-prices') ?>" class="nav-link <?= uri_string() == 'products/manage-prices' ? 'active' : '' ?>">
                                <i class=" nav-icon fa-solid fa-indian-rupee-sign"></i>
                                <p> Manage Prices </p>
                            </a>
                        </li>
                    </ul>
                </li>







                <?php
                // Determine if any page under 'products/stock-overview', 'stock-in', or 'stock-out' is active
                $isStockManagementActive = url_is('products/stock-overview*') || url_is('stock-in*') || url_is('stock-out*');
                ?>
                <li class="nav-item <?= $isStockManagementActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isStockManagementActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-warehouse"></i> <!-- Icon for Stock Management parent menu -->
                        <p>
                            Stock Management
                            <i class="nav-arrow fas fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link <?= uri_string() == 'products/stock-overview' ? 'active' : '' ?>" href="<?= base_url('products/stock-overview') ?>">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Available Stock</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('stock-in') ?>" class="nav-link <?= uri_string() == 'stock-in' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-arrow-down"></i>
                                <p>Stock In</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= uri_string() == 'stock-out' ? 'active' : '' ?>" href="<?= base_url('stock-out') ?>">
                                <i class="nav-icon fas fa-truck-moving"></i>
                                <p> Stock Out </p>
                            </a>
                        </li>
                    </ul>
                </li>






                <?php
                $isMarketingSalesActive = url_is('marketing-persons*') || url_is('marketing-distribution*') || url_is('sales*') || url_is('reports/person-stock*');
                ?>
                <li class="nav-item <?= $isMarketingSalesActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isMarketingSalesActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Marketing & Sales
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-header">Marketing</li>

                        <li class="nav-item">
                            <a href="<?= base_url('marketing-persons') ?>" class="nav-link <?= uri_string() == 'marketing-persons' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Marketing Persons</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('marketing-distribution') ?>" class="nav-link <?= uri_string() == 'marketing-distribution' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-share-square"></i>
                                <p>Distribute Products</p>
                            </a>
                        </li>

                        <li class="nav-header">SALES</li>
                        <li class="nav-item">
                            <a href="<?= base_url('sales/create') ?>" class="nav-link <?= uri_string() == 'sales/create' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Add Sale</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('sales') ?>" class="nav-link <?= uri_string() == 'sales' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                <p>Sales List</p>
                            </a>
                        </li>

                        <li class="nav-header">REPORTS</li>
                        <li class="nav-item">
                            <a href="<?= base_url('reports/person-stock') ?>" class="nav-link <?= uri_string() == 'reports/person-stock' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Person Stock Report</p>
                            </a>
                        </li>
                    </ul>
                </li>










                <li class="nav-item">
                    <a href="<?= base_url('company-settings') ?>" class="nav-link <?= uri_string() == 'company-settings' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>Company Settings</p>
                    </a>
                </li>


            </ul>

        </nav>
    </div>
</aside>