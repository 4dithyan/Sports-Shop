<?php

ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title : 'COSMOS - The Digital Universe of Peak Performance';
$page_description = isset($page_description) ? $page_description : 'COSMOS - The Digital Universe of Peak Performance. A clean, brightly-lit universe where the products are the stars.';

// Check if we're in the admin area
$is_admin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;

// Determine the correct path for assets
$asset_path = $is_admin ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?php echo $asset_path; ?>assets/css/style.css" rel="stylesheet">
    <?php if ($is_admin): ?>
    <link href="<?php echo $asset_path; ?>assets/css/admin-modern.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $asset_path; ?>assets/images/favicon.ico">
</head>
<body>
    <?php if ($is_admin): ?>
    <!-- Admin Header -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="dashboard.php">
            <i class="fas fa-infinity me-2"></i>COSMOS Admin
        </a>
        <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Sign out
                </a>
            </div>
        </div>
    </header>
    <?php else: ?>
    <!-- Frontend Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-infinity me-2"></i>COSMOS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Collections
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="products.php">All Products</a></li>
                            <li><a class="dropdown-item" href="categories.php">By Category</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">The Observatory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Search Bar -->
                    <li class="nav-item me-3">
                        <form class="d-flex" method="GET" action="products.php">
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="search" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button class="btn btn-outline-primary btn-sm" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </li>
                    
                    <!-- User Account -->
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-astronaut me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="my-account.php"><i class="fas fa-user me-2"></i>My Account</a></li>
                                <li><a class="dropdown-item" href="my-account.php#orders"><i class="fas fa-box me-2"></i>Recent Orders</a></li>
                                <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Cart -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                <?php echo getCartCount(); ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php echo displayFlashMessages(); ?>

    <!-- Main Content -->
    <main>
        <?php if ($is_admin): ?>
        <div class="container-fluid">
            <div class="row">
                <?php 
                require_once __DIR__ . '/admin-sidebar.php'; 
                echo generateAdminSidebar();
                ?>
                <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <?php endif; ?>