<?php
// Quick Database Setup Script
// This script will create the database and essential tables

require_once 'config.php';

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database created successfully!\n";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            image VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(50),
            state VARCHAR(50),
            zip_code VARCHAR(10),
            role ENUM('customer', 'admin') DEFAULT 'customer',
            status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            stock_quantity INT DEFAULT 0,
            image VARCHAR(255),
            featured BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
            subtotal DECIMAL(10,2) NOT NULL,
            tax DECIMAL(10,2) NOT NULL,
            shipping DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            shipping_address TEXT,
            shipping_city VARCHAR(50),
            shipping_state VARCHAR(50),
            shipping_zip VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist (user_id, product_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            review_title VARCHAR(255),
            review_text TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product_review (user_id, product_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS product_sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            size_name VARCHAR(50) NOT NULL,
            size_value VARCHAR(50) NOT NULL,
            stock_quantity INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_size (product_id, size_name)
        )"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    echo "Tables created successfully!\n";
    
    // Insert sample data
    $pdo->exec("INSERT IGNORE INTO categories (name, description) VALUES
        ('Football', 'American football equipment and gear'),
        ('Basketball', 'Basketball equipment and apparel'),
        ('Soccer', 'Soccer equipment and gear'),
        ('Baseball', 'Baseball equipment and accessories'),
        ('Tennis', 'Tennis equipment and gear'),
        ('Golf', 'Golf equipment and accessories'),
        ('Running', 'Running shoes and apparel'),
        ('Fitness', 'Fitness equipment and accessories')");
    
    $pdo->exec("INSERT IGNORE INTO users (first_name, last_name, email, password, role) VALUES
        ('Admin', 'User', 'admin@cosmossports.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
    
    $pdo->exec("INSERT IGNORE INTO products (name, description, price, category_id, stock_quantity, featured) VALUES
        ('Professional Football Helmet', 'High-quality football helmet with advanced protection technology.', 299.99, 1, 50, 1),
        ('Basketball Official Size 7', 'Official size and weight basketball with premium leather construction.', 89.99, 2, 100, 1),
        ('Soccer Cleats - Professional Grade', 'Lightweight soccer cleats with superior traction and comfort.', 159.99, 3, 75, 1),
        ('Baseball Glove - Premium Leather', 'Professional-grade baseball glove made from premium leather.', 199.99, 4, 60, 0),
        ('Tennis Racket - Carbon Fiber', 'High-performance tennis racket with carbon fiber construction.', 249.99, 5, 40, 0),
        ('Golf Driver - Titanium Head', 'Professional golf driver with titanium head for maximum distance.', 399.99, 6, 30, 1),
        ('Running Shoes - Cushioned', 'Comfortable running shoes with advanced cushioning technology.', 129.99, 7, 120, 0),
        ('Dumbbell Set - Adjustable', 'Adjustable dumbbell set with multiple weight options.', 199.99, 8, 25, 0)");
    
    echo "Sample data inserted successfully!\n";
    echo "\nSetup completed! You can now access:\n";
    echo "- Website: " . SITE_URL . "\n";
    echo "- Admin Panel: " . SITE_URL . "/admin/dashboard.php\n";
    echo "- Admin Login: admin@cosmossports.com / admin123\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>