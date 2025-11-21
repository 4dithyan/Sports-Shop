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

    require_once '../includes/classes/Size.class.php';
    require_once '../includes/classes/Product.class.php';
    
    $size = new Size();
    $product = new Product();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $product_id = intval($_POST['product_id']);
                $size_name = sanitizeInput($_POST['size_name']);
                $size_value = sanitizeInput($_POST['size_value']);
                $stock_quantity = intval($_POST['stock_quantity']);
                
                $result = $size->addSize($product_id, $size_name, $size_value, $stock_quantity);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
                header('Location: sizes.php?product=' . $product_id);
                exit();
                
            case 'edit':
                $id = intval($_POST['id']);
                $size_name = sanitizeInput($_POST['size_name']);
                $size_value = sanitizeInput($_POST['size_value']);
                $stock_quantity = intval($_POST['stock_quantity']);
                
                $result = $size->updateSize($id, $size_name, $size_value, $stock_quantity);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
                // Get the product ID to redirect back to the correct product
                $size_data = $size->getSizeById($id);
                $product_id = $size_data['product_id'] ?? 0;
                header('Location: sizes.php?product=' . $product_id);
                exit();
                
            case 'delete':
                $id = intval($_POST['id']);
                // Get the product ID before deleting
                $size_data = $size->getSizeById($id);
                $product_id = $size_data['product_id'] ?? 0;
                
                $result = $size->deleteSize($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
                header('Location: sizes.php?product=' . $product_id);
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

$page_title = 'Manage Product Sizes - Admin Panel';
$page_description = 'Manage sizes for products in the admin panel.';

require_once '../includes/header.php';

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

require_once '../includes/classes/Size.class.php';
require_once '../includes/classes/Product.class.php';

$size = new Size();
$product = new Product();

// Get product ID from URL
$product_id = $_GET['product'] ?? null;
$product_data = null;
$sizes = [];

if ($product_id) {
    $product_data = $product->getById($product_id);
    if ($product_data) {
        $sizes = $size->getSizesByProduct($product_id);
    } else {
        setFlashMessage('error', 'Product not found.');
        header('Location: sizes.php');
        exit();
    }
}

// Get all products for dropdown
$db = getDB();
$products_stmt = $db->prepare("SELECT id, name FROM products WHERE status = 'active' ORDER BY name");
$products_stmt->execute();
$all_products = $products_stmt->fetchAll();

// Get size for editing
$edit_size = null;
if (isset($_GET['edit'])) {
    $edit_size = $size->getSizeById($_GET['edit']);
    if (!$edit_size) {
        setFlashMessage('error', 'Size not found.');
        header('Location: sizes.php?product=' . $product_id);
        exit();
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage Product Sizes</h1>
            </div>
        </div>
    </div>
    
    <!-- Product Selection -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="sizes.php" class="row g-3">
                        <div class="col-md-8">
                            <label for="product_select" class="form-label">Select Product</label>
                            <select class="form-select" id="product_select" name="product" onchange="this.form.submit()">
                                <option value="">Select a product</option>
                                <?php foreach ($all_products as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>" <?php echo $product_id == $prod['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prod['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Load Sizes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($product_data): ?>
    <!-- Product Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Product: <?php echo htmlspecialchars($product_data['name']); ?></h5>
                    <p class="card-text">Manage sizes for this product.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sizes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product Sizes</h5>
                    <button class="btn btn-primary" onclick="showAddSizeModal()">
                        <i class="fas fa-plus me-2"></i>Add Size
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Size Name</th>
                                    <th>Size Value</th>
                                    <th>Stock Quantity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sizes)): ?>
                                    <?php foreach ($sizes as $size_item): ?>
                                        <tr>
                                            <td><?php echo $size_item['id']; ?></td>
                                            <td><?php echo htmlspecialchars($size_item['size_name']); ?></td>
                                            <td><?php echo htmlspecialchars($size_item['size_value']); ?></td>
                                            <td><?php echo $size_item['stock_quantity']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="editSize(<?php echo $size_item['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this size?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $size_item['id']; ?>">
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
                                        <td colspan="5" class="text-center">No sizes found for this product.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Size Modal -->
<div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="sizes.php">
                <input type="hidden" name="action" value="add" id="sizeFormAction">
                <input type="hidden" name="id" value="" id="sizeFormId">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" id="sizeFormProductId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="sizeModalLabel">Add Size</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="size_name" class="form-label">Size Name *</label>
                        <input type="text" class="form-control" id="size_name" name="size_name" required>
                        <div class="form-text">e.g., "S", "M", "L", "XL", "8", "9", "10"</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="size_value" class="form-label">Size Value *</label>
                        <input type="text" class="form-control" id="size_value" name="size_value" required>
                        <div class="form-text">e.g., "Small", "Medium", "Large", "8 US", "42 EU"</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="0" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="sizeSubmitBtn">Add Size</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script>
// Function to decode HTML entities
function decodeHtmlEntities(text) {
    var textArea = document.createElement("textarea");
    textArea.innerHTML = text;
    return textArea.value;
}

// Store size data in JavaScript
var sizesData = {};
' . ($sizes ? implode("", array_map(function($size_item) {
    return 'sizesData[' . $size_item['id'] . '] = ' . json_encode($size_item) . ';';
}, $sizes)) : '') . '

// Function to show the add size modal
function showAddSizeModal() {
    // Reset the form
    document.getElementById("sizeModal").querySelector("form").reset();
    
    // Set the action to "add"
    document.getElementById("sizeFormAction").value = "add";
    
    // Clear the ID field
    document.getElementById("sizeFormId").value = "";
    
    // Set product ID
    document.getElementById("sizeFormProductId").value = "' . $product_id . '";
    
    // Update modal title
    document.getElementById("sizeModalLabel").textContent = "Add Size";
    
    // Update submit button text
    document.getElementById("sizeSubmitBtn").textContent = "Add Size";
    
    // Set stock quantity to 0
    document.getElementById("stock_quantity").value = "0";
    
    // Show the modal
    var sizeModal = new bootstrap.Modal(document.getElementById("sizeModal"));
    sizeModal.show();
}

// Function to edit a size
function editSize(id) {
    // Get size data
    var sizeData = sizesData[id];
    if (!sizeData) {
        console.error("Size data not found for ID: " + id);
        return;
    }
    
    // Set the action to "edit"
    document.getElementById("sizeFormAction").value = "edit";
    
    // Set the ID field
    document.getElementById("sizeFormId").value = id;
    
    // Populate form fields
    document.getElementById("size_name").value = sizeData.size_name ? decodeHtmlEntities(sizeData.size_name) : "";
    document.getElementById("size_value").value = sizeData.size_value ? decodeHtmlEntities(sizeData.size_value) : "";
    document.getElementById("stock_quantity").value = sizeData.stock_quantity || "0";
    
    // Set product ID
    document.getElementById("sizeFormProductId").value = "' . $product_id . '";
    
    // Update modal title
    document.getElementById("sizeModalLabel").textContent = "Edit Size";
    
    // Update submit button text
    document.getElementById("sizeSubmitBtn").textContent = "Update Size";
    
    // Show the modal
    var sizeModal = new bootstrap.Modal(document.getElementById("sizeModal"));
    sizeModal.show();
}

// Auto-focus on size name field when modal opens
document.getElementById("sizeModal").addEventListener("shown.bs.modal", function () {
    document.getElementById("size_name").focus();
});
</script>
';

require_once '../includes/footer.php';
?>