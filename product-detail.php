<?php
$page_title = 'Product Details - The Athlete\'s Arsenal';
$page_description = 'View detailed information about our premium sports equipment.';

require_once 'includes/header.php';
require_once 'includes/classes/Product.class.php';
require_once 'includes/classes/Review.class.php';
require_once 'includes/classes/Size.class.php';

$product = new Product();
$review = new Review();
$size = new Size();

// Get product ID from URL
$product_id = $_GET['id'] ?? 0;
$product_item = $product->getById($product_id);

// Get reviews for this product
$reviews = $review->getReviewsByProduct($product_id);
$avg_rating = $review->getAverageRating($product_id);
$review_count = $review->getReviewCount($product_id);
$user_review = null;
$has_user_reviewed = false;

if (isLoggedIn()) {
    $user_review = $review->getUserReview($product_id, $_SESSION['user_id']);
    $has_user_reviewed = $review->hasUserReviewed($product_id, $_SESSION['user_id']);
}

if (!$product_item) {
    header('Location: products.php');
    exit();
}

$page_title = $product_item['name'] . ' - The Athlete\'s Arsenal';
$page_description = $product_item['description'];

// Get related products
$related_products = $product->getRelated($product_id, $product_item['category_id'], 4);

// Get product sizes
$product_sizes = $size->getSizesByProduct($product_id);

// Check if product is in wishlist (if user is logged in)
$in_wishlist = isLoggedIn() ? isInWishlist($product_id) : false;
?>

<div class="container py-5 staggered-animation">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Collections</a></li>
            <?php if ($product_item['category_name']): ?>
                <li class="breadcrumb-item">
                    <a href="products.php?category=<?php echo $product_item['category_id']; ?>">
                        <?php echo htmlspecialchars($product_item['category_name']); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product_item['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-image-container shadow-lg">
                <img src="<?php echo $product->getImageUrl($product_item['image'], $product_item['id']); ?>" 
                     class="img-fluid rounded" 
                     alt="<?php echo htmlspecialchars($product_item['name']); ?>"
                     id="main-image">
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-details">
                <h1 class="mb-3"><?php echo htmlspecialchars($product_item['name']); ?></h1>
                
                <!-- Price -->
                <div class="price-section mb-4">
                    <span class="price"><?php echo formatPrice($product_item['price']); ?></span>
                </div>
                
                <!-- Stock Status -->
                <div class="stock-status mb-4">
                    <?php if ($product_item['stock_quantity'] > 0): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>
                            In Stock (<?php echo $product_item['stock_quantity']; ?> available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger">
                            <i class="fas fa-times-circle me-1"></i>
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <div class="description mb-4">
                    <p><?php echo nl2br(htmlspecialchars($product_item['description'])); ?></p>
                </div>
                
                <!-- Add to Cart Form -->
                <?php if ($product_item['stock_quantity'] > 0): ?>
                    <form class="add-to-cart-form mb-5" method="POST" action="ajax/add-to-cart.php">
                        <?php if (!empty($product_sizes)): ?>
                            <!-- Size Selection - Flipkart Style -->
                            <div class="mb-4">
                                <h6 class="mb-2">Select Size</h6>
                                <div class="size-selection d-flex flex-wrap gap-2">
                                    <?php foreach ($product_sizes as $size_item): ?>
                                        <?php if ($size_item['stock_quantity'] > 0): ?>
                                            <input type="radio" class="btn-check" name="size_id" id="size_<?php echo $size_item['id']; ?>" value="<?php echo $size_item['id']; ?>" required>
                                            <label class="btn btn-outline-primary" for="size_<?php echo $size_item['id']; ?>">
                                                <?php echo htmlspecialchars_decode($size_item['size_name']); ?>
                                                <small class="d-block text-muted"><?php echo htmlspecialchars_decode($size_item['size_value']); ?></small>
                                                <?php if ($size_item['stock_quantity'] <= 5): ?>
                                                    <small class="d-block text-warning">Only <?php echo $size_item['stock_quantity']; ?> left!</small>
                                                <?php endif; ?>
                                            </label>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text text-danger" id="size-error" style="display: none;">Please select a size before adding to cart.</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row align-items-center">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <select class="form-select" id="quantity" name="quantity">
                                    <?php 
                                    // If sizes are available, we'll update quantity options with JavaScript
                                    $max_quantity = !empty($product_sizes) ? 1 : min(10, $product_item['stock_quantity']);
                                    for ($i = 1; $i <= $max_quantity; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                    </button>
                                    <?php if (isLoggedIn()): ?>
                                        <button type="button" class="btn btn-outline-danger btn-lg wishlist-btn" 
                                                data-product-id="<?php echo $product_id; ?>"
                                                data-in-wishlist="<?php echo $in_wishlist ? 'true' : 'false'; ?>">
                                            <i class="fas fa-heart<?php echo $in_wishlist ? '' : '-o'; ?>"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    </form>
                    
                    <?php if (!empty($product_sizes)): ?>
                        <script>
                        // Update quantity options based on selected size
                        document.querySelectorAll('input[name="size_id"]').forEach(radio => {
                            radio.addEventListener('change', function() {
                                const sizeId = this.value;
                                const quantitySelect = document.getElementById('quantity');
                                
                                // Reset quantity options
                                quantitySelect.innerHTML = '';
                                
                                if (sizeId) {
                                    // Get the selected size data
                                    const sizesData = <?php echo json_encode($product_sizes); ?>;
                                    const selectedSize = sizesData.find(size => size.id == sizeId);
                                    
                                    if (selectedSize) {
                                        const maxQuantity = Math.min(10, parseInt(selectedSize.stock_quantity));
                                        for (let i = 1; i <= maxQuantity; i++) {
                                            const option = document.createElement('option');
                                            option.value = i;
                                            option.textContent = i;
                                            quantitySelect.appendChild(option);
                                        }
                                    }
                                } else {
                                    // Default options when no size is selected
                                    for (let i = 1; i <= 1; i++) {
                                        const option = document.createElement('option');
                                        option.value = i;
                                        option.textContent = i;
                                        quantitySelect.appendChild(option);
                                    }
                                }
                            });
                        });
                        </script>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This product is currently out of stock. Please check back later or contact us for availability.
                    </div>
                <?php endif; ?>
                
                <!-- Product Features -->
                <div class="product-features">
                    <h5>Performance Features:</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Premium Quality Materials</li>
                        <li><i class="fas fa-check text-success me-2"></i>Professional Grade Equipment</li>
                        <li><i class="fas fa-check text-success me-2"></i>30-Day Performance Guarantee</li>
                        <li><i class="fas fa-check text-success me-2"></i>Complimentary Shipping</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                            data-bs-target="#description" type="button" role="tab">
                        Description
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" 
                            data-bs-target="#specifications" type="button" role="tab">
                        Specifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" 
                            data-bs-target="#shipping" type="button" role="tab">
                        Shipping & Returns
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="p-4">
                        <p><?php echo nl2br(htmlspecialchars($product_item['description'])); ?></p>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="specifications" role="tabpanel">
                    <div class="p-4">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><?php echo htmlspecialchars($product_item['category_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Price:</strong></td>
                                    <td><?php echo formatPrice($product_item['price']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Stock:</strong></td>
                                    <td><?php echo $product_item['stock_quantity']; ?> units</td>
                                </tr>
                                <tr>
                                    <td><strong>Added:</strong></td>
                                    <td><?php echo formatDate($product_item['created_at']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <div class="p-4">
                        <h6>Shipping Information</h6>
                        <ul>
                            <li>Complimentary shipping on all orders</li>
                            <li>Processing time: 1-2 business days</li>
                            <li>Delivery time: 3-7 business days</li>
                        </ul>
                        
                        <h6 class="mt-4">Returns & Exchanges</h6>
                        <ul>
                            <li>30-day performance guarantee</li>
                            <li>Items must be in original condition</li>
                            <li>Complimentary return shipping</li>
                            <li>Refunds processed within 5-7 business days</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Related Performance Gear</h3>
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card product-card h-100 shadow-sm hover-lift">
                                <div class="position-relative">
                                    <img src="<?php echo $product->getImageUrl($related['image'], $related['id']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?php echo htmlspecialchars(substr($related['description'], 0, 80)) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="price mb-0"><?php echo formatPrice($related['price']); ?></span>
                                        <a href="product-detail.php?id=<?php echo $related['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Product Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Customer Reviews</h3>
            
            <!-- Average Rating -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="rating-display">
                                <span class="display-4 fw-bold"><?php echo $avg_rating && $avg_rating['avg_rating'] ? number_format($avg_rating['avg_rating'], 1) : '0.0'; ?></span>
                                <div class="rating-stars mb-2">
                                    <?php 
                                    $avg = $avg_rating && $avg_rating['avg_rating'] ? $avg_rating['avg_rating'] : 0;
                                    for ($i = 1; $i <= 5; $i++): 
                                        if ($i <= $avg): 
                                            echo '<i class="fas fa-star text-warning"></i>';
                                        elseif ($i - 0.5 <= $avg):
                                            echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                        else:
                                            echo '<i class="far fa-star text-warning"></i>';
                                        endif;
                                    endfor;
                                    ?>
                                </div>
                                <p class="text-muted mb-0"><?php echo $review_count; ?> reviews</p>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?php if (isLoggedIn()): ?>
                                <?php if (!$has_user_reviewed): ?>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                        <i class="fas fa-plus-circle me-2"></i>Write a Review
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-check-circle me-2"></i>You have already reviewed this product.
                                        <button class="btn btn-outline-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                            Edit Review
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Write a Review
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reviews List -->
            <?php if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review_item): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($review_item['first_name'] . ' ' . $review_item['last_name']); ?></h6>
                                        <div class="rating-stars mb-2">
                                            <?php 
                                            for ($i = 1; $i <= 5; $i++): 
                                                if ($i <= $review_item['rating']): 
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                else:
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                endif;
                                            endfor;
                                            ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo formatDate($review_item['created_at']); ?></small>
                                </div>
                                <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($review_item['review_title']); ?></h6>
                                <p class="mb-0"><?php echo htmlspecialchars($review_item['review_text']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No reviews yet. Be the first to review this product!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Add to cart form submission
    const addToCartForm = document.querySelector(".add-to-cart-form");
    if (addToCartForm) {
        addToCartForm.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Check if sizes are required but not selected
            const sizeRequired = document.querySelector("input[name=\"size_id\"]");
            const sizeError = document.getElementById("size-error");
            
            if (sizeRequired && !formData.get("size_id")) {
                if (sizeError) {
                    sizeError.style.display = "block";
                }
                showAlert("Please select a size before adding to cart", "error");
                return;
            } else {
                if (sizeError) {
                    sizeError.style.display = "none";
                }
            }
            
            const data = {
                product_id: formData.get("product_id"),
                quantity: formData.get("quantity")
            };
            
            // Add size_id to request if provided
            const sizeId = formData.get("size_id");
            if (sizeId) {
                data.size_id = sizeId;
            }
            
            fetch("ajax/add-to-cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data)
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
                    showAlert("Added to your arsenal!", "success");
                } else {
                    showAlert("Failed to add product to cart", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showAlert("An error occurred", "error");
            });
        });
    }
    
    // Wishlist functionality
    const wishlistBtn = document.querySelector(".wishlist-btn");
    if (wishlistBtn) {
        wishlistBtn.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            const inWishlist = this.getAttribute("data-in-wishlist") === "true";
            
            fetch("ajax/wishlist.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    product_id: productId,
                    action: inWishlist ? "remove" : "add"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = this.querySelector("i");
                    if (inWishlist) {
                        icon.className = "fas fa-heart-o";
                        this.setAttribute("data-in-wishlist", "false");
                        showAlert("Removed from wishlist", "info");
                    } else {
                        icon.className = "fas fa-heart";
                        this.setAttribute("data-in-wishlist", "true");
                        showAlert("Added to wishlist", "success");
                    }
                } else {
                    showAlert("Failed to update wishlist", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showAlert("An error occurred", "error");
            });
        });
    }
});

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type === "error" ? "danger" : type === "info" ? "info" : "success"} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
';

// Review Modal
$additional_scripts .= '
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">' . ($has_user_reviewed ? 'Edit Your Review' : 'Write a Review') . '</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm" method="POST" action="ajax/add-review.php">
                <div class="modal-body">
                    <input type="hidden" name="product_id" value="' . $product_id . '">
                    ' . ($has_user_reviewed ? '<input type="hidden" name="review_id" value="' . $user_review['id'] . '">' : '') . '
                    
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-input">
                            <div class="btn-group" role="group">
                                ' . implode('', array_map(function($i) use ($user_review) {
                                    $checked = ($user_review && $user_review['rating'] == $i) || (!$user_review && $i == 5) ? ' checked' : '';
                                    return '<input type="radio" class="btn-check" name="rating" id="rating' . $i . '" value="' . $i . '"' . $checked . ' required>
                                            <label class="btn btn-outline-warning" for="rating' . $i . '"><i class="fas fa-star"></i> ' . $i . '</label>';
                                }, range(1, 5))) . '
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="review_title" name="review_title" value="' . ($user_review ? htmlspecialchars($user_review['review_title']) : '') . '" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Review</label>
                        <textarea class="form-control" id="review_text" name="review_text" rows="4" required>' . ($user_review ? htmlspecialchars($user_review['review_text']) : '') . '</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">' . ($has_user_reviewed ? 'Update Review' : 'Submit Review') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle review form submission
document.getElementById("reviewForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("ajax/add-review.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, "success");
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById("reviewModal")).hide();
            // Reload page to show new review
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message, "error");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showAlert("An error occurred. Please try again.", "error");
    });
});
</script>
';

require_once 'includes/footer.php';
?>