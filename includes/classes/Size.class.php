<?php
// Size Class
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class Size {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Add a new size for a product
    public function addSize($product_id, $size_name, $size_value, $stock_quantity = 0) {
        try {
            $sql = "INSERT INTO product_sizes (product_id, size_name, size_value, stock_quantity, created_at) 
                    VALUES (?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    size_value = VALUES(size_value), 
                    stock_quantity = VALUES(stock_quantity), 
                    updated_at = NOW()";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$product_id, $size_name, $size_value, $stock_quantity]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Size added/updated successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to add/update size.'];
            }
        } catch (Exception $e) {
            error_log("Exception in addSize: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while adding/updating the size.'];
        }
    }
    
    // Get all sizes for a product
    public function getSizesByProduct($product_id) {
        $sql = "SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll();
    }
    
    // Get a specific size by ID
    public function getSizeById($size_id) {
        $sql = "SELECT * FROM product_sizes WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$size_id]);
        return $stmt->fetch();
    }
    
    // Update a size
    public function updateSize($size_id, $size_name, $size_value, $stock_quantity) {
        try {
            $sql = "UPDATE product_sizes SET size_name = ?, size_value = ?, stock_quantity = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$size_name, $size_value, $stock_quantity, $size_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Size updated successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to update size.'];
            }
        } catch (Exception $e) {
            error_log("Exception in updateSize: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the size.'];
        }
    }
    
    // Delete a size
    public function deleteSize($size_id) {
        try {
            $sql = "DELETE FROM product_sizes WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$size_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Size deleted successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete size.'];
            }
        } catch (Exception $e) {
            error_log("Exception in deleteSize: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the size.'];
        }
    }
    
    // Update stock quantity for a specific size
    public function updateStock($size_id, $quantity) {
        $sql = "UPDATE product_sizes SET stock_quantity = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $size_id]);
    }
    
    // Check if a size is in stock
    public function isInStock($size_id, $quantity = 1) {
        $size = $this->getSizeById($size_id);
        return $size && $size['stock_quantity'] >= $quantity;
    }
}
?>