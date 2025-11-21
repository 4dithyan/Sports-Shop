<?php
$page_title = 'Product Categories - Cosmos Sports';
$page_description = 'Browse our sports equipment by category. Find the perfect gear for your sport.';

require_once 'includes/header.php';
require_once 'includes/functions.php';

$db = getDB();
$categories_stmt = $db->prepare("SELECT c.*, COUNT(p.id) as product_count 
                                 FROM categories c 
                                 LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                                 WHERE c.status = 'active'
                                 GROUP BY c.id 
                                 ORDER BY c.name");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Product Categories</h1>
            <p class="lead text-muted">Find the perfect sports equipment for your favorite activities</p>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($categories as $category): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card category-card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="<?php echo getOnlineCategoryImageUrl($category['id'], $category['name']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>"
                             style="height: 250px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-primary">
                                <?php echo $category['product_count']; ?> products
                            </span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?php echo htmlspecialchars($category['description']); ?>
                        </p>
                        <div class="mt-auto">
                            <a href="products.php?category=<?php echo $category['id']; ?>" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-arrow-right me-2"></i>Shop <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>