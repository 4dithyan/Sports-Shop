<?php
// Review Class
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class Review {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Add a new review
    public function addReview($product_id, $user_id, $rating, $review_title, $review_text) {
        try {
            // Check if user has already reviewed this product
            $stmt = $this->db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'You have already reviewed this product.'];
            }
            
            // Insert the review
            $stmt = $this->db->prepare("INSERT INTO reviews (product_id, user_id, rating, review_title, review_text, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$product_id, $user_id, $rating, $review_title, $review_text]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Review added successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to add review.'];
            }
        } catch (Exception $e) {
            error_log("Exception in addReview: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while adding the review.'];
        }
    }
    
    // Get reviews for a product
    public function getReviewsByProduct($product_id, $limit = null, $offset = 0) {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? 
                ORDER BY r.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id]);
        
        return $stmt->fetchAll();
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
    
    // Get review count for a product
    public function getReviewCount($product_id) {
        $sql = "SELECT COUNT(*) as count FROM reviews WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    // Check if user has reviewed a product
    public function hasUserReviewed($product_id, $user_id) {
        $sql = "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id, $user_id]);
        return $stmt->fetch() !== false;
    }
    
    // Get user's review for a product
    public function getUserReview($product_id, $user_id) {
        $sql = "SELECT * FROM reviews WHERE product_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id, $user_id]);
        return $stmt->fetch();
    }
    
    // Update a review
    public function updateReview($review_id, $rating, $review_title, $review_text) {
        try {
            $sql = "UPDATE reviews SET rating = ?, review_title = ?, review_text = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$rating, $review_title, $review_text, $review_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Review updated successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to update review.'];
            }
        } catch (Exception $e) {
            error_log("Exception in updateReview: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the review.'];
        }
    }
    
    // Delete a review
    public function deleteReview($review_id, $user_id = null) {
        try {
            if ($user_id) {
                // Only allow user to delete their own review
                $sql = "DELETE FROM reviews WHERE id = ? AND user_id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$review_id, $user_id]);
            } else {
                // Admin can delete any review
                $sql = "DELETE FROM reviews WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$review_id]);
            }
            
            if ($result) {
                return ['success' => true, 'message' => 'Review deleted successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete review.'];
            }
        } catch (Exception $e) {
            error_log("Exception in deleteReview: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the review.'];
        }
    }
}
?>