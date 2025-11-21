<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to use wishlist']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['action'])) {
        throw new Exception('Invalid request data');
    }
    
    $product_id = intval($input['product_id']);
    $action = $input['action']; // 'add' or 'remove'
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if (!in_array($action, ['add', 'remove'])) {
        throw new Exception('Invalid action');
    }
    
    $success = false;
    $message = '';
    
    if ($action === 'add') {
        $success = addToWishlist($product_id);
        $message = $success ? 'Added to wishlist' : 'Already in wishlist';
    } else {
        $success = removeFromWishlist($product_id);
        $message = $success ? 'Removed from wishlist' : 'Not in wishlist';
    }
    
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
