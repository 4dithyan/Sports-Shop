<?php
require_once 'config.php';
require_once 'includes/db.php';

// Only allow access from localhost for security
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('Access denied. This script can only be run from localhost.');
}

try {
    $db = getDB();
    
    // Create product_sizes table
    $sql = "
    CREATE TABLE IF NOT EXISTS product_sizes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        size_name VARCHAR(50) NOT NULL,
        size_value VARCHAR(50) NOT NULL,
        stock_quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_product_size (product_id, size_name)
    )";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();
    
    if ($result) {
        echo "<h2>Success!</h2>";
        echo "<p>The product_sizes table has been created successfully.</p>";
        echo "<p>You can now manage product sizes in the admin panel.</p>";
    } else {
        echo "<h2>Error</h2>";
        echo "<p>Failed to create the product_sizes table.</p>";
        echo "<p>Error info: " . print_r($stmt->errorInfo(), true) . "</p>";
    }
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>An exception occurred: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='admin/sizes.php'>Go to Product Sizes Management</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>