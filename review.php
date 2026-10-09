<?php
// review.php - handle review submission
include_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pkg = intval($_POST['package_id'] ?? 0);
    $name = $conn->real_escape_string(trim($_POST['user_name'] ?? ''));
    $rating = intval($_POST['rating'] ?? 5);
    $comment = $conn->real_escape_string(trim($_POST['comment'] ?? ''));
    if ($pkg && $name && $comment) {
        $sql = "INSERT INTO reviews (package_id, user_name, rating, comment) VALUES ($pkg, '$name', $rating, '$comment')";
        if ($conn->query($sql)) {
            header('Location: package_details.php?id=' . $pkg . '&review=1');
            exit;
        } else {
            echo 'DB Error: ' . $conn->error;
        }
    } else {
        echo 'Missing fields';
    }
}
