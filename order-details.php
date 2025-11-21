<?php
// Check if user is logged in - do this BEFORE including header.php
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/classes/Order.class.php';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    $order = new Order();
    $order_id = intval($_POST['id'] ?? 0);
    
    if ($order_id) {
        // Cancel order (only if it belongs to the user and is in a cancellable state)
        $order_details = $order->getById($order_id);
        if ($order_details && $order_details['user_id'] == $_SESSION['user_id']) {
            // Only allow cancellation for pending or processing orders
            if (in_array($order_details['status'], ['pending', 'processing'])) {
                if ($order->cancel($order_id, $_SESSION['user_id'])) {
                    setFlashMessage('success', 'Order has been successfully cancelled.');
                } else {
                    setFlashMessage('error', 'Failed to cancel order. Please try again.');
                }
            } else {
                setFlashMessage('error', 'Order cannot be cancelled at this stage.');
            }
        } else {
            setFlashMessage('error', 'Order not found or access denied.');
        }
    }
    
    header('Location: order-details.php?id=' . $order_id);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Order Details - Cosmos Sports';
$page_description = 'View detailed information about your order.';

require_once 'includes/header.php';

$order = new Order();

// Get order ID from URL
$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: my-account.php');
    exit();
}

// Get order details
$order_details = $order->getById($order_id);
$order_items = $order->getItems($order_id);

// Check if order belongs to current user
if (!$order_details || $order_details['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('error', 'Order not found or access denied');
    header('Location: my-account.php');
    exit();
}

$status_options = $order->getStatusOptions();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="my-account.php">My Account</a></li>
                    <li class="breadcrumb-item active">Order #<?php echo $order_details['id']; ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Order Details</h1>
                <a href="my-account.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Account
                </a>
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
                            
                            <!-- Cancellation Button -->
                            <?php if (in_array($order_details['status'], ['pending', 'processing'])): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="id" value="<?php echo $order_id; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm mt-2">
                                        <i class="fas fa-times-circle me-1"></i>Cancel Order
                                    </button>
                                </form>
                            <?php endif; ?>
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
                        <div class="col-md-6 offset-md-6">
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
                                        <td class="text-end">
                                            <?php if ($order_details['shipping'] == 0): ?>
                                                <span class="text-success">FREE</span>
                                            <?php else: ?>
                                                <?php echo formatPrice($order_details['shipping']); ?>
                                            <?php endif; ?>
                                        </td>
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

<?php
require_once 'includes/footer.php';
?>