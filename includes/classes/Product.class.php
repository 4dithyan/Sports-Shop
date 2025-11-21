<?php
// Product Class
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Get all products with optional filters
    public function getAll($filters = []) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active'";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['featured'])) {
            $sql .= " AND p.featured = 1";
        }
        
        // Sorting
        $order_by = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $order_by = 'p.price ASC';
                    break;
                case 'price_high':
                    $order_by = 'p.price DESC';
                    break;
                case 'name':
                    $order_by = 'p.name ASC';
                    break;
                case 'newest':
                    $order_by = 'p.created_at DESC';
                    break;
            }
        }
        
        $sql .= " ORDER BY {$order_by}";
        
        // Pagination
        if (!empty($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $sql .= " LIMIT {$limit}";
            
            if (!empty($filters['offset'])) {
                $offset = (int)$filters['offset'];
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get product by ID
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ? AND p.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    // Get featured products
    public function getFeatured($limit = 8) {
        $limit = (int)$limit; // Ensure it's an integer
        $sql = "SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    // Get related products
    public function getRelated($product_id, $category_id, $limit = 4) {
        $limit = (int)$limit; // Ensure it's an integer
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.category_id = ? AND p.id != ? 
                ORDER BY RAND() 
                LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category_id, $product_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get products by category
    public function getByCategory($category_id, $limit = null, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.category_id = ? 
                ORDER BY p.created_at DESC";
        
        $params = [$category_id];
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Count products with filters
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM products p 
                WHERE p.status = 'active'";
        
        $params = [];
        
        // Apply same filters as getAll
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['featured'])) {
            $sql .= " AND p.featured = 1";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    // Create new product (Admin only)
    public function create($data) {
        try {
            $sql = "INSERT INTO products (name, description, price, category_id, stock_quantity, image, featured, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category_id'],
                $data['stock_quantity'],
                $data['image'] ?? '',
                $data['featured'] ?? 0
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            } else {
                error_log("Failed to create product: " . implode(', ', $stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception in create product: " . $e->getMessage());
            return false;
        }
    }
    
    // Update product (Admin only)
    public function update($id, $data) {
        try {
            $sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    price = ?, 
                    category_id = ?, 
                    stock_quantity = ?, 
                    image = ?, 
                    featured = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category_id'],
                $data['stock_quantity'],
                $data['image'] ?? '',
                $data['featured'] ?? 0,
                $id
            ]);
            
            if ($result) {
                return $stmt->rowCount() > 0; // Return true if any rows were affected
            } else {
                error_log("Failed to update product: " . implode(', ', $stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception in update product: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete product (Admin only)
    public function delete($id) {
        $sql = "UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Update stock quantity
    public function updateStock($id, $quantity) {
        $sql = "UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $id]);
    }
    
    // Check if product is in stock
    public function isInStock($id, $quantity = 1) {
        $product = $this->getById($id);
        return $product && $product['stock_quantity'] >= $quantity;
    }
    
    // Get average rating for a product
    public function getAverageRating($product_id) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                FROM reviews 
                WHERE product_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id]);
        
        return $stmt->fetch();
    }
    
    // Get product image URL - now using online images
    public function getImageUrl($image, $productId = null) {
        // If we have a specific product ID, use the online image function
        if ($productId) {
            return getOnlineProductImageUrl($productId);
        }
        
        // If we have an image filename, try to use it
        if (!empty($image)) {
            // Check if it's already a full URL
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            // Otherwise treat it as a local image
            return UPLOAD_PATH . 'products/' . $image;
        }
        
        // Default fallback
        return 'https://images.unsplash.com/photo-1577222285500-0b5342d4df57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80';
    }
}
?>