<?php
// Check if user is admin - do this BEFORE including header.php
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/classes/Order.class.php';

// Handle form submissions and redirects BEFORE including header.php to avoid output
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
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

    $order = new Order();
    $order_id = intval($_POST['id'] ?? 0);
    
    if (!$order_id) {
        header('Location: orders.php');
        exit();
    }

    $status = sanitizeInput($_POST['status']);
    
    if ($order->updateStatus($order_id, $status)) {
        setFlashMessage('success', 'Order status updated successfully!');
        header('Location: order-details.php?id=' . $order_id);
        exit();
    } else {
        setFlashMessage('error', 'Failed to update order status');
    }
}

// Handle print request
$print_mode = false;
if (isset($_GET['print'])) {
    $print_mode = true;
}

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

$page_title = 'Order Details - Admin Panel';
$page_description = 'View detailed information about a specific order.';

// Only include header if not in print mode
if (!$print_mode) {
    require_once '../includes/header.php';
}

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

$order = new Order();

// Get order ID from URL
$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Get order details
$order_details = $order->getById($order_id);
$order_items = $order->getItems($order_id);

if (!$order_details) {
    setFlashMessage('error', 'Order not found');
    header('Location: orders.php');
    exit();
}

$status_options = $order->getStatusOptions();
?>

<?php if (!$print_mode): ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Order Details</h1>
                <div>
                    <a href="order-details.php?id=<?php echo $order_id; ?>&print=1" target="_blank" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-2"></i>Print
                    </a>
                    <a href="orders.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Order Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order #<?php echo $order_details['id']; ?></h5>
                            <p class="mb-1">Date: <?php echo formatDate($order_details['created_at']); ?></p>
                            <p class="mb-1">Status: 
                                <span class="badge bg-<?php echo $order_details['status'] === 'completed' ? 'success' : ($order_details['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                    <?php echo ucfirst($order_details['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5>Customer</h5>
                            <p class="mb-1"><?php echo htmlspecialchars($order_details['first_name'] . ' ' . $order_details['last_name']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order_details['email']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Shipping Address</h5>
                            <p class="mb-1"><?php echo htmlspecialchars($order_details['shipping_address']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order_details['shipping_city']); ?>, <?php echo htmlspecialchars($order_details['shipping_state']); ?> <?php echo htmlspecialchars($order_details['shipping_zip']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Order Items</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td><?php echo formatPrice($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Status</h5>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $order_id; ?>">
                                <div class="input-group">
                                    <select name="status" class="form-select">
                                        <?php foreach ($status_options as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" 
                                                    <?php echo $order_details['status'] === $value ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h5>Order Summary</h5>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td>Subtotal</td>
                                        <td class="text-end"><?php echo formatPrice($order_details['subtotal']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax (<?php echo (TAX_RATE * 100); ?>%)</td>
                                        <td class="text-end"><?php echo formatPrice($order_details['tax']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping</td>
                                        <td class="text-end"><?php echo formatPrice($order_details['shipping']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td class="text-end"><strong><?php echo formatPrice($order_details['total']); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Print View -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_details['id']; ?> - Print</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
        body {
            font-size: 14px;
        }
        .table th, .table td {
            padding: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2>Order Invoice</h2>
                <p class="no-print">
                    <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
                    <a href="order-details.php?id=<?php echo $order_id; ?>" class="btn btn-secondary">Back to Order</a>
                </p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Order #<?php echo $order_details['id']; ?></h5>
                <p class="mb-1">Date: <?php echo formatDate($order_details['created_at']); ?></p>
                <p class="mb-1">Status: <?php echo ucfirst($order_details['status']); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h5>Customer</h5>
                <p class="mb-1"><?php echo htmlspecialchars($order_details['first_name'] . ' ' . $order_details['last_name']); ?></p>
                <p class="mb-1"><?php echo htmlspecialchars($order_details['email']); ?></p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <h5>Shipping Address</h5>
                <p class="mb-1"><?php echo htmlspecialchars($order_details['shipping_address']); ?></p>
                <p class="mb-1"><?php echo htmlspecialchars($order_details['shipping_city']); ?>, <?php echo htmlspecialchars($order_details['shipping_state']); ?> <?php echo htmlspecialchars($order_details['shipping_zip']); ?></p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <h5>Order Items</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo formatPrice($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-end"><?php echo formatPrice($order_details['subtotal']); ?></td>
                        </tr>
                        <tr>
                            <td>Tax (<?php echo (TAX_RATE * 100); ?>%)</td>
                            <td class="text-end"><?php echo formatPrice($order_details['tax']); ?></td>
                        </tr>
                        <tr>
                            <td>Shipping</td>
                            <td class="text-end"><?php echo formatPrice($order_details['shipping']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td class="text-end"><strong><?php echo formatPrice($order_details['total']); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>

<?php
if (!$print_mode) {
    require_once '../includes/footer.php';
}
?>