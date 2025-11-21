<?php
// Check if user is logged in - do this BEFORE including header.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Checkout - Cosmos Sports';
$page_description = 'Complete your purchase securely.';

require_once 'includes/header.php';
require_once 'includes/classes/Product.class.php';
require_once 'includes/classes/Order.class.php';
require_once 'includes/classes/User.class.php';

// Check if user is logged in (redundant now, but kept for consistency)
requireLogin();

$product = new Product();
$order = new Order();
$cart_items = [];
$cart_total = 0;

// Get cart items
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $db = getDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id IN (" . implode(',', array_fill(0, count($product_ids), '?')) . ") 
            AND p.status = 'active'";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();
    
    foreach ($products as $product_item) {
        // Find the matching cart item (could be with or without size)
        $cart_item = null;
        $quantity = 0;
        $cart_key = $product_item['id'];
        
        // Check for direct match (no size)
        if (isset($_SESSION['cart'][$product_item['id']])) {
            $cart_item = $_SESSION['cart'][$product_item['id']];
            $quantity = $cart_item['quantity'];
            $cart_key = $product_item['id'];
        } else {
            // Check for size variants
            foreach ($_SESSION['cart'] as $key => $item) {
                if (is_array($item) && isset($item['product_id']) && $item['product_id'] == $product_item['id']) {
                    $cart_item = $item;
                    $quantity = $item['quantity'];
                    $cart_key = $key;
                    break;
                }
            }
        }
        
        if ($quantity > 0) {
            $item_total = $product_item['price'] * $quantity;
            $cart_total += $item_total;
            
            $cart_items[] = [
                'product' => $product_item,
                'quantity' => $quantity,
                'total' => $item_total,
                'cart_key' => $cart_key
            ];
        }
    }
}

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$tax = $cart_total * TAX_RATE;
$shipping = ($cart_total >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_COST;
$grand_total = $cart_total + $tax + $shipping;

// Get user information
$user = new User();
$user_info = $user->getById($_SESSION['user_id']);

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_info = [
        'address' => sanitizeInput($_POST['shipping_address']),
        'city' => sanitizeInput($_POST['shipping_city']),
        'state' => sanitizeInput($_POST['shipping_state']),
        'zip' => sanitizeInput($_POST['shipping_zip'])
    ];
    
    // Validate shipping information
    $errors = [];
    if (empty($shipping_info['address'])) $errors[] = 'Shipping address is required';
    if (empty($shipping_info['city'])) $errors[] = 'City is required';
    if (empty($shipping_info['state'])) $errors[] = 'State is required';
    if (empty($shipping_info['zip'])) $errors[] = 'ZIP code is required';
    
    if (empty($errors)) {
        // Create order
        $result = $order->create($_SESSION['user_id'], $_SESSION['cart'], $shipping_info);
        
        if ($result['success']) {
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order confirmation
            header('Location: order-confirmation.php?id=' . $result['order_id']);
            exit();
        } else {
            setFlashMessage('error', $result['message']);
        }
    } else {
        setFlashMessage('error', implode('<br>', $errors));
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Checkout</h1>
        </div>
    </div>
    
    <form method="POST" action="checkout.php">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user_info['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user_info['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="shipping_address" name="shipping_address" 
                                   value="<?php echo htmlspecialchars($user_info['address']); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                       value="<?php echo htmlspecialchars($user_info['city']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_state" class="form-label">State</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state" 
                                       value="<?php echo htmlspecialchars($user_info['state']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_zip" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" 
                                       value="<?php echo htmlspecialchars($user_info['zip_code']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This is a demo checkout. No real payment processing is implemented.
                        </div>
                        
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                   placeholder="1234 5678 9012 3456" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="expiry_month" class="form-label">Expiry Month</label>
                                <select class="form-select" id="expiry_month" name="expiry_month" required>
                                    <option value="">Month</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="expiry_year" class="form-label">Expiry Year</label>
                                <select class="form-select" id="expiry_year" name="expiry_year" required>
                                    <option value="">Year</option>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" 
                                       placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Order Items -->
                        <div class="order-items mb-3">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product']['name']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <span class="fw-bold"><?php echo formatPrice($item['total']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Totals -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (<?php echo (TAX_RATE * 100); ?>%):</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>
                                <?php if ($shipping == 0): ?>
                                    <span class="text-success">FREE</span>
                                <?php else: ?>
                                    <?php echo formatPrice($shipping); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary"><?php echo formatPrice($grand_total); ?></strong>
                        </div>
                        
                        <!-- Place Order Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Place Order
                            </button>
                        </div>
                        
                        <!-- Security Notice -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                Your payment information is secure and encrypted.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$additional_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Format card number input
    const cardNumberInput = document.getElementById("card_number");
    cardNumberInput.addEventListener("input", function() {
        let value = this.value.replace(/\s/g, "").replace(/[^0-9]/gi, "");
        let formattedValue = value.match(/.{1,4}/g)?.join(" ") || value;
        this.value = formattedValue;
    });
    
    // Format CVV input
    const cvvInput = document.getElementById("cvv");
    cvvInput.addEventListener("input", function() {
        this.value = this.value.replace(/[^0-9]/g, "");
    });
    
    // Form validation
    const form = document.querySelector("form");
    form.addEventListener("submit", function(e) {
        const requiredFields = form.querySelectorAll("[required]");
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add("is-invalid");
                isValid = false;
            } else {
                field.classList.remove("is-invalid");
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert("Please fill in all required fields", "error");
        }
    });
});

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type === "error" ? "danger" : "success"} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector("main").insertBefore(alertDiv, document.querySelector("main").firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
';

require_once 'includes/footer.php';
?>
