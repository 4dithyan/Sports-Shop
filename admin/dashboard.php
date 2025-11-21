<?php
// Check if user is admin - do this BEFORE including header.php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Admin Dashboard - Cosmos Sports';
$page_description = 'Admin dashboard with key metrics and management tools.';

require_once '../includes/header.php';
require_once '../includes/classes/Product.class.php';
require_once '../includes/classes/User.class.php';
require_once '../includes/classes/Order.class.php';
require_once '../includes/classes/Category.class.php';

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

$product = new Product();
$user = new User();
$order = new Order();
$category = new Category();

// Get statistics
$stats = $order->getStats();
$recent_orders = $order->getRecent(5);
$featured_products = $product->getFeatured(5);
$recent_users = $user->getAll(5, 0);

// Additional statistics
$total_products = $product->count();
$total_categories = $category->count();
$total_users_count = $user->count();
$total_orders_count = $order->count();

// Get sales data for the last 7 days
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $sql = "SELECT SUM(total) as daily_sales, COUNT(*) as daily_orders 
            FROM orders 
            WHERE DATE(created_at) = ? AND status IN ('completed', 'shipped', 'delivered')";
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$date]);
    $result = $stmt->fetch();
    $sales_data[] = [
        'date' => $date,
        'sales' => $result['daily_sales'] ?? 0,
        'orders' => $result['daily_orders'] ?? 0
    ];
}

// Get top selling products
$sql = "SELECT p.name, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('completed', 'shipped', 'delivered')
        GROUP BY p.id, p.name
        ORDER BY total_sold DESC
        LIMIT 5";
$stmt = getDB()->prepare($sql);
$stmt->execute();
$top_products = $stmt->fetchAll();

// Get user registration data for the last 7 days
$user_reg_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $sql = "SELECT COUNT(*) as new_users FROM users WHERE DATE(created_at) = ?";
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$date]);
    $result = $stmt->fetch();
    $user_reg_data[] = [
        'date' => $date,
        'users' => $result['new_users'] ?? 0
    ];
}
?>

<div class="container-fluid py-4">
    <!-- Dashboard Header -->
    <div class="dashboard-header animate__fadeInUp">
        <h1>Dashboard Overview</h1>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>. Here's what's happening today.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4 animate__fadeInUp" style="animation-delay: 0.1s">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-primary text-white">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-title">Total Orders</div>
                    <div class="text-success small mt-2">
                        <i class="fas fa-arrow-up me-1"></i>
                        <?php echo number_format($stats['orders_this_month']); ?> this month
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4 animate__fadeInUp" style="animation-delay: 0.2s">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-success text-white">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></div>
                    <div class="stat-title">Total Revenue</div>
                    <div class="text-success small mt-2">
                        <i class="fas fa-arrow-up me-1"></i>
                        <?php echo formatPrice($stats['revenue_this_month']); ?> this month
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4 animate__fadeInUp" style="animation-delay: 0.3s">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-info text-white">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['pending_orders']); ?></div>
                    <div class="stat-title">Pending Orders</div>
                    <div class="text-warning small mt-2">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Requires attention
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4 animate__fadeInUp" style="animation-delay: 0.4s">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-warning text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($total_users_count); ?></div>
                    <div class="stat-title">Total Users</div>
                    <div class="text-primary small mt-2">
                        <i class="fas fa-arrow-up me-1"></i>
                        <?php echo number_format(count(array_filter($user_reg_data, function($item) { return $item['users'] > 0; }))); ?> active days
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-6 mb-4 animate__fadeInUp" style="animation-delay: 0.5s">
            <div class="chart-container">
                <div class="chart-header">
                    <h6>Sales Overview (Last 7 Days)</h6>
                </div>
                <div class="chart-area">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- User Registration Chart -->
        <div class="col-lg-6 mb-4 animate__fadeInUp" style="animation-delay: 0.6s">
            <div class="chart-container">
                <div class="chart-header">
                    <h6>User Registrations (Last 7 Days)</h6>
                </div>
                <div class="chart-area">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Quick Tips -->
        <div class="col-12 mb-4 animate__fadeInUp" style="animation-delay: 0.7s">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Admin Quick Tips</h6>
                <ul class="mb-0">
                    <li>After adding a new product, you'll be automatically redirected to manage its sizes.</li>
                    <li>Use the <i class="fas fa-ruler-combined"></i> icon in the products list to quickly manage sizes for existing products.</li>
                    <li>Remember to set stock quantities for each size to ensure accurate inventory tracking.</li>
                </ul>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4 animate__fadeInUp" style="animation-delay: 0.8s">
            <div class="table-container">
                <div class="table-header">
                    <h6>Recent Orders</h6>
                    <a href="orders.php" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="table-responsive">
                    <?php if (!empty($recent_orders)): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order_item): ?>
                                    <tr>
                                        <td>#<?php echo $order_item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order_item['first_name'] . ' ' . $order_item['last_name']); ?></td>
                                        <td><?php echo formatDate($order_item['created_at']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order_item['status'] === 'completed' ? 'success' : 
                                                   ($order_item['status'] === 'pending' ? 'warning' : 
                                                   ($order_item['status'] === 'cancelled' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($order_item['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatPrice($order_item['total']); ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order_item['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No orders found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions & Recent Users -->
        <div class="col-lg-4 animate__fadeInUp" style="animation-delay: 0.8s">
            <!-- Quick Actions -->
            <div class="table-container mb-4">
                <div class="table-header">
                    <h6>Quick Actions</h6>
                </div>
                <div class="quick-actions">
                    <div class="d-grid gap-2">
                        <a href="products.php?action=add" class="btn btn-outline-primary">
                            <i class="fas fa-plus-circle"></i> Add Product
                        </a>
                        <a href="orders.php" class="btn btn-outline-success">
                            <i class="fas fa-shopping-cart"></i> Manage Orders
                        </a>
                        <a href="users.php" class="btn btn-outline-info">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <a href="categories.php" class="btn btn-outline-warning">
                            <i class="fas fa-tags"></i> Manage Categories
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Users -->
            <div class="table-container">
                <div class="table-header">
                    <h6>Recent Users</h6>
                </div>
                <div class="recent-users">
                    <?php if (!empty($recent_users)): ?>
                        <?php foreach ($recent_users as $user_item): ?>
                            <div class="user-item d-flex align-items-center">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($user_item['first_name'] . ' ' . $user_item['last_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($user_item['email']); ?></small>
                                </div>
                                <small class="text-muted"><?php echo formatDate($user_item['created_at'], 'd M'); ?></small>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="users.php" class="btn btn-outline-primary btn-sm">View All Users</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted">No users found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Featured Products & Top Selling Products -->
    <div class="row mb-4">
        <!-- Featured Products -->
        <div class="col-lg-6 animate__fadeInUp" style="animation-delay: 0.9s">
            <div class="table-container mb-4">
                <div class="table-header">
                    <h6>Featured Products</h6>
                    <a href="products.php" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($featured_products)): ?>
                        <div class="row">
                            <?php foreach ($featured_products as $product_item): ?>
                                <div class="col-lg-6 mb-4">
                                    <div class="product-card h-100">
                                        <img src="<?php echo $product->getImageUrl($product_item['image'], $product_item['id']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($product_item['name']); ?>"
                                             style="height: 150px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title"><?php echo htmlspecialchars($product_item['name']); ?></h6>
                                            <p class="card-text text-muted small">
                                                <?php echo htmlspecialchars(substr($product_item['description'], 0, 60)) . '...'; ?>
                                            </p>
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold"><?php echo formatPrice($product_item['price']); ?></span>
                                                    <span class="badge bg-<?php echo $product_item['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($product_item['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted">No featured products found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top Selling Products -->
        <div class="col-lg-6 animate__fadeInUp" style="animation-delay: 1.0s">
            <div class="table-container">
                <div class="table-header">
                    <h6>Top Selling Products</h6>
                </div>
                <div class="table-responsive">
                    <?php if (!empty($top_products)): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Units Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><?php echo number_format($product['total_sold']); ?></span>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" 
                                                         role="progressbar" 
                                                         style="width: <?php echo min(100, ($product['total_sold'] / max(array_column($top_products, 'total_sold')) * 100)); ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted">No sales data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
var salesCtx = document.getElementById("salesChart").getContext("2d");
var salesChart = new Chart(salesCtx, {
    type: "line",
    data: {
        labels: ' . json_encode(array_map(function($item) { return date('d M', strtotime($item['date'])); }, $sales_data)) . ',
        datasets: [{
            label: "Sales (₹)",
            data: ' . json_encode(array_column($sales_data, 'sales')) . ',
            borderColor: "#4361ee",
            backgroundColor: "rgba(67, 97, 238, 0.1)",
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: "#fff",
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7
        }, {
            label: "Orders",
            data: ' . json_encode(array_column($sales_data, 'orders')) . ',
            borderColor: "#4cc9f0",
            backgroundColor: "rgba(76, 201, 240, 0.1)",
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: "#fff",
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7,
            yAxisID: "y1"
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "top",
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: "Sales (₹)"
                },
                grid: {
                    color: "rgba(0, 0, 0, 0.05)"
                }
            },
            y1: {
                beginAtZero: true,
                position: "right",
                title: {
                    display: true,
                    text: "Orders"
                },
                grid: {
                    drawOnChartArea: false
                }
            },
            x: {
                grid: {
                    color: "rgba(0, 0, 0, 0.05)"
                }
            }
        }
    }
});

// User Registration Chart
var userCtx = document.getElementById("userChart").getContext("2d");
var userChart = new Chart(userCtx, {
    type: "bar",
    data: {
        labels: ' . json_encode(array_map(function($item) { return date('d M', strtotime($item['date'])); }, $user_reg_data)) . ',
        datasets: [{
            label: "New Users",
            data: ' . json_encode(array_column($user_reg_data, 'users')) . ',
            backgroundColor: "rgba(67, 97, 238, 0.7)",
            borderColor: "#4361ee",
            borderWidth: 1,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "top",
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: "Number of Users"
                },
                grid: {
                    color: "rgba(0, 0, 0, 0.05)"
                }
            },
            x: {
                grid: {
                    color: "rgba(0, 0, 0, 0.05)"
                }
            }
        }
    }
});

// Add animation to stat cards on hover
document.querySelectorAll(".stat-card").forEach(card => {
    card.addEventListener("mouseenter", function() {
        this.style.transform = "translateY(-5px)";
    });
    
    card.addEventListener("mouseleave", function() {
        this.style.transform = "translateY(0)";
    });
});
</script>
';

require_once '../includes/footer.php'; ?>