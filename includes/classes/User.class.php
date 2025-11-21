<?php
// User Class
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Register new user
    public function register($data) {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, zip_code, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashed_password,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['zip_code'] ?? null
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    // Login user
    public function login($email, $password) {
        $sql = "SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                return ['success' => true, 'message' => 'Login successful', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Account is inactive'];
            }
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    }
    
    // Get user by ID
    public function getById($id) {
        $sql = "SELECT id, first_name, last_name, email, phone, address, city, state, zip_code, role, status, created_at 
                FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Get user by email
    public function getByEmail($email) {
        $sql = "SELECT id, first_name, last_name, email, phone, address, city, state, zip_code, role, status, created_at 
                FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    // Update user profile
    public function updateProfile($id, $data) {
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                phone = ?, 
                address = ?, 
                city = ?, 
                state = ?, 
                zip_code = ?, 
                updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['zip_code'],
            $id
        ]);
    }
    
    // Change password
    public function changePassword($id, $current_password, $new_password) {
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($current_password, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$hashed_password, $id])) {
            return ['success' => true, 'message' => 'Password updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    }
    
    // Get user orders
    public function getOrders($user_id, $limit = null, $offset = 0) {
        $sql = "SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.quantity * oi.price) as total_amount 
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
    
    // Get user wishlist
    public function getWishlist($user_id) {
        $sql = "SELECT p.*, c.name as category_name, w.created_at as added_date 
                FROM wishlist w 
                JOIN products p ON w.product_id = p.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE w.user_id = ? AND p.status = 'active' 
                ORDER BY w.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Check if email exists
    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    // Get all users (Admin only)
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT id, first_name, last_name, email, phone, role, status, created_at 
                FROM users 
                ORDER BY created_at DESC";
        
        $params = [];
        
        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Count users
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    // Count users by role
    public function countByRole($role) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE role = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    // Count users by status
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    // Update user status (Admin only)
    public function updateStatus($id, $status) {
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    // Update user role (Admin only)
    public function updateRole($id, $role) {
        $sql = "UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$role, $id]);
    }
    
    // Delete user (Admin only)
    public function delete($id) {
        $sql = "UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Logout user
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Validate user data
    public function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }
        
        return $errors;
    }
}
?>