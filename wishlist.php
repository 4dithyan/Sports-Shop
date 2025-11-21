<?php
// Check if user is logged in - do this BEFORE including header.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'My Wishlist - Cosmos Sports';
$page_description = 'View and manage your saved products.';

require_once 'includes/header.php';
require_once 'includes/classes/User.class.php';
require_once 'includes/classes/Product.class.php';
require_once 'includes/functions.php';

// Check if user is logged in (redundant now, but kept for consistency)
requireLogin();

$user = new User();
$product = new Product();
$wishlist_items = $user->getWishlist($_SESSION['user_id']);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Wishlist</h1>
        </div>
    </div>
    
    <?php if (!empty($wishlist_items)): ?>
        <div class="row">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="<?php echo $product->getImageUrl($item['image'], $item['id']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="position-absolute top-0 end-0 m-2">
                                <button class="btn btn-outline-danger btn-sm remove-wishlist" 
                                        data-product-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($item['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h6 text-primary mb-0"><?php echo formatPrice($item['price']); ?></span>
                                <div class="btn-group" role="group">
                                    <a href="product-detail.php?id=<?php echo $item['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">View</a>
                                    <?php if ($item['stock_quantity'] > 0): ?>
                                        <button class="btn btn-primary btn-sm add-to-cart" 
                                                data-product-id="<?php echo $item['id']; ?>">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <small class="text-muted mt-2">
                                Added: <?php echo formatDate($item['added_date']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-heart fa-4x text-muted mb-4"></i>
                <h3>Your wishlist is empty</h3>
                <p class="text-muted mb-4">Save products you love to your wishlist for easy access later.</p>
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
    // Remove from wishlist functionality
    document.querySelectorAll(".remove-wishlist").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            
            if (confirm("Remove this item from your wishlist?")) {
                fetch("ajax/wishlist.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        action: "remove"
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the card from the page
                        this.closest(".col-lg-3").remove();
                        showAlert("Item removed from wishlist", "success");
                    } else {
                        showAlert("Failed to remove item", "error");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    showAlert("An error occurred", "error");
                });
            }
        });
    });
    
    // Add to cart functionality
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            
            fetch("ajax/add-to-cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in navbar
                    const cartBadge = document.querySelector(".badge");
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    
                    showAlert("Product added to cart!", "success");
                } else {
                    showAlert("Failed to add product to cart", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showAlert("An error occurred", "error");
            });
        });
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
