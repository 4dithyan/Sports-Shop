<?php
$page_title = 'COSMOS - The Digital Universe of Peak Performance';
$page_description = 'COSMOS - The Digital Universe of Peak Performance. A clean, brightly-lit universe where the products are the stars.';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/header.php';
require_once 'includes/classes/Product.class.php';

// Debug: Check if Product class is loaded
if (!class_exists('Product')) {
    die("Product class not found");
}

$product = new Product();

// Debug: Check if getFeatured method exists
if (!method_exists($product, 'getFeatured')) {
    die("getFeatured method not found");
}

// Debug: Try to get featured products
try {
    $featured_products = $product->getFeatured(8);
    // Debug: Check what we got
    if (!is_array($featured_products)) {
        die("getFeatured didn't return an array. Returned: " . gettype($featured_products));
    }
} catch (Exception $e) {
    die("Error getting featured products: " . $e->getMessage());
}
?>

<section class="hero-section py-5 staggered-animation">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-3 fw-bold mb-4">Ignite Your Performance<br><span class="text-primary">With Premium Gear</span></h1>
                <p class="lead mb-5">
                    Fuel your passion with our meticulously curated collection of peak performance gear. 
                    Experience the perfect blend of innovation and reliability that powers champions.
                </p>
                <div class="d-flex gap-4 flex-wrap">
                    <a href="products.php" class="btn btn-primary btn-lg px-5 py-3">Explore Collection</a>
                    <a href="#featured-products" class="btn btn-outline-primary btn-lg px-5 py-3">Featured Gear</a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div class="position-relative">
                    <div class="hero-circle"></div>
                    <!-- Kerosene effect visual element -->
                    <div class="kerosene-glow"></div>
                    <img src="<?php echo $asset_path; ?>assets/images/placeholder.svg" class="img-fluid rounded shadow-lg hover-zoom" alt="Hero Image">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 staggered-animation" id="featured-products">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="fw-bold mb-3">Curated Performance Gear</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Handpicked essentials for athletes who refuse to compromise</p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $product_item): ?>
                    <div class="col-lg-3 col-md-6 mb-4 staggered-animation">
                        <div class="card product-card h-100 shadow-sm hover-lift">
                            <div class="position-relative">
                                <img src="<?php echo $product->getImageUrl($product_item['image'], $product_item['id']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($product_item['name']); ?>">
                                <?php if ($product_item['stock_quantity'] <= 0): ?>
                                    <div class="position-absolute top-0 end-0 m-3">
                                        <span class="badge bg-danger">Out of Stock</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo htmlspecialchars($product_item['name']); ?></h6>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?php echo htmlspecialchars(substr($product_item['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div>
                                        <span class="price mb-0"><?php echo formatPrice($product_item['price']); ?></span>
                                        <?php 
                                        $rating_data = $product->getAverageRating($product_item['id']);
                                        $avg_rating = $rating_data['avg_rating'];
                                        $total_reviews = $rating_data['total_reviews'];
                                        if ($avg_rating):
                                        ?>
                                        <div class="rating-stars d-block">
                                            <?php 
                                            for ($i = 1; $i <= 5; $i++): 
                                                if ($i <= $avg_rating): 
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                elseif ($i - 0.5 <= $avg_rating):
                                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                else:
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                endif;
                                            endfor;
                                            ?>
                                            <span class="text-muted small ms-1"><?php echo number_format($avg_rating, 1); ?> (<?php echo $total_reviews; ?>)</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <a href="product-detail.php?id=<?php echo $product_item['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">View</a>
                                        <?php if ($product_item['stock_quantity'] > 0): ?>
                                            <button class="btn btn-primary btn-sm add-to-cart" 
                                                    data-product-id="<?php echo $product_item['id']; ?>">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No featured products available at the moment.</p>
                    <!-- Debug information -->
                    <div class="alert alert-info mt-3">
                        <h5>Debug Information:</h5>
                        <p>Featured products array is empty. Count: <?php echo is_array($featured_products) ? count($featured_products) : 'Not an array'; ?></p>
                        <p>Products variable type: <?php echo gettype($featured_products); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="products.php" class="btn btn-primary btn-lg px-5 py-3">View Complete Collection</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light staggered-animation">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="fw-bold mb-3">Performance Categories</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Explore our premium collections by category</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 staggered-animation">
                <div class="category-card card h-100 shadow-sm hover-lift">
                    <img src="https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Cricket">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cricket</h5>
                        <p class="card-text">Premium cricket equipment for all formats</p>
                        <a href="products.php?category=cricket" class="btn btn-primary">Explore</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 staggered-animation">
                <div class="category-card card h-100 shadow-sm hover-lift">
                    <img src="https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Football">
                    <div class="card-body text-center">
                        <h5 class="card-title">Football</h5>
                        <p class="card-text">Quality football gear for the beautiful game</p>
                        <a href="products.php?category=football" class="btn btn-primary">Explore</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 staggered-animation">
                <div class="category-card card h-100 shadow-sm hover-lift">
                    <img src="https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Badminton">
                    <div class="card-body text-center">
                        <h5 class="card-title">Badminton</h5>
                        <p class="card-text">Rackets and shuttlecocks for all skill levels</p>
                        <a href="products.php?category=badminton" class="btn btn-primary">Explore</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 staggered-animation">
                <div class="category-card card h-100 shadow-sm hover-lift">
                    <img src="https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Running">
                    <div class="card-body text-center">
                        <h5 class="card-title">Running</h5>
                        <p class="card-text">Performance running shoes and apparel</p>
                        <a href="products.php?category=running" class="btn btn-primary">Explore</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 staggered-animation">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="fw-bold mb-3">Why Choose COSMOS?</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Premium quality products from top brands</p>
            </div>
        </div>
        
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-card p-4 h-100">
                    <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                    <h5>Lightning Fast Shipping</h5>
                    <p class="text-muted">Quick delivery on all orders with tracking</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card p-4 h-100">
                    <i class="fas fa-sync fa-3x text-primary mb-3"></i>
                    <h5>Easy Returns</h5>
                    <p class="text-muted">30-day return policy on all items</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card p-4 h-100">
                    <i class="fas fa-award fa-3x text-primary mb-3"></i>
                    <h5>Quality Guarantee</h5>
                    <p class="text-muted">Premium quality products from top brands</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-2">Stay Updated</h3>
                <p class="mb-0">Get the latest news, promotions, and product updates delivered to your inbox.</p>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form" method="POST" action="newsletter-signup.php">
                    <div class="input-group">
                        <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                        <button class="btn btn-light" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
$additional_scripts = '
<script>
// Add to cart functionality
document.addEventListener("DOMContentLoaded", function() {
    const addToCartButtons = document.querySelectorAll(".add-to-cart");
    
    addToCartButtons.forEach(button => {
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
                    
                    // Show success message
                    showAlert("Product added to your collection!", "success");
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