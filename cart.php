<?php
$page_title = 'Shopping Cart - Cosmos Sports';
$page_description = 'Review your selected items and proceed to checkout.';

require_once 'includes/header.php';
require_once 'includes/classes/Product.class.php';

$product = new Product();
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

$tax = $cart_total * TAX_RATE;
$shipping = ($cart_total >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_COST;
$grand_total = $cart_total + $tax + $shipping;
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Shopping Cart</h1>
        </div>
    </div>
    
    <?php if (!empty($cart_items)): ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $product->getImageUrl($item['product']['image'], $item['product']['id']); ?>" 
                                                         class="me-3" style="width: 60px; height: 60px; object-fit: cover;" 
                                                         alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product']['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($item['product']['category_name']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo formatPrice($item['product']['price']); ?></td>
                                            <td>
                                                <div class="input-group" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary btn-sm update-quantity" 
                                                            data-product-id="<?php echo $item['product']['id']; ?>" 
                                                            data-action="decrease">-</button>
                                                    <input type="number" class="form-control form-control-sm text-center" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           data-product-id="<?php echo $item['product']['id']; ?>" 
                                                           data-cart-key="<?php echo $cart_key; ?>" 
                                                           min="1" max="<?php echo $item['product']['stock_quantity']; ?>">
                                                    <button class="btn btn-outline-secondary btn-sm update-quantity" 
                                                            data-product-id="<?php echo $item['product']['id']; ?>" 
                                                            data-action="increase">+</button>
                                                </div>
                                            </td>
                                            <td class="fw-bold"><?php echo formatPrice($item['total']); ?></td>
                                            <td>
                                                <button class="btn btn-outline-danger btn-sm remove-item" 
                                                        data-product-id="<?php echo $item['product']['id']; ?>" 
                                                        data-cart-key="<?php echo $item['cart_key']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="mt-3">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
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
                        
                        <?php if ($shipping > 0): ?>
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Add <?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $cart_total); ?> more for free shipping!
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$additional_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Update quantity buttons
    const updateButtons = document.querySelectorAll(".update-quantity");
    updateButtons.forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            const action = this.getAttribute("data-action");
            const input = this.parentElement.querySelector("input");
            let newQuantity = parseInt(input.value);
            
            if (action === "increase") {
                newQuantity++;
            } else if (action === "decrease" && newQuantity > 1) {
                newQuantity--;
            }
            
            updateCartQuantity(productId, newQuantity);
        });
    });
    
    // Quantity input change
    const quantityInputs = document.querySelectorAll("input[type=number]");
    quantityInputs.forEach(input => {
        input.addEventListener("change", function() {
            const productId = this.getAttribute("data-product-id");
            const newQuantity = parseInt(this.value);
            
            if (newQuantity >= 1) {
                updateCartQuantity(productId, newQuantity);
            }
        });
    });
    
    // Remove item buttons
    const removeButtons = document.querySelectorAll(".remove-item");
    removeButtons.forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            removeFromCart(productId);
        });
    });
});

function updateCartQuantity(productId, quantity) {
    // Get the cart key from the data attribute
    const input = document.querySelector(`input[data-product-id="${productId}"]`);
    const cartKey = input ? input.getAttribute("data-cart-key") : productId;
    
    fetch("ajax/update-cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            product_id: productId,
            cart_key: cartKey,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated totals
        } else {
            showAlert("Failed to update cart", "error");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showAlert("An error occurred", "error");
    });
}

function removeFromCart(productId) {
    if (confirm("Are you sure you want to remove this item from your cart?")) {
        // Get the cart key from the data attribute
        const button = document.querySelector(`button[data-product-id="${productId}"]`);
        const cartKey = button ? button.getAttribute("data-cart-key") : productId;
        
        fetch("ajax/remove-from-cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                product_id: productId,
                cart_key: cartKey
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated cart
            } else {
                showAlert("Failed to remove item", "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("An error occurred", "error");
        });
    }
}

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type === "error" ? "danger" : "success"} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector("main").insertBefore(alertDiv, document.querySelector("main").firstChild);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
';

require_once 'includes/footer.php';
?>
