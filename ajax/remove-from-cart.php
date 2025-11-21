<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $product_id = intval($input['product_id']);
    $cart_key = $input['cart_key'] ?? $product_id;
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    // Remove from cart
    removeFromCart($cart_key);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product removed from cart',
        'cart_count' => getCartCount(),
        'cart_total' => getCartTotal()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
