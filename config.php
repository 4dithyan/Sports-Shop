<?php
// COSMOS - The Digital Universe of Peak Performance
// Database and site-wide settings

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'project');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'COSMOS - The Digital Universe of Peak Performance');
define('SITE_URL', 'http://localhost/PROJECTS/project');
define('SITE_EMAIL', 'info@cosmos.com');
define('SITE_PHONE', '+91 98765 43210');

// Security
define('SECRET_KEY', 'your-secret-key-here-change-this');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// E-commerce Settings
define('CURRENCY', 'INR');
define('CURRENCY_SYMBOL', '₹');
define('TAX_RATE', 0.18); // 18% GST rate
define('SHIPPING_COST', 99.00);
define('FREE_SHIPPING_THRESHOLD', 999.00);

// File Upload Settings
define('UPLOAD_PATH', '/assets/images/products/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>