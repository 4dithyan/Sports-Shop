<?php
$page_title = 'Products - Cosmos Sports';
$page_description = 'Browse our complete selection of sports equipment and gear. Find the perfect products for your sport.';

require_once 'includes/header.php';
require_once 'includes/classes/Product.class.php';

$product = new Product();

// Get filters from URL
$filters = [];
$filters['search'] = $_GET['search'] ?? '';
$filters['category_id'] = $_GET['category'] ?? '';
$filters['min_price'] = $_GET['min_price'] ?? '';
$filters['max_price'] = $_GET['max_price'] ?? '';
$filters['sort'] = $_GET['sort'] ?? 'newest';

// Pagination
$current_page = max(1, $_GET['page'] ?? 1);
$pagination = paginate($product->count($filters), PRODUCTS_PER_PAGE, $current_page);
$filters['limit'] = PRODUCTS_PER_PAGE;
$filters['offset'] = $pagination['offset'];

// Get products
$products = $product->getAll($filters);

// Get categories for filter
$db = getDB();
$categories_stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="products.php">
                        <!-- Search -->
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($filters['search']); ?>" 
                                   placeholder="Search products...">
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $filters['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Price Range</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" 
                                           placeholder="Min" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" 
                                           placeholder="Max" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort -->
                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php echo $filters['sort'] == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $filters['sort'] == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $filters['sort'] == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name" <?php echo $filters['sort'] == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="products.php" class="btn btn-outline-secondary">Clear All</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Products</h2>
                    <p class="text-muted mb-0">
                        Showing <?php echo count($products); ?> of <?php echo $product->count($filters); ?> products
                    </p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-2">View:</span>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="grid-view">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="list-view">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Products -->
            <div class="row" id="products-container">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product_item): ?>
                        <div class="col-lg-4 col-md-6 mb-4 product-item">
                            <div class="card product-card h-100 shadow-sm">
                                <div class="position-relative">
                                    <img src="<?php echo $product->getImageUrl($product_item['image'], $product_item['id']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($product_item['name']); ?>">
                                    <?php if ($product_item['stock_quantity'] <= 0): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-danger">Out of Stock</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($product_item['featured']): ?>
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-warning">Featured</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title"><?php echo htmlspecialchars($product_item['name']); ?></h6>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?php echo htmlspecialchars(substr($product_item['description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="h5 text-primary mb-0"><?php echo formatPrice($product_item['price']); ?></span>
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
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">Try adjusting your search criteria or browse all products.</p>
                        <a href="products.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Products pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['prev_page']])); ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['next_page']])); ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

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
    
    // View toggle functionality
    const gridViewBtn = document.getElementById("grid-view");
    const listViewBtn = document.getElementById("list-view");
    const productsContainer = document.getElementById("products-container");
    
    gridViewBtn.addEventListener("click", function() {
        this.classList.add("active");
        listViewBtn.classList.remove("active");
        productsContainer.className = "row";
    });
    
    listViewBtn.addEventListener("click", function() {
        this.classList.add("active");
        gridViewBtn.classList.remove("active");
        productsContainer.className = "row list-view";
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
