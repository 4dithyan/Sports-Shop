<?php
// Check if user is logged in - do this BEFORE including header.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'My Account - Cosmos Sports';
$page_description = 'Manage your account information, view orders, and update your profile.';

require_once 'includes/header.php';
require_once 'includes/classes/User.class.php';
require_once 'includes/classes/Order.class.php';

// Check if user is logged in (redundant now, but kept for consistency)
requireLogin();

$user = new User();
$order = new Order();

$user_info = $user->getById($_SESSION['user_id']);
$user_orders = $order->getByUser($_SESSION['user_id'], 5);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $data = [
            'first_name' => sanitizeInput($_POST['first_name']),
            'last_name' => sanitizeInput($_POST['last_name']),
            'phone' => sanitizeInput($_POST['phone']),
            'address' => sanitizeInput($_POST['address']),
            'city' => sanitizeInput($_POST['city']),
            'state' => sanitizeInput($_POST['state']),
            'zip_code' => sanitizeInput($_POST['zip_code'])
        ];
        
        if ($user->updateProfile($_SESSION['user_id'], $data)) {
            setFlashMessage('success', 'Profile updated successfully!');
            header('Location: my-account.php');
            exit();
        } else {
            setFlashMessage('error', 'Failed to update profile');
        }
    } elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            setFlashMessage('error', 'New passwords do not match');
        } else {
            $result = $user->changePassword($_SESSION['user_id'], $current_password, $new_password);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        }
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Account</h1>
        </div>
    </div>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user text-white fa-2x"></i>
                        </div>
                        <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($user_info['email']); ?></small>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#profile" data-bs-toggle="tab">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#orders" data-bs-toggle="tab">
                                <i class="fas fa-shopping-bag me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#wishlist" data-bs-toggle="tab">
                                <i class="fas fa-heart me-2"></i>Wishlist
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#password" data-bs-toggle="tab">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="my-account.php">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($user_info['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($user_info['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($user_info['email']); ?>" disabled>
                                    <small class="form-text text-muted">Email cannot be changed</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                                </div>
                                
                                <h6 class="mt-4 mb-3">Address Information</h6>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?php echo htmlspecialchars($user_info['address']); ?>">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo htmlspecialchars($user_info['city']); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo htmlspecialchars($user_info['state']); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="zip_code" class="form-label">ZIP Code</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                               value="<?php echo htmlspecialchars($user_info['zip_code']); ?>">
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div class="tab-pane fade" id="orders">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order History</h5>
                            <a href="orders.php" class="btn btn-outline-primary btn-sm">View All Orders</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($user_orders)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user_orders as $order_item): ?>
                                                <tr>
                                                    <td>#<?php echo $order_item['id']; ?></td>
                                                    <td><?php echo formatDate($order_item['created_at']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $order_item['status'] === 'completed' ? 'success' : ($order_item['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                                            <?php echo ucfirst($order_item['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo formatPrice($order_item['total']); ?></td>
                                                    <td>
                                                        <a href="order-details.php?id=<?php echo $order_item['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                    <h5>No orders yet</h5>
                                    <p class="text-muted">You haven't placed any orders yet.</p>
                                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Wishlist Tab -->
                <div class="tab-pane fade" id="wishlist">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">My Wishlist</h5>
                        </div>
                        <div class="card-body">
                            <a href="wishlist.php" class="btn btn-outline-primary mb-3">
                                <i class="fas fa-heart me-2"></i>View Full Wishlist
                            </a>
                            <p class="text-muted">Your saved items will appear here.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="password">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="my-account.php">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="form-text text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-lock me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additional_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Password confirmation validation
    const newPassword = document.getElementById("new_password");
    const confirmPassword = document.getElementById("confirm_password");
    
    function validatePassword() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords do not match");
        } else {
            confirmPassword.setCustomValidity("");
        }
    }
    
    newPassword.addEventListener("change", validatePassword);
    confirmPassword.addEventListener("keyup", validatePassword);
});
</script>
';

require_once 'includes/footer.php';
?>

