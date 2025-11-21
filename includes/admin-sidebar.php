<?php
// Admin Sidebar Navigation
require_once dirname(__FILE__) . '/classes/Order.class.php';

// Function to generate the sidebar HTML
function generateAdminSidebar() {
    $current_page = basename($_SERVER['PHP_SELF']);
    $pending_count = 0;
    
    // Only show pending orders count if not processing a form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // Get pending orders count
        $order = new Order();
        $pending_count = $order->count(['status' => 'pending']);
    }
    
    $sidebar_html = '
    <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" id="sidebarMenu">
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link ' . ($current_page == 'dashboard.php' ? 'active' : '') . '" 
                       href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($current_page == 'orders.php' ? 'active' : '') . '" 
                       href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Orders';
    
    if ($pending_count > 0) {
        $sidebar_html .= '<span class="badge bg-danger ms-2">' . $pending_count . '</span>';
    }
    
    $sidebar_html .= '
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($current_page == 'products.php' ? 'active' : '') . '" 
                       href="products.php">
                        <i class="fas fa-box me-2"></i>
                        Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($current_page == 'categories.php' ? 'active' : '') . '" 
                       href="categories.php">
                        <i class="fas fa-tags me-2"></i>
                        Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($current_page == 'users.php' ? 'active' : '') . '" 
                       href="users.php">
                        <i class="fas fa-users me-2"></i>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($current_page == 'sizes.php' ? 'active' : '') . '" 
                       href="sizes.php">
                        <i class="fas fa-ruler-combined me-2"></i>
                        Product Sizes
                    </a>
                </li>
            </ul>
            
            <h6 class="sidebar-heading">
                <span>Reports</span>
            </h6>
            <ul class="nav flex-column mb-2">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-chart-bar me-2"></i>
                        Sales Report
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-file-invoice me-2"></i>
                        Product Report
                    </a>
                </li>
            </ul>
            
            <h6 class="sidebar-heading">
                <span>Settings</span>
            </h6>
            <ul class="nav flex-column mb-2">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cog me-2"></i>
                        Site Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>';
    
    return $sidebar_html;
}
// No closing PHP tag to prevent output issues