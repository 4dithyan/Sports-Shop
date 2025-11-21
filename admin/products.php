<?php
// Check if user is admin - do this BEFORE including header.php
require_once '../config.php';
require_once '../includes/functions.php';

// Handle form submissions BEFORE including header.php to avoid output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    require_once '../includes/classes/Product.class.php';
    
    $product = new Product();
    $db = getDB();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'price' => floatval($_POST['price']),
                    'category_id' => intval($_POST['category_id']),
                    'stock_quantity' => intval($_POST['stock_quantity']),
                    'featured' => isset($_POST['featured']) ? 1 : 0
                ];
                
                // Handle image upload
                $image_uploaded = false;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded_image = uploadImage($_FILES['image']);
                    if ($uploaded_image) {
                        $data['image'] = $uploaded_image;
                        $image_uploaded = true;
                    } else {
                        setFlashMessage('warning', 'Product added but image upload failed. Using default image.');
                    }
                } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    // There was an upload error
                    error_log("Image upload error: " . $_FILES['image']['error']);
                    setFlashMessage('warning', 'Product added but image upload failed (error code: ' . $_FILES['image']['error'] . '). Using default image.');
                }
                
                $result = $product->create($data);
                if ($result) {
                    setFlashMessage('success', 'Product added successfully!' . ($image_uploaded ? '' : ' (without image)'));
                    // Redirect to sizes management page for the new product
                    header('Location: sizes.php?product=' . $result);
                    exit();
                } else {
                    setFlashMessage('error', 'Failed to add product. Please check error logs.');
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'price' => floatval($_POST['price']),
                    'category_id' => intval($_POST['category_id']),
                    'stock_quantity' => intval($_POST['stock_quantity']),
                    'featured' => isset($_POST['featured']) ? 1 : 0
                ];
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded_image = uploadImage($_FILES['image']);
                    if ($uploaded_image) {
                        $data['image'] = $uploaded_image;
                    } else {
                        // Keep existing image if upload fails
                        $existing_product = $product->getById($id);
                        $data['image'] = $existing_product['image'] ?? '';
                        setFlashMessage('warning', 'Product updated but image upload failed. Keeping existing image.');
                    }
                } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    // There was an upload error
                    error_log("Image upload error: " . $_FILES['image']['error']);
                    // Keep existing image
                    $existing_product = $product->getById($id);
                    $data['image'] = $existing_product['image'] ?? '';
                    setFlashMessage('warning', 'Product updated but image upload failed (error code: ' . $_FILES['image']['error'] . '). Keeping existing image.');
                } else {
                    // No new image uploaded, keep existing image
                    $existing_product = $product->getById($id);
                    $data['image'] = $existing_product['image'] ?? '';
                }
                
                if ($product->update($id, $data)) {
                    setFlashMessage('success', 'Product updated successfully!');
                    // Redirect to sizes management page for the updated product
                    header('Location: sizes.php?product=' . $id);
                    exit();
                } else {
                    setFlashMessage('error', 'Failed to update product. Please check error logs.');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                if ($product->delete($id)) {
                    setFlashMessage('success', 'Product deleted successfully!');
                } else {
                    setFlashMessage('error', 'Failed to delete product');
                }
                header('Location: products.php');
                exit();
        }
    }
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

$page_title = 'Manage Products - Admin Panel';
$page_description = 'Add, edit, and manage products in the admin panel.';

require_once '../includes/header.php';

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

// Only include Product.class.php once
require_once '../includes/classes/Product.class.php';

$product = new Product();
$db = getDB();

// Get categories for dropdown
$categories_stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();

// Get products with pagination
$current_page = max(1, $_GET['page'] ?? 1);
$pagination = paginate($product->count(), 10, $current_page);
$products = $product->getAll(['limit' => 10, 'offset' => $pagination['offset']]);

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_product = $product->getById($_GET['edit']);
    if (!$edit_product) {
        setFlashMessage('error', 'Product not found.');
        header('Location: products.php');
        exit();
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage Products</h1>
                <button class="btn btn-primary" onclick="showAddProductModal()">
                    <i class="fas fa-plus me-2"></i>Add Product
                </button>
            </div>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $product_item): ?>
                                        <tr>
                                            <td><?php echo $product_item['id']; ?></td>
                                            <td>
                                                <img src="<?php echo $product->getImageUrl($product_item['image'], $product_item['id']); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover;" 
                                                     alt="<?php echo htmlspecialchars($product_item['name']); ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($product_item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product_item['category_name']); ?></td>
                                            <td><?php echo formatPrice($product_item['price']); ?></td>
                                            <td><?php echo $product_item['stock_quantity']; ?></td>
                                            <td>
                                                <?php if ($product_item['featured']): ?>
                                                    <span class="badge bg-warning">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product_item['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($product_item['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="editProduct(<?php echo $product_item['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="sizes.php?product=<?php echo $product_item['id']; ?>" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-ruler-combined"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $product_item['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No products found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Products pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['has_prev']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['prev_page']])); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['next_page']])); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="products.php" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="id" value="" id="formId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Please enter a product name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (₹) *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter a valid price.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="0" required>
                            <div class="invalid-feedback">Please enter a valid stock quantity.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="invalid-feedback">Please select a valid image file.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1">
                            <label class="form-check-label" for="featured">
                                Featured Product
                            </label>
                        </div>
                    </div>
                    
                    <!-- Size Management Section -->
                    <div class="mb-3" id="sizeManagementSection" style="display: none;">
                        <h6>Product Sizes</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            After creating the product, you can manage sizes in the <a href="sizes.php" class="alert-link">Product Sizes</a> section.
                        </div>
                        <div class="form-text">Sizes can be managed after the product is created.</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="productSubmitBtn">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script>
// Store product data in JavaScript
var productsData = {};
' . implode("", array_map(function($product_item) {
    return 'productsData[' . $product_item['id'] . '] = ' . json_encode($product_item) . ';';
}, $products)) . '

// Function to show the add product modal
function showAddProductModal() {
    // Reset the form
    document.getElementById("productForm").reset();
    
    // Set the action to "add"
    document.getElementById("formAction").value = "add";
    
    // Clear the ID field
    document.getElementById("formId").value = "";
    
    // Update modal title
    document.getElementById("productModalLabel").textContent = "Add Product";
    
    // Update submit button text
    document.getElementById("productSubmitBtn").textContent = "Add Product";
    
    // Uncheck featured checkbox
    document.getElementById("featured").checked = false;
    
    // Set stock quantity to 0
    document.getElementById("stock_quantity").value = "0";
    
    // Hide size management section for new products
    document.getElementById("sizeManagementSection").style.display = "none";
    
    // Show the modal
    var productModal = new bootstrap.Modal(document.getElementById("productModal"));
    productModal.show();
}

// Function to edit a product
function editProduct(id) {
    // Get product data
    var productData = productsData[id];
    if (!productData) {
        console.error("Product data not found for ID: " + id);
        return;
    }
    
    // Set the action to "edit"
    document.getElementById("formAction").value = "edit";
    
    // Set the ID field
    document.getElementById("formId").value = id;
    
    // Populate form fields
    document.getElementById("name").value = productData.name || "";
    document.getElementById("description").value = productData.description || "";
    document.getElementById("price").value = productData.price || "";
    document.getElementById("stock_quantity").value = productData.stock_quantity || "0";
    
    // Set category
    var categorySelect = document.getElementById("category_id");
    if (categorySelect) {
        categorySelect.value = productData.category_id || "";
    }
    
    // Set featured checkbox
    document.getElementById("featured").checked = productData.featured == "1";
    
    // Show size management section for existing products
    document.getElementById("sizeManagementSection").style.display = "block";
    
    // Update modal title
    document.getElementById("productModalLabel").textContent = "Edit Product";
    
    // Update submit button text
    document.getElementById("productSubmitBtn").textContent = "Update Product";
    
    // Show the modal
    var productModal = new bootstrap.Modal(document.getElementById("productModal"));
    productModal.show();
}

// Auto-focus on name field when modal opens
document.getElementById("productModal").addEventListener("shown.bs.modal", function () {
    document.getElementById("name").focus();
});

// Show the modal automatically if we are editing a product
document.addEventListener("DOMContentLoaded", function() {
    // Check if there is an edit parameter in the URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("edit")) {
        const editId = urlParams.get("edit");
        if (editId && productsData[editId]) {
            editProduct(editId);
        }
    }
});
</script>
';

require_once '../includes/footer.php';
?>