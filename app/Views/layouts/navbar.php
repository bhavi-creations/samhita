<!-- Navbar -->
<nav class="app-header navbar navbar-expand bg-body">
    <!--begin::Container-->
    <div class="container-fluid">
        <!-- Start navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>
        <!-- End navbar links -->

        <ul class="navbar-nav ms-auto">
            <!-- Navbar Search (Keep commented out if not in use) -->
            <!-- <li class="nav-item">
                <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="bi bi-search"></i>
                </a>
            </li> -->

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <!-- User Avatar Icon -->
                    <i class="bi bi-person-circle fs-3 me-2"></i> <!-- Larger person circle icon -->
                    
                    <!-- Display Username if logged in -->
                    <span class="d-none d-md-inline">
                        <?php 
                        // Access the session to get the username
                        $session = \Config\Services::session();
                        echo esc($session->get('username') ?? 'Guest'); 
                        ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!-- User image in dropdown header -->
                    <li class="user-header text-bg-primary">
                        <i class="bi bi-person-circle fs-1" style="font-size: 5rem;"></i> <!-- Larger icon in header -->
                        <p>
                            <?php 
                            // Display Username and Role if available
                            echo esc($session->get('username') ?? 'Guest');
                            if ($session->has('role')) {
                                echo ' - ' . esc(ucfirst($session->get('role')));
                            }
                            ?>
                            <small>Member since 
                                <?php 
                                // You might need to fetch the actual user creation date from the DB
                                // For now, a placeholder or skip this line if date isn't readily available in session
                                // echo 'Nov. 2023'; 
                                ?>
                            </small>
                        </p>
                    </li>
                   
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <!-- <a href="#" class="btn btn-default btn-flat">Profile</a> -->
                        <!-- Logout Button -->
                        <a href="<?= base_url('logout') ?>" class="btn btn-default btn-flat float-end">Sign out</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <!--end::Container-->
</nav>
<!-- /.navbar -->
