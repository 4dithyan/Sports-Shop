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

    require_once '../includes/classes/User.class.php';
    
    $user = new User();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_role':
                $id = intval($_POST['id']);
                $role = sanitizeInput($_POST['role']);
                
                if ($user->updateRole($id, $role)) {
                    setFlashMessage('success', 'User role updated successfully!');
                } else {
                    setFlashMessage('error', 'Failed to update user role');
                }
                break;
                
            case 'update_status':
                $id = intval($_POST['id']);
                $status = sanitizeInput($_POST['status']);
                
                if ($user->updateStatus($id, $status)) {
                    setFlashMessage('success', 'User status updated successfully!');
                } else {
                    setFlashMessage('error', 'Failed to update user status');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                if ($user->delete($id)) {
                    setFlashMessage('success', 'User deleted successfully!');
                } else {
                    setFlashMessage('error', 'Failed to delete user');
                }
                break;
        }
        // Redirect to avoid resubmission
        header('Location: users.php?' . http_build_query($_GET));
        exit();
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

$page_title = 'Manage Users - Admin Panel';
$page_description = 'View and manage users in the admin panel.';

require_once '../includes/header.php';
require_once '../includes/classes/User.class.php';

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

$user = new User();

// Get filters
$filters = [];
$filters['role'] = $_GET['role'] ?? '';
$filters['status'] = $_GET['status'] ?? '';
$filters['search'] = $_GET['search'] ?? '';

// Get users with pagination
$current_page = max(1, $_GET['page'] ?? 1);
$pagination = paginate($user->count(), 10, $current_page);
$users = $user->getAll(10, $pagination['offset']);

// Get user counts for statistics
$total_users = $user->count();
$admin_users = $user->countByRole('admin');
$customer_users = $user->countByRole('customer');
$active_users = $user->countByStatus('active');
$inactive_users = $user->countByStatus('inactive');
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Manage Users</h1>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_users); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Admin Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($admin_users); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($active_users); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Inactive Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($inactive_users); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="users.php">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">All Roles</option>
                                    <option value="admin" <?php echo ($filters['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="customer" <?php echo ($filters['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php echo ($filters['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($filters['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="deleted" <?php echo ($filters['status'] === 'deleted') ? 'selected' : ''; ?>>Deleted</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Name, Email" 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="users.php" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Users Table -->
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
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user_item): ?>
                                        <tr>
                                            <td><?php echo $user_item['id']; ?></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user_item['first_name'] . ' ' . $user_item['last_name']); ?></strong>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;" class="role-form">
                                                    <input type="hidden" name="action" value="update_role">
                                                    <input type="hidden" name="id" value="<?php echo $user_item['id']; ?>">
                                                    <select name="role" class="form-select form-select-sm" 
                                                            onchange="this.form.submit()">
                                                        <option value="customer" <?php echo ($user_item['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                                        <option value="admin" <?php echo ($user_item['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;" class="status-form">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $user_item['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm" 
                                                            onchange="this.form.submit()">
                                                        <option value="active" <?php echo ($user_item['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo ($user_item['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                        <option value="deleted" <?php echo ($user_item['status'] === 'deleted') ? 'selected' : ''; ?>>Deleted</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo formatDate($user_item['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $user_item['id']; ?>">
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
                                        <td colspan="7" class="text-center">No users found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Users pagination">
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

<?php
$additional_scripts = '
<script>
// Auto-submit forms with confirmation
document.querySelectorAll(".role-form, .status-form").forEach(form => {
    form.addEventListener("submit", function(e) {
        e.preventDefault();
        
        if (confirm("Are you sure you want to update this user?")) {
            this.submit();
        }
    });
});
</script>
';

require_once '../includes/footer.php';
?>