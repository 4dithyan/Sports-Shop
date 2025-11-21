<?php
// Category Class
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Get all categories
    public function getAll($filters = []) {
        $sql = "SELECT * FROM categories WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get category by ID
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Create new category
    public function create($data) {
        $sql = "INSERT INTO categories (name, description, status) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['status'] ?? 'active'
        ]);
    }
    
    // Update category
    public function update($id, $data) {
        $sql = "UPDATE categories SET name = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }
    
    // Delete category (soft delete)
    public function delete($id) {
        $sql = "UPDATE categories SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Get category count
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM categories WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    // Get categories for dropdown (active only)
    public function getActiveCategories() {
        $sql = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>