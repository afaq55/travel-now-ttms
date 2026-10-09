<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to rate']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$rating = intval($data['rating']);

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit;
}

try {
    // Check if user already rated
    $check_stmt = $pdo->prepare("SELECT id FROM footer_ratings WHERE user_id = ?");
    $check_stmt->execute([$_SESSION['user_id']]);
    
    if ($check_stmt->fetch()) {
        // Update existing rating
        $stmt = $pdo->prepare("UPDATE footer_ratings SET rating = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$rating, $_SESSION['user_id']]);
    } else {
        // Insert new rating
        $stmt = $pdo->prepare("INSERT INTO footer_ratings (user_id, rating) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $rating]);
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>