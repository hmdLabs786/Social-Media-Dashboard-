<?php
session_start();

$app_id = "YOUR_APP_ID_HERE";
$redirect_url = "https://amee-epididymal-roslyn.ngrok-free.dev/SMD/fb-callback.php"; // Replace with your callback URL
$permissions = ['pages_manage_posts', 'pages_read_engagement', 'pages_show_list']; // Permissions needed to post to pages

$login_url = "https://www.facebook.com/v24.0/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . urlencode($redirect_url) . "&scope=" . implode(',', $permissions) . "&state=st" . $_SESSION['user_id'];

header("Location: " . $login_url);
exit();