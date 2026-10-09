<?php
// download_invoice.php - serves generated PDF files from system temp dir in demo
$file = $_GET['file'] ?? '';
$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($file);
if (file_exists($path)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    readfile($path);
    exit;
} else {
    echo 'File not found.';
}
