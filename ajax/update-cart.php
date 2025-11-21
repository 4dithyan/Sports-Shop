<?php
require_once '../includes/functions.php';
require_once '../includes/classes/Product.class.php';

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
    $quantity = intval($input['quantity'] ?? 1);
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if ($quantity <= 0) {
        // Remove from cart if quantity is 0 or negative
        removeFromCart($product_id);
    } else {
        // Check if product exists and is in stock
        $product = new Product();
        $product_item = $product->getById($product_id);
        
        if (!$product_item) {
            throw new Exception('Product not found');
        }
        
        if ($product_item['stock_quantity'] < $quantity) {
            throw new Exception('Insufficient stock');
        }
        
        // Update cart quantity
        updateCartQuantity($cart_key, $quantity);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated',
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
