<?php
// Order confirmation page
require_once 'config.php';

$page_title = 'Order Confirmation - Cosmos Sports';
$page_description = 'Thank you for your order!';

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$order_id = (int)$_GET['id'];

require_once 'includes/header.php';
require_once 'includes/classes/Order.class.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // If admin is viewing the order, allow it
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit();
    }
}

$order = new Order();

// Get order details
$order_details = $order->getById($order_id);

// Check if order exists and belongs to the current user (unless admin)
if (!$order_details || 
    (!isset($_SESSION['admin_id']) && $order_details['user_id'] != $_SESSION['user_id'])) {
    setFlashMessage('error', 'Order not found or you do not have permission to view it.');
    header('Location: my-account.php');
    exit();
}

// Get order items
$order_items = $order->getItems($order_id);
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success fa-4x"></i>
            </div>
            <h1 class="mb-2">Thank You for Your Order!</h1>
            <p class="lead">Your order has been placed successfully.</p>
            <p>Order #<?php echo $order_details['id']; ?> | <?php echo formatDate($order_details['created_at']); ?></p>
            <p>A confirmation email has been sent to your email address.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <!-- Order Status -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shipping-fast fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Order Status: <span class="badge bg-<?php echo $order_details['status'] === 'completed' ? 'success' : ($order_details['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                    <?php echo ucfirst($order_details['status']); ?>
                                </span></h5>
                                <p class="mb-0">We'll notify you when your order ships.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Shipping Address</h5>
                            <p class="mb-1"><?php echo htmlspecialchars($order_details['shipping_address']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order_details['shipping_city']); ?>, <?php echo htmlspecialchars($order_details['shipping_state']); ?> <?php echo htmlspecialchars($order_details['shipping_zip']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Method</h5>
                            <p class="mb-1">Credit Card (Demo)</p>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <h5>Order Items</h5>
                    <div class="table-responsive mb-4">
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
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo getOnlineProductImageUrl($item['product_id'], $item['product_name']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                         class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img src="<?php echo $asset_path; ?>assets/images/placeholder.svg" 
                                                         alt="Product placeholder"
                                                         class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Order Totals -->
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
            
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary me-2">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
                <a href="my-account.php" class="btn btn-outline-secondary">
                    <i class="fas fa-user me-2"></i>My Account
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>