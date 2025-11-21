<?php
// Check if user is already logged in - do this BEFORE including header.php
// Session is already started in config.php, so we don't need to start it again
// But we need to include config.php to access the session
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/classes/User.class.php';

// Process registration before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $data = [
        'first_name' => sanitizeInput($_POST['first_name']),
        'last_name' => sanitizeInput($_POST['last_name']),
        'email' => sanitizeInput($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'phone' => sanitizeInput($_POST['phone']),
        'address' => sanitizeInput($_POST['address']),
        'city' => sanitizeInput($_POST['city']),
        'state' => sanitizeInput($_POST['state']),
        'zip_code' => sanitizeInput($_POST['zip_code'])
    ];
    
    // Validate data
    $errors = $user->validateRegistration($data);
    
    if (empty($errors)) {
        $result = $user->register($data);
        
        if ($result['success']) {
            setFlashMessage('success', 'Registration successful! You can now sign in.');
            header('Location: login.php');
            exit();
        } else {
            setFlashMessage('error', $result['message']);
        }
    } else {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$page_title = 'Register - Cosmos Sports';
$page_description = 'Create your Cosmos Sports account to start shopping and track your orders.';

// Include header after all redirects
require_once 'includes/header.php';

$user = new User();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Join Cosmos Sports today</p>
                    </div>
                    
                    <form method="POST" action="register.php">
                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <h6 class="mt-4 mb-3">Shipping Address (Optional)</h6>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="zip_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                       value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                and <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account?</p>
                        <a href="login.php" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
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
    
    // Toggle confirm password visibility
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const confirmPasswordInput = document.getElementById("confirm_password");
    
    toggleConfirmPassword.addEventListener("click", function() {
        const type = confirmPasswordInput.getAttribute("type") === "password" ? "text" : "password";
        confirmPasswordInput.setAttribute("type", type);
        
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    });
    
    // Password confirmation validation
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords do not match");
        } else {
            confirmPassword.setCustomValidity("");
        }
    }
    
    password.addEventListener("change", validatePassword);
    confirmPassword.addEventListener("keyup", validatePassword);
    
    // Form validation
    const form = document.querySelector("form");
    form.addEventListener("submit", function(e) {
        const requiredFields = form.querySelectorAll("[required]");
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add("is-invalid");
                isValid = false;
            } else {
                field.classList.remove("is-invalid");
            }
        });
        
        // Check password match
        if (password.value !== confirmPassword.value) {
            confirmPassword.classList.add("is-invalid");
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            showAlert("Please fill in all required fields and ensure passwords match", "error");
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
