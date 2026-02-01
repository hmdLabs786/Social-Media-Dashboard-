<?php
session_start();
require 'db.php'; // Your database connection

$app_id = "YOUR_APP_ID_HERE";
$app_secret = "YOUR_SECRET_KEY_HERE";
$redirect_url = "https://amee-epididymal-roslyn.ngrok-free.dev/SMD/fb-callback.php"; 

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 1. Exchange Code for short-lived User Access Token
    $token_url = "https://graph.facebook.com/v24.0/oauth/access_token?client_id=$app_id&redirect_uri=" . urlencode($redirect_url) . "&client_secret=$app_secret&code=$code";
    
    $response = file_get_contents($token_url);
    $params = json_decode($response, true);
    $user_token = $params['access_token'];

    // 2. Since we want to schedule posts, we usually need a PAGE token
    // Let's get the list of pages the user manages
    $pages_url = "https://graph.facebook.com/me/accounts?access_token=" . $user_token;
    $pages_response = file_get_contents($pages_url);
    $pages_data = json_decode($pages_response, true);

    if (!empty($pages_data['data'])) {
        // For this example, we'll just link the first page found
        $page_id = $pages_data['data'][0]['id'];
        $page_token = $pages_data['data'][0]['access_token'];   
        $platform = 'facebook';

        // 3. Save to Database
        $stmt = $pdo->prepare("INSERT INTO linked_socials (user_id, platform, platform_id, access_token) VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE access_token = ?");
        $stmt->execute([$_SESSION['user_id'], $platform, $page_id, $page_token, $page_token]);

        header("Location: linked_accounts.php?success=1");
    } else {
        echo "No Facebook Pages found for this account.";
    }
} else {
    echo "Login failed or user cancelled.";
}   