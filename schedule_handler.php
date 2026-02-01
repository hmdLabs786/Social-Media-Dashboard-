<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $user_id = $_SESSION['user_id'];
    $caption = $_POST['caption'];
    $platforms = implode(',', $_POST['platforms']); // Convert array ['fb','ig'] to string "fb,ig"
    $scheduled_at = $_POST['scheduled_at'];

    // Handle Image Upload
    $upload_dir = 'uploads/';
    $file_name = time() . '_' . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_url = "https://amee-epididymal-roslyn.ngrok-free.dev/SMD/" . $target_file;

        // Insert into Database
        $stmt = $pdo->prepare("INSERT INTO scheduled_posts (user_id, caption, image_url, platforms, scheduled_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $caption, $image_url, $platforms, $scheduled_at]);

        header("Location: dashboard.php?status=scheduled");
        exit();
    }
}