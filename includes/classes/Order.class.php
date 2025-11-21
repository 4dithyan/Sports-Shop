<?php
// Order Class
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class Order {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Create new order
    public function create($user_id, $cart_items, $shipping_info) {
        try {
            $this->db->beginTransaction();
            
            // Calculate totals
            $subtotal = 0;
            $processed_items = [];
            
            // Process cart items to extract product IDs and quantities
            foreach ($cart_items as $key => $item) {
                if (is_array($item)) {
                    // Composite key structure (product with size)
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                } else {
                    // Simple structure (product without size)
                    $product_id = $key;
                    $quantity = $item;
                }
                
                $processed_items[$product_id] = [
                    'quantity' => $quantity,
                    'key' => $key
                ];
            }
            
            $product_ids = array_keys($processed_items);
            
            // Get product prices and validate stock
            $sql = "SELECT id, price, stock_quantity FROM products WHERE id IN (" . implode(',', array_fill(0, count($product_ids), '?')) . ")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll();
            
            $product_prices = [];
            foreach ($products as $product) {
                $product_id = $product['id'];
                $quantity = $processed_items[$product_id]['quantity'];
                
                $product_prices[$product_id] = $product['price'];
                
                // Check stock
                if ($product['stock_quantity'] < $quantity) {
                    throw new Exception("Insufficient stock for product ID: " . $product_id);
                }
                
                $subtotal += $product['price'] * $quantity;
            }
            
            $tax = $subtotal * TAX_RATE;
            $shipping = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_COST;
            $total = $subtotal + $tax + $shipping;
            
            // Create order
            $sql = "INSERT INTO orders (user_id, status, subtotal, tax, shipping, total, shipping_address, shipping_city, shipping_state, shipping_zip, created_at) 
                    VALUES (?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $user_id,
                $subtotal,
                $tax,
                $shipping,
                $total,
                $shipping_info['address'],
                $shipping_info['city'],
                $shipping_info['state'],
                $shipping_info['zip']
            ]);
            
            $order_id = $this->db->lastInsertId();
            
            // Create order items and update stock
            foreach ($processed_items as $product_id => $item_data) {
                $quantity = $item_data['quantity'];
                $price = $product_prices[$product_id];
                
                // Insert order item
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$order_id, $product_id, $quantity, $price]);
                
                // Update product stock
                $sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$quantity, $product_id]);
            }
            
            $this->db->commit();
            return ['success' => true, 'order_id' => $order_id];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Get order by ID
    public function getById($id) {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Get order items
    public function getItems($order_id) {
        $sql = "SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll();
    }
    
    // Get orders by user
    public function getByUser($user_id, $limit = null, $offset = 0) {
        $sql = "SELECT o.*, COUNT(oi.id) as item_count 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";
        
        $params = [$user_id];
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get all orders (Admin only)
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email, COUNT(oi.id) as item_count 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Count orders
    public function count($filters = []) {
        $sql = "SELECT COUNT(DISTINCT o.id) as total 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        // Apply same filters as getAll
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    // Update order status
    public function updateStatus($id, $status) {
        $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    // Get order statistics (Admin dashboard)
    public function getStats() {
        $stats = [];
        
        // Total orders
        $sql = "SELECT COUNT(*) as total FROM orders";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch()['total'];
        
        // Total revenue
        $sql = "SELECT SUM(total) as total FROM orders WHERE status IN ('completed', 'shipped', 'delivered')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        // Orders this month
        $sql = "SELECT COUNT(*) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['orders_this_month'] = $stmt->fetch()['total'];
        
        // Revenue this month
        $sql = "SELECT SUM(total) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND status IN ('completed', 'shipped', 'delivered')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['revenue_this_month'] = $stmt->fetch()['total'] ?? 0;
        
        // Pending orders
        $sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['pending_orders'] = $stmt->fetch()['total'];
        
        return $stats;
    }
    
    // Get recent orders (Admin dashboard)
    public function getRecent($limit = 10) {
        $limit = (int)$limit;
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Cancel order
    public function cancel($id, $user_id = null) {
        $sql = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $params = [$id];
        
        if ($user_id) {
            $sql .= " AND user_id = ?";
            $params[] = $user_id;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    // Get order status options
    public function getStatusOptions() {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded'
        ];
    }
}
?>
