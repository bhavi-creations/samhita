<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="<?= base_url('/') ?>" class="brand-link">
            <img src="<?= base_url('assets/img/credit/samhita logo.jpg') ?>" alt="Samhita Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text font-weight-light">Samhita </span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-header">MAIN</li>
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Stock Management -->
                <li class="nav-header">STOCK MANAGEMENT</li>
                <li class="nav-item mt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Product Pricing</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('products') ?>" class="nav-link <?= uri_string() == 'products' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-box"></i>
                        <p> Products List</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('products/stock-overview') ?>">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>Available Stock</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('products/manage-prices') ?>">

                        <i class=" nav-icon fa-solid fa-indian-rupee-sign"></i>
                        <p> Manage Prices </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('vendors') ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Vendors</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('vendors/vendorReport') ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Vendor Report</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('units') ?>" class="nav-link <?= uri_string() == 'units' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cubes"></i>
                        <p>Units</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('stock-in') ?>" class="nav-link <?= uri_string() == 'stock-in' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-arrow-down"></i>
                        <p>Stock In</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('stock-out') ?>">
                        <i class="nav-icon fas fa-truck-moving"></i>
                        <p> Stock Out </p>
                    </a>
                </li>
                <!-- Tax Settings -->
                <li class="nav-header">TAX SETTINGS</li>
                <li class="nav-item">
                    <a href="<?= base_url('gst-rates') ?>" class="nav-link <?= uri_string() == 'gst-rates' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-percent"></i>
                        <p>GST Rates</p>
                    </a>
                </li>

                <!-- Sales -->
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

                <!-- Marketing -->
                <li class="nav-header">MARKETING</li>
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

                <!-- Reports -->
                <li class="nav-header">REPORTS</li>
                <li class="nav-item">
                    <a href="<?= base_url('reports/person-stock') ?>" class="nav-link <?= uri_string() == 'reports/person-stock' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Person Stock Report</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>