<?php
session_start();

$app_id = "YOUR_APP_ID_HERE";
$redirect_url = "https://amee-epididymal-roslyn.ngrok-free.dev/SMD/ig-callback.php"; 

// Permissions specifically for Instagram
$permissions = [
    'pages_show_list', 
    'instagram_basic', 
    'instagram_content_publish', 
    'pages_read_engagement',
    'business_management'
]; 

$login_url = "https://www.facebook.com/v24.0/dialog/oauth?client_id=" . $app_id . 
             "&redirect_uri=" . urlencode($redirect_url) . 
             "&scope=" . implode(',', $permissions) . 
             "&state=st" . $_SESSION['user_id'];

header("Location: " . $login_url);
exit();