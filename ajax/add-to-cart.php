<?php
require_once '../includes/functions.php';
require_once '../includes/classes/Product.class.php';
require_once '../includes/classes/Size.class.php';

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
    $quantity = intval($input['quantity'] ?? 1);
    $size_id = isset($input['size_id']) ? intval($input['size_id']) : null;
    
    if ($product_id <= 0 || $quantity <= 0) {
        throw new Exception('Invalid product ID or quantity');
    }
    
    // Check if product exists and is in stock
    $product = new Product();
    $product_item = $product->getById($product_id);
    
    if (!$product_item) {
        throw new Exception('Product not found');
    }
    
    // If size is selected, check size stock
    if ($size_id) {
        $size = new Size();
        $size_item = $size->getSizeById($size_id);
        
        // Verify the size belongs to this product
        if (!$size_item || $size_item['product_id'] != $product_id) {
            throw new Exception('Invalid size selection');
        }
        
        // Check if size has enough stock
        if ($size_item['stock_quantity'] < $quantity) {
            throw new Exception('Insufficient stock for selected size');
        }
    } else {
        // Check product stock if no size selected
        if ($product_item['stock_quantity'] < $quantity) {
            throw new Exception('Insufficient stock');
        }
    }
    
    // Add to cart (with size if selected)
    addToCart($product_id, $quantity, $size_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
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
