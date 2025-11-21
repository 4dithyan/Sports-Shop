<?php
// Add Review AJAX Endpoint
require_once '../includes/functions.php';
require_once '../includes/classes/Review.class.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add a review.']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get POST data
$product_id = $_POST['product_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$review_title = sanitizeInput($_POST['review_title'] ?? '');
$review_text = sanitizeInput($_POST['review_text'] ?? '');
$review_id = $_POST['review_id'] ?? 0;

// Validate input
if (empty($product_id) || empty($rating) || empty($review_title) || empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5.']);
    exit();
}

if (strlen($review_title) < 3) {
    echo json_encode(['success' => false, 'message' => 'Review title must be at least 3 characters long.']);
    exit();
}

if (strlen($review_text) < 10) {
    echo json_encode(['success' => false, 'message' => 'Review text must be at least 10 characters long.']);
    exit();
}

$review = new Review();

// If review_id is provided, update existing review
if (!empty($review_id)) {
    $result = $review->updateReview($review_id, $rating, $review_title, $review_text);
} else {
    // Add new review
    $result = $review->addReview($product_id, $_SESSION['user_id'], $rating, $review_title, $review_text);
}

echo json_encode($result);
?>