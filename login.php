<?php
// Check if user is already logged in - do this BEFORE including header.php
require_once 'config.php';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Check if user is admin and redirect to admin dashboard
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

$page_title = 'Login - Cosmos Sports';
$page_description = 'Sign in to your Cosmos Sports account to access your orders, wishlist, and more.';

// Process login BEFORE including header.php to avoid headers already sent error
// But we need to include the functions file to access sanitizeInput()
require_once 'includes/functions.php';
require_once 'includes/classes/User.class.php';
$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        setFlashMessage('error', 'Please fill in all fields');
    } else {
        $result = $user->login($email, $password);
        
        if ($result['success']) {
            setFlashMessage('success', 'Login successful! Welcome back.');
            
            // Check if user is admin and redirect to admin dashboard
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin/dashboard.php');
                exit();
            } else {
                // Redirect to intended page or home for regular users
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
}

// Only include header after processing login to avoid headers already sent error
require_once 'includes/header.php';

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <a href="forgot-password.php" class="text-decoration-none">
                                Forgot your password?
                            </a>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account?</p>
                        <a href="register.php" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </a>
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
    // Toggle password visibility
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
    
    togglePassword.addEventListener("click", function() {
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);
        
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    });
    
    // Form validation
    const form = document.querySelector("form");
    form.addEventListener("submit", function(e) {
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        
        if (!email || !password) {
            e.preventDefault();
            showAlert("Please fill in all fields", "error");
        }
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
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
';

require_once 'includes/footer.php';
?>