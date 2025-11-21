<?php
// Check if user is admin - do this BEFORE including header.php
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/classes/Order.class.php';

// Handle status update BEFORE including header.php to avoid output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
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

    $order = new Order();
    $order_id = intval($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    
    if ($order->updateStatus($order_id, $status)) {
        setFlashMessage('success', 'Order status updated successfully!');
    } else {
        setFlashMessage('error', 'Failed to update order status');
    }
    // Redirect to avoid resubmission
    header('Location: orders.php?' . http_build_query($_GET));
    exit();
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

$page_title = 'Manage Orders - Admin Panel';
$page_description = 'View and manage customer orders in the admin panel.';

require_once '../includes/header.php';

// Check if user is admin (redundant now, but kept for consistency)
requireAdmin();

$order = new Order();

// Get filters
$filters = [];
$filters['status'] = $_GET['status'] ?? '';
$filters['date_from'] = $_GET['date_from'] ?? '';
$filters['date_to'] = $_GET['date_to'] ?? '';
$filters['search'] = $_GET['search'] ?? '';

// Get orders with pagination
$current_page = max(1, $_GET['page'] ?? 1);
$pagination = paginate($order->count($filters), 10, $current_page);
$orders = $order->getAll($filters, 10, $pagination['offset']);

$status_options = $order->getStatusOptions();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Manage Orders</h1>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="orders.php">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($status_options as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" 
                                                <?php echo $filters['status'] === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Order ID, Customer Name, Email" 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="orders.php" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $order_item): ?>
                                        <tr>
                                            <td>#<?php echo $order_item['id']; ?></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($order_item['first_name'] . ' ' . $order_item['last_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($order_item['email']); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo formatDate($order_item['created_at']); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;" class="status-form">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm" 
                                                            onchange="this.form.submit()">
                                                        <?php foreach ($status_options as $value => $label): ?>
                                                            <option value="<?php echo $value; ?>" 
                                                                    <?php echo $order_item['status'] === $value ? 'selected' : ''; ?>>
                                                                <?php echo $label; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo formatPrice($order_item['total']); ?></td>
                                            <td><?php echo $order_item['item_count']; ?> items</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="order-details.php?id=<?php echo $order_item['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="order-details.php?id=<?php echo $order_item['id']; ?>&print=1" 
                                                       class="btn btn-outline-secondary btn-sm" target="_blank">
                                                        <i class="fas fa-print"></i> Print
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Orders pagination">
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
document.querySelectorAll(".status-form").forEach(form => {
    form.addEventListener("submit", function(e) {
        e.preventDefault();
        
        if (confirm("Are you sure you want to update this order status?")) {
            this.submit();
        }
    });
});
</script>
';

require_once '../includes/footer.php';
?>