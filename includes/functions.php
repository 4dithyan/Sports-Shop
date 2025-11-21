<?php
// The Athlete's Arsenal - Utility Functions
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Security Functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// User Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Order Functions
function getOrderStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Cart Functions
function getCartCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function addToCart($product_id, $quantity = 1, $size_id = null) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Create a unique key for the cart item (product_id + size_id)
    $cart_key = $size_id ? "{$product_id}_{$size_id}" : $product_id;
    
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'size_id' => $size_id
        ];
    }
}

function removeFromCart($cart_key) {
    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
    }
}

function updateCartQuantity($cart_key, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($cart_key);
    } else {
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
        }
    }
}

function getCartTotal() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    $db = getDB();
    
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        $stmt = $db->prepare("SELECT price FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }
    
    return $total;
}

// Wishlist Functions
function addToWishlist($product_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    // Check if already in wishlist
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    
    if ($stmt->fetch()) {
        return false; // Already in wishlist
    }
    
    // Add to wishlist
    $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$user_id, $product_id]);
}

function removeFromWishlist($product_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

function isInWishlist($product_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    
    return $stmt->fetch() !== false;
}

// Utility Functions
function formatPrice($price) {
    // For Indian Rupees, we'll format with commas as per Indian numbering system
    if (CURRENCY === 'INR') {
        // Convert to integer first to avoid floating point issues
        $price = round($price, 2);
        
        // Split the number into integer and decimal parts
        $parts = explode('.', number_format($price, 2, '.', ''));
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '00';
        
        // Format the integer part with Indian numbering system
        // For numbers >= 100000, we use lakhs and crores format
        if (strlen($integerPart) > 3) {
            $lastThree = substr($integerPart, -3);
            $rest = substr($integerPart, 0, -3);
            
            // Add commas for thousands, lakhs, crores, etc.
            $formattedInteger = '';
            while (strlen($rest) > 2) {
                $formattedInteger = ',' . substr($rest, -2) . $formattedInteger;
                $rest = substr($rest, 0, -2);
            }
            
            if (strlen($rest) > 0) {
                $formattedInteger = $rest . $formattedInteger;
            }
            
            $formattedPrice = $formattedInteger . ',' . $lastThree;
        } else {
            $formattedPrice = $integerPart;
        }
        
        return CURRENCY_SYMBOL . $formattedPrice . '.' . $decimalPart;
    } else {
        return CURRENCY_SYMBOL . number_format($price, 2);
    }
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

function uploadImage($file, $directory = 'products') {
    // Use a more reliable way to get the document root
    $document_root = realpath($_SERVER['DOCUMENT_ROOT']) ?: dirname(__DIR__);
    $upload_path = $document_root . UPLOAD_PATH . $directory . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $upload_path . $file_name;
    
    // Validate file
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        error_log("Invalid file extension: " . $file_extension);
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        error_log("File too large: " . $file['size']);
        return false;
    }
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return $file_name;
    } else {
        error_log("Failed to move uploaded file. Error: " . print_r(error_get_last(), true));
        return false;
    }
}

function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = 'From: ' . SITE_EMAIL . "\r\n" .
                   'Reply-To: ' . SITE_EMAIL . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
    }
    
    return mail($to, $subject, $message, $headers);
}

// Pagination Function
function paginate($total_records, $records_per_page, $current_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'offset' => $offset,
        'current_page' => $current_page,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'prev_page' => $current_page - 1,
        'next_page' => $current_page + 1
    ];
}

// Flash Messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function displayFlashMessages() {
    $types = ['success', 'error', 'warning', 'info'];
    $output = '';
    
    foreach ($types as $type) {
        $message = getFlashMessage($type);
        if ($message) {
            $output .= "<div class='alert alert-{$type}'>{$message}</div>";
        }
    }
    
    return $output;
}

// Function to get online product image URL
function getOnlineProductImageUrl($productId, $productName = '') {
    // Array of sports equipment image URLs from Unsplash
    $images = [
        1 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Cricket bat
        2 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Cricket bat
        3 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Cricket kit
        4 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Football
        5 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Football
        6 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Badminton racket
        7 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Badminton racket
        8 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Badminton shuttlecock
        9 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Basketball
        10 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Tennis racket
        11 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Swimming goggles
        12 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Swimming cap
        13 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Running shoes
        14 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Running shoes
        15 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Dumbbell set
        16 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Yoga mat
        17 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Cricket jersey
        18 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Badminton kit
        // Additional generic sports images for products with higher IDs
        19 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        20 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        21 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        22 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        23 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        24 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        25 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        26 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        27 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        28 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        29 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        30 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        31 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        32 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        33 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        34 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        35 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        36 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        37 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        38 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        39 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        40 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        41 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        42 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        43 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        44 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
        45 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80'
    ];
    
    // Return specific image if exists, otherwise return a generic sports image
    if (isset($images[$productId])) {
        return $images[$productId];
    }
    
    // Generic sports equipment image
    return 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80';
}

// Function to get online category image URL
function getOnlineCategoryImageUrl($categoryId, $categoryName = '') {
    // Array of category image URLs from Unsplash
    $images = [
        1 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Cricket
        2 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Football
        3 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Badminton
        4 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Basketball
        5 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Tennis
        6 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Swimming
        7 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Running
        8 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Fitness
        9 => 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', // Cycling
        10 => 'https://images.unsplash.com/photo-1523978512162-0fc9e49aab00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80' // Yoga
    ];
    
    // Return specific image if exists, otherwise return a generic sports image
    if (isset($images[$categoryId])) {
        return $images[$categoryId];
    }
    
    // Generic sports equipment image
    return 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80';
}
?>