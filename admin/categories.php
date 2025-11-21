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

    require_once '../includes/classes/Category.class.php';
    
    $category = new Category();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'status' => sanitizeInput($_POST['status'])
                ];
                
                if ($category->create($data)) {
                    setFlashMessage('success', 'Category added successfully!');
                    header('Location: categories.php');
                    exit();
                } else {
                    setFlashMessage('error', 'Failed to add category');
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'status' => sanitizeInput($_POST['status'])
                ];
                
                if ($category->update($id, $data)) {
                    setFlashMessage('success', 'Category updated successfully!');
                    header('Location: categories.php');
                    exit();
                } else {
                    setFlashMessage('error', 'Failed to update category');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                if ($category->delete($id)) {
                    setFlashMessage('success', 'Category deleted successfully!');
                } else {
                    setFlashMessage('error', 'Failed to delete category');
                }
                header('Location: categories.php');
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

$page_title = 'Manage Categories - Admin Panel';
$page_description = 'Add, edit, and manage categories in the admin panel.';

require_once '../includes/header.php';
require_once '../includes/classes/Category.class.php';

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

$category = new Category();

// Get categories with pagination
$current_page = max(1, $_GET['page'] ?? 1);
$filters = [];
$filters['search'] = $_GET['search'] ?? '';
$pagination = paginate($category->count($filters), 10, $current_page);
$categories = $category->getAll($filters);

// Get category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_category = $category->getById($_GET['edit']);
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage Categories</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="fas fa-plus me-2"></i>Add Category
                </button>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="categories.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Category name or description" 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="categories.php" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categories Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category_item): ?>
                                        <tr>
                                            <td><?php echo $category_item['id']; ?></td>
                                            <td><?php echo htmlspecialchars($category_item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($category_item['description']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $category_item['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($category_item['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($category_item['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="editCategory(<?php echo $category_item['id']; ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $category_item['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No categories found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Categories pagination">
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

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">
                        <?php echo $edit_category ? 'Edit Category' : 'Add Category'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo (!$edit_category || $edit_category['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_category && $edit_category['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script>
// Function to populate edit modal
function editCategory(id) {
    // In a real implementation, you would fetch the category data via AJAX
    // For now, we\'ll just redirect to the page with the edit parameter
    window.location.href = "categories.php?edit=" + id;
}

// Auto-focus on name field when modal opens
document.getElementById("categoryModal").addEventListener("shown.bs.modal", function () {
    document.getElementById("name").focus();
});
</script>
';

require_once '../includes/footer.php';
?>